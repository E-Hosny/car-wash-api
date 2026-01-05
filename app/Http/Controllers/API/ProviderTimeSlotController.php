<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\HourSlotInstance;
use App\Models\Setting;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProviderTimeSlotController extends Controller
{
    /**
     * الحصول على مواعيد اليوم الحالي
     */
    public function getTodayTimeSlots(Request $request): JsonResponse
    {
        $today = Carbon::now();
        $dateString = $today->toDateString();
        
        // عدد المواعيد المتاحة لكل ساعة من الإعدادات
        $maxSlotsPerHour = (int) Setting::getValue('max_slots_per_hour', 2);
        
        // الحصول على جميع الطلبات لليوم الحالي
        $bookedOrders = Order::whereDate('scheduled_at', $dateString)
            ->whereIn('status', ['pending', 'accepted', 'in_progress'])
            ->with(['customer', 'services'])
            ->orderBy('scheduled_at')
            ->get();
        
        // إنشاء جميع الساعات (10 AM - 11 PM)
        $allHours = range(10, 23);
        $hoursData = [];
        
        foreach ($allHours as $hour) {
            // الحصول على جميع الـ slots لهذه الساعة
            $slots = HourSlotInstance::getSlotsForHour($dateString, $hour, $maxSlotsPerHour);
            
            // حساب الإحصائيات
            $bookedCount = HourSlotInstance::getBookedSlotsCount($dateString, $hour, $maxSlotsPerHour);
            $disabledCount = HourSlotInstance::getDisabledSlotsCount($dateString, $hour, $maxSlotsPerHour);
            $availableCount = HourSlotInstance::getAvailableSlotsCount($dateString, $hour, $maxSlotsPerHour);
            
            // تحديد حالة الساعة: OFF فقط إذا كانت جميع الـ slots غير متاحة
            $isUnavailable = HourSlotInstance::areAllSlotsUnavailable($dateString, $hour, $maxSlotsPerHour);
            $isFullyBooked = $bookedCount >= $maxSlotsPerHour;
            
            $period = $hour < 12 ? 'AM' : 'PM';
            $displayHour = $hour > 12 ? $hour - 12 : $hour;
            if ($hour == 12) $displayHour = 12;
            
            // إضافة معلومات الـ slots
            $slotsData = [];
            foreach ($slots as $index => $slot) {
                $slotsData[] = [
                    'slot_index' => $slot['slot_index'],
                    'status' => $slot['status'],
                    'order_id' => $slot['order_id'],
                ];
            }
            
            $hoursData[] = [
                'hour' => $hour,
                'display_hour' => $displayHour,
                'period' => $period,
                'label' => $displayHour . ':00 ' . $period,
                'is_unavailable' => $isUnavailable,
                'is_fully_booked' => $isFullyBooked,
                'bookings_count' => $bookedCount,
                'disabled_count' => $disabledCount,
                'available_count' => $availableCount,
                'max_slots' => $maxSlotsPerHour,
                'slots' => $slotsData,
            ];
        }
        
        return response()->json([
            'success' => true,
            'date' => $dateString,
            'date_label' => $today->format('Y-m-d'),
            'hours_data' => $hoursData,
            'max_slots_per_hour' => $maxSlotsPerHour,
        ]);
    }
    
    /**
     * تبديل حالة slot محدد
     */
    public function toggleSlot(Request $request, $hour): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
            'slot_index' => 'required|integer|min:1',
        ]);
        
        $date = $request->get('date');
        $slotIndex = $request->get('slot_index');
        
        // الحصول على max_slots_per_hour
        $maxSlotsPerHour = (int) Setting::getValue('max_slots_per_hour', 2);
        
        // التحقق من أن slot_index صحيح
        if ($slotIndex > $maxSlotsPerHour) {
            return response()->json([
                'success' => false,
                'message' => 'رقم الـ slot غير صحيح'
            ], 400);
        }
        
        // التحقق من أن الـ slot ليس محجوز
        $slots = HourSlotInstance::getSlotsForHour($date, $hour, $maxSlotsPerHour);
        $targetSlot = $slots[$slotIndex] ?? null;
        
        if ($targetSlot && $targetSlot['status'] === 'booked') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تغيير حالة الـ slot المحجوز'
            ], 400);
        }
        
        $slot = HourSlotInstance::toggleSlot($date, $hour, $slotIndex);
        
        // الحصول على حالة الساعة بعد التبديل
        $isUnavailable = HourSlotInstance::areAllSlotsUnavailable($date, $hour, $maxSlotsPerHour);
        $bookedCount = HourSlotInstance::getBookedSlotsCount($date, $hour, $maxSlotsPerHour);
        $disabledCount = HourSlotInstance::getDisabledSlotsCount($date, $hour, $maxSlotsPerHour);
        $availableCount = HourSlotInstance::getAvailableSlotsCount($date, $hour, $maxSlotsPerHour);
        
        // الحصول على جميع الـ slots
        $updatedSlots = HourSlotInstance::getSlotsForHour($date, $hour, $maxSlotsPerHour);
        $slotsData = [];
        foreach ($updatedSlots as $index => $slotData) {
            $slotsData[] = [
                'slot_index' => $slotData['slot_index'],
                'status' => $slotData['status'],
                'order_id' => $slotData['order_id'],
            ];
        }
        
        return response()->json([
            'success' => true,
            'slot_status' => $slot->status,
            'is_available' => $slot->status === 'available',
            'hour_is_unavailable' => $isUnavailable,
            'booked_count' => $bookedCount,
            'disabled_count' => $disabledCount,
            'available_count' => $availableCount,
            'slots' => $slotsData,
            'message' => $slot->status === 'available' ? 'تم تفعيل الـ slot' : 'تم إيقاف الـ slot',
            'date' => $date,
            'hour' => $hour,
            'slot_index' => $slotIndex
        ]);
    }
}
