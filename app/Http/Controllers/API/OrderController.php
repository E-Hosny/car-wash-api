<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Order;
use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FirebaseNotificationService;
use App\Models\FcmToken;

class OrderController extends Controller
{
    // ✅ إنشاء طلب
 public function store(Request $request)
{
    $request->validate([
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'address' => 'nullable|string',
        'scheduled_at' => 'nullable|date',
        'car_id' => 'required|exists:cars,id',
        'services' => 'required|array',
        'services.*' => 'exists:services,id',
    ]);

    // نتأكد إن السيارة دي تخص المستخدم الحالي
    $car = Car::where('id', $request->car_id)
              ->where('user_id', auth()->id())
              ->first();

    if (! $car) {
        return response()->json(['message' => 'Car not found or does not belong to you'], 403);
    }

    // نحسب total من أسعار الخدمات
    $total = Service::whereIn('id', $request->services)->sum('price');

    // إنشاء الطلب مع حفظ total
    $order = Order::create([
        'customer_id' => auth()->id(),
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
        'address' => $request->address,
        'scheduled_at' => $request->scheduled_at,
        'car_id' => $car->id,
        'total' => $total,
    ]);

    $order->services()->attach($request->services);

    // 🟢 إرسال إشعار إلى جميع مزودي الخدمة
   // بعد Order::create()
    $tokens = FcmToken::whereHas('user', fn($q) => $q->where('role', 'provider'))->pluck('token')->toArray();
    \Log::info('FCM Provider Tokens:', $tokens);

    $firebase = new FirebaseNotificationService();
    foreach ($tokens as $token) {
        $response = $firebase->sendToToken($token, '🚘 New Order', 'A new car wash has been requested, open the app');
        \Log::info('FCM Notification Response', ['token' => $token, 'response' => $response]);
    }


        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order->load('services', 'car')
        ]);
}

    public function myOrders()
{
    $orders = Order::where('customer_id', auth()->id())
        ->with(['services', 'car.brand', 'car.model', 'car.year']) // ✅ تأكد من تضمين العلاقات هنا
        ->latest()
        ->get();

    return response()->json($orders);
}
    

    

    // ✅ عرض طلب مفرد
    public function show($id)
    {
        $order = Order::with('services', 'customer', 'provider')->findOrFail($id);
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
        ->with(['services', 'customer', 'car.brand', 'car.model','assignedUser']) // ✅ أضف العلاقات هنا
        ->get();
    }else{
        $orders = Order::where('assigned_to', auth()->id())
         ->where('status','pending')
        ->with(['services', 'customer', 'car.brand', 'car.model'])
        ->get();

    }
   

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
                ->with(['services', 'customer', 'car.brand', 'car.model','assignedUser']) // ✅ أضف العلاقات هنا
                ->get();
            }else{
                $orders = Order::where('assigned_to', auth()->id())
                ->where('status','completed')
                ->with(['services', 'customer', 'car.brand', 'car.model'])
                ->get();

            }

            return response()->json($orders);
        }
        public function acceptedOrders()
        {
            if (auth()->user()->role !== 'provider'  && auth()->user()->role !== 'worker' ) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

         if(auth()->user()->role=='provider'){
            $orders = Order::where('status', 'accepted')
            ->with(['services', 'customer', 'car.brand', 'car.model','assignedUser']) // ✅ أضف العلاقات هنا
            ->get();
        }else{
            $orders = Order::where('assigned_to', auth()->id())
            ->where('status','accepted')
            ->with(['services', 'customer', 'car.brand', 'car.model'])
            ->get();

        }

            return response()->json($orders);
        }
        public function inProgressOrders()
        {
            if (auth()->user()->role !== 'provider'  && auth()->user()->role !== 'worker' ) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            if(auth()->user()->role=='provider'){
             $orders = Order::where('status', 'in_progress')
                ->with(['services', 'customer', 'car.brand', 'car.model','assignedUser']) // ✅ أضف العلاقات هنا
                ->get();
            }else{
                $orders = Order::where('assigned_to', auth()->id())
                ->where('status','in_progress')
                ->with(['services', 'customer', 'car.brand', 'car.model'])
                ->get();

            }

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

}
