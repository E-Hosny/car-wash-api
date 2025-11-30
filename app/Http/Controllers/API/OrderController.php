<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Order;
use App\Models\User;
use App\Models\Service;
use App\Models\DailyTimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\FirebaseNotificationService;
use App\Models\FcmToken;
use App\Services\WhatsAppService;

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

    // نتأكد إن السيارة دي تخص المستخدم الحالي
    $car = Car::where('id', $request->car_id)
              ->where('user_id', auth()->id())
              ->first();

    if (! $car) {
        return response()->json(['message' => 'Car not found or does not belong to you'], 403);
    }

    $user = auth()->user();
    $total = 0;
    $pointsUsed = 0;
    $userPackage = null;

    // Check if user wants to use package
    if ($request->use_package) {
        $userPackage = \App\Models\UserPackage::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>=', now()->toDateString())
            ->where('remaining_points', '>', 0)
            ->first();

        if (!$userPackage) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد باقة نشطة أو نقاط متبقية'
            ], 400);
        }

        // Calculate points needed for selected services
        $services = Service::with('servicePoint')->whereIn('id', $request->services)->get();
        $totalPointsNeeded = $services->sum(function($service) {
            return $service->servicePoint ? $service->servicePoint->points_required : 0;
        });

        if ($totalPointsNeeded > $userPackage->remaining_points) {
            return response()->json([
                'success' => false,
                'message' => 'النقاط المتبقية غير كافية للخدمات المطلوبة'
            ], 400);
        }

        $pointsUsed = $totalPointsNeeded;
        $total = 0; // Free when using package
    } else {
        // Calculate total from service prices
        $total = Service::whereIn('id', $request->services)->sum('price');
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


    // If using package, create package order and update remaining points
    if ($request->use_package && $userPackage) {
        \App\Models\PackageOrder::create([
            'user_package_id' => $userPackage->id,
            'order_id' => $order->id,
            'points_used' => $pointsUsed,
        ]);

        $userPackage->update([
            'remaining_points' => $userPackage->remaining_points - $pointsUsed
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
        $responseData['package_info'] = [
            'remaining_points' => $userPackage->remaining_points,
            'points_used' => $pointsUsed
        ];
    }

    return response()->json($responseData);
}

    public function myOrders()
{
    $userId = auth()->id();
    Log::info("Fetching orders for user ID: $userId");
    
    $orders = Order::where('customer_id', $userId)
        ->with(['services', 'car.brand', 'car.model', 'car.year', 'orderCars.car.brand', 'orderCars.car.model', 'orderCars.car.year', 'orderCars.services']) // ✅ تأكد من تضمين العلاقات هنا
        ->latest()
        ->get();
    
    Log::info("Found {$orders->count()} orders for user ID: $userId");

    // Add multi-car information to each order
    $orders->each(function ($order) {
        if ($order->orderCars->count() > 0) {
            $order->is_multi_car = $order->orderCars->count() > 1;
            $order->cars_count = $order->orderCars->count();
            $order->all_cars = $order->orderCars->map(function ($orderCar) {
                return [
                    'id' => $orderCar->car->id,
                    'brand' => $orderCar->car->brand->name,
                    'model' => $orderCar->car->model->name,
                    'year' => $orderCar->car->year->year,
                    'services' => $orderCar->services->pluck('name'),
                    'subtotal' => $orderCar->subtotal,
                    'points_used' => $orderCar->points_used,
                ];
            });
        } else {
            $order->is_multi_car = false;
            $order->cars_count = 1;
            // For orders without orderCars, use the main car and services
            $order->all_cars = [[
                'id' => $order->car->id,
                'brand' => $order->car->brand->name,
                'model' => $order->car->model->name,
                'year' => $order->car->year->year,
                'services' => $order->services->pluck('name'),
                'subtotal' => $order->total,
                'points_used' => 0,
            ]];
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
                return [
                    'id' => $orderCar->car->id,
                    'brand' => $orderCar->car->brand->name,
                    'model' => $orderCar->car->model->name,
                    'year' => $orderCar->car->year->year,
                    'services' => $orderCar->services->pluck('name'),
                    'subtotal' => $orderCar->subtotal,
                    'points_used' => $orderCar->points_used,
                ];
            });
        } else {
            $order->is_multi_car = false;
            $order->cars_count = 1;
            // For orders without orderCars, use the main car and services
            $order->all_cars = [[
                'id' => $order->car->id,
                'brand' => $order->car->brand->name,
                'model' => $order->car->model->name,
                'year' => $order->car->year->year,
                'services' => $order->services->pluck('name'),
                'subtotal' => $order->total,
                'points_used' => 0,
            ]];
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
            // For orders without orderCars, use the main car and services
            $order->all_cars = [[
                'id' => $order->car->id,
                'brand' => $order->car->brand->name,
                'model' => $order->car->model->name,
                'services' => $order->services->pluck('name'),
                'subtotal' => $order->total,
            ]];
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
                    // For orders without orderCars, use the main car and services
                    $order->all_cars = [[
                        'id' => $order->car->id,
                        'brand' => $order->car->brand->name,
                        'model' => $order->car->model->name,
                        'services' => $order->services->pluck('name'),
                        'subtotal' => $order->total,
                    ]];
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
                // For orders without orderCars, use the main car and services
                $order->all_cars = [[
                    'id' => $order->car->id,
                    'brand' => $order->car->brand->name,
                    'model' => $order->car->model->name,
                    'services' => $order->services->pluck('name'),
                    'subtotal' => $order->total,
                ]];
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
                    // For orders without orderCars, use the main car and services
                    $order->all_cars = [[
                        'id' => $order->car->id,
                        'brand' => $order->car->brand->name,
                        'model' => $order->car->model->name,
                        'services' => $order->services->pluck('name'),
                        'subtotal' => $order->total,
                    ]];
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

    $order->status = $request->status;
    $order->save();

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

        $user = auth()->user();
        $total = 0;
        $pointsUsed = 0;
        $userPackage = null;
        $orderCars = [];

        // Check if user wants to use package
        if ($request->use_package) {
            $userPackage = \App\Models\UserPackage::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '>=', now()->toDateString())
                ->where('remaining_points', '>', 0)
                ->first();

            if (!$userPackage) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد باقة نشطة أو نقاط متبقية'
                ], 400);
            }

            // Calculate total points needed for all cars
            $totalPointsNeeded = 0;
            foreach ($request->cars as $carData) {
                $services = Service::with('servicePoint')->whereIn('id', $carData['services'])->get();
                $totalPointsNeeded += $services->sum(function($service) {
                    return $service->servicePoint ? $service->servicePoint->points_required : 0;
                });
            }

            if ($totalPointsNeeded > $userPackage->remaining_points) {
                return response()->json([
                    'success' => false,
                    'message' => 'النقاط المتبقية غير كافية للخدمات المطلوبة'
                ], 400);
            }

            $pointsUsed = $totalPointsNeeded;
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
            $carPointsUsed = 0;

            if ($request->use_package) {
                $services = Service::with('servicePoint')->whereIn('id', $carData['services'])->get();
                $carPointsUsed = $services->sum(function($service) {
                    return $service->servicePoint ? $service->servicePoint->points_required : 0;
                });
                $carTotal = 0; // Free when using package
            } else {
                $carTotal = Service::whereIn('id', $carData['services'])->sum('price');
                $total += $carTotal;
            }

            $orderCars[] = [
                'car_id' => $car->id,
                'services' => $carData['services'],
                'subtotal' => $carTotal,
                'points_used' => $carPointsUsed,
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
                'points_used' => $carData['points_used'],
            ]);

            // Attach services for this car
            $orderCar->services()->attach($carData['services']);
        }

        // If using package, create package order
        if ($request->use_package && $userPackage) {
            \App\Models\PackageOrder::create([
                'user_package_id' => $userPackage->id,
                'order_id' => $order->id,
                'points_used' => $pointsUsed,
                'services' => json_encode(array_merge(...array_column($orderCars, 'services'))),
            ]);

            // Update remaining points
            $userPackage->remaining_points -= $pointsUsed;
            $userPackage->save();
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
        
        Log::info('Fetching booked time slots for date: ' . $date);
        
        // الحصول على الطلبات المحجوزة لليوم المحدد
        $bookedOrders = Order::whereDate('scheduled_at', $date)
            ->whereIn('status', ['pending', 'accepted', 'in_progress'])
            ->get(['scheduled_at', 'status']);
        
        Log::info('Found ' . $bookedOrders->count() . ' booked orders');
        
        // استخراج الساعات المحجوزة
        $bookedHours = $bookedOrders->map(function ($order) {
            $hour = Carbon::parse($order->scheduled_at)->hour;
            Log::info("Order at {$order->scheduled_at} (status: {$order->status}) -> hour: {$hour}");
            return $hour;
        })->toArray();
        
        // الحصول على الساعات غير المتاحة من إعدادات الأدمن
        $unavailableHours = DailyTimeSlot::getUnavailableHoursForDate($date);
        
        Log::info('Booked hours: ' . json_encode($bookedHours));
        Log::info('Unavailable hours: ' . json_encode($unavailableHours));
        
        // الساعات المتاحة = جميع الساعات - المحجوزة - غير المتاحة
        $allHours = range(10, 23);
        $unavailableHours = array_merge($bookedHours, $unavailableHours);
        $availableHours = array_diff($allHours, $unavailableHours);
        
        Log::info('Available hours: ' . json_encode($availableHours));
        
        return response()->json([
            'success' => true,
            'date' => $date,
            'booked_hours' => $bookedHours,
            'unavailable_hours' => DailyTimeSlot::getUnavailableHoursForDate($date),
            'available_hours' => $availableHours,
            'total_booked' => count($bookedHours),
            'total_unavailable' => count(DailyTimeSlot::getUnavailableHoursForDate($date)),
            'total_available' => count($availableHours)
        ]);
    }
}
