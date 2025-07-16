<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServicePoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'points_required',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
} 