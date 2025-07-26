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
        'points_used',
        'services',
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