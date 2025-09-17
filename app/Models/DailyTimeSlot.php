<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DailyTimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'hour',
        'is_available',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean',
    ];

    /**
     * الحصول على الساعات غير المتاحة لتاريخ محدد
     */
    public static function getUnavailableHoursForDate($date)
    {
        return self::where('date', $date)
            ->where('is_available', false)
            ->pluck('hour')
            ->toArray();
    }

    /**
     * التحقق من توفر الساعة في تاريخ محدد
     */
    public static function isHourAvailableForDate($date, $hour)
    {
        $slot = self::where('date', $date)
            ->where('hour', $hour)
            ->first();
            
        // إذا لم توجد سجل، الساعة متاحة افتراضياً
        if (!$slot) {
            return true;
        }
        
        return $slot->is_available;
    }

    /**
     * تعيين حالة الساعة لتاريخ محدد
     */
    public static function setHourAvailabilityForDate($date, $hour, $isAvailable, $notes = null)
    {
        return self::updateOrCreate(
            [
                'date' => $date,
                'hour' => $hour,
            ],
            [
                'is_available' => $isAvailable,
                'notes' => $notes,
            ]
        );
    }

    /**
     * تبديل حالة الساعة لتاريخ محدد
     */
    public static function toggleHourAvailabilityForDate($date, $hour)
    {
        $slot = self::where('date', $date)
            ->where('hour', $hour)
            ->first();
            
        if (!$slot) {
            // إنشاء سجل جديد مع حالة معكوسة (افتراضياً الساعة متاحة)
            return self::setHourAvailabilityForDate($date, $hour, false);
        }
        
        $slot->update(['is_available' => !$slot->is_available]);
        return $slot;
    }

    /**
     * الحصول على جميع الساعات لتاريخ محدد
     */
    public static function getTimeSlotsForDate($date)
    {
        $slots = self::where('date', $date)->get();
        $result = [];
        
        // إنشاء مصفوفة لجميع الساعات (10-23)
        for ($hour = 10; $hour <= 23; $hour++) {
            $slot = $slots->firstWhere('hour', $hour);
            $result[$hour] = [
                'hour' => $hour,
                'is_available' => $slot ? $slot->is_available : true,
                'notes' => $slot ? $slot->notes : null,
                'has_custom_setting' => $slot ? true : false,
            ];
        }
        
        return $result;
    }

    /**
     * نسخ إعدادات الساعات من يوم إلى يوم آخر
     */
    public static function copyTimeSlotsFromDate($fromDate, $toDate)
    {
        $fromSlots = self::where('date', $fromDate)->get();
        
        foreach ($fromSlots as $slot) {
            self::setHourAvailabilityForDate(
                $toDate,
                $slot->hour,
                $slot->is_available,
                $slot->notes
            );
        }
        
        return true;
    }

    /**
     * إعادة تعيين جميع الساعات لتاريخ محدد إلى الحالة الافتراضية (متاحة)
     */
    public static function resetTimeSlotsForDate($date)
    {
        return self::where('date', $date)->delete();
    }

    /**
     * الحصول على تسمية الساعة
     */
    public function getFormattedHourAttribute()
    {
        $period = $this->hour < 12 ? 'AM' : 'PM';
        $displayHour = $this->hour > 12 ? $this->hour - 12 : $this->hour;
        if ($this->hour == 12) $displayHour = 12;
        
        return $displayHour . ':00 ' . $period;
    }

    /**
     * الحصول على حالة الساعة كنص
     */
    public function getStatusTextAttribute()
    {
        return $this->is_available ? 'متاح' : 'غير متاح';
    }

    /**
     * الحصول على لون الساعة
     */
    public function getStatusColorAttribute()
    {
        return $this->is_available ? 'success' : 'danger';
    }
}