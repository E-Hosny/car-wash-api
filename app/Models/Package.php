<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function userPackages()
    {
        return $this->hasMany(UserPackage::class);
    }

    public function packageServices()
    {
        return $this->hasMany(PackageService::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'package_services')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function getServicesWithQuantities()
    {
        return $this->packageServices()->with('service')->get()->map(function ($packageService) {
            return [
                'service_id' => $packageService->service_id,
                'service' => $packageService->service,
                'quantity' => $packageService->quantity,
            ];
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
} 