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
        'remaining_points',
        'total_points',
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

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('expires_at', '>=', now()->toDateString())
                    ->where('remaining_points', '>', 0);
    }

    public function isExpired()
    {
        return $this->expires_at < now()->toDateString();
    }

    public function hasEnoughPoints($points)
    {
        return $this->remaining_points >= $points;
    }
} 