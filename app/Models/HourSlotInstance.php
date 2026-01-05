<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HourSlotInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'hour',
        'slot_index',
        'status',
        'order_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * علاقة مع Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * الحصول على جميع الـ slots لتاريخ وساعة محددة
     */
    public static function getSlotsForHour($date, $hour, $maxSlots)
    {
        $dateString = is_string($date) ? $date : $date->toDateString();
        
        // الحصول على الـ slots الموجودة
        $existingSlots = self::where('date', $dateString)
            ->where('hour', $hour)
            ->get()
            ->keyBy('slot_index');
        
        // إنشاء مصفوفة لجميع الـ slots
        $slots = [];
        for ($i = 1; $i <= $maxSlots; $i++) {
            $slot = $existingSlots->get($i);
            $slots[$i] = [
                'slot_index' => $i,
                'status' => $slot ? $slot->status : 'available',
                'order_id' => $slot ? $slot->order_id : null,
                'order' => $slot && $slot->order ? $slot->order : null,
            ];
        }
        
        return $slots;
    }

    /**
     * تبديل حالة slot محدد
     */
    public static function toggleSlot($date, $hour, $slotIndex)
    {
        $dateString = is_string($date) ? $date : $date->toDateString();
        
        $slot = self::where('date', $dateString)
            ->where('hour', $hour)
            ->where('slot_index', $slotIndex)
            ->first();
        
        if (!$slot) {
            // إنشاء slot جديد بحالة disabled
            return self::create([
                'date' => $dateString,
                'hour' => $hour,
                'slot_index' => $slotIndex,
                'status' => 'disabled',
            ]);
        }
        
        // إذا كان محجوز، لا يمكن تغيير حالته
        if ($slot->status === 'booked') {
            return $slot;
        }
        
        // تبديل بين available و disabled
        $slot->status = $slot->status === 'available' ? 'disabled' : 'available';
        $slot->save();
        
        return $slot;
    }

    /**
     * تعيين حالة slot محدد
     */
    public static function setSlotStatus($date, $hour, $slotIndex, $status, $orderId = null)
    {
        $dateString = is_string($date) ? $date : $date->toDateString();
        
        return self::updateOrCreate(
            [
                'date' => $dateString,
                'hour' => $hour,
                'slot_index' => $slotIndex,
            ],
            [
                'status' => $status,
                'order_id' => $orderId,
            ]
        );
    }

    /**
     * حجز slot (ربطه بطلب)
     */
    public static function bookSlot($date, $hour, $slotIndex, $orderId)
    {
        return self::setSlotStatus($date, $hour, $slotIndex, 'booked', $orderId);
    }

    /**
     * تحرير slot (إلغاء الحجز)
     */
    public static function releaseSlot($date, $hour, $slotIndex)
    {
        $dateString = is_string($date) ? $date : $date->toDateString();
        
        $slot = self::where('date', $dateString)
            ->where('hour', $hour)
            ->where('slot_index', $slotIndex)
            ->first();
        
        if ($slot && $slot->status === 'booked') {
            $slot->status = 'available';
            $slot->order_id = null;
            $slot->save();
        }
        
        return $slot;
    }

    /**
     * الحصول على عدد الـ slots المتاحة لساعة محددة
     */
    public static function getAvailableSlotsCount($date, $hour, $maxSlots)
    {
        $dateString = is_string($date) ? $date : $date->toDateString();
        
        $slots = self::getSlotsForHour($dateString, $hour, $maxSlots);
        $availableCount = 0;
        
        foreach ($slots as $slot) {
            if ($slot['status'] === 'available') {
                $availableCount++;
            }
        }
        
        return $availableCount;
    }

    /**
     * التحقق من أن جميع الـ slots غير متاحة (OFF)
     */
    public static function areAllSlotsUnavailable($date, $hour, $maxSlots)
    {
        $dateString = is_string($date) ? $date : $date->toDateString();
        
        $slots = self::getSlotsForHour($dateString, $hour, $maxSlots);
        
        foreach ($slots as $slot) {
            if ($slot['status'] === 'available') {
                return false; // يوجد slot متاح واحد على الأقل
            }
        }
        
        return true; // جميع الـ slots غير متاحة
    }

    /**
     * الحصول على عدد الـ slots المحجوزة
     */
    public static function getBookedSlotsCount($date, $hour, $maxSlots)
    {
        $dateString = is_string($date) ? $date : $date->toDateString();
        
        $slots = self::getSlotsForHour($dateString, $hour, $maxSlots);
        $bookedCount = 0;
        
        foreach ($slots as $slot) {
            if ($slot['status'] === 'booked') {
                $bookedCount++;
            }
        }
        
        return $bookedCount;
    }

    /**
     * الحصول على عدد الـ slots المقفلة
     */
    public static function getDisabledSlotsCount($date, $hour, $maxSlots)
    {
        $dateString = is_string($date) ? $date : $date->toDateString();
        
        $slots = self::getSlotsForHour($dateString, $hour, $maxSlots);
        $disabledCount = 0;
        
        foreach ($slots as $slot) {
            if ($slot['status'] === 'disabled') {
                $disabledCount++;
            }
        }
        
        return $disabledCount;
    }
}
