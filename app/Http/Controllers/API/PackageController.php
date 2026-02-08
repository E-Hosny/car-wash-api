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

    public function index(Request $request)
    {
        if (!$this->packagesEnabled()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'packages_enabled' => false,
            ]);
        }

        // Get language from request header or default to 'en'
        $language = $request->header('Accept-Language', 'en');
        $language = in_array($language, ['ar', 'en']) ? $language : 'en';

        $packages = Package::active()->with('packageServices.service')->get();
        
        // Get current user package if authenticated
        $currentPackage = null;
        $canUpgrade = false;
        
        if (Auth::check()) {
            $user = Auth::user();
            $userPackage = UserPackage::where('user_id', $user->id)
                ->where('status', 'active')
                ->with(['package', 'packageServices.service'])
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($userPackage) {
                $isExpired = $userPackage->expires_at < now()->toDateString();
                $hasRemainingServices = $userPackage->hasRemainingServices();
                $canUpgrade = $isExpired || !$hasRemainingServices;
                
                // Get description based on language
                $description = $language === 'ar' && $userPackage->package->description_ar 
                    ? $userPackage->package->description_ar 
                    : $userPackage->package->description;
                $descriptionData = $this->parsePackageDescription($description);
                
                // Get name based on language
                $packageName = $language === 'ar' && $userPackage->package->name_ar 
                    ? $userPackage->package->name_ar 
                    : $userPackage->package->name;
                
                $currentPackage = [
                    'id' => $userPackage->package->id,
                    'name' => $packageName,
                    'description' => $descriptionData['full'],
                    'description_headers' => $descriptionData['headers'],
                    'price' => $userPackage->package->price,
                    'expires_at' => $userPackage->expires_at,
                    'status' => $userPackage->status,
                    'can_upgrade' => $canUpgrade,
                ];
            }
        }
        
        // Format packages with services and quantities
        $formattedPackages = $packages->map(function($package) use ($language) {
            $services = $package->packageServices->map(function($packageService) use ($language) {
                // Get service name and description based on language
                $serviceName = $language === 'ar' && $packageService->service && $packageService->service->name_ar
                    ? $packageService->service->name_ar
                    : ($packageService->service->name ?? '');
                $serviceDescription = $language === 'ar' && $packageService->service && $packageService->service->description_ar
                    ? $packageService->service->description_ar
                    : ($packageService->service->description ?? '');
                
                return [
                    'id' => $packageService->service_id,
                    'name' => $serviceName,
                    'description' => $serviceDescription,
                    'quantity' => $packageService->quantity,
                ];
            });
            
            // Get description based on language
            $description = $language === 'ar' && $package->description_ar 
                ? $package->description_ar 
                : $package->description;
            $descriptionData = $this->parsePackageDescription($description);
            
            // Get name based on language
            $packageName = $language === 'ar' && $package->name_ar 
                ? $package->name_ar 
                : $package->name;
            
            return [
                'id' => $package->id,
                'name' => $packageName,
                'description' => $descriptionData['full'], // Full description for popup
                'description_headers' => $descriptionData['headers'], // Headers only for card
                'price' => $package->price,
                'image' => $package->image, // This should be like 'packages/image.jpg'
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
            'current_package' => $currentPackage,
        ]);
    }

    public function show(Request $request, $id)
    {
        if (!$this->packagesEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Packages feature is disabled',
            ], 403);
        }

        // Get language from request header or default to 'en'
        $language = $request->header('Accept-Language', 'en');
        $language = in_array($language, ['ar', 'en']) ? $language : 'en';

        $package = Package::active()->with('packageServices.service')->findOrFail($id);
        
        $services = $package->packageServices->map(function($packageService) use ($language) {
            // Get service name and description based on language
            $serviceName = $language === 'ar' && $packageService->service && $packageService->service->name_ar
                ? $packageService->service->name_ar
                : ($packageService->service->name ?? '');
            $serviceDescription = $language === 'ar' && $packageService->service && $packageService->service->description_ar
                ? $packageService->service->description_ar
                : ($packageService->service->description ?? '');
            
            return [
                'id' => $packageService->service_id,
                'name' => $serviceName,
                'description' => $serviceDescription,
                'price' => $packageService->service->price ?? 0,
                'quantity' => $packageService->quantity,
            ];
        });
        
        // Get description based on language
        $description = $language === 'ar' && $package->description_ar 
            ? $package->description_ar 
            : $package->description;
        $descriptionData = $this->parsePackageDescription($description);
        
        // Get name based on language
        $packageName = $language === 'ar' && $package->name_ar 
            ? $package->name_ar 
            : $package->name;
        
        return response()->json([
            'success' => true,
            'data' => [
                'package' => [
                    'id' => $package->id,
                    'name' => $packageName,
                    'description' => $descriptionData['full'],
                    'description_headers' => $descriptionData['headers'],
                    'price' => $package->price,
                    'image' => $package->image,
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
            ->with('packageServices')
            ->first();

        // Allow purchase if package is expired or all services are consumed
        if ($activePackage) {
            $isExpired = $activePackage->expires_at < now()->toDateString();
            $hasRemainingServices = $activePackage->hasRemainingServices();
            
            // Only block if package is active, not expired, and has remaining services
            if (!$isExpired && $hasRemainingServices) {
                return response()->json([
                    'success' => false,
                    'message' => 'لديك باقة نشطة بالفعل'
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            // Disable old active packages when purchasing a new one
            UserPackage::where('user_id', $user->id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);
            
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

    public function myPackage(Request $request)
    {
        if (!$this->packagesEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Packages feature is disabled',
            ], 403);
        }

        // Get language from request header or default to 'en'
        $language = $request->header('Accept-Language', 'en');
        $language = in_array($language, ['ar', 'en']) ? $language : 'en';

        $user = Auth::user();
        
        // Get the most recent package (active or expired)
        $userPackage = UserPackage::where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['package', 'packageServices.service'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$userPackage) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد باقة نشطة'
            ], 404);
        }

        // Check if package can be upgraded
        $isExpired = $userPackage->expires_at < now()->toDateString();
        $hasRemainingServices = $userPackage->hasRemainingServices();
        $canUpgrade = $isExpired || !$hasRemainingServices;

        // Format services with remaining quantities
        $services = $userPackage->packageServices->map(function($userPackageService) use ($language) {
            // Get service name and description based on language
            $serviceName = $language === 'ar' && $userPackageService->service && $userPackageService->service->name_ar
                ? $userPackageService->service->name_ar
                : ($userPackageService->service->name ?? '');
            $serviceDescription = $language === 'ar' && $userPackageService->service && $userPackageService->service->description_ar
                ? $userPackageService->service->description_ar
                : ($userPackageService->service->description ?? '');
            
            return [
                'id' => $userPackageService->service_id,
                'name' => $serviceName,
                'description' => $serviceDescription,
                'price' => $userPackageService->service->price ?? 0,
                'total_quantity' => $userPackageService->total_quantity,
                'remaining_quantity' => $userPackageService->remaining_quantity,
            ];
        });

        // Get description based on language
        $description = $language === 'ar' && $userPackage->package->description_ar 
            ? $userPackage->package->description_ar 
            : $userPackage->package->description;
        $descriptionData = $this->parsePackageDescription($description);
        
        // Get name based on language
        $packageName = $language === 'ar' && $userPackage->package->name_ar 
            ? $userPackage->package->name_ar 
            : $userPackage->package->name;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $userPackage->id,
                'package' => [
                    'id' => $userPackage->package->id,
                    'name' => $packageName,
                    'description' => $descriptionData['full'],
                    'description_headers' => $descriptionData['headers'],
                    'price' => $userPackage->package->price,
                ],
                'expires_at' => $userPackage->expires_at,
                'status' => $userPackage->status,
                'services' => $services,
                'can_upgrade' => $canUpgrade,
            ]
        ]);
    }

    public function availableServices(Request $request)
    {
        if (!$this->packagesEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Packages feature is disabled',
            ], 403);
        }

        // Get language from request header or default to 'en'
        $language = $request->header('Accept-Language', 'en');
        $language = in_array($language, ['ar', 'en']) ? $language : 'en';

        $user = Auth::user();
        
        $userPackage = UserPackage::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>=', now()->toDateString())
            ->with('packageServices.service')
            ->orderBy('created_at', 'desc')
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
            ->map(function($userPackageService) use ($language) {
                // Get service name and description based on language
                $serviceName = $language === 'ar' && $userPackageService->service && $userPackageService->service->name_ar
                    ? $userPackageService->service->name_ar
                    : ($userPackageService->service->name ?? '');
                $serviceDescription = $language === 'ar' && $userPackageService->service && $userPackageService->service->description_ar
                    ? $userPackageService->service->description_ar
                    : ($userPackageService->service->description ?? '');
                
                return [
                    'id' => $userPackageService->service_id,
                    'name' => $serviceName,
                    'description' => $serviceDescription,
                    'price' => $userPackageService->service->price ?? 0,
                    'total_quantity' => $userPackageService->total_quantity,
                    'remaining_quantity' => $userPackageService->remaining_quantity,
                ];
            });

        // Get package name based on language
        $packageName = $language === 'ar' && $userPackage->package->name_ar 
            ? $userPackage->package->name_ar 
            : $userPackage->package->name;

        return response()->json([
            'success' => true,
            'data' => [
                'user_package' => [
                    'id' => $userPackage->id,
                    'package' => [
                        'id' => $userPackage->package->id,
                        'name' => $packageName,
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

    /**
     * Parse package description (handles both JSON and string formats)
     * 
     * @param string|null $description
     * @return array ['full' => string|array, 'headers' => array]
     */
    private function parsePackageDescription($description)
    {
        if (empty($description)) {
            return [
                'full' => null,
                'headers' => []
            ];
        }

        // Try to decode as JSON first
        $decoded = json_decode($description, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            // It's JSON format
            $headers = array_map(function($item) {
                return $item['header'] ?? '';
            }, $decoded);
            
            return [
                'full' => $decoded, // Full structured data
                'headers' => array_filter($headers) // Only headers
            ];
        }

        // It's a plain string (old format)
        return [
            'full' => $description,
            'headers' => []
        ];
    }
} 