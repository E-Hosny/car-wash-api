<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeographicalBound extends Model
{
    protected $fillable = [
        'name',
        'min_latitude',
        'max_latitude',
        'min_longitude',
        'max_longitude',
    ];

    protected $casts = [
        'min_latitude' => 'float',
        'max_latitude' => 'float',
        'min_longitude' => 'float',
        'max_longitude' => 'float',
    ];

    /**
     * التحقق من أن الموقع يقع ضمن هذا الحد
     */
    public function isLocationWithin(float $latitude, float $longitude): bool
    {
        return $latitude >= $this->min_latitude &&
               $latitude <= $this->max_latitude &&
               $longitude >= $this->min_longitude &&
               $longitude <= $this->max_longitude;
    }

    /**
     * قواعد التحقق من صحة البيانات
     */
    public static function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'min_latitude' => 'required|numeric|min:-90|max:90',
            'max_latitude' => 'required|numeric|min:-90|max:90',
            'min_longitude' => 'required|numeric|min:-180|max:180',
            'max_longitude' => 'required|numeric|min:-180|max:180',
        ];
    }

    /**
     * رسائل التحقق المخصصة
     */
    public static function validationMessages(): array
    {
        return [
            'name.required' => 'اسم الحد مطلوب',
            'min_latitude.required' => 'الحد الأدنى لخط العرض مطلوب',
            'max_latitude.required' => 'الحد الأقصى لخط العرض مطلوب',
            'min_longitude.required' => 'الحد الأدنى لخط الطول مطلوب',
            'max_longitude.required' => 'الحد الأقصى لخط الطول مطلوب',
            'min_latitude.numeric' => 'الحد الأدنى لخط العرض يجب أن يكون رقماً',
            'max_latitude.numeric' => 'الحد الأقصى لخط العرض يجب أن يكون رقماً',
            'min_longitude.numeric' => 'الحد الأدنى لخط الطول يجب أن يكون رقماً',
            'max_longitude.numeric' => 'الحد الأقصى لخط الطول يجب أن يكون رقماً',
        ];
    }
}
