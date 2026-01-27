<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'rating',
        'comment',
    ];

    /**
     * Get the user that owns the rating.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order that this rating belongs to.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
