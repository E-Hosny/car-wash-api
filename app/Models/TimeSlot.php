<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'hour',
        'label',
        'is_available',
        'notes',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    /**
     * الحصول على جميع الساعات المتاحة
     */
    public static function getAvailableHours()
    {
        return self::where('is_available', true)
            ->pluck('hour')
            ->toArray();
    }

    /**
     * الحصول على جميع الساعات غير المتاحة
     */
    public static function getUnavailableHours()
    {
        return self::where('is_available', false)
            ->pluck('hour')
            ->toArray();
    }

    /**
     * التحقق من توفر الساعة
     */
    public static function isHourAvailable($hour)
    {
        return self::where('hour', $hour)
            ->where('is_available', true)
            ->exists();
    }

    /**
     * تبديل حالة الساعة
     */
    public function toggleAvailability()
    {
        $this->update([
            'is_available' => !$this->is_available
        ]);
        
        return $this->is_available;
    }

    /**
     * تعيين حالة الساعة
     */
    public function setAvailability($isAvailable)
    {
        $this->update([
            'is_available' => $isAvailable
        ]);
        
        return $this->is_available;
    }

    /**
     * الحصول على تسمية الساعة
     */
    public function getFormattedLabelAttribute()
    {
        return $this->label;
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