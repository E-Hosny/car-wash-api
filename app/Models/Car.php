<?php

namespace App\Models;

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\CarYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = ['user_id', 'brand_id', 'model_id', 'car_year_id', 'color', 'license_plate'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function model()
    {
        return $this->belongsTo(CarModel::class);
    }

    public function year()
    {
        return $this->belongsTo(CarYear::class, 'car_year_id');
    }

    public function orders()
{
    return $this->hasMany(Order::class);
}

}
