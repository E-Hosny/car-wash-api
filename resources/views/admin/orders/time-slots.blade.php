@extends('admin.layout')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-calendar-clock text-primary"></i>
                        إدارة المواعيد والساعات المتاحة
                    </h2>
                    <p class="text-muted mb-0">عرض وإدارة جميع المواعيد المحجوزة والمتاحة</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> العودة للطلبات
                    </a>
                    <button class="btn btn-primary" onclick="refreshTimeSlots()" id="refreshBtn">
                        <i class="bi bi-arrow-clockwise"></i> تحديث
                    </button>
                    <button class="btn btn-success" onclick="exportTimeSlots()">
                        <i class="bi bi-download"></i> تصدير
                    </button>
                    <div class="btn-group">
                        <button class="btn btn-warning dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> إدارة الساعات
                        </button>
                        <ul class="dropdown-menu">
                            <li><h6 class="dropdown-header">اليوم</h6></li>
                            <li><a class="dropdown-item" href="#" onclick="enableAllTimeSlotsForDate('{{ $timeSlotsData['today']['date_string'] }}')">
                                <i class="bi bi-check-circle text-success"></i> تفعيل جميع الساعات
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="disableAllTimeSlotsForDate('{{ $timeSlotsData['today']['date_string'] }}')">
                                <i class="bi bi-power text-warning"></i> إيقاف جميع الساعات
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">غداً</h6></li>
                            <li><a class="dropdown-item" href="#" onclick="enableAllTimeSlotsForDate('{{ $timeSlotsData['tomorrow']['date_string'] }}')">
                                <i class="bi bi-check-circle text-success"></i> تفعيل جميع الساعات
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="disableAllTimeSlotsForDate('{{ $timeSlotsData['tomorrow']['date_string'] }}')">
                                <i class="bi bi-power text-warning"></i> إيقاف جميع الساعات
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">بعد غد</h6></li>
                            <li><a class="dropdown-item" href="#" onclick="enableAllTimeSlotsForDate('{{ $timeSlotsData['day_after']['date_string'] }}')">
                                <i class="bi bi-check-circle text-success"></i> تفعيل جميع الساعات
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="disableAllTimeSlotsForDate('{{ $timeSlotsData['day_after']['date_string'] }}')">
                                <i class="bi bi-power text-warning"></i> إيقاف جميع الساعات
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="showTimeSlotsManagement()">
                                <i class="bi bi-list-check"></i> إدارة متقدمة
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-gradient-primary text-white shadow-lg">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">اليوم</h5>
                            <h2 class="mb-0">{{ $timeSlotsData['today']['total_booked'] }}</h2>
                            <small>مواعيد محجوزة</small>
                        </div>
                        <div class="fs-1 opacity-75">
                            <i class="bi bi-calendar-day"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-gradient-success text-white shadow-lg">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">غداً</h5>
                            <h2 class="mb-0">{{ $timeSlotsData['tomorrow']['total_booked'] }}</h2>
                            <small>مواعيد محجوزة</small>
                        </div>
                        <div class="fs-1 opacity-75">
                            <i class="bi bi-calendar-plus"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-gradient-info text-white shadow-lg">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">بعد غد</h5>
                            <h2 class="mb-0">{{ $timeSlotsData['day_after']['total_booked'] }}</h2>
                            <small>مواعيد محجوزة</small>
                        </div>
                        <div class="fs-1 opacity-75">
                            <i class="bi bi-calendar-week"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Slots Display -->
    <div class="row" id="timeSlotsContainer">
        @foreach(['today', 'tomorrow', 'day_after'] as $dayKey)
            @php $dayData = $timeSlotsData[$dayKey]; @endphp
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-{{ $dayKey === 'today' ? 'primary' : ($dayKey === 'tomorrow' ? 'success' : 'info') }} text-white position-relative">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">
                                    <i class="bi bi-calendar-date"></i>
                                    {{ $dayData['label'] }}
                                </h5>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-light text-dark">
                                        <i class="bi bi-x-circle"></i> {{ $dayData['total_booked'] }} محجوز
                                    </span>
                                    <span class="badge bg-light text-dark">
                                        <i class="bi bi-check-circle"></i> {{ $dayData['total_available'] }} متاح
                                    </span>
                                </div>
                            </div>
                            <div class="fs-4 opacity-75">
                                @if($dayKey === 'today')
                                    <i class="bi bi-sun"></i>
                                @elseif($dayKey === 'tomorrow')
                                    <i class="bi bi-moon"></i>
                                @else
                                    <i class="bi bi-stars"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <!-- Time Slots Grid -->
                        <div class="row g-2">
                            @foreach($dayData['hours_data'] as $hourData)
                                <div class="col-4">
                                    <div class="time-slot-card 
                                        {{ $hourData['is_booked'] ? 'booked' : ($hourData['is_unavailable'] ? 'unavailable' : 'available') }} 
                                        {{ $hourData['is_booked'] ? 'border-danger' : ($hourData['is_unavailable'] ? 'border-warning' : 'border-success') }}"
                                        data-hour="{{ $hourData['hour'] }}"
                                        data-date="{{ $dayData['date_string'] }}"
                                        data-day="{{ $dayKey }}"
                                        @if($hourData['is_booked'])
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top"
                                            title="محجوز - {{ $hourData['order']->customer->name ?? 'عميل' }} - {{ $hourData['order']->status }}"
                                        @elseif($hourData['is_unavailable'])
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top"
                                            title="غير متاح - اضغط للتفعيل"
                                        @endif
                                        onclick="handleTimeSlotClick('{{ $dayKey }}', {{ $hourData['hour'] }}, {{ $hourData['is_booked'] ? 'true' : 'false' }}, {{ $hourData['is_unavailable'] ? 'true' : 'false' }}, '{{ $dayData['date_string'] }}')">
                                        <div class="text-center p-2">
                                            <div class="fw-bold {{ $hourData['is_booked'] ? 'text-danger' : ($hourData['is_unavailable'] ? 'text-warning' : 'text-success') }} fs-6">
                                                {{ $hourData['label'] }}
                                            </div>
                                            @if($hourData['is_booked'])
                                                <small class="text-danger d-block">
                                                    <i class="bi bi-x-circle"></i> محجوز
                                                </small>
                                                @if($hourData['order'])
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">
                                                        {{ $hourData['order']->customer->name ?? 'عميل' }}
                                                    </small>
                                                @endif
                                            @elseif($hourData['is_unavailable'])
                                                <small class="text-warning d-block">
                                                    <i class="bi bi-power"></i> OFF
                                                </small>
                                                <small class="text-muted d-block" style="font-size: 0.6rem;">
                                                    اضغط للتفعيل
                                                </small>
                                            @else
                                                <small class="text-success d-block">
                                                    <i class="bi bi-check-circle"></i> متاح
                                                </small>
                                            @endif
                                        </div>
                                        
                                        <!-- Toggle Button for Admin -->
                                        @if(!$hourData['is_booked'])
                                            <div class="toggle-btn-container">
                                                <button class="btn btn-sm toggle-btn {{ $hourData['is_unavailable'] ? 'btn-warning' : 'btn-success' }}"
                                                        onclick="event.stopPropagation(); toggleTimeSlot({{ $hourData['hour'] }}, '{{ $dayData['date_string'] }}')"
                                                        data-bs-toggle="tooltip" 
                                                        title="{{ $hourData['is_unavailable'] ? 'تفعيل الساعة' : 'إيقاف الساعة' }}">
                                                    <i class="bi {{ $hourData['is_unavailable'] ? 'bi-power' : 'bi-check-circle' }}"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Detailed Orders Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul text-primary"></i>
                            تفاصيل الطلبات المحجوزة
                        </h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="statusFilter" onchange="filterOrders()">
                                <option value="">جميع الحالات</option>
                                <option value="pending">معلق</option>
                                <option value="accepted">مقبول</option>
                                <option value="in_progress">قيد التنفيذ</option>
                                <option value="completed">مكتمل</option>
                                <option value="cancelled">ملغي</option>
                            </select>
                            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="البحث..." onkeyup="searchOrders()">
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="ordersTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>التاريخ</th>
                                    <th>الوقت</th>
                                    <th>العميل</th>
                                    <th>الهاتف</th>
                                    <th>الخدمات</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(['today', 'tomorrow', 'day_after'] as $dayKey)
                                    @foreach($timeSlotsData[$dayKey]['orders'] as $order)
                                        <tr data-status="{{ $order->status }}" data-customer="{{ $order->customer->name ?? '' }}">
                                            <td>
                                                <span class="badge bg-{{ $dayKey === 'today' ? 'primary' : ($dayKey === 'tomorrow' ? 'success' : 'info') }}">
                                                    {{ $timeSlotsData[$dayKey]['label'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ \Carbon\Carbon::parse($order->scheduled_at)->format('h:i A') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        {{ substr($order->customer->name ?? 'ع', 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $order->customer->name ?? 'غير محدد' }}</div>
                                                        <small class="text-muted">{{ $order->customer->email ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $order->customer->phone ?? 'غير محدد' }}</td>
                                            <td>
                                                @foreach($order->services as $service)
                                                    <span class="badge bg-secondary me-1 mb-1">{{ $service->name }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">{{ number_format($order->total, 2) }} AED</span>
                                            </td>
                                            <td>
                                                @php
                                                    $badgeClass = match($order->status) {
                                                        'pending' => 'warning',
                                                        'accepted' => 'info',
                                                        'in_progress' => 'primary',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $badgeClass }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-info" 
                                                            onclick="viewOrderDetails({{ $order->id }})"
                                                            data-bs-toggle="tooltip" title="عرض التفاصيل">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning" 
                                                            onclick="editOrderStatus({{ $order->id }})"
                                                            data-bs-toggle="tooltip" title="تعديل الحالة">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" 
                                                            onclick="cancelOrder({{ $order->id }})"
                                                            data-bs-toggle="tooltip" title="إلغاء الطلب">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل الطلب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تحديث حالة الطلب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="statusUpdateForm">
                    <input type="hidden" id="orderId" name="order_id">
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">الحالة الجديدة</label>
                        <select class="form-select" id="newStatus" name="status" required>
                            <option value="pending">معلق</option>
                            <option value="accepted">مقبول</option>
                            <option value="in_progress">قيد التنفيذ</option>
                            <option value="completed">مكتمل</option>
                            <option value="cancelled">ملغي</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="statusNotes" class="form-label">ملاحظات (اختياري)</label>
                        <textarea class="form-control" id="statusNotes" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="updateOrderStatus()">تحديث</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom Styles */
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745, #1e7e34);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8, #117a8b);
}

.time-slot-card {
    border: 2px solid;
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
    min-height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.time-slot-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.time-slot-card:hover::before {
    left: 100%;
}

.time-slot-card.available {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-color: #28a745;
}

.time-slot-card.available:hover {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
}

.time-slot-card.booked {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    border-color: #dc3545;
    opacity: 0.8;
}

.time-slot-card.booked:hover {
    background: linear-gradient(135deg, #f5c6cb, #f1b0b7);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.3);
}

.time-slot-card.unavailable {
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    border-color: #ffc107;
    opacity: 0.9;
}

.time-slot-card.unavailable:hover {
    background: linear-gradient(135deg, #ffeaa7, #fdcb6e);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 193, 7, 0.3);
}

.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 15px;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.avatar-sm {
    width: 35px;
    height: 35px;
    font-size: 14px;
}

.badge {
    font-size: 0.75em;
    padding: 0.5em 0.75em;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.9rem;
}

.table td {
    vertical-align: middle;
    font-size: 0.9rem;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

/* Loading Animation */
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.loading {
    animation: pulse 1.5s ease-in-out infinite;
}

/* Responsive Design */
@media (max-width: 768px) {
    .time-slot-card {
        min-height: 60px;
    }
    
    .time-slot-card .fs-6 {
        font-size: 0.8rem !important;
    }
    
    .card-body {
        padding: 1rem !important;
    }
}

/* Custom Scrollbar */
.table-responsive::-webkit-scrollbar {
    height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Toggle Button Styles */
.toggle-btn-container {
    position: absolute;
    top: 5px;
    right: 5px;
    z-index: 10;
}

.toggle-btn {
    width: 24px;
    height: 24px;
    padding: 0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    border: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.toggle-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

.toggle-btn.btn-success {
    background-color: #28a745;
    color: white;
}

.toggle-btn.btn-warning {
    background-color: #ffc107;
    color: #212529;
}

.toggle-btn:active {
    transform: scale(0.95);
}

/* Position relative for time slot cards to contain toggle buttons */
.time-slot-card {
    position: relative;
}

/* Enhanced Modal Styles */
.modal-content {
    border-radius: 15px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.modal-header {
    border-radius: 15px 15px 0 0;
}

.modal-footer {
    border-radius: 0 0 15px 15px;
}

/* Enhanced Notification Styles */
.alert {
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border: none;
    font-weight: 500;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
}

.alert-warning {
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    color: #856404;
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1, #bee5eb);
    color: #0c5460;
}

/* Enhanced Button Styles */
.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn:active {
    transform: translateY(0);
}

/* Enhanced Toggle Button Animation */
.toggle-btn {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.toggle-btn:hover {
    transform: scale(1.15);
    box-shadow: 0 6px 20px rgba(0,0,0,0.25);
}

.toggle-btn:active {
    transform: scale(0.95);
}

/* Time Slot Card Enhanced Animation */
.time-slot-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.time-slot-card:hover {
    transform: translateY(-3px) scale(1.02);
}

/* Success Animation */
@keyframes successPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.success-animation {
    animation: successPulse 0.6s ease-in-out;
}

/* Loading Animation Enhancement */
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.spin {
    animation: spin 1s linear infinite;
}

/* Enhanced Tooltip Styles */
.tooltip {
    font-size: 0.875rem;
}

.tooltip-inner {
    background-color: rgba(0,0,0,0.9);
    border-radius: 8px;
    padding: 8px 12px;
}

/* Responsive Enhancements */
@media (max-width: 768px) {
    .modal-dialog {
        margin: 1rem;
    }
    
    .alert {
        min-width: 280px;
        font-size: 0.9rem;
    }
    
    .time-slot-card {
        min-height: 70px;
    }
}
</style>

<script>
// Global variables
let currentOrderId = null;
let refreshInterval = null;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
    startAutoRefresh();
    initializeSoundEffects();
});

// Initialize sound effects
function initializeSoundEffects() {
    // Create audio context for success sound
    window.audioContext = null;
    try {
        window.audioContext = new (window.AudioContext || window.webkitAudioContext)();
    } catch (e) {
        console.log('Web Audio API not supported');
    }
}

// Play success sound
function playSuccessSound() {
    if (!window.audioContext) return;
    
    try {
        const oscillator = window.audioContext.createOscillator();
        const gainNode = window.audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(window.audioContext.destination);
        
        oscillator.frequency.setValueAtTime(800, window.audioContext.currentTime);
        oscillator.frequency.setValueAtTime(1000, window.audioContext.currentTime + 0.1);
        
        gainNode.gain.setValueAtTime(0.1, window.audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, window.audioContext.currentTime + 0.3);
        
        oscillator.start(window.audioContext.currentTime);
        oscillator.stop(window.audioContext.currentTime + 0.3);
    } catch (e) {
        console.log('Could not play sound');
    }
}

// Initialize tooltips
function initializeTooltips() {
    // Dispose existing tooltips first
    var existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    existingTooltips.forEach(function(element) {
        var tooltip = bootstrap.Tooltip.getInstance(element);
        if (tooltip) {
            tooltip.dispose();
        }
    });
    
    // Initialize new tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Auto refresh every 30 seconds
function startAutoRefresh() {
    refreshInterval = setInterval(function() {
        refreshTimeSlots(true);
    }, 30000);
}

// Stop auto refresh
function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
    }
}

// Refresh time slots
function refreshTimeSlots(silent = false) {
    if (!silent) {
        const refreshBtn = document.getElementById('refreshBtn');
        refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> جاري التحديث...';
        refreshBtn.disabled = true;
    }
    
    fetch('{{ route("admin.orders.time-slots") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateTimeSlotsDisplay(data.data);
            if (!silent) {
                showNotification('تم تحديث البيانات بنجاح', 'success');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (!silent) {
            showNotification('حدث خطأ في تحديث البيانات', 'error');
        }
    })
    .finally(() => {
        if (!silent) {
            const refreshBtn = document.getElementById('refreshBtn');
            refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> تحديث';
            refreshBtn.disabled = false;
        }
    });
}

// Update time slots display
function updateTimeSlotsDisplay(data) {
    // Update stats cards
    document.querySelector('.col-md-4:nth-child(1) h2').textContent = data.today.total_booked;
    document.querySelector('.col-md-4:nth-child(2) h2').textContent = data.tomorrow.total_booked;
    document.querySelector('.col-md-4:nth-child(3) h2').textContent = data.day_after.total_booked;
    
    // Update time slots
    ['today', 'tomorrow', 'day_after'].forEach(dayKey => {
        const dayData = data[dayKey];
        const dayContainer = document.querySelector(`[data-day="${dayKey}"]`).closest('.col-lg-4');
        
        // Update badges
        const badges = dayContainer.querySelectorAll('.badge');
        badges[0].innerHTML = `<i class="bi bi-x-circle"></i> ${dayData.total_booked} محجوز`;
        badges[1].innerHTML = `<i class="bi bi-check-circle"></i> ${dayData.total_available} متاح`;
        
        // Update time slots
        dayData.hours_data.forEach(hourData => {
            const slotElement = dayContainer.querySelector(`[data-hour="${hourData.hour}"]`);
            if (slotElement) {
                const isBooked = hourData.is_booked;
                const isUnavailable = hourData.is_unavailable;
                
                // Update slot classes and styling
                if (isBooked) {
                    slotElement.className = 'time-slot-card booked border-danger';
                } else if (isUnavailable) {
                    slotElement.className = 'time-slot-card unavailable border-warning';
                } else {
                    slotElement.className = 'time-slot-card available border-success';
                }
                
                // Update time text color immediately
                const timeText = slotElement.querySelector('.fw-bold');
                if (timeText) {
                    if (isBooked) {
                        timeText.className = 'fw-bold text-danger fs-6';
                    } else if (isUnavailable) {
                        timeText.className = 'fw-bold text-warning fs-6';
                    } else {
                        timeText.className = 'fw-bold text-success fs-6';
                    }
                }
                
                const statusText = slotElement.querySelector('small');
                const toggleBtn = slotElement.querySelector('.toggle-btn');
                
                if (isBooked) {
                    // Booked slot
                    statusText.innerHTML = `<i class="bi bi-x-circle"></i> محجوز`;
                    statusText.className = 'text-danger d-block';
                    
                    // Add customer name if available
                    const customerName = hourData.order?.customer?.name || 'عميل';
                    const existingCustomer = slotElement.querySelector('.text-muted');
                    if (existingCustomer) {
                        existingCustomer.textContent = customerName;
                    } else {
                        const customerSpan = document.createElement('small');
                        customerSpan.className = 'text-muted d-block';
                        customerSpan.style.fontSize = '0.7rem';
                        customerSpan.textContent = customerName;
                        statusText.parentNode.appendChild(customerSpan);
                    }
                    
                    // Remove toggle button for booked slots
                    if (toggleBtn) {
                        toggleBtn.remove();
                    }
                    
                } else if (isUnavailable) {
                    // Unavailable slot
                    statusText.innerHTML = `<i class="bi bi-power"></i> OFF`;
                    statusText.className = 'text-warning d-block';
                    
                    // Add instruction text
                    const instructionSpan = slotElement.querySelector('.text-muted');
                    if (instructionSpan) {
                        instructionSpan.textContent = 'اضغط للتفعيل';
                        instructionSpan.style.fontSize = '0.6rem';
                    } else {
                        const newInstructionSpan = document.createElement('small');
                        newInstructionSpan.className = 'text-muted d-block';
                        newInstructionSpan.style.fontSize = '0.6rem';
                        newInstructionSpan.textContent = 'اضغط للتفعيل';
                        statusText.parentNode.appendChild(newInstructionSpan);
                    }
                    
                    // Update toggle button
                    if (toggleBtn) {
                        toggleBtn.className = 'btn btn-sm toggle-btn btn-warning';
                        toggleBtn.innerHTML = '<i class="bi bi-power"></i>';
                        toggleBtn.title = 'تفعيل الساعة';
                    }
                    
                } else {
                    // Available slot
                    statusText.innerHTML = `<i class="bi bi-check-circle"></i> متاح`;
                    statusText.className = 'text-success d-block';
                    
                    // Remove instruction text if exists
                    const instructionSpan = slotElement.querySelector('.text-muted');
                    if (instructionSpan && instructionSpan.textContent === 'اضغط للتفعيل') {
                        instructionSpan.remove();
                    }
                    
                    // Update toggle button
                    if (toggleBtn) {
                        toggleBtn.className = 'btn btn-sm toggle-btn btn-success';
                        toggleBtn.innerHTML = '<i class="bi bi-check-circle"></i>';
                        toggleBtn.title = 'إيقاف الساعة';
                    }
                }
                
                // Update tooltip
                if (isBooked) {
                    slotElement.setAttribute('data-bs-original-title', `محجوز - ${hourData.order?.customer?.name || 'عميل'}`);
                } else if (isUnavailable) {
                    slotElement.setAttribute('data-bs-original-title', 'غير متاح - اضغط للتفعيل');
                } else {
                    slotElement.setAttribute('data-bs-original-title', 'متاح - اضغط للحجز');
                }
            }
        });
    });
    
    // Reinitialize tooltips after update
    setTimeout(() => {
        initializeTooltips();
    }, 100);
}

// Handle time slot click
function handleTimeSlotClick(dayKey, hour, isBooked, isUnavailable, date) {
    if (isBooked) {
        // Show order details for booked slot
        const slotElement = document.querySelector(`[data-day="${dayKey}"][data-hour="${hour}"]`);
        const orderId = slotElement.getAttribute('data-order-id');
        if (orderId) {
            viewOrderDetails(orderId);
        }
    } else if (isUnavailable) {
        // Show option to enable the slot
        if (confirm(`هل تريد تفعيل الساعة ${hour}:00 لليوم ${date}؟`)) {
            toggleTimeSlot(hour, date);
        }
    } else {
        // Show available slot info
        showNotification(`الساعة ${hour}:00 متاحة للحجز لليوم ${date}`, 'info');
    }
}

// View order details
function viewOrderDetails(orderId) {
    currentOrderId = orderId;
    const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    modal.show();
    
    // Load order details via AJAX
    fetch(`/admin/orders/${orderId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('orderDetailsContent').innerHTML = data.html;
        } else {
            document.getElementById('orderDetailsContent').innerHTML = '<div class="alert alert-danger">حدث خطأ في تحميل التفاصيل</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('orderDetailsContent').innerHTML = '<div class="alert alert-danger">حدث خطأ في تحميل التفاصيل</div>';
    });
}

// Edit order status
function editOrderStatus(orderId) {
    currentOrderId = orderId;
    document.getElementById('orderId').value = orderId;
    
    // Load current status
    fetch(`/admin/orders/${orderId}/status`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('newStatus').value = data.status;
        }
    });
    
    const modal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
    modal.show();
}

// Update order status
function updateOrderStatus() {
    const formData = new FormData(document.getElementById('statusUpdateForm'));
    
    fetch(`/admin/orders/${currentOrderId}/status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('تم تحديث حالة الطلب بنجاح', 'success');
            bootstrap.Modal.getInstance(document.getElementById('statusUpdateModal')).hide();
            refreshTimeSlots(true);
        } else {
            showNotification('حدث خطأ في تحديث الحالة', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('حدث خطأ في تحديث الحالة', 'error');
    });
}

// Cancel order
function cancelOrder(orderId) {
    if (confirm('هل أنت متأكد من إلغاء هذا الطلب؟')) {
        fetch(`/admin/orders/${orderId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('تم إلغاء الطلب بنجاح', 'success');
                refreshTimeSlots(true);
            } else {
                showNotification('حدث خطأ في إلغاء الطلب', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('حدث خطأ في إلغاء الطلب', 'error');
        });
    }
}

// Filter orders by status
function filterOrders() {
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('#ordersTable tbody tr');
    
    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        if (statusFilter === '' || status === statusFilter) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Search orders
function searchOrders() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#ordersTable tbody tr');
    
    rows.forEach(row => {
        const customerName = row.getAttribute('data-customer').toLowerCase();
        if (customerName.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Toggle time slot availability
function toggleTimeSlot(hour, date) {
    const toggleBtn = document.querySelector(`[onclick*="toggleTimeSlot(${hour}, '${date}')"]`);
    const originalContent = toggleBtn.innerHTML;
    const isCurrentlyUnavailable = toggleBtn.classList.contains('btn-warning');
    
    // Show confirmation modal
    showTimeSlotConfirmationModal(hour, date, isCurrentlyUnavailable, () => {
        // User confirmed, proceed with the update
        performTimeSlotToggle(hour, date, toggleBtn, originalContent);
    });
}

// Show confirmation modal for time slot toggle
function showTimeSlotConfirmationModal(hour, date, isCurrentlyUnavailable, onConfirm) {
    const action = isCurrentlyUnavailable ? 'تفعيل' : 'إيقاف';
    const actionIcon = isCurrentlyUnavailable ? 'bi-check-circle' : 'bi-power';
    const actionColor = isCurrentlyUnavailable ? 'success' : 'warning';
    const actionText = isCurrentlyUnavailable ? 'متاح' : 'غير متاح';
    
    const modalHtml = `
        <div class="modal fade" id="timeSlotConfirmModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-${actionColor} text-white border-0">
                        <h5 class="modal-title d-flex align-items-center">
                            <i class="bi ${actionIcon} me-2"></i>
                            تأكيد ${action} الساعة
                        </h5>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="mb-3">
                            <i class="bi ${actionIcon} text-${actionColor}" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="mb-3">هل تريد ${action} الساعة <strong>${hour}:00</strong> لليوم <strong>${date}</strong>؟</h6>
                        <p class="text-muted mb-0">
                            ستصبح الساعة <span class="badge bg-${actionColor === 'success' ? 'success' : 'warning'}">${actionText}</span> 
                            ${isCurrentlyUnavailable ? 'للاستقبال الطلبات' : 'ولن تكون متاحة للحجز'}
                        </p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> إلغاء
                        </button>
                        <button type="button" class="btn btn-${actionColor}" onclick="confirmTimeSlotToggle()">
                            <i class="bi ${actionIcon} me-1"></i> ${action}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('timeSlotConfirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Store the callback function globally
    window.timeSlotConfirmCallback = onConfirm;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('timeSlotConfirmModal'));
    modal.show();
    
    // Auto-hide modal after 10 seconds if no action taken
    setTimeout(() => {
        const modalElement = document.getElementById('timeSlotConfirmModal');
        if (modalElement && modalElement.classList.contains('show')) {
            modal.hide();
        }
    }, 10000);
}

// Confirm time slot toggle
function confirmTimeSlotToggle() {
    if (window.timeSlotConfirmCallback) {
        window.timeSlotConfirmCallback();
    }
    
    // Hide modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('timeSlotConfirmModal'));
    if (modal) {
        modal.hide();
    }
    
    // Clean up
    setTimeout(() => {
        const modalElement = document.getElementById('timeSlotConfirmModal');
        if (modalElement) {
            modalElement.remove();
        }
        delete window.timeSlotConfirmCallback;
    }, 300);
}

// Perform the actual time slot toggle
function performTimeSlotToggle(hour, date, toggleBtn, originalContent) {
    // Show loading state
    toggleBtn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
    toggleBtn.disabled = true;
    
    // Show loading notification
    showNotification(`جاري ${toggleBtn.classList.contains('btn-warning') ? 'تفعيل' : 'إيقاف'} الساعة ${hour}:00...`, 'info');
    
    fetch(`/admin/time-slots/${hour}/toggle?date=${date}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success notification with animation
            showSuccessNotification(data.message, hour, date);
            
            // Update the UI immediately without full refresh
            updateTimeSlotUI(hour, date, data.is_available);
            
            // Force a complete refresh to ensure all states are correct
            setTimeout(() => {
                refreshTimeSlots(true);
            }, 1000);
        } else {
            showNotification(data.message || 'حدث خطأ في تغيير حالة الساعة', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('حدث خطأ في تغيير حالة الساعة', 'error');
    })
    .finally(() => {
        // Restore button state
        toggleBtn.innerHTML = originalContent;
        toggleBtn.disabled = false;
    });
}

// Set time slot status directly
function setTimeSlotStatus(hour, isAvailable) {
    fetch(`/admin/time-slots/${hour}/set-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            is_available: isAvailable
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => {
                refreshTimeSlots(true);
            }, 1000);
        } else {
            showNotification(data.message || 'حدث خطأ في تغيير حالة الساعة', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('حدث خطأ في تغيير حالة الساعة', 'error');
    });
}

// Export time slots
function exportTimeSlots() {
    const data = {
        today: @json($timeSlotsData['today']),
        tomorrow: @json($timeSlotsData['tomorrow']),
        day_after: @json($timeSlotsData['day_after'])
    };
    
    const csvContent = generateCSV(data);
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `time-slots-${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Generate CSV content
function generateCSV(data) {
    let csv = 'Date,Time,Status,Customer,Phone,Services,Total,Order Status\n';
    
    ['today', 'tomorrow', 'day_after'].forEach(dayKey => {
        const dayData = data[dayKey];
        dayData.hours_data.forEach(hourData => {
            const row = [
                dayData.label,
                hourData.label,
                hourData.is_booked ? 'Booked' : 'Available',
                hourData.order?.customer?.name || '',
                hourData.order?.customer?.phone || '',
                hourData.order?.services?.map(s => s.name).join(', ') || '',
                hourData.order?.total || '',
                hourData.order?.status || ''
            ];
            csv += row.map(field => `"${field}"`).join(',') + '\n';
        });
    });
    
    return csv;
}

// Show notification
function showNotification(message, type = 'info') {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Show success notification with animation
function showSuccessNotification(message, hour, date) {
    // Play success sound
    playSuccessSound();
    
    const notification = document.createElement('div');
    notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 350px; border-left: 4px solid #28a745;';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 1.5rem;"></i>
            </div>
            <div class="flex-grow-1">
                <strong>تم بنجاح!</strong><br>
                <small>${message}</small>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Add success animation
    notification.style.transform = 'translateX(100%)';
    notification.style.transition = 'transform 0.3s ease';
    
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }
    }, 4000);
}

// Update time slot UI immediately
function updateTimeSlotUI(hour, date, isAvailable) {
    // Find the time slot element
    const slotElement = document.querySelector(`[data-hour="${hour}"][data-date="${date}"]`);
    if (!slotElement) return;
    
    const toggleBtn = slotElement.querySelector('.toggle-btn');
    const statusText = slotElement.querySelector('small');
    
    if (isAvailable) {
        // Make available
        slotElement.className = 'time-slot-card available border-success';
        
        // Update time text color
        const timeText = slotElement.querySelector('.fw-bold');
        if (timeText) {
            timeText.className = 'fw-bold text-success fs-6';
        }
        
        if (toggleBtn) {
            toggleBtn.className = 'btn btn-sm toggle-btn btn-success';
            toggleBtn.innerHTML = '<i class="bi bi-check-circle"></i>';
            toggleBtn.title = 'إيقاف الساعة';
        }
        
        if (statusText) {
            statusText.innerHTML = '<i class="bi bi-check-circle"></i> متاح';
            statusText.className = 'text-success d-block';
        }
        
        // Remove instruction text if exists
        const instructionSpan = slotElement.querySelector('.text-muted');
        if (instructionSpan && instructionSpan.textContent === 'اضغط للتفعيل') {
            instructionSpan.remove();
        }
        
        // Update tooltip
        slotElement.setAttribute('data-bs-original-title', 'متاح - اضغط للحجز');
        
    } else {
        // Make unavailable
        slotElement.className = 'time-slot-card unavailable border-warning';
        
        // Update time text color
        const timeText = slotElement.querySelector('.fw-bold');
        if (timeText) {
            timeText.className = 'fw-bold text-warning fs-6';
        }
        
        if (toggleBtn) {
            toggleBtn.className = 'btn btn-sm toggle-btn btn-warning';
            toggleBtn.innerHTML = '<i class="bi bi-power"></i>';
            toggleBtn.title = 'تفعيل الساعة';
        }
        
        if (statusText) {
            statusText.innerHTML = '<i class="bi bi-power"></i> OFF';
            statusText.className = 'text-warning d-block';
            
            // Add instruction text
            const instructionSpan = slotElement.querySelector('.text-muted');
            if (instructionSpan) {
                instructionSpan.textContent = 'اضغط للتفعيل';
                instructionSpan.style.fontSize = '0.6rem';
            } else {
                const newInstructionSpan = document.createElement('small');
                newInstructionSpan.className = 'text-muted d-block';
                newInstructionSpan.style.fontSize = '0.6rem';
                newInstructionSpan.textContent = 'اضغط للتفعيل';
                statusText.parentNode.appendChild(newInstructionSpan);
            }
        }
        
        // Update tooltip
        slotElement.setAttribute('data-bs-original-title', 'غير متاح - اضغط للتفعيل');
    }
    
    // Add visual feedback animation
    slotElement.style.transform = 'scale(1.05)';
    slotElement.style.transition = 'transform 0.2s ease';
    
    setTimeout(() => {
        slotElement.style.transform = 'scale(1)';
    }, 200);
    
    // Update stats badges
    updateStatsBadges(date);
}

// Update stats badges for specific date
function updateStatsBadges(date) {
    const dayKey = getDayKeyFromDate(date);
    if (!dayKey) return;
    
    const dayContainer = document.querySelector(`[data-day="${dayKey}"]`).closest('.col-lg-4');
    if (!dayContainer) return;
    
    // Count available, booked, and unavailable slots
    const slots = dayContainer.querySelectorAll('.time-slot-card');
    let availableCount = 0;
    let bookedCount = 0;
    let unavailableCount = 0;
    
    slots.forEach(slot => {
        if (slot.classList.contains('available')) {
            availableCount++;
        } else if (slot.classList.contains('booked')) {
            bookedCount++;
        } else if (slot.classList.contains('unavailable')) {
            unavailableCount++;
        }
    });
    
    // Update badges
    const badges = dayContainer.querySelectorAll('.badge');
    if (badges.length >= 2) {
        badges[0].innerHTML = `<i class="bi bi-x-circle"></i> ${bookedCount} محجوز`;
        badges[1].innerHTML = `<i class="bi bi-check-circle"></i> ${availableCount} متاح`;
    }
    
    // Update main stats cards
    const statsCards = document.querySelectorAll('.col-md-4');
    const dayIndex = ['today', 'tomorrow', 'day_after'].indexOf(dayKey);
    if (statsCards[dayIndex]) {
        const statNumber = statsCards[dayIndex].querySelector('h2');
        if (statNumber) {
            statNumber.textContent = bookedCount;
        }
    }
}

// Get day key from date string
function getDayKeyFromDate(dateString) {
    const today = new Date().toISOString().split('T')[0];
    const tomorrow = new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    const dayAfter = new Date(Date.now() + 2 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    
    if (dateString === today) return 'today';
    if (dateString === tomorrow) return 'tomorrow';
    if (dateString === dayAfter) return 'day_after';
    
    return null;
}

// Enable all time slots for specific date
function enableAllTimeSlotsForDate(date) {
    showBulkActionConfirmationModal(date, true, () => {
        performBulkTimeSlotAction(date, true);
    });
}

// Disable all time slots for specific date
function disableAllTimeSlotsForDate(date) {
    showBulkActionConfirmationModal(date, false, () => {
        performBulkTimeSlotAction(date, false);
    });
}

// Show bulk action confirmation modal
function showBulkActionConfirmationModal(date, enable, onConfirm) {
    const action = enable ? 'تفعيل' : 'إيقاف';
    const actionIcon = enable ? 'bi-check-circle' : 'bi-power';
    const actionColor = enable ? 'success' : 'warning';
    const actionText = enable ? 'متاحة' : 'غير متاحة';
    
    const modalHtml = `
        <div class="modal fade" id="bulkActionConfirmModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-${actionColor} text-white border-0">
                        <h5 class="modal-title d-flex align-items-center">
                            <i class="bi ${actionIcon} me-2"></i>
                            تأكيد ${action} جميع الساعات
                        </h5>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="mb-3">
                            <i class="bi ${actionIcon} text-${actionColor}" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="mb-3">هل تريد ${action} جميع الساعات لليوم <strong>${date}</strong>؟</h6>
                        <p class="text-muted mb-0">
                            ستكون جميع الساعات <span class="badge bg-${actionColor === 'success' ? 'success' : 'warning'}">${actionText}</span> 
                            ${enable ? 'للاستقبال الطلبات' : 'ولن تكون متاحة للحجز'}
                        </p>
                        <div class="alert alert-info mt-3">
                            <small><i class="bi bi-info-circle me-1"></i> سيتم ${action} الساعات من 10:00 صباحاً حتى 11:00 مساءً</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> إلغاء
                        </button>
                        <button type="button" class="btn btn-${actionColor}" onclick="confirmBulkAction()">
                            <i class="bi ${actionIcon} me-1"></i> ${action} جميع الساعات
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('bulkActionConfirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Store the callback function globally
    window.bulkActionConfirmCallback = onConfirm;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('bulkActionConfirmModal'));
    modal.show();
}

// Confirm bulk action
function confirmBulkAction() {
    if (window.bulkActionConfirmCallback) {
        window.bulkActionConfirmCallback();
    }
    
    // Hide modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('bulkActionConfirmModal'));
    if (modal) {
        modal.hide();
    }
    
    // Clean up
    setTimeout(() => {
        const modalElement = document.getElementById('bulkActionConfirmModal');
        if (modalElement) {
            modalElement.remove();
        }
        delete window.bulkActionConfirmCallback;
    }, 300);
}

// Perform bulk time slot action
function performBulkTimeSlotAction(date, enable) {
    const hours = [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
    let completed = 0;
    const total = hours.length;
    const action = enable ? 'تفعيل' : 'إيقاف';
    
    // Show progress notification
    showNotification(`جاري ${action} جميع الساعات لليوم ${date}...`, 'info');
    
    // Create progress indicator
    const progressNotification = document.createElement('div');
    progressNotification.className = 'alert alert-info alert-dismissible fade show position-fixed';
    progressNotification.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 350px;';
    progressNotification.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i class="bi bi-hourglass-split text-info" style="font-size: 1.5rem;"></i>
            </div>
            <div class="flex-grow-1">
                <strong>جاري المعالجة...</strong><br>
                <small>${action} الساعات: <span id="progressCount">0</span>/${total}</small>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         id="progressBar" style="width: 0%"></div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(progressNotification);
    
    hours.forEach(hour => {
        fetch(`/admin/time-slots/${hour}/set-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                is_available: enable,
                date: date
            })
        })
        .then(response => response.json())
        .then(data => {
            completed++;
            
            // Update progress
            const progressCount = document.getElementById('progressCount');
            const progressBar = document.getElementById('progressBar');
            if (progressCount) progressCount.textContent = completed;
            if (progressBar) progressBar.style.width = `${(completed / total) * 100}%`;
            
            if (completed === total) {
                // Remove progress notification
                setTimeout(() => {
                    if (progressNotification.parentNode) {
                        progressNotification.remove();
                    }
                }, 1000);
                
                // Show success notification
                showSuccessNotification(`تم ${action} جميع الساعات لليوم ${date} بنجاح`, null, date);
                
                // Update UI
                setTimeout(() => {
                    refreshTimeSlots(true);
                }, 500);
            }
        })
        .catch(error => {
            console.error(`Error updating hour ${hour}:`, error);
            completed++;
            
            if (completed === total) {
                // Remove progress notification
                setTimeout(() => {
                    if (progressNotification.parentNode) {
                        progressNotification.remove();
                    }
                }, 1000);
                
                showNotification(`تم ${action} معظم الساعات، حدثت بعض الأخطاء البسيطة`, 'warning');
                
                // Update UI
                setTimeout(() => {
                    refreshTimeSlots(true);
                }, 500);
            }
        });
    });
}

// Show advanced time slots management
function showTimeSlotsManagement() {
    // Create modal for advanced management
    const modalHtml = `
        <div class="modal fade" id="timeSlotsManagementModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">إدارة الساعات المتقدمة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>الساعات المتاحة</h6>
                                <div id="availableSlots" class="border p-3 rounded" style="max-height: 300px; overflow-y: auto;">
                                    <!-- Will be populated by JavaScript -->
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>الساعات غير المتاحة</h6>
                                <div id="unavailableSlots" class="border p-3 rounded" style="max-height: 300px; overflow-y: auto;">
                                    <!-- Will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="button" class="btn btn-primary" onclick="saveTimeSlotsChanges()">حفظ التغييرات</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('timeSlotsManagementModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('timeSlotsManagementModal'));
    modal.show();
    
    // Load time slots data
    loadTimeSlotsForManagement();
}

// Load time slots for management modal
function loadTimeSlotsForManagement() {
    fetch('/admin/time-slots/status')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateTimeSlotsManagement(data.data);
            }
        })
        .catch(error => {
            console.error('Error loading time slots:', error);
        });
}

// Populate time slots management modal
function populateTimeSlotsManagement(timeSlots) {
    const availableContainer = document.getElementById('availableSlots');
    const unavailableContainer = document.getElementById('unavailableSlots');
    
    availableContainer.innerHTML = '';
    unavailableContainer.innerHTML = '';
    
    Object.values(timeSlots).forEach(slot => {
        const slotHtml = `
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                <span>${slot.label}</span>
                <button class="btn btn-sm ${slot.is_available ? 'btn-warning' : 'btn-success'}" 
                        onclick="toggleSlotInManagement(${slot.hour})">
                    <i class="bi ${slot.is_available ? 'bi-power' : 'bi-check-circle'}"></i>
                </button>
            </div>
        `;
        
        if (slot.is_available) {
            availableContainer.insertAdjacentHTML('beforeend', slotHtml);
        } else {
            unavailableContainer.insertAdjacentHTML('beforeend', slotHtml);
        }
    });
}

// Toggle slot in management modal
function toggleSlotInManagement(hour) {
    const slotElement = document.querySelector(`[onclick="toggleSlotInManagement(${hour})"]`);
    const isCurrentlyAvailable = slotElement.classList.contains('btn-warning');
    
    setTimeSlotStatus(hour, isCurrentlyAvailable);
    
    // Update UI immediately
    setTimeout(() => {
        loadTimeSlotsForManagement();
    }, 500);
}

// Save time slots changes
function saveTimeSlotsChanges() {
    showNotification('تم حفظ التغييرات بنجاح', 'success');
    bootstrap.Modal.getInstance(document.getElementById('timeSlotsManagementModal')).hide();
    refreshTimeSlots(true);
}

// Add spin animation
const style = document.createElement('style');
style.textContent = `
    .spin {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);
</script>
@endsection
