<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'expires_at',
        'status',
        'payment_intent_id',
        'paid_amount',
        'purchased_at',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'purchased_at' => 'datetime',
        'paid_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function packageOrders()
    {
        return $this->hasMany(PackageOrder::class);
    }

    public function packageServices()
    {
        return $this->hasMany(UserPackageService::class);
    }

    public function getRemainingServices()
    {
        return $this->packageServices()
            ->with('service')
            ->where('remaining_quantity', '>', 0)
            ->get()
            ->map(function ($userPackageService) {
                return [
                    'service_id' => $userPackageService->service_id,
                    'service' => $userPackageService->service,
                    'total_quantity' => $userPackageService->total_quantity,
                    'remaining_quantity' => $userPackageService->remaining_quantity,
                ];
            });
    }

    public function hasServiceAvailable($serviceId, $quantity = 1)
    {
        $userPackageService = $this->packageServices()
            ->where('service_id', $serviceId)
            ->where('remaining_quantity', '>=', $quantity)
            ->first();

        return $userPackageService !== null;
    }

    public function useService($serviceId, $quantity = 1)
    {
        $userPackageService = $this->packageServices()
            ->where('service_id', $serviceId)
            ->first();

        if (!$userPackageService || !$userPackageService->hasEnoughQuantity($quantity)) {
            throw new \Exception('Insufficient quantity available for this service');
        }

        $userPackageService->useQuantity($quantity);

        return $userPackageService;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('expires_at', '>=', now()->toDateString())
                    ->whereHas('packageServices', function ($q) {
                        $q->where('remaining_quantity', '>', 0);
                    });
    }

    public function isExpired()
    {
        return $this->expires_at < now()->toDateString();
    }

    public function hasRemainingServices()
    {
        return $this->packageServices()
            ->where('remaining_quantity', '>', 0)
            ->exists();
    }
} 