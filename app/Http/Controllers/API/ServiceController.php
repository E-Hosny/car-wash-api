<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\Storage;


class ServiceController extends Controller
{
    //
    public function index()
    {
        // التحقق من وجود مستخدم مصادق عليه وعدد طلباته
        $user = auth()->user();
        $hasOrders = false;
        
        if ($user) {
            $hasOrders = $user->customerOrders()->count() > 0;
        }
        
        // تطبيق خصم 50% إذا كان المستخدم غير مصادق عليه أو ليس لديه طلبات
        $shouldApplyDiscount = !$user || !$hasOrders;
        
        // استخدام scope ordered() للحصول على الخدمات مرتبة حسب sort_order
        $services = Service::ordered()->get()->map(function ($service) use ($shouldApplyDiscount) {
            if ($service->image) {
                $imagePath = Storage::url($service->image);
                $service->image_url = url($imagePath);
            } else {
                $service->image_url = null;
            }
            
            // إضافة معلومات الخصم
            $originalPrice = $service->price;
            $originalName = $service->name;
            
            if ($shouldApplyDiscount) {
                $service->has_discount = true;
                $service->discount_percentage = 50;
                $service->discount_label = "🔥 - 50% off";
                $service->original_price = $originalPrice;
                $service->discounted_price = $originalPrice / 2;
                $service->price = $service->discounted_price;
                // إضافة "🔥 - 50% off" بجانب عنوان الخدمة
                $service->name = $originalName . " 🔥 - 50% off";
                $service->original_name = $originalName;
            } else {
                $service->has_discount = false;
                $service->original_price = $originalPrice;
                $service->discounted_price = $originalPrice;
                $service->price = $originalPrice;
                $service->original_name = $originalName;
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
        
        // التحقق من وجود مستخدم مصادق عليه وعدد طلباته
        $user = auth()->user();
        $hasOrders = false;
        
        if ($user) {
            $hasOrders = $user->customerOrders()->count() > 0;
        }
        
        // تطبيق خصم 50% إذا كان المستخدم غير مصادق عليه أو ليس لديه طلبات
        $shouldApplyDiscount = !$user || !$hasOrders;
        
        // إضافة معلومات الخصم
        $originalPrice = $service->price;
        $originalName = $service->name;
        
        if ($shouldApplyDiscount) {
            $service->has_discount = true;
            $service->discount_percentage = 50;
            $service->discount_label = "🔥 - 50% off";
            $service->original_price = $originalPrice;
            $service->discounted_price = $originalPrice / 2;
            $service->price = $service->discounted_price;
            // إضافة "🔥 - 50% off" بجانب عنوان الخدمة
            $service->name = $originalName . " 🔥 - 50% off";
            $service->original_name = $originalName;
        } else {
            $service->has_discount = false;
            $service->original_price = $originalPrice;
            $service->discounted_price = $originalPrice;
            $service->price = $originalPrice;
            $service->original_name = $originalName;
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
