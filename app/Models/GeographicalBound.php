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
        'polygon_points',
    ];

    protected $casts = [
        'min_latitude' => 'float',
        'max_latitude' => 'float',
        'min_longitude' => 'float',
        'max_longitude' => 'float',
        'polygon_points' => 'array',
    ];

    /**
     * التحقق من أن الموقع يقع ضمن هذا الحد
     */
    public function isLocationWithin(float $latitude, float $longitude): bool
    {
        // الاعتماد فقط على نقاط المضلع المرسوم
        if (!$this->polygon_points || !is_array($this->polygon_points) || count($this->polygon_points) < 3) {
            return false;
        }
        
        return $this->isPointInPolygon($latitude, $longitude, $this->polygon_points);
    }
    
    /**
     * التحقق من أن النقطة داخل المضلع باستخدام خوارزمية Ray Casting المحسنة
     */
    private function isPointInPolygon(float $latitude, float $longitude, array $polygonPoints): bool
    {
        // Bounding Box check أولاً للأداء
        if (!$this->isPointInBoundingBox($latitude, $longitude, $polygonPoints)) {
            return false;
        }
        
        // Ray Casting algorithm للتحقق الدقيق
        $inside = false;
        $j = count($polygonPoints) - 1;
        
        for ($i = 0; $i < count($polygonPoints); $i++) {
            $xi = $polygonPoints[$i]['lat'] ?? 0;
            $yi = $polygonPoints[$i]['lng'] ?? 0;
            $xj = $polygonPoints[$j]['lat'] ?? 0;
            $yj = $polygonPoints[$j]['lng'] ?? 0;
            
            // التحقق من أن الحافة أفقية (لتجنب القسمة على صفر)
            if (abs($yj - $yi) < 0.0000001) {
                $j = $i;
                continue;
            }
            
            // Ray Casting: إرسال شعاع أفقي من النقطة إلى اليمين
            // التحقق من تقاطع الشعاع مع الحافة
            $intersect = (($yi > $longitude) != ($yj > $longitude)) &&
                         ($latitude < ($xj - $xi) * ($longitude - $yi) / ($yj - $yi) + $xi);
            
            if ($intersect) {
                $inside = !$inside;
            }
            
            $j = $i;
        }
        
        return $inside;
    }
    
    /**
     * التحقق السريع من أن النقطة داخل Bounding Box للمضلع
     */
    private function isPointInBoundingBox(float $latitude, float $longitude, array $polygonPoints): bool
    {
        if (empty($polygonPoints)) {
            return false;
        }
        
        // استخدام min/max lat/lng المحفوظة إذا كانت موجودة
        if ($this->min_latitude && $this->max_latitude && 
            $this->min_longitude && $this->max_longitude) {
            return $latitude >= $this->min_latitude &&
                   $latitude <= $this->max_latitude &&
                   $longitude >= $this->min_longitude &&
                   $longitude <= $this->max_longitude;
        }
        
        // حساب Bounding Box من النقاط
        $minLat = PHP_FLOAT_MAX;
        $maxLat = PHP_FLOAT_MIN;
        $minLng = PHP_FLOAT_MAX;
        $maxLng = PHP_FLOAT_MIN;
        
        foreach ($polygonPoints as $point) {
            $lat = $point['lat'] ?? 0;
            $lng = $point['lng'] ?? 0;
            
            $minLat = min($minLat, $lat);
            $maxLat = max($maxLat, $lat);
            $minLng = min($minLng, $lng);
            $maxLng = max($maxLng, $lng);
        }
        
        return $latitude >= $minLat &&
               $latitude <= $maxLat &&
               $longitude >= $minLng &&
               $longitude <= $maxLng;
    }

    /**
     * قواعد التحقق من صحة البيانات
     */
    public static function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'polygon_points' => 'required|json',
        ];
    }

    /**
     * رسائل التحقق المخصصة
     */
    public static function validationMessages(): array
    {
        return [
            'name.required' => 'اسم الحد مطلوب',
            'polygon_points.required' => 'يجب رسم منطقة على الخريطة أولاً',
            'polygon_points.json' => 'نقاط المضلع غير صحيحة',
        ];
    }
}
