<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TimeSlot;
use App\Models\DailyTimeSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
                ->with(['customer', 'services'])
                ->orderBy('scheduled_at')
                ->get();
            
            // استخراج الساعات المحجوزة (فقط الطلبات النشطة)
            $bookedHours = $bookedOrders->filter(function ($order) {
                return in_array($order->status, ['pending', 'accepted', 'in_progress']);
            })->map(function ($order) {
                return Carbon::parse($order->scheduled_at)->hour;
            })->toArray();
            
            // الحصول على الساعات غير المتاحة من إعدادات الأدمن لهذا التاريخ
            $unavailableTimeSlots = DailyTimeSlot::getUnavailableHoursForDate($dateString);
            
            // إنشاء جميع الساعات (10 AM - 11 PM)
            $allHours = range(10, 23);
            $availableHours = array_diff($allHours, array_merge($bookedHours, $unavailableTimeSlots));
            
            // تنظيم بيانات الساعات
            $hoursData = [];
            foreach ($allHours as $hour) {
                $isBooked = in_array($hour, $bookedHours);
                $isUnavailable = in_array($hour, $unavailableTimeSlots);
                $period = $hour < 12 ? 'AM' : 'PM';
                $displayHour = $hour > 12 ? $hour - 12 : $hour;
                if ($hour == 12) $displayHour = 12;
                
                // البحث عن الطلب لهذه الساعة (بغض النظر عن الحالة)
                $order = $bookedOrders->firstWhere(function($order) use ($hour) {
                    return Carbon::parse($order->scheduled_at)->hour == $hour;
                });
                
                // تحديد ما إذا كانت الساعة محجوزة فعلياً (فقط للطلبات النشطة)
                $isBooked = $order && in_array($order->status, ['pending', 'accepted', 'in_progress']);
                
                // الحصول على معلومات الساعة من جدول time_slots
                $timeSlot = TimeSlot::where('hour', $hour)->first();
                
                $hoursData[] = [
                    'hour' => $hour,
                    'display_hour' => $displayHour,
                    'period' => $period,
                    'label' => $displayHour . ':00 ' . $period,
                    'is_booked' => $isBooked,
                    'is_unavailable' => $isUnavailable,
                    'is_available' => !$isUnavailable && !$isBooked,
                    'order' => $order,
                    'time_slot' => $timeSlot
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
     * تبديل حالة الساعة (ON/OFF) لتاريخ محدد
     */
    public function toggleTimeSlot(Request $request, $hour)
    {
        $date = $request->get('date', now()->toDateString());
        
        $slot = DailyTimeSlot::toggleHourAvailabilityForDate($date, $hour);
        
        return response()->json([
            'success' => true,
            'is_available' => $slot->is_available,
            'message' => $slot->is_available ? 'تم تفعيل الساعة' : 'تم إيقاف الساعة',
            'date' => $date
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
