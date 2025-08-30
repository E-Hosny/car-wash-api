<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;


class ServiceController extends Controller
{
    //
    public function index()
    {
        // استخدام scope ordered() للحصول على الخدمات مرتبة حسب sort_order
        return response()->json(Service::ordered()->get());
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
