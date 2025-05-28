<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            return response()->json(['message' => 'السيارة غير موجودة أو لا تخصك'], 403);
        }
    
        $order = Order::create([
            'customer_id' => auth()->id(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'scheduled_at' => $request->scheduled_at,
            'car_id' => $car->id,
        ]);
    
        $order->services()->attach($request->services);
    
        return response()->json([
            'message' => 'تم إنشاء الطلب بنجاح',
            'order' => $order->load('services', 'car')
        ]);
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
            if (auth()->user()->role !== 'provider') {
                return response()->json(['message' => 'غير مسموح'], 403);
            }

            $orders = Order::whereNull('provider_id')
                            ->where('status', 'pending')
                            ->with('services', 'customer')
                            ->get();

            return response()->json($orders);
        }
public function accept($id)
{
    $order = Order::findOrFail($id);

    if ($order->provider_id !== null || $order->status !== 'pending') {
        return response()->json(['message' => 'الطلب غير متاح للقبول.'], 400);
    }

    $order->provider_id = auth()->id();
    $order->status = 'accepted';
    $order->save();

    return response()->json(['message' => 'تم قبول الطلب.', 'order' => $order]);
}
public function updateStatus(Request $request, $id)
{
    $order = Order::findOrFail($id);

    $request->validate([
        'status' => 'required|in:in_progress,completed,cancelled'
    ]);

    if ($order->provider_id !== auth()->id()) {
        return response()->json(['message' => 'غير مسموح.'], 403);
    }

    $order->status = $request->status;
    $order->save();

    return response()->json(['message' => 'تم تحديث الحالة.', 'order' => $order]);
}

}
