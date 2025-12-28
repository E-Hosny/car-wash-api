<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'sort_order',
    ];

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_service');
    }

    public function packageServices()
    {
        return $this->hasMany(PackageService::class);
    }

    public function userPackageServices()
    {
        return $this->hasMany(UserPackageService::class);
    }

    /**
     * Scope to order services by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
}
