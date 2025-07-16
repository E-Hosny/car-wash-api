<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Service;
use App\Models\UserPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::active()->get();
        
        return response()->json([
            'success' => true,
            'data' => $packages
        ]);
    }

    public function show($id)
    {
        $package = Package::active()->findOrFail($id);
        $services = Service::with('servicePoint')->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'package' => $package,
                'services' => $services
            ]
        ]);
    }

    public function purchase(Request $request, $id)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'paid_amount' => 'required|numeric|min:0'
        ]);

        $package = Package::active()->findOrFail($id);
        $user = Auth::user();

        // Check if user already has an active package
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
                'remaining_points' => $package->points,
                'total_points' => $package->points,
                'expires_at' => now()->addMonth(),
                'status' => 'active',
                'payment_intent_id' => $request->payment_intent_id,
                'paid_amount' => $request->paid_amount,
                'purchased_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم شراء الباقة بنجاح',
                'data' => $userPackage->load('package')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء شراء الباقة'
            ], 500);
        }
    }

    public function myPackage()
    {
        $user = Auth::user();
        
        $userPackage = UserPackage::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>=', now()->toDateString())
            ->with('package')
            ->first();

        if (!$userPackage) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد باقة نشطة'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $userPackage
        ]);
    }

    public function availableServices()
    {
        $user = Auth::user();
        
        $userPackage = UserPackage::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>=', now()->toDateString())
            ->where('remaining_points', '>', 0)
            ->first();

        if (!$userPackage) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد باقة نشطة أو نقاط متبقية'
            ], 404);
        }

        $services = Service::with('servicePoint')
            ->whereHas('servicePoint', function($query) use ($userPackage) {
                $query->where('points_required', '<=', $userPackage->remaining_points);
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user_package' => $userPackage,
                'available_services' => $services
            ]
        ]);
    }

    public function packageHistory()
    {
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