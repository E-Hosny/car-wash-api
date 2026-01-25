<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TimeSlot;
use App\Models\DailyTimeSlot;
use App\Models\HourSlotInstance;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\OneSignalService;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('customer', 'provider', 'services')->latest()->get();
        return view('admin.orders.index', compact('orders'));
    }

    public function timeSlots(Request $request)
    {
        $today = Carbon::now();
        $tomorrow = Carbon::now()->addDay();
        $dayAfter = Carbon::now()->addDays(2);
        
        // عدد المواعيد المتاحة لكل ساعة من الإعدادات
        $maxSlotsPerHour = (int) Setting::getValue('max_slots_per_hour', 2);
        
        $dates = [
            'today' => $today,
            'tomorrow' => $tomorrow,
            'day_after' => $dayAfter
        ];
        
        $timeSlotsData = [];
        
        foreach ($dates as $key => $date) {
            $dateString = $date->toDateString();
            
            // الحصول على جميع الطلبات لليوم المحدد (بما في ذلك المكتملة والملغية)
            $bookedOrders = Order::whereDate('scheduled_at', $dateString)
                ->whereIn('status', ['pending', 'accepted', 'in_progress', 'completed', 'cancelled'])
                ->with(['customer', 'services', 'hourSlotInstance'])
                ->orderBy('scheduled_at')
                ->get();
            
            // ربط الطلبات مع الـ slots (إذا لم تكن مرتبطة بالفعل)
            foreach ($bookedOrders as $order) {
                if (in_array($order->status, ['pending', 'accepted', 'in_progress']) && $order->scheduled_at) {
                    $hour = Carbon::parse($order->scheduled_at)->hour;
                    
                    // التحقق من أن الطلب غير مرتبط ب slot بالفعل
                    $existingSlot = HourSlotInstance::where('order_id', $order->id)
                        ->where('date', $dateString)
                        ->where('hour', $hour)
                        ->first();
                    
                    if (!$existingSlot) {
                        // البحث عن slot متاح لهذه الساعة وربطه بالطلب
                        $allSlots = HourSlotInstance::getSlotsForHour($dateString, $hour, $maxSlotsPerHour);
                        $availableSlotIndex = null;
                        foreach ($allSlots as $index => $slot) {
                            if ($slot['status'] === 'available' && !$slot['order_id']) {
                                $availableSlotIndex = $index;
                                break;
                            }
                        }
                        if ($availableSlotIndex) {
                            HourSlotInstance::bookSlot($dateString, $hour, $availableSlotIndex, $order->id);
                        }
                    }
                }
            }
            
            // إنشاء جميع الساعات (10 AM - 11 PM)
            $allHours = range(10, 23);
            $bookedHours = [];
            $availableHours = [];
            
            // تنظيم بيانات الساعات
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
                
                // البحث عن الطلبات لهذه الساعة
                $ordersForHour = $bookedOrders->filter(function($order) use ($hour) {
                    return $order->scheduled_at && Carbon::parse($order->scheduled_at)->hour == $hour;
                });
                
                // الحصول على الطلب الأول النشط لهذه الساعة (للعرض)
                $order = $ordersForHour->firstWhere(function($order) {
                    return in_array($order->status, ['pending', 'accepted', 'in_progress']);
                });
                
                $period = $hour < 12 ? 'AM' : 'PM';
                $displayHour = $hour > 12 ? $hour - 12 : $hour;
                if ($hour == 12) $displayHour = 12;
                
                // الحصول على معلومات الساعة من جدول time_slots
                $timeSlot = TimeSlot::where('hour', $hour)->first();
                
                // إضافة معلومات الـ slots
                $slotsData = [];
                foreach ($slots as $index => $slot) {
                    $slotsData[] = [
                        'slot_index' => $slot['slot_index'],
                        'status' => $slot['status'],
                        'order_id' => $slot['order_id'],
                        'order' => $slot['order'],
                    ];
                }
                
                if ($isUnavailable) {
                    $bookedHours[] = $hour;
                } else {
                    $availableHours[] = $hour;
                }
                
                $hoursData[] = [
                    'hour' => $hour,
                    'display_hour' => $displayHour,
                    'period' => $period,
                    'label' => $displayHour . ':00 ' . $period,
                    'is_booked' => $isFullyBooked,
                    'is_unavailable' => $isUnavailable,
                    'is_available' => !$isUnavailable,
                    'order' => $order,
                    'orders' => $ordersForHour->values(),
                    'time_slot' => $timeSlot,
                    'bookings_count' => $bookedCount,
                    'disabled_count' => $disabledCount,
                    'available_count' => $availableCount,
                    'max_slots' => $maxSlotsPerHour,
                    'is_fully_booked' => $isFullyBooked,
                    'slots' => $slotsData,
                ];
            }
            
            $timeSlotsData[$key] = [
                'date' => $date,
                'date_string' => $dateString,
                'label' => $this->getDateLabel($key, $date),
                'booked_hours' => $bookedHours,
                'available_hours' => $availableHours,
                'total_booked' => count($bookedHours),
                'total_available' => count($availableHours),
                'hours_data' => $hoursData,
                'orders' => $bookedOrders
            ];
        }
        
        // إذا كان الطلب AJAX، إرجاع JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $timeSlotsData
            ]);
        }
        
        return view('admin.orders.time-slots', compact('timeSlotsData'));
    }
    
    private function getDateLabel($key, $date)
    {
        switch ($key) {
            case 'today':
                return __('messages.today') . ' - ' . $date->format('d/m');
            case 'tomorrow':
                return __('messages.tomorrow') . ' - ' . $date->format('d/m');
            case 'day_after':
                return __('messages.day_after') . ' - ' . $date->format('d/m');
            default:
                return $date->format('d/m/Y');
        }
    }

    public function show($id)
    {
        $order = Order::with(['customer', 'provider', 'services', 'car'])->findOrFail($id);
        
        if (request()->ajax()) {
            $html = view('admin.orders.details', compact('order'))->render();
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        }
        
        return view('admin.orders.show', compact('order'));
    }

    public function getStatus($id)
    {
        $order = Order::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'status' => $order->status
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,accepted,in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);
        
        $order->update([
            'status' => $request->status,
            'admin_notes' => $request->notes
        ]);
        
        // Send OneSignal notification when order status is changed to completed
        if ($request->status === 'completed') {
            try {
                $order->load('customer');
                if ($order->customer) {
                    app(OneSignalService::class)->sendOrderCompletionRatingNotification(
                        $order->customer_id,
                        $order->id,
                        $order->customer->name
                    );
                    Log::info("OneSignal rating notification sent for order {$order->id} completion (from admin panel)");
                }
            } catch (\Exception $e) {
                // Log error but don't fail the status update
                Log::error('Failed to send OneSignal rating notification for order completion (from admin panel)', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الطلب بنجاح'
        ]);
    }

    public function cancel($id)
    {
        $order = Order::findOrFail($id);
        
        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء الطلب بنجاح'
        ]);
    }

    /**
     * تبديل حالة slot محدد (ON/OFF) لتاريخ وساعة محددة
     */
    public function toggleTimeSlot(Request $request, $hour)
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
        
        $slot = HourSlotInstance::toggleSlot($date, $hour, $slotIndex);
        
        // الحصول على حالة الساعة بعد التبديل
        $isUnavailable = HourSlotInstance::areAllSlotsUnavailable($date, $hour, $maxSlotsPerHour);
        $bookedCount = HourSlotInstance::getBookedSlotsCount($date, $hour, $maxSlotsPerHour);
        $disabledCount = HourSlotInstance::getDisabledSlotsCount($date, $hour, $maxSlotsPerHour);
        $availableCount = HourSlotInstance::getAvailableSlotsCount($date, $hour, $maxSlotsPerHour);
        
        // الحصول على جميع الـ slots
        $slots = HourSlotInstance::getSlotsForHour($date, $hour, $maxSlotsPerHour);
        $slotsData = [];
        foreach ($slots as $index => $slotData) {
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

    /**
     * تعيين حالة الساعة مباشرة لتاريخ محدد
     */
    public function setTimeSlotStatus(Request $request, $hour)
    {
        $request->validate([
            'is_available' => 'required|boolean',
            'date' => 'required|date'
        ]);
        
        $slot = DailyTimeSlot::setHourAvailabilityForDate(
            $request->date, 
            $hour, 
            $request->is_available,
            $request->notes
        );
        
        return response()->json([
            'success' => true,
            'is_available' => $slot->is_available,
            'message' => $slot->is_available ? 'تم تفعيل الساعة' : 'تم إيقاف الساعة',
            'date' => $request->date
        ]);
    }

    /**
     * الحصول على حالة جميع الساعات لتاريخ محدد
     */
    public function getTimeSlotsStatus(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $timeSlots = DailyTimeSlot::getTimeSlotsForDate($date);
        
        return response()->json([
            'success' => true,
            'date' => $date,
            'data' => $timeSlots
        ]);
    }
}
