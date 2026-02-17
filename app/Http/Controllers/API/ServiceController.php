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
        // Get language from request header or default to 'en'
        $language = $request->header('Accept-Language', 'en');
        $language = in_array($language, ['ar', 'en']) ? $language : 'en';
        
        // استخدام scope ordered() للحصول على الخدمات مرتبة حسب sort_order
        $services = Service::ordered()->get()->map(function ($service) use ($language) {
            if ($service->image) {
                $imagePath = Storage::url($service->image);
                $service->image_url = url($imagePath);
            } else {
                $service->image_url = null;
            }
            
            $originalPrice = $service->price;
            
            // تحديد الاسم والوصف حسب اللغة
            if ($language === 'ar' && $service->name_ar) {
                $originalName = $service->name_ar;
                $service->name = $service->name_ar;
            } else {
                $originalName = $service->name;
            }
            
            if ($language === 'ar' && $service->description_ar) {
                $service->description = $service->description_ar;
            }
            
            // لا خصم لأول طلب - عرض السعر الأصلي فقط
            $service->has_discount = false;
            $service->original_price = $originalPrice;
            $service->discounted_price = $originalPrice;
            $service->price = $originalPrice;
            $service->original_name = $originalName;
            
            // Add updated_at timestamp for cache invalidation
            $service->updated_at_timestamp = $service->updated_at ? $service->updated_at->timestamp : null;
            return $service;
        });
        
        return response()->json([
            'services' => $services,
            'cache_version' => 1,
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
        
        // Get language from request header or default to 'en'
        $language = $request->header('Accept-Language', 'en');
        $language = in_array($language, ['ar', 'en']) ? $language : 'en';
        
        $originalPrice = $service->price;
        
        // تحديد الاسم والوصف حسب اللغة
        if ($language === 'ar' && $service->name_ar) {
            $originalName = $service->name_ar;
            $service->name = $service->name_ar;
        } else {
            $originalName = $service->name;
        }
        
        if ($language === 'ar' && $service->description_ar) {
            $service->description = $service->description_ar;
        }
        
        // لا خصم لأول طلب - عرض السعر الأصلي فقط
        $service->has_discount = false;
        $service->original_price = $originalPrice;
        $service->discounted_price = $originalPrice;
        $service->price = $originalPrice;
        $service->original_name = $originalName;
        $service->cache_version = 1;
        
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
