<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Service;
use App\Models\UserPackage;
use App\Models\UserPackageService;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PackageController extends Controller
{
    private function packagesEnabled(): bool
    {
        $value = Setting::getValue('packages_enabled', '1');
        return $value === '1' || $value === true || $value === 1;
    }

    public function index()
    {
        if (!$this->packagesEnabled()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'packages_enabled' => false,
            ]);
        }

        $packages = Package::active()->with('packageServices.service')->get();
        
        // Format packages with services and quantities
        $formattedPackages = $packages->map(function($package) {
            $services = $package->packageServices->map(function($packageService) {
                return [
                    'id' => $packageService->service_id,
                    'name' => $packageService->service->name ?? '',
                    'description' => $packageService->service->description ?? '',
                    'quantity' => $packageService->quantity,
                ];
            });
            
            return [
                'id' => $package->id,
                'name' => $package->name,
                'description' => $package->description,
                'price' => $package->price,
                'image' => $package->image ? asset('storage/' . $package->image) : null,
                'is_active' => $package->is_active,
                'services' => $services,
                'created_at' => $package->created_at,
                'updated_at' => $package->updated_at,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $formattedPackages,
            'packages_enabled' => true,
        ]);
    }

    public function show($id)
    {
        if (!$this->packagesEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Packages feature is disabled',
            ], 403);
        }

        $package = Package::active()->with('packageServices.service')->findOrFail($id);
        
        $services = $package->packageServices->map(function($packageService) {
            return [
                'id' => $packageService->service_id,
                'name' => $packageService->service->name ?? '',
                'description' => $packageService->service->description ?? '',
                'price' => $packageService->service->price ?? 0,
                'quantity' => $packageService->quantity,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'package' => [
                    'id' => $package->id,
                    'name' => $package->name,
                    'description' => $package->description,
                    'price' => $package->price,
                    'image' => $package->image ? asset('storage/' . $package->image) : null,
                    'is_active' => $package->is_active,
                ],
                'services' => $services
            ]
        ]);
    }

    public function purchase(Request $request, $id)
    {
        if (!$this->packagesEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Packages feature is disabled',
            ], 403);
        }

        $request->validate([
            'payment_intent_id' => 'required|string',
            'paid_amount' => 'required|numeric|min:0'
        ]);

        $package = Package::active()->findOrFail($id);
        $user = Auth::user();

        $activePackage = UserPackage::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>=', now()->toDateString())
            ->first();

        if ($activePackage) {
            return response()->json([
                'success' => false,
                'message' => 'لديك باقة نشطة بالفعل'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $userPackage = UserPackage::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'expires_at' => now()->addMonth(),
                'status' => 'active',
                'payment_intent_id' => $request->payment_intent_id,
                'paid_amount' => $request->paid_amount,
                'purchased_at' => now(),
            ]);

            // Create user package services from package services
            $packageServices = $package->packageServices;
            foreach ($packageServices as $packageService) {
                UserPackageService::create([
                    'user_package_id' => $userPackage->id,
                    'service_id' => $packageService->service_id,
                    'total_quantity' => $packageService->quantity,
                    'remaining_quantity' => $packageService->quantity,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم شراء الباقة بنجاح',
                'data' => $userPackage->load(['package', 'packageServices.service'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء شراء الباقة: ' . $e->getMessage()
            ], 500);
        }
    }

    public function myPackage()
    {
        if (!$this->packagesEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Packages feature is disabled',
            ], 403);
        }

        $user = Auth::user();
        
        $userPackage = UserPackage::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>=', now()->toDateString())
            ->with(['package', 'packageServices.service'])
            ->first();

        if (!$userPackage) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد باقة نشطة'
            ], 404);
        }

        // Format services with remaining quantities
        $services = $userPackage->packageServices->map(function($userPackageService) {
            return [
                'id' => $userPackageService->service_id,
                'name' => $userPackageService->service->name ?? '',
                'description' => $userPackageService->service->description ?? '',
                'price' => $userPackageService->service->price ?? 0,
                'total_quantity' => $userPackageService->total_quantity,
                'remaining_quantity' => $userPackageService->remaining_quantity,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $userPackage->id,
                'package' => [
                    'id' => $userPackage->package->id,
                    'name' => $userPackage->package->name,
                    'description' => $userPackage->package->description,
                    'price' => $userPackage->package->price,
                ],
                'expires_at' => $userPackage->expires_at,
                'status' => $userPackage->status,
                'services' => $services,
            ]
        ]);
    }

    public function availableServices()
    {
        if (!$this->packagesEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Packages feature is disabled',
            ], 403);
        }

        $user = Auth::user();
        
        $userPackage = UserPackage::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>=', now()->toDateString())
            ->with('packageServices.service')
            ->first();

        if (!$userPackage) {
            return response()->json([
                'success' => false,
                'message' => 'No active package found'
            ], 404);
        }

        // Get services with remaining quantities
        $availableServices = $userPackage->packageServices()
            ->where('remaining_quantity', '>', 0)
            ->with('service')
            ->get()
            ->map(function($userPackageService) {
                return [
                    'id' => $userPackageService->service_id,
                    'name' => $userPackageService->service->name ?? '',
                    'description' => $userPackageService->service->description ?? '',
                    'price' => $userPackageService->service->price ?? 0,
                    'total_quantity' => $userPackageService->total_quantity,
                    'remaining_quantity' => $userPackageService->remaining_quantity,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'user_package' => [
                    'id' => $userPackage->id,
                    'package' => [
                        'id' => $userPackage->package->id,
                        'name' => $userPackage->package->name,
                    ],
                    'expires_at' => $userPackage->expires_at,
                ],
                'available_services' => $availableServices
            ]
        ]);
    }

    public function packageHistory()
    {
        if (!$this->packagesEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Packages feature is disabled',
            ], 403);
        }

        $user = Auth::user();
        
        $packages = UserPackage::where('user_id', $user->id)
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $packages
        ]);
    }
} 