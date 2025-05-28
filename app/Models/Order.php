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

}
