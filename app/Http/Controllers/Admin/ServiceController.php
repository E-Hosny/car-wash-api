<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::ordered()->get();
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'price' => 'required|numeric',
            'sort_order' => 'required|integer|min:1',
        ]);

        // If sort_order is provided, use it; otherwise, get the highest and increment
        $sortOrder = $request->sort_order ?? (Service::max('sort_order') ?? 0) + 1;
        
        Service::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'sort_order' => $sortOrder,
        ]);

        return redirect()->route('admin.services.index')->with('success', __('messages.service_added_successfully'));
    }

    public function edit($id)
    {
        $service = Service::findOrFail($id);
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'price' => 'required|numeric',
            'sort_order' => 'required|integer|min:1',
        ]);

        $service = Service::findOrFail($id);
        $service->update($request->all());

        return redirect()->route('admin.services.index')->with('success', __('messages.service_updated_successfully'));
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return back()->with('success', __('messages.service_deleted_successfully'));
    }

    /**
     * Update service order
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'services' => 'required|array',
            'services.*.id' => 'required|exists:services,id',
            'services.*.sort_order' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->services as $serviceData) {
                Service::where('id', $serviceData['id'])
                    ->update(['sort_order' => $serviceData['sort_order']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('messages.order_updated')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('messages.order_update_error')
            ], 500);
        }
    }

    /**
     * Move service up in order
     */
    public function moveUp($id)
    {
        $service = Service::findOrFail($id);
        $previousService = Service::where('sort_order', '<', $service->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previousService) {
            $tempOrder = $service->sort_order;
            $service->update(['sort_order' => $previousService->sort_order]);
            $previousService->update(['sort_order' => $tempOrder]);
        }

        return back()->with('success', __('messages.move_up_success'));
    }

    /**
     * Move service down in order
     */
    public function moveDown($id)
    {
        $service = Service::findOrFail($id);
        $nextService = Service::where('sort_order', '>', $service->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($nextService) {
            $tempOrder = $service->sort_order;
            $service->update(['sort_order' => $nextService->sort_order]);
            $nextService->update(['sort_order' => $tempOrder]);
        }

        return back()->with('success', __('messages.move_down_success'));
    }
}
