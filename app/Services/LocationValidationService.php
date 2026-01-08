<?php

namespace App\Services;

use App\Models\Setting;

class LocationValidationService
{
    // القيم الافتراضية لحدود دبي
    private const DEFAULT_MIN_LAT = 24.5;
    private const DEFAULT_MAX_LAT = 25.5;
    private const DEFAULT_MIN_LNG = 54.5;
    private const DEFAULT_MAX_LNG = 56.0;

    /**
     * الحصول على حدود دبي من الإعدادات
     */
    private static function getDubaiBounds(): array
    {
        return [
            'min_lat' => (float) Setting::getValue('dubai_min_latitude', self::DEFAULT_MIN_LAT),
            'max_lat' => (float) Setting::getValue('dubai_max_latitude', self::DEFAULT_MAX_LAT),
            'min_lng' => (float) Setting::getValue('dubai_min_longitude', self::DEFAULT_MIN_LNG),
            'max_lng' => (float) Setting::getValue('dubai_max_longitude', self::DEFAULT_MAX_LNG),
        ];
    }

    /**
     * التحقق من أن الموقع داخل حدود دبي
     */
    public static function isWithinDubai(float $latitude, float $longitude): bool
    {
        $bounds = self::getDubaiBounds();
        
        \Log::info('Location validation check', [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'bounds' => $bounds,
            'lat_check' => [
                'min' => $latitude >= $bounds['min_lat'],
                'max' => $latitude <= $bounds['max_lat'],
            ],
            'lng_check' => [
                'min' => $longitude >= $bounds['min_lng'],
                'max' => $longitude <= $bounds['max_lng'],
            ],
        ]);

        $result = $latitude >= $bounds['min_lat'] &&
               $latitude <= $bounds['max_lat'] &&
               $longitude >= $bounds['min_lng'] &&
               $longitude <= $bounds['max_lng'];
        
        \Log::info('Location validation result', [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_within_bounds' => $result,
        ]);

        return $result;
    }

    /**
     * التحقق من الموقع وإرجاع رسالة خطأ إذا كان خارج الحدود
     */
    public static function validateLocation(float $latitude, float $longitude): array
    {
        if (!self::isWithinDubai($latitude, $longitude)) {
            return [
                'valid' => false,
                'message' => 'Service is only available within Dubai. Please select a location within Dubai boundaries.'
            ];
        }

        return ['valid' => true];
    }
}

