<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderCar extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'car_id',
        'subtotal',
        'points_used',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'order_car_service');
    }
} 