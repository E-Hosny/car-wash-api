<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Service;
use App\Models\PackageService;
use App\Models\UserPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::withCount('userPackages')
            ->with('packageServices.service')
            ->get();
        
        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        $services = Service::ordered()->get();
        return view('admin.packages.create', compact('services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_items' => 'nullable|array',
            'description_items.*.header' => 'required_with:description_items|string|max:255',
            'description_items.*.description' => 'required_with:description_items|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'services' => 'required|array',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Process description: use description_items if provided, otherwise use description
            $descriptionValue = null;
            if ($request->has('description_items') && is_array($request->description_items) && count($request->description_items) > 0) {
                // Filter out empty items
                $filteredItems = array_filter($request->description_items, function($item) {
                    return !empty($item['header']) && !empty($item['description']);
                });
                if (count($filteredItems) > 0) {
                    $descriptionValue = json_encode(array_values($filteredItems));
                }
            } elseif ($request->has('description') && !empty($request->description)) {
                $descriptionValue = $request->description;
            }

            $package = Package::create([
                'name' => $request->name,
                'description' => $descriptionValue,
                'price' => $request->price,
                'image' => null,
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('packages', 'public');
                $package->update(['image' => $imagePath]);
            }

            // Create package services with quantities
            foreach ($request->services as $serviceData) {
                if (isset($serviceData['quantity']) && $serviceData['quantity'] > 0) {
                    PackageService::create([
                        'package_id' => $package->id,
                        'service_id' => $serviceData['service_id'],
                        'quantity' => $serviceData['quantity'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.packages.index')
                ->with('success', __('packages.package_created_successfully'));

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', __('packages.error_creating_package'));
        }
    }

    public function edit($id)
    {
        $package = Package::with('packageServices')->findOrFail($id);
        $services = Service::ordered()->get();
        
        // Get current quantities for each service
        $packageServices = $package->packageServices->keyBy('service_id');
        
        return view('admin.packages.edit', compact('package', 'services', 'packageServices'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_items' => 'nullable|array',
            'description_items.*.header' => 'required_with:description_items|string|max:255',
            'description_items.*.description' => 'required_with:description_items|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'services' => 'required|array',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|integer|min:0',
        ]);

        $package = Package::findOrFail($id);

        DB::beginTransaction();
        try {
            // Process description: use description_items if provided, otherwise use description
            $descriptionValue = null;
            if ($request->has('description_items') && is_array($request->description_items) && count($request->description_items) > 0) {
                // Filter out empty items
                $filteredItems = array_filter($request->description_items, function($item) {
                    return !empty($item['header']) && !empty($item['description']);
                });
                if (count($filteredItems) > 0) {
                    $descriptionValue = json_encode(array_values($filteredItems));
                }
            } elseif ($request->has('description') && !empty($request->description)) {
                $descriptionValue = $request->description;
            }

            $package->update([
                'name' => $request->name,
                'description' => $descriptionValue,
                'price' => $request->price,
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($package->image) {
                    Storage::disk('public')->delete($package->image);
                }
                
                $imagePath = $request->file('image')->store('packages', 'public');
                $package->update(['image' => $imagePath]);
            }

            // Delete existing package services
            $package->packageServices()->delete();

            // Create/update package services with quantities
            foreach ($request->services as $serviceData) {
                if (isset($serviceData['quantity']) && $serviceData['quantity'] > 0) {
                    PackageService::create([
                        'package_id' => $package->id,
                        'service_id' => $serviceData['service_id'],
                        'quantity' => $serviceData['quantity'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.packages.index')
                ->with('success', __('packages.package_updated_successfully'));

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', __('packages.error_updating_package'));
        }
    }

    public function destroy($id)
    {
        $package = Package::findOrFail($id);

        // Check if package has active users
        $activeUsers = UserPackage::where('package_id', $id)
            ->where('status', 'active')
            ->where('expires_at', '>=', now()->toDateString())
            ->count();

        if ($activeUsers > 0) {
            return back()->with('error', __('packages.cannot_delete_active_package'));
        }

        DB::beginTransaction();
        try {
            // Delete image
            if ($package->image) {
                Storage::disk('public')->delete($package->image);
            }

            $package->delete();
            DB::commit();

            return redirect()->route('admin.packages.index')
                ->with('success', __('packages.package_deleted_successfully'));

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', __('packages.error_deleting_package'));
        }
    }

    public function toggleStatus($id)
    {
        $package = Package::findOrFail($id);
        $package->update(['is_active' => !$package->is_active]);

        return redirect()->route('admin.packages.index')
            ->with('success', __('packages.package_status_updated'));
    }

    public function statistics()
    {
        $totalPackages = Package::count();
        $activePackages = Package::where('is_active', true)->count();
        $totalPurchases = UserPackage::count();
        $activeSubscriptions = UserPackage::where('status', 'active')
            ->where('expires_at', '>=', now()->toDateString())
            ->count();
        $totalRevenue = UserPackage::sum('paid_amount');

        $topPackages = Package::withCount('userPackages')
            ->orderBy('user_packages_count', 'desc')
            ->take(5)
            ->get();

        return view('admin.packages.statistics', compact(
            'totalPackages',
            'activePackages',
            'totalPurchases',
            'activeSubscriptions',
            'totalRevenue',
            'topPackages'
        ));
    }
} 