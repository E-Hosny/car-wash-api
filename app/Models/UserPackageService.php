<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPackageService extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_package_id',
        'service_id',
        'total_quantity',
        'remaining_quantity',
    ];

    public function userPackage()
    {
        return $this->belongsTo(UserPackage::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Use a specific quantity from this service
     */
    public function useQuantity($amount = 1)
    {
        if ($this->remaining_quantity < $amount) {
            throw new \Exception('Insufficient quantity available');
        }

        $this->remaining_quantity -= $amount;
        $this->save();

        return $this;
    }

    /**
     * Check if there's enough quantity available
     */
    public function hasEnoughQuantity($amount = 1)
    {
        return $this->remaining_quantity >= $amount;
    }
}

