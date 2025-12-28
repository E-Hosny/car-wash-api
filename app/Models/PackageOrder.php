<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackageOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_package_id',
        'order_id',
        'services_used',
    ];

    protected $casts = [
        'services_used' => 'array',
    ];

    public function userPackage()
    {
        return $this->belongsTo(UserPackage::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
} 