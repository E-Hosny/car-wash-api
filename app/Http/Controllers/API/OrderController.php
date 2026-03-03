<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use App\Models\Service;
use App\Models\DailyTimeSlot;
use App\Models\HourSlotInstance;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\FirebaseNotificationService;
use App\Models\FcmToken;
use App\Services\WhatsAppService;
use App\Services\LocationValidationService;
use App\Services\OneSignalService;

class OrderController extends Controller
{
    // ✅ إنشاء طلب
 public function store(Request $request)
{
    $request->validate([
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'address' => 'nullable|string',
        'street' => 'nullable|string',
        'building' => 'nullable|string',
        'floor' => 'nullable|string',
        'apartment' => 'nullable|string',
        'scheduled_at' => 'nullable|date',
        'car_id' => 'required|exists:cars,id',
        'services' => 'required|array',
        'services.*' => 'exists:services,id',
        'use_package' => 'nullable|boolean',
    ]);

    // التحقق الجغرافي للطلبات التي تستخدم الباقة (لأنها لا تمر بـ createPaymentIntent)
    if ($request->use_package) {
        $locationValidation = LocationValidationService::validateLocation(
            (float) $request->latitude,
            (float) $request->longitude
        );

        if (!$locationValidation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $locationValidation['message']
            ], 400);
        }
    }
    // للطلبات العادية، التحقق تم في createPaymentIntent قبل الدفع

    // نتأكد إن السيارة دي تخص المستخدم الحالي
    $car = Car::where('id', $request->car_id)
              ->where('user_id', auth()->id())
              ->first();

    if (! $car) {
        return response()->json(['message' => 'Car not found or does not belong to you'], 403);
    }

    $user = auth()->user();
    $total = 0;
    $servicesUsed = [];
    $userPackage = null;

    // Check if user wants to use package
    if ($request->use_package) {
        $userPackage = \App\Models\UserPackage::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>=', now()->toDateString())
            ->with('packageServices')
            ->first();

        if (!$userPackage) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد باقة نشطة'
            ], 400);
        }

        // Check if user has remaining services
        if (!$userPackage->hasRemainingServices()) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد خدمات متبقية في الباقة'
            ], 400);
        }

        // Verify and use services from package
        $requestedServices = $request->services;
        foreach ($requestedServices as $serviceId) {
            if (!$userPackage->hasServiceAvailable($serviceId, 1)) {
                $service = Service::find($serviceId);
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد كمية متبقية للخدمة: ' . ($service ? $service->name : 'غير معروف')
                ], 400);
            }
        }

        // Use services from package
        foreach ($requestedServices as $serviceId) {
            try {
                $userPackageService = $userPackage->useService($serviceId, 1);
                $servicesUsed[] = [
                    'service_id' => $serviceId,
                    'service_name' => $userPackageService->service->name ?? '',
                    'quantity_used' => 1,
                ];
            } catch (\Exception $e) {
                // Rollback if any service fails
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء استخدام الخدمة: ' . $e->getMessage()
                ], 400);
            }
        }

        $total = 0; // Free when using package
    } else {
        // Calculate total from service prices
        $total = Service::whereIn('id', $request->services)->sum('price');
    }

    // التحقق من توفر slot إذا كان هناك scheduled_at
    if ($request->scheduled_at) {
        $scheduledDate = Carbon::parse($request->scheduled_at)->toDateString();
        $scheduledHour = Carbon::parse($request->scheduled_at)->hour;
        $maxSlotsPerHour = (int) Setting::getValue('max_slots_per_hour', 2);
        
        // التحقق من أن الساعة متاحة (يوجد slot متاح واحد على الأقل)
        $isUnavailable = HourSlotInstance::areAllSlotsUnavailable($scheduledDate, $scheduledHour, $maxSlotsPerHour);
        
        if ($isUnavailable) {
            return response()->json([
                'success' => false,
                'message' => 'الساعة المحددة غير متاحة للحجز'
            ], 400);
        }
        
        // البحث عن slot متاح وربطه بالطلب
        $slots = HourSlotInstance::getSlotsForHour($scheduledDate, $scheduledHour, $maxSlotsPerHour);
        $availableSlotIndex = null;
        
        foreach ($slots as $index => $slot) {
            if ($slot['status'] === 'available' && !$slot['order_id']) {
                $availableSlotIndex = $index;
                break;
            }
        }
        
        if (!$availableSlotIndex) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد slots متاحة في الساعة المحددة'
            ], 400);
        }
    }

    // إنشاء الطلب مع حفظ total
    $order = Order::create([
        'customer_id' => auth()->id(),
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
        'address' => $request->address,
        'street' => $request->street,
        'building' => $request->building,
        'floor' => $request->floor,
        'apartment' => $request->apartment,
        'scheduled_at' => $request->scheduled_at,
        'car_id' => $car->id,
        'total' => $total,
        'payment_status' => $request->use_package ? 'paid' : 'pending',
    ]);

    $order->services()->attach($request->services);
    
    // ربط الطلب مع slot محدد
    if ($request->scheduled_at && isset($availableSlotIndex)) {
        $scheduledDate = Carbon::parse($request->scheduled_at)->toDateString();
        $scheduledHour = Carbon::parse($request->scheduled_at)->hour;
        HourSlotInstance::bookSlot($scheduledDate, $scheduledHour, $availableSlotIndex, $order->id);

        // توجيه تلقائي للعامل المرتبط بهذا الـ slot (من إعدادات الأدمن)
        $slotWorkerIds = Setting::getValue('slot_worker_ids', []);
        if (is_array($slotWorkerIds)) {
            $slotKey = (string) $availableSlotIndex;
            $workerId = isset($slotWorkerIds[$slotKey]) ? (int) $slotWorkerIds[$slotKey] : null;
            if ($workerId) {
                $worker = User::find($workerId);
                if ($worker && $worker->role === 'worker') {
                    $order->assigned_to = $workerId;
                    $order->save();
                    $tokens = $worker->fcmTokens->pluck('token')->toArray();
                    $firebase = new FirebaseNotificationService();
                    foreach ($tokens as $token) {
                        $firebase->sendToToken($token, '🧽 New assignment', 'A new order has been assigned to you');
                    }
                    try {
                        if ($worker->phone) {
                            $workerPhone = trim($worker->phone);
                            if (!str_starts_with($workerPhone, '+')) {
                                $workerPhone = '+' . $workerPhone;
                            }
                            $components = [];
                            app(WhatsAppService::class)->sendTemplate($workerPhone, $components);
                        }
                    } catch (\Throwable $e) {
                        Log::error('Failed to send WhatsApp to worker after auto-assign by slot', [
                            'error' => $e->getMessage(),
                            'worker_id' => $worker->id,
                            'order_id' => $order->id,
                        ]);
                    }
                }
            }
        }
    }
    // 🟢 إرسال رسالة واتساب بقالب Meta إلى مستلمين محددين (يدعم عدة أرقام مستقبلًا)
    try {
        $recipientsCsv = (string) config('services.whatsapp.notify_recipients', '');
        $recipients = array_filter(array_map('trim', explode(',', $recipientsCsv)));
        if (!empty($recipients)) {
            // إذا كان القالب لا يحتوي متغيرات، اترك components فارغة
            $components = [];
            // مثال تمرير متغيرات إن احتجت لاحقًا:
            // $components = [[
            //     'type' => 'body',
            //     'parameters' => [
            //         ['type' => 'text', 'text' => (string) $order->id],
            //         ['type' => 'text', 'text' => number_format($total, 2)],
            //     ],
            // ]];
            app(WhatsAppService::class)->sendTemplateToMany($recipients, $components);
        }
    } catch (\Throwable $e) {
        \Log::error('Failed to send WhatsApp template after order create', ['error' => $e->getMessage()]);
    }


    // If using package, create package order and save services used
    if ($request->use_package && $userPackage && !empty($servicesUsed)) {
        \App\Models\PackageOrder::create([
            'user_package_id' => $userPackage->id,
            'order_id' => $order->id,
            'services_used' => $servicesUsed,
        ]);
    }

    // 🟢 إرسال إشعار إلى جميع مزودي الخدمة
   // بعد Order::create()
    $tokens = FcmToken::whereHas('user', fn($q) => $q->where('role', 'provider'))->pluck('token')->toArray();
    \Log::info('FCM Provider Tokens:', $tokens);

    $firebase = new FirebaseNotificationService();
    foreach ($tokens as $token) {
        $response = $firebase->sendToToken($token, '🚘 New Order', 'A new car wash has been requested, open the app');
        \Log::info('FCM Notification Response', ['token' => $token, 'response' => $response]);
    }

    $responseData = [
        'message' => 'Order created successfully',
        'id' => $order->id,
        'order' => $order->load('services', 'car')
    ];

    if ($request->use_package && $userPackage) {
        // Get remaining services
        $remainingServices = $userPackage->getRemainingServices();
        $responseData['package_info'] = [
            'services_used' => $servicesUsed,
            'remaining_services' => $remainingServices->map(function($item) {
                return [
                    'service_id' => $item['service_id'],
                    'service_name' => $item['service']['name'] ?? '',
                    'remaining_quantity' => $item['remaining_quantity'],
                    'total_quantity' => $item['total_quantity'],
                ];
            })
        ];
    }

    return response()->json($responseData);
}

    public function myOrders(Request $request)
{
    $userId = auth()->id();
    Log::info("Fetching orders for user ID: $userId");

    $orders = Order::where('customer_id', $userId)
        ->with(['services', 'car.brand', 'car.model', 'car.year', 'orderCars.car.brand', 'orderCars.car.model', 'orderCars.car.year', 'orderCars.services']) // ✅ تأكد من تضمين العلاقات هنا
        ->latest()
        ->get();
    
    Log::info("Found {$orders->count()} orders for user ID: $userId");

    // Return full service objects (name + name_ar) so the app can display based on user's current language
    $getServicesForDisplay = function ($services) {
        return $services->map(function ($s) {
            return ['name' => $s->name ?? '', 'name_ar' => $s->name_ar ?? null];
        })->values();
    };

    // Add multi-car information to each order
    $orders->each(function ($order) use ($getServicesForDisplay) {
        if ($order->orderCars->count() > 0) {
            $order->is_multi_car = $order->orderCars->count() > 1;
            $order->cars_count = $order->orderCars->count();
            $order->all_cars = $order->orderCars->map(function ($orderCar) use ($getServicesForDisplay) {
                // التحقق من وجود car و brand و model و year
                if (!$orderCar->car || !$orderCar->car->brand || !$orderCar->car->model) {
                    return [
                        'id' => $orderCar->car_id ?? null,
                        'brand' => 'Unknown',
                        'model' => 'Unknown',
                        'year' => $orderCar->car && $orderCar->car->year ? $orderCar->car->year->year : null,
                        'services' => $getServicesForDisplay($orderCar->services),
                        'subtotal' => $orderCar->subtotal ?? 0,
                        'points_used' => $orderCar->points_used ?? 0,
                    ];
                }
                return [
                    'id' => $orderCar->car->id,
                    'brand' => $orderCar->car->brand->name,
                    'model' => $orderCar->car->model->name,
                    'year' => $orderCar->car->year ? $orderCar->car->year->year : null,
                    'services' => $getServicesForDisplay($orderCar->services),
                    'subtotal' => $orderCar->subtotal,
                    'points_used' => $orderCar->points_used,
                ];
            });
        } else {
            $order->is_multi_car = false;
            $order->cars_count = 1;
            // التحقق من وجود car قبل الوصول إليه
            if ($order->car && $order->car->brand && $order->car->model) {
                $order->all_cars = [[
                    'id' => $order->car->id,
                    'brand' => $order->car->brand->name,
                    'model' => $order->car->model->name,
                    'year' => $order->car->year ? $order->car->year->year : null,
                    'services' => $getServicesForDisplay($order->services),
                    'subtotal' => $order->total,
                    'points_used' => 0,
                ]];
            } else {
                // إذا لم تكن هناك car، إرجاع بيانات افتراضية
                $order->all_cars = [[
                    'id' => $order->car_id ?? null,
                    'brand' => 'Unknown',
                    'model' => 'Unknown',
                    'year' => null,
                    'services' => $getServicesForDisplay($order->services),
                    'subtotal' => $order->total ?? 0,
                    'points_used' => 0,
                ]];
            }
        }
    });

    return response()->json($orders);
}
    

    

    // ✅ عرض طلب مفرد
    public function show($id)
    {
        $order = Order::with(['services', 'customer', 'provider', 'orderCars.car.brand', 'orderCars.car.model', 'orderCars.car.year', 'orderCars.services'])->findOrFail($id);
        
        // Add multi-car information
        if ($order->orderCars->count() > 0) {
            $order->is_multi_car = $order->orderCars->count() > 1;
            $order->cars_count = $order->orderCars->count();
            $order->all_cars = $order->orderCars->map(function ($orderCar) {
                // التحقق من وجود car و brand و model و year
                if (!$orderCar->car || !$orderCar->car->brand || !$orderCar->car->model) {
                    return [
                        'id' => $orderCar->car_id ?? null,
                        'brand' => 'Unknown',
                        'model' => 'Unknown',
                        'year' => $orderCar->car && $orderCar->car->year ? $orderCar->car->year->year : null,
                        'services' => $orderCar->services->pluck('name'),
                        'subtotal' => $orderCar->subtotal ?? 0,
                        'points_used' => $orderCar->points_used ?? 0,
                    ];
                }
                return [
                    'id' => $orderCar->car->id,
                    'brand' => $orderCar->car->brand->name,
                    'model' => $orderCar->car->model->name,
                    'year' => $orderCar->car->year ? $orderCar->car->year->year : null,
                    'services' => $orderCar->services->pluck('name'),
                    'subtotal' => $orderCar->subtotal,
                    'points_used' => $orderCar->points_used,
                ];
            });
        } else {
            $order->is_multi_car = false;
            $order->cars_count = 1;
            // التحقق من وجود car قبل الوصول إليه
            if ($order->car && $order->car->brand && $order->car->model) {
                $order->all_cars = [[
                    'id' => $order->car->id,
                    'brand' => $order->car->brand->name,
                    'model' => $order->car->model->name,
                    'year' => $order->car->year ? $order->car->year->year : null,
                    'services' => $order->services->pluck('name'),
                    'subtotal' => $order->total,
                    'points_used' => 0,
                ]];
            } else {
                // إذا لم تكن هناك car، إرجاع بيانات افتراضية
                $order->all_cars = [[
                    'id' => $order->car_id ?? null,
                    'brand' => 'Unknown',
                    'model' => 'Unknown',
                    'year' => null,
                    'services' => $order->services->pluck('name'),
                    'subtotal' => $order->total ?? 0,
                    'points_used' => 0,
                ]];
            }
        }
        
        return response()->json($order);
    }

    // ✅ عرض الطلبات اللي لسه محددناش لها مزود خدمة
      public function availableOrders()
{
    if (auth()->user()->role !== 'provider' && auth()->user()->role !== 'worker') {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    if(auth()->user()->role=='provider'){
         $orders = Order::where('status', 'pending')
        ->with(['services', 'customer', 'car.brand', 'car.model','assignedUser', 'orderCars.car.brand', 'orderCars.car.model', 'orderCars.services']) // ✅ أضف العلاقات هنا
        ->get();
    }else{
        $orders = Order::where('assigned_to', auth()->id())
         ->where('status','pending')
        ->with(['services', 'customer', 'car.brand', 'car.model', 'orderCars.car.brand', 'orderCars.car.model', 'orderCars.services'])
        ->get();

    }

    // Add multi-car information to each order
    $orders->each(function ($order) {
        if ($order->orderCars->count() > 0) {
            $order->is_multi_car = $order->orderCars->count() > 1;
            $order->cars_count = $order->orderCars->count();
            $order->all_cars = $order->orderCars->map(function ($orderCar) {
                // التحقق من وجود car و brand و model
                if (!$orderCar->car || !$orderCar->car->brand || !$orderCar->car->model) {
                    return [
                        'id' => $orderCar->car_id ?? null,
                        'brand' => 'Unknown',
                        'model' => 'Unknown',
                        'services' => $orderCar->services->pluck('name'),
                        'subtotal' => $orderCar->subtotal ?? 0,
                    ];
                }
                return [
                    'id' => $orderCar->car->id,
                    'brand' => $orderCar->car->brand->name,
                    'model' => $orderCar->car->model->name,
                    'services' => $orderCar->services->pluck('name'),
                    'subtotal' => $orderCar->subtotal,
                ];
            });
        } else {
            $order->is_multi_car = false;
            $order->cars_count = 1;
            // التحقق من وجود car قبل الوصول إليه
            if ($order->car && $order->car->brand && $order->car->model) {
                $order->all_cars = [[
                    'id' => $order->car->id,
                    'brand' => $order->car->brand->name,
                    'model' => $order->car->model->name,
                    'services' => $order->services->pluck('name'),
                    'subtotal' => $order->total,
                ]];
            } else {
                // إذا لم تكن هناك car، إرجاع بيانات افتراضية
                $order->all_cars = [[
                    'id' => $order->car_id ?? null,
                    'brand' => 'Unknown',
                    'model' => 'Unknown',
                    'services' => $order->services->pluck('name'),
                    'subtotal' => $order->total ?? 0,
                ]];
            }
        }
    });
   

    return response()->json($orders);
}

public function assignToWorker(Request $request, $id)
{
    $request->validate([
        'worker_id' => 'required|exists:users,id',
    ]);

    $order = Order::findOrFail($id);

    if (auth()->user()->role !== 'provider') {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    $order->assigned_to = $request->worker_id;
    $order->save();

    $worker = User::find($request->worker_id);
    $tokens = $worker->fcmTokens->pluck('token')->toArray();

    $firebase = new FirebaseNotificationService();
    foreach ($tokens as $token) {
        $firebase->sendToToken($token, '🧽 New assignment', 'A new order has been assigned to you');
    }

    // 🟢 إرسال إشعار واتساب للعامل عند توجيه الطلب إليه
    try {
        if ($worker->phone) {
            $workerPhone = trim($worker->phone);
            // Ensure E.164 format (add + if not present)
            if (!str_starts_with($workerPhone, '+')) {
                $workerPhone = '+' . $workerPhone;
            }
            // Send WhatsApp notification with same template as order creation
            $components = []; // Same empty components as in store/storeMultiCar
            app(WhatsAppService::class)->sendTemplate($workerPhone, $components);
        }
    } catch (\Throwable $e) {
        \Log::error('Failed to send WhatsApp notification to worker after assignment', [
            'error' => $e->getMessage(),
            'worker_id' => $worker->id,
            'order_id' => $order->id
        ]);
    }

    return response()->json(['message' => 'Order assigned to worker successfully']);
}


        public function completedOrders()
        {
            if (auth()->user()->role !== 'provider'  && auth()->user()->role !== 'worker' ) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            if(auth()->user()->role=='provider'){
                $orders = Order::where('status', 'completed')
                ->with(['services', 'customer', 'car.brand', 'car.model','assignedUser', 'orderCars.car.brand', 'orderCars.car.model', 'orderCars.services'])
                ->get();
            }else{
                $orders = Order::where('assigned_to', auth()->id())
                ->where('status','completed')
                ->with(['services', 'customer', 'car.brand', 'car.model', 'orderCars.car.brand', 'orderCars.car.model', 'orderCars.services'])
                ->get();
            }

            // Add multi-car information to each order
            $orders->each(function ($order) {
                if ($order->orderCars->count() > 0) {
                    $order->is_multi_car = $order->orderCars->count() > 1;
                    $order->cars_count = $order->orderCars->count();
                    $order->all_cars = $order->orderCars->map(function ($orderCar) {
                        // التحقق من وجود car و brand و model
                        if (!$orderCar->car || !$orderCar->car->brand || !$orderCar->car->model) {
                            return [
                                'id' => $orderCar->car_id ?? null,
                                'brand' => 'Unknown',
                                'model' => 'Unknown',
                                'services' => $orderCar->services->pluck('name'),
                                'subtotal' => $orderCar->subtotal ?? 0,
                            ];
                        }
                        return [
                            'id' => $orderCar->car->id,
                            'brand' => $orderCar->car->brand->name,
                            'model' => $orderCar->car->model->name,
                            'services' => $orderCar->services->pluck('name'),
                            'subtotal' => $orderCar->subtotal,
                        ];
                    });
                } else {
                    $order->is_multi_car = false;
                    $order->cars_count = 1;
                    // التحقق من وجود car قبل الوصول إليه
                    if ($order->car && $order->car->brand && $order->car->model) {
                        $order->all_cars = [[
                            'id' => $order->car->id,
                            'brand' => $order->car->brand->name,
                            'model' => $order->car->model->name,
                            'services' => $order->services->pluck('name'),
                            'subtotal' => $order->total,
                        ]];
                    } else {
                        // إذا لم تكن هناك car، إرجاع بيانات افتراضية
                        $order->all_cars = [[
                            'id' => $order->car_id ?? null,
                            'brand' => 'Unknown',
                            'model' => 'Unknown',
                            'services' => $order->services->pluck('name'),
                            'subtotal' => $order->total ?? 0,
                        ]];
                    }
                }
            });

            return response()->json($orders);
        }
        public function acceptedOrders()
        {
            if (auth()->user()->role !== 'provider'  && auth()->user()->role !== 'worker' ) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

         if(auth()->user()->role=='provider'){
            $orders = Order::where('status', 'accepted')
            ->with(['services', 'customer', 'car.brand', 'car.model','assignedUser', 'orderCars.car.brand', 'orderCars.car.model', 'orderCars.services']) // ✅ أضف العلاقات هنا
            ->get();
        }else{
            $orders = Order::where('assigned_to', auth()->id())
            ->where('status','accepted')
            ->with(['services', 'customer', 'car.brand', 'car.model', 'orderCars.car.brand', 'orderCars.car.model', 'orderCars.services'])
            ->get();

        }

        // Add multi-car information to each order
        $orders->each(function ($order) {
            if ($order->orderCars->count() > 0) {
                $order->is_multi_car = $order->orderCars->count() > 1;
                $order->cars_count = $order->orderCars->count();
                $order->all_cars = $order->orderCars->map(function ($orderCar) {
                    // التحقق من وجود car و brand و model
                    if (!$orderCar->car || !$orderCar->car->brand || !$orderCar->car->model) {
                        return [
                            'id' => $orderCar->car_id ?? null,
                            'brand' => 'Unknown',
                            'model' => 'Unknown',
                            'services' => $orderCar->services->pluck('name'),
                            'subtotal' => $orderCar->subtotal ?? 0,
                        ];
                    }
                    return [
                        'id' => $orderCar->car->id,
                        'brand' => $orderCar->car->brand->name,
                        'model' => $orderCar->car->model->name,
                        'services' => $orderCar->services->pluck('name'),
                        'subtotal' => $orderCar->subtotal,
                    ];
                });
            } else {
                $order->is_multi_car = false;
                $order->cars_count = 1;
                // التحقق من وجود car قبل الوصول إليه
                if ($order->car && $order->car->brand && $order->car->model) {
                    $order->all_cars = [[
                        'id' => $order->car->id,
                        'brand' => $order->car->brand->name,
                        'model' => $order->car->model->name,
                        'services' => $order->services->pluck('name'),
                        'subtotal' => $order->total,
                    ]];
                } else {
                    // إذا لم تكن هناك car، إرجاع بيانات افتراضية
                    $order->all_cars = [[
                        'id' => $order->car_id ?? null,
                        'brand' => 'Unknown',
                        'model' => 'Unknown',
                        'services' => $order->services->pluck('name'),
                        'subtotal' => $order->total ?? 0,
                    ]];
                }
            }
        });

            return response()->json($orders);
        }
        public function inProgressOrders()
        {
            if (auth()->user()->role !== 'provider'  && auth()->user()->role !== 'worker' ) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            if(auth()->user()->role=='provider'){
             $orders = Order::where('status', 'in_progress')
                ->with(['services', 'customer', 'car.brand', 'car.model','assignedUser', 'orderCars.car.brand', 'orderCars.car.model', 'orderCars.services'])
                ->get();
            }else{
                $orders = Order::where('assigned_to', auth()->id())
                ->where('status','in_progress')
                ->with(['services', 'customer', 'car.brand', 'car.model', 'orderCars.car.brand', 'orderCars.car.model', 'orderCars.services'])
                ->get();

            }

            // Add multi-car information to each order
            $orders->each(function ($order) {
                if ($order->orderCars->count() > 0) {
                    $order->is_multi_car = $order->orderCars->count() > 1;
                    $order->cars_count = $order->orderCars->count();
                    $order->all_cars = $order->orderCars->map(function ($orderCar) {
                        // التحقق من وجود car و brand و model
                        if (!$orderCar->car || !$orderCar->car->brand || !$orderCar->car->model) {
                            return [
                                'id' => $orderCar->car_id ?? null,
                                'brand' => 'Unknown',
                                'model' => 'Unknown',
                                'services' => $orderCar->services->pluck('name'),
                                'subtotal' => $orderCar->subtotal ?? 0,
                            ];
                        }
                        return [
                            'id' => $orderCar->car->id,
                            'brand' => $orderCar->car->brand->name,
                            'model' => $orderCar->car->model->name,
                            'services' => $orderCar->services->pluck('name'),
                            'subtotal' => $orderCar->subtotal,
                        ];
                    });
                } else {
                    $order->is_multi_car = false;
                    $order->cars_count = 1;
                    // التحقق من وجود car قبل الوصول إليه
                    if ($order->car && $order->car->brand && $order->car->model) {
                        $order->all_cars = [[
                            'id' => $order->car->id,
                            'brand' => $order->car->brand->name,
                            'model' => $order->car->model->name,
                            'services' => $order->services->pluck('name'),
                            'subtotal' => $order->total,
                        ]];
                    } else {
                        // إذا لم تكن هناك car، إرجاع بيانات افتراضية
                        $order->all_cars = [[
                            'id' => $order->car_id ?? null,
                            'brand' => 'Unknown',
                            'model' => 'Unknown',
                            'services' => $order->services->pluck('name'),
                            'subtotal' => $order->total ?? 0,
                        ]];
                    }
                }
            });

            return response()->json($orders);
        }
public function accept($id)
{
    $order = Order::findOrFail($id);

    if ($order->status !== 'pending') {
        return response()->json(['message' => 'This order cannot be accepted.'], 400);
    }

    $order->provider_id = auth()->id();
    $order->status = 'accepted';
    $order->save();

    return response()->json(['message' => 'Order accepted.', 'order' => $order]);
}
public function updateStatus(Request $request, $id)
{
    $order = Order::findOrFail($id);

    $request->validate([
        'status' => 'required|in:in_progress,completed,cancelled'
    ]);

    // if ($order->provider_id !== auth()->id()) {
    //     return response()->json(['message' => 'غير مسموح.'], 403);
    // }

    // حفظ الحالة السابقة
    $previousStatus = $order->status;
    $newStatus = $request->status;

    // تحديث حالة الطلب
    $order->status = $newStatus;
    $order->save();

    // تسجيل تغيير الحالة في order_status_history
    OrderStatusHistory::create([
        'order_id' => $order->id,
        'previous_status' => $previousStatus,
        'new_status' => $newStatus,
        'changed_by' => auth()->id(),
        'notes' => $request->notes ?? null,
        'ip_address' => $request->ip(),
    ]);

    // Send OneSignal notification when order status is changed to completed
    if ($request->status === 'completed') {
        try {
            $order->load('customer');
            if ($order->customer) {
                app(OneSignalService::class)->sendOrderCompletionRatingNotification(
                    $order->customer_id,
                    $order->id,
                    $order->customer->name
                );
                Log::info("OneSignal rating notification sent for order {$order->id} completion");
            }
        } catch (\Exception $e) {
            // Log error but don't fail the status update
            Log::error('Failed to send OneSignal rating notification for order completion', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    return response()->json(['message' => 'Status updated successfully.', 'order' => $order]);
}

    public function saveLocation(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $order = Order::find($request->order_id);
        $order->lat = $request->lat;
        $order->lng = $request->lng;
        $order->save();

        return response()->json(['message' => 'Location saved']);
    }

    public function getLocation($id)
    {
        $order = Order::findOrFail($id);
        return response()->json([
            'lat' => $order->lat,
            'lng' => $order->lng,
        ]);
    }

    // ✅ إنشاء طلب متعدد السيارات
    public function storeMultiCar(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'nullable|string',
            'street' => 'nullable|string',
            'building' => 'nullable|string',
            'floor' => 'nullable|string',
            'apartment' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'cars' => 'required|array|min:1',
            'cars.*.car_id' => 'required|exists:cars,id',
            'cars.*.services' => 'required|array|min:1',
            'cars.*.services.*' => 'exists:services,id',
            'use_package' => 'nullable|boolean',
        ]);

        // التحقق الجغرافي للطلبات التي تستخدم الباقة (لأنها لا تمر بـ createPaymentIntent)
        if ($request->use_package) {
            $locationValidation = LocationValidationService::validateLocation(
                (float) $request->latitude,
                (float) $request->longitude
            );

            if (!$locationValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $locationValidation['message']
                ], 400);
            }
        }
        // للطلبات العادية، التحقق تم في createPaymentIntent قبل الدفع

        $user = auth()->user();
        $total = 0;
        $servicesUsed = [];
        $userPackage = null;
        $orderCars = [];
        $pointsUsed = 0;

        // Check if user wants to use package
        if ($request->use_package) {
            $userPackage = \App\Models\UserPackage::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '>=', now()->toDateString())
                ->with('packageServices')
                ->first();

            if (!$userPackage) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد باقة نشطة'
                ], 400);
            }

            if (!$userPackage->hasRemainingServices()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد خدمات متبقية في الباقة'
                ], 400);
            }

            // Collect all requested services
            $allRequestedServices = [];
            foreach ($request->cars as $carData) {
                foreach ($carData['services'] as $serviceId) {
                    $allRequestedServices[] = $serviceId;
                }
            }

            // Count service occurrences
            $serviceCounts = array_count_values($allRequestedServices);

            // Verify and use services from package
            foreach ($serviceCounts as $serviceId => $count) {
                if (!$userPackage->hasServiceAvailable($serviceId, $count)) {
                    $service = Service::find($serviceId);
                    return response()->json([
                        'success' => false,
                        'message' => 'لا توجد كمية كافية للخدمة: ' . ($service ? $service->name : 'غير معروف') . ' (مطلوب: ' . $count . ')'
                    ], 400);
                }
            }
        }

        // Verify all cars belong to user and calculate totals
        foreach ($request->cars as $carData) {
            $car = Car::where('id', $carData['car_id'])
                      ->where('user_id', $user->id)
                      ->first();

            if (!$car) {
                return response()->json([
                    'success' => false,
                    'message' => "Car ID {$carData['car_id']} not found or does not belong to you"
                ], 403);
            }

            // Calculate total for this car
            $carTotal = 0;
            $carServicesUsed = [];

            if ($request->use_package) {
                // Use services from package for this car
                foreach ($carData['services'] as $serviceId) {
                    try {
                        $userPackageService = $userPackage->useService($serviceId, 1);
                        $carServicesUsed[] = [
                            'service_id' => $serviceId,
                            'service_name' => $userPackageService->service->name ?? '',
                            'quantity_used' => 1,
                        ];
                        $servicesUsed[] = [
                            'service_id' => $serviceId,
                            'service_name' => $userPackageService->service->name ?? '',
                            'quantity_used' => 1,
                        ];
                    } catch (\Exception $e) {
                        return response()->json([
                            'success' => false,
                            'message' => 'حدث خطأ أثناء استخدام الخدمة: ' . $e->getMessage()
                        ], 400);
                    }
                }
                $carTotal = 0; // Free when using package
            } else {
                $carTotal = Service::whereIn('id', $carData['services'])->sum('price');
                $total += $carTotal;
            }

            $orderCars[] = [
                'car_id' => $car->id,
                'services' => $carData['services'],
                'subtotal' => $carTotal,
                'services_used' => $carServicesUsed,
            ];
        }

        // Create single order for all cars
        $order = Order::create([
            'customer_id' => $user->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'street' => $request->street,
            'building' => $request->building,
            'floor' => $request->floor,
            'apartment' => $request->apartment,
            'scheduled_at' => $request->scheduled_at,
            'car_id' => $orderCars[0]['car_id'], // Use first car as primary
            'total' => $total,
            'payment_status' => $request->use_package ? 'paid' : 'pending',
        ]);

        // Create order_cars records for each car
        foreach ($orderCars as $carData) {
            $orderCar = \App\Models\OrderCar::create([
                'order_id' => $order->id,
                'car_id' => $carData['car_id'],
                'subtotal' => $carData['subtotal'],
                'points_used' => 0, // Keep for backward compatibility, but not used
            ]);

            // Attach services for this car
            $orderCar->services()->attach($carData['services']);
        }

        // If using package, create package order
        if ($request->use_package && $userPackage && !empty($servicesUsed)) {
            \App\Models\PackageOrder::create([
                'user_package_id' => $userPackage->id,
                'order_id' => $order->id,
                'services_used' => $servicesUsed,
            ]);
        }

        // Send notification to providers
        $this->sendOrderNotification([$order]);

        // 🟢 إرسال رسالة واتساب بقالب Meta إلى مستلمين محددين (يدعم عدة أرقام مستقبلًا)
        try {
            $recipientsCsv = (string) config('services.whatsapp.notify_recipients', '');
            $recipients = array_filter(array_map('trim', explode(',', $recipientsCsv)));
            if (!empty($recipients)) {
                $components = [];
                app(\App\Services\WhatsAppService::class)->sendTemplateToMany($recipients, $components);
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to send WhatsApp template after multi-car order create', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Multi-car order created successfully',
            'order' => $order->load('orderCars.car', 'orderCars.services'),
            'total_cars' => count($orderCars),
            'total_amount' => $total,
            'points_used' => $pointsUsed,
        ], 201);
    }

    private function sendOrderNotification($orders)
    {
        try {
            $fcmTokens = FcmToken::where('user_id', '!=', auth()->id())
                ->whereHas('user', function ($query) {
                    $query->where('role', 'provider');
                })
                ->pluck('token')
                ->toArray();

            if (!empty($fcmTokens)) {
                $firebaseService = new FirebaseNotificationService();
                $firebaseService->sendNotification(
                    $fcmTokens,
                    'New Multi-Car Order',
                    'You have a new multi-car order to review',
                    [
                        'type' => 'new_order',
                        'order_count' => count($orders),
                    ]
                );
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send notification: ' . $e->getMessage());
        }
    }

    // ✅ الحصول على المواعيد المحجوزة لليوم الحالي
    public function getBookedTimeSlots(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        
        // عدد المواعيد المتاحة لكل ساعة من الإعدادات
        $maxSlotsPerHour = (int) Setting::getValue('max_slots_per_hour', 2);
        
        Log::info('Fetching booked time slots for date: ' . $date . ' (max slots per hour: ' . $maxSlotsPerHour . ')');
        
        // الحصول على الطلبات المحجوزة لليوم المحدد
        $bookedOrders = Order::whereDate('scheduled_at', $date)
            ->whereIn('status', ['pending', 'accepted', 'in_progress'])
            ->get(['scheduled_at', 'status']);
        
        Log::info('Found ' . $bookedOrders->count() . ' booked orders');
        
        // عد الطلبات لكل ساعة بدلاً من إضافة الساعة مباشرة
        $hourlyBookings = [];
        foreach ($bookedOrders as $order) {
            $hour = Carbon::parse($order->scheduled_at)->hour;
            $hourlyBookings[$hour] = ($hourlyBookings[$hour] ?? 0) + 1;
            Log::info("Order at {$order->scheduled_at} (status: {$order->status}) -> hour: {$hour}");
        }
        
        // الساعات المحجوزة بالكامل هي التي وصلت للحد الأقصى
        $bookedHours = [];
        foreach ($hourlyBookings as $hour => $count) {
            if ($count >= $maxSlotsPerHour) {
                $bookedHours[] = $hour;
                Log::info("Hour {$hour} is fully booked ({$count}/{$maxSlotsPerHour})");
            } else {
                Log::info("Hour {$hour} has {$count}/{$maxSlotsPerHour} bookings (still available)");
            }
        }
        
        // استخدام النظام الجديد: التحقق من الساعات غير المتاحة بناءً على HourSlotInstance
        // الساعة غير متاحة فقط إذا كانت جميع الـ slots إما محجوزة أو مقفلة
        $allHours = range(10, 23);
        $unavailableHours = [];
        $availableHours = [];
        
        foreach ($allHours as $hour) {
            // التحقق من حالة الساعة بناءً على جميع الـ slots
            $isUnavailable = HourSlotInstance::areAllSlotsUnavailable($date, $hour, $maxSlotsPerHour);
            
            if ($isUnavailable) {
                $unavailableHours[] = $hour;
                Log::info("Hour {$hour} is unavailable: all slots are either booked or disabled");
            } else {
                $availableHours[] = $hour;
                $bookedCount = HourSlotInstance::getBookedSlotsCount($date, $hour, $maxSlotsPerHour);
                $availableCount = HourSlotInstance::getAvailableSlotsCount($date, $hour, $maxSlotsPerHour);
                Log::info("Hour {$hour} is available: {$bookedCount} booked, {$availableCount} available out of {$maxSlotsPerHour}");
            }
        }
        
        // الساعات المحجوزة بالكامل (للمعلومات فقط)
        $fullyBookedHours = [];
        foreach ($allHours as $hour) {
            $bookedCount = HourSlotInstance::getBookedSlotsCount($date, $hour, $maxSlotsPerHour);
            if ($bookedCount >= $maxSlotsPerHour) {
                $fullyBookedHours[] = $hour;
            }
        }
        
        // التحقق من الوقت المتبقي قبل أول ساعة متاحة (اليوم الحالي فقط)
        $minimumAdvanceMinutes = (int) Setting::getValue('minimum_booking_advance_minutes', 60);
        
        if ($date === now()->toDateString()) {
            $currentTime = now();
            $currentHour = $currentTime->hour;
            $currentMinute = $currentTime->minute;
            
            Log::info("Checking auto-close logic: Current time = {$currentHour}:{$currentMinute}, Minimum advance = {$minimumAdvanceMinutes} minutes");
            Log::info("Available hours before auto-close check: " . json_encode($availableHours));
            
            // العثور على أول ساعة متاحة (يجب أن تكون أكبر من الساعة الحالية)
            $firstAvailableHour = null;
            foreach ($availableHours as $hour) {
                Log::info("Checking hour {$hour} against current hour {$currentHour}: " . ($hour > $currentHour ? 'YES' : 'NO'));
                if ($hour > $currentHour) {
                    $firstAvailableHour = $hour;
                    Log::info("Found first available hour: {$firstAvailableHour}");
                    break;
                }
            }
            
            // إذا وجدنا ساعة متاحة
            if ($firstAvailableHour !== null) {
                // حساب الوقت المتبقي حتى بداية أول ساعة متاحة
                $firstAvailableTime = Carbon::createFromDate($currentTime->year, $currentTime->month, $currentTime->day)
                    ->setTime($firstAvailableHour, 0, 0);
                
                // حساب الفرق بالدقائق بشكل مباشر
                // الوقت الحالي بالدقائق: (الساعة * 60) + الدقائق
                $currentMinutes = ($currentTime->hour * 60) + $currentTime->minute;
                // الساعة المستقبلية بالدقائق: (الساعة * 60)
                $targetMinutes = $firstAvailableHour * 60;
                // الفرق بالدقائق
                $minutesUntilFirstHour = $targetMinutes - $currentMinutes;
                
                Log::info("Current time: {$currentTime->format('H:i:s')} ({$currentMinutes} minutes), First available hour: {$firstAvailableHour}:00 ({$targetMinutes} minutes), Minutes until first hour: {$minutesUntilFirstHour}, Minimum required: {$minimumAdvanceMinutes}");
                
                // إذا كان الوقت المتبقي أقل من الحد الأدنى المحدد
                if ($minutesUntilFirstHour < $minimumAdvanceMinutes) {
                    Log::info("Auto-closing hour {$firstAvailableHour}: only {$minutesUntilFirstHour} minutes remaining (minimum: {$minimumAdvanceMinutes})");
                    
                    // إغلاق جميع slots في هذه الساعة
                    for ($slotIndex = 1; $slotIndex <= $maxSlotsPerHour; $slotIndex++) {
                        HourSlotInstance::setSlotStatus($date, $firstAvailableHour, $slotIndex, 'disabled');
                    }
                    
                    // التحقق من أن الساعة أصبحت غير متاحة
                    $isNowUnavailable = HourSlotInstance::areAllSlotsUnavailable($date, $firstAvailableHour, $maxSlotsPerHour);
                    Log::info("After closing slots, hour {$firstAvailableHour} is now unavailable: " . ($isNowUnavailable ? 'YES' : 'NO'));
                    
                    // إعادة حساب الساعات المتاحة بعد الإغلاق
                    $availableHours = array_filter($availableHours, function($hour) use ($firstAvailableHour) {
                        return $hour != $firstAvailableHour;
                    });
                    $availableHours = array_values($availableHours); // إعادة ترقيم المصفوفة
                    
                    $unavailableHours[] = $firstAvailableHour;
                    $unavailableHours = array_unique($unavailableHours);
                    sort($unavailableHours);
                    
                    Log::info("Hour {$firstAvailableHour} has been auto-closed and removed from available hours. Updated available hours: " . json_encode($availableHours));
                } else {
                    Log::info("Hour {$firstAvailableHour} remains available: {$minutesUntilFirstHour} minutes >= {$minimumAdvanceMinutes} minutes");
                }
            } else {
                Log::info("No available hours found after current hour {$currentHour}");
            }
        }
        
        Log::info('Booked hours (fully booked): ' . json_encode($fullyBookedHours));
        Log::info('Unavailable hours (all slots unavailable): ' . json_encode($unavailableHours));
        Log::info('Available hours: ' . json_encode($availableHours));
        
        return response()->json([
            'success' => true,
            'date' => $date,
            'booked_hours' => $fullyBookedHours, // الساعات المحجوزة بالكامل فقط
            'unavailable_hours' => $unavailableHours, // الساعات التي جميع slots غير متاحة
            'available_hours' => $availableHours,
            'total_booked' => count($fullyBookedHours),
            'total_unavailable' => count($unavailableHours),
            'total_available' => count($availableHours),
            'hourly_bookings' => $hourlyBookings, // معلومات إضافية: عدد الطلبات لكل ساعة
            'max_slots_per_hour' => $maxSlotsPerHour // معلومات إضافية: الحد الأقصى لكل ساعة
        ]);
    }

    /**
     * التحقق من أن الموقع داخل حدود دبي (قبل إنشاء الطلب)
     */
    public function validateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $locationValidation = LocationValidationService::validateLocation(
            (float) $request->latitude,
            (float) $request->longitude
        );

        if (!$locationValidation['valid']) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => $locationValidation['message']
            ], 400);
        }

        return response()->json([
            'success' => true,
            'valid' => true,
            'message' => 'Location is within service area'
        ], 200);
    }
}
