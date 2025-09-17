<?php

namespace App\Models;

use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'provider_id',
        'status',
        'scheduled_at',
        'latitude',
        'longitude',
        'address',
        'street',
        'building',
        'floor',
        'apartment',
        'car_id',
        'total',
        'payment_status',
        'payment_intent_id',
        'paid_at',
        'admin_notes',
        'cancelled_at',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'order_service');
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function assignedUser()
{
    return $this->belongsTo(User::class, 'assigned_to');
}

    public function packageOrder()
    {
        return $this->hasOne(PackageOrder::class);
    }

    public function isPackageOrder()
    {
        return $this->packageOrder()->exists();
    }

    public function orderCars()
    {
        return $this->hasMany(OrderCar::class);
    }

    public function isMultiCarOrder()
    {
        return $this->orderCars()->count() > 1;
    }
}
