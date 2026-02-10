<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'price',
        'validity_months',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Get description as array (handles both JSON and string formats)
     */
    public function getDescriptionArrayAttribute()
    {
        if (empty($this->description)) {
            return null;
        }

        // Try to decode as JSON first
        $decoded = json_decode($this->description, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // If not JSON, return as single item array with header
        return [
            [
                'header' => 'Description',
                'description' => $this->description
            ]
        ];
    }

    /**
     * Get only headers from description
     */
    public function getDescriptionHeaders()
    {
        $descriptionArray = $this->description_array;
        if (!$descriptionArray) {
            return [];
        }

        return array_map(function($item) {
            return $item['header'] ?? '';
        }, $descriptionArray);
    }

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