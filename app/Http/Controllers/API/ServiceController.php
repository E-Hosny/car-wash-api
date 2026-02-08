<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\Storage;


class ServiceController extends Controller
{
    //
    public function index(Request $request)
    {
        // التحقق من وجود مستخدم مصادق عليه وعدد طلباته
        // استخدام auth('sanctum') للتحقق من التوكن على المسارات العامة
        $user = auth('sanctum')->user();
        
        $hasOrders = false;
        $orderCount = 0;
        
        if ($user) {
            // التحقق من عدد الطلبات
            $orderCount = $user->customerOrders()->count();
            $hasOrders = $orderCount > 0;
        }
        
        // تطبيق خصم 50% إذا كان المستخدم غير مصادق عليه أو ليس لديه طلبات
        $shouldApplyDiscount = !$user || !$hasOrders;
        
        // Get language from request header or default to 'en'
        $language = $request->header('Accept-Language', 'en');
        $language = in_array($language, ['ar', 'en']) ? $language : 'en';
        
        // استخدام scope ordered() للحصول على الخدمات مرتبة حسب sort_order
        $services = Service::ordered()->get()->map(function ($service) use ($shouldApplyDiscount, $language) {
            if ($service->image) {
                $imagePath = Storage::url($service->image);
                $service->image_url = url($imagePath);
            } else {
                $service->image_url = null;
            }
            
            // إضافة معلومات الخصم
            $originalPrice = $service->price;
            
            // تحديد الاسم والوصف حسب اللغة
            if ($language === 'ar' && $service->name_ar) {
                $originalName = $service->name_ar;
                $service->name = $service->name_ar;
            } else {
                $originalName = $service->name;
                $service->name = $service->name;
            }
            
            if ($language === 'ar' && $service->description_ar) {
                $service->description = $service->description_ar;
            }
            
            if ($shouldApplyDiscount) {
                $service->has_discount = true;
                $service->discount_percentage = 50;
                $service->discount_label = $language === 'ar' ? "🔥 - خصم 50%" : "🔥 - 50% off";
                $service->original_price = $originalPrice;
                $service->discounted_price = $originalPrice / 2;
                $service->price = $service->discounted_price;
                // إضافة التسمية بجانب عنوان الخدمة
                $service->name = $originalName . ($language === 'ar' ? " 🔥 - خصم 50%" : " 🔥 - 50% off");
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
        
        // حساب cache_version بناءً على حالة المستخدم
        // إذا كان المستخدم لديه طلبات (لا خصم) = 1، إذا لم يكن لديه طلبات (خصم) = 0
        // هذا يجبر Flutter على إبطال cache عند تغيير حالة المستخدم
        $cacheVersion = $user && $hasOrders ? 1 : 0;
        
        // Return services with cache_version to force cache invalidation when user status changes
        return response()->json([
            'services' => $services,
            'cache_version' => $cacheVersion,
        ]);
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
    public function show(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        if ($service->image) {
            $service->image_url = url(Storage::url($service->image));
        } else {
            $service->image_url = null;
        }
        
        // التحقق من وجود مستخدم مصادق عليه وعدد طلباته
        // استخدام auth('sanctum') للتحقق من التوكن على المسارات العامة
        $user = auth('sanctum')->user();
        
        $hasOrders = false;
        
        if ($user) {
            $hasOrders = $user->customerOrders()->count() > 0;
        }
        
        // تطبيق خصم 50% إذا كان المستخدم غير مصادق عليه أو ليس لديه طلبات
        $shouldApplyDiscount = !$user || !$hasOrders;
        
        // Get language from request header or default to 'en'
        $language = $request->header('Accept-Language', 'en');
        $language = in_array($language, ['ar', 'en']) ? $language : 'en';
        
        // إضافة معلومات الخصم
        $originalPrice = $service->price;
        
        // تحديد الاسم والوصف حسب اللغة
        if ($language === 'ar' && $service->name_ar) {
            $originalName = $service->name_ar;
            $service->name = $service->name_ar;
        } else {
            $originalName = $service->name;
            $service->name = $service->name;
        }
        
        if ($language === 'ar' && $service->description_ar) {
            $service->description = $service->description_ar;
        }
        
        if ($shouldApplyDiscount) {
            $service->has_discount = true;
            $service->discount_percentage = 50;
            $service->discount_label = $language === 'ar' ? "🔥 - خصم 50%" : "🔥 - 50% off";
            $service->original_price = $originalPrice;
            $service->discounted_price = $originalPrice / 2;
            $service->price = $service->discounted_price;
            // إضافة التسمية بجانب عنوان الخدمة
            $service->name = $originalName . ($language === 'ar' ? " 🔥 - خصم 50%" : " 🔥 - 50% off");
            $service->original_name = $originalName;
        } else {
            $service->has_discount = false;
            $service->original_price = $originalPrice;
            $service->discounted_price = $originalPrice;
            $service->price = $originalPrice;
            $service->original_name = $originalName;
        }
        
        // حساب cache_version بناءً على حالة المستخدم
        $cacheVersion = $user && $hasOrders ? 1 : 0;
        
        // إضافة cache_version إلى الاستجابة
        $service->cache_version = $cacheVersion;
        
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
