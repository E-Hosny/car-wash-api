<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // ✅ علاقة الطلبات التي قام بها العميل
    public function customerOrders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    // ✅ علاقة الطلبات التي خدمها المزود
    public function providerOrders()
    {
        return $this->hasMany(Order::class, 'provider_id');
    }

    public function cars()
    {
        return $this->hasMany(Car::class);
    }

}


