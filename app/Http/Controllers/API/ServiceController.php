<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


class ServiceController extends Controller
{
    //
    public function index()
    {
        // Check if user has completed orders
        $userHasCompletedOrders = false;
        if (Auth::check()) {
            $userHasCompletedOrders = Order::where('customer_id', Auth::id())
                ->where('status', 'completed')
                ->exists();
        }
        
        // استخدام scope ordered() للحصول على الخدمات مرتبة حسب sort_order
        $services = Service::ordered()->get()->map(function ($service) use ($userHasCompletedOrders) {
            if ($service->image) {
                $imagePath = Storage::url($service->image);
                $service->image_url = url($imagePath);
            } else {
                $service->image_url = null;
            }
            
            // Apply discount if user has no completed orders
            $originalPrice = $service->price;
            if (!$userHasCompletedOrders) {
                $service->original_price = $originalPrice;
                $service->price = $originalPrice / 2;
                $service->discount_label = "- 50% off";
                $service->has_discount = true;
            } else {
                $service->original_price = $originalPrice;
                $service->has_discount = false;
            }
            
            // Add updated_at timestamp for cache invalidation
            $service->updated_at_timestamp = $service->updated_at ? $service->updated_at->timestamp : null;
            return $service;
        });
        
        // Return services directly (keep old format for compatibility)
        // Cache version is handled via TTL in Flutter
        return response()->json($services);
    }

    // ✅ إنشاء خدمة جديدة
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'price' => 'required|numeric',
        ]);

        $service = Service::create($request->all());

        return response()->json([
            'message' => 'تم إنشاء الخدمة بنجاح',
            'service' => $service,
        ]);
    }

    // ✅ عرض خدمة مفردة
    public function show($id)
    {
        $service = Service::findOrFail($id);
        if ($service->image) {
            $service->image_url = url(Storage::url($service->image));
        } else {
            $service->image_url = null;
        }
        
        // Check if user has completed orders
        $userHasCompletedOrders = false;
        if (Auth::check()) {
            $userHasCompletedOrders = Order::where('customer_id', Auth::id())
                ->where('status', 'completed')
                ->exists();
        }
        
        // Apply discount if user has no completed orders
        $originalPrice = $service->price;
        if (!$userHasCompletedOrders) {
            $service->original_price = $originalPrice;
            $service->price = $originalPrice / 2;
            $service->discount_label = "- 50% off";
            $service->has_discount = true;
        } else {
            $service->original_price = $originalPrice;
            $service->has_discount = false;
        }
        
        return response()->json($service);
    }

    // ✅ تعديل خدمة
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'price' => 'required|numeric',
        ]);

        $service->update($request->all());

        return response()->json([
            'message' => 'تم تعديل الخدمة',
            'service' => $service,
        ]);
    }

    // ✅ حذف خدمة
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json(['message' => 'تم حذف الخدمة']);
    }
}
