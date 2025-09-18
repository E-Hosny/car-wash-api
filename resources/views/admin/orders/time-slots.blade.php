@extends('admin.layout')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="header-section mb-4">
                <div class="header-content">
                    <div class="header-text">
                    <h2 class="mb-1">
                        <i class="bi bi-calendar-clock text-primary"></i>
                        {{ __('messages.time_slots_management') }}
                    </h2>
                    <p class="text-muted mb-0">{{ __('messages.time_slots_description') }}</p>
                </div>
                    <div class="header-actions">
                        <div class="action-buttons">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> 
                                <span class="d-none d-sm-inline">{{ __('messages.back_to_orders') }}</span>
                    </a>
                    <button class="btn btn-primary" onclick="refreshTimeSlots()" id="refreshBtn">
                                <i class="bi bi-arrow-clockwise"></i> 
                                <span class="d-none d-sm-inline">{{ __('messages.refresh') }}</span>
                    </button>
                    <button class="btn btn-success" onclick="exportTimeSlots()">
                                <i class="bi bi-download"></i> 
                                <span class="d-none d-sm-inline">{{ __('messages.export') }}</span>
                    </button>
                    <div class="btn-group">
                        <button class="btn btn-warning dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-gear"></i> 
                                    <span class="d-none d-sm-inline">{{ __('messages.manage_hours') }}</span>
                        </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">{{ __('messages.today') }}</h6></li>
                            <li><a class="dropdown-item" href="#" onclick="enableAllTimeSlotsForDate('{{ $timeSlotsData['today']['date_string'] }}')">
                                <i class="bi bi-check-circle text-success"></i> {{ __('messages.enable_all_hours') }}
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="disableAllTimeSlotsForDate('{{ $timeSlotsData['today']['date_string'] }}')">
                                <i class="bi bi-power text-warning"></i> {{ __('messages.disable_all_hours') }}
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">{{ __('messages.tomorrow') }}</h6></li>
                            <li><a class="dropdown-item" href="#" onclick="enableAllTimeSlotsForDate('{{ $timeSlotsData['tomorrow']['date_string'] }}')">
                                <i class="bi bi-check-circle text-success"></i> {{ __('messages.enable_all_hours') }}
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="disableAllTimeSlotsForDate('{{ $timeSlotsData['tomorrow']['date_string'] }}')">
                                <i class="bi bi-power text-warning"></i> {{ __('messages.disable_all_hours') }}
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">{{ __('messages.day_after') }}</h6></li>
                            <li><a class="dropdown-item" href="#" onclick="enableAllTimeSlotsForDate('{{ $timeSlotsData['day_after']['date_string'] }}')">
                                <i class="bi bi-check-circle text-success"></i> {{ __('messages.enable_all_hours') }}
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="disableAllTimeSlotsForDate('{{ $timeSlotsData['day_after']['date_string'] }}')">
                                <i class="bi bi-power text-warning"></i> {{ __('messages.disable_all_hours') }}
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="showTimeSlotsManagement()">
                                <i class="bi bi-list-check"></i> {{ __('messages.advanced_management') }}
                            </a></li>
                        </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4 g-3">
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card bg-gradient-primary text-white shadow-lg stats-card">
                <div class="card-body text-center">
                    <div class="stats-content">
                        <div class="stats-info">
                            <h5 class="card-title mb-1">{{ __('messages.today') }}</h5>
                            <h2 class="mb-0">{{ $timeSlotsData['today']['total_booked'] }}</h2>
                            <small>{{ __('messages.booked_appointments') }}</small>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-calendar-day"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card bg-gradient-success text-white shadow-lg stats-card">
                <div class="card-body text-center">
                    <div class="stats-content">
                        <div class="stats-info">
                            <h5 class="card-title mb-1">{{ __('messages.tomorrow') }}</h5>
                            <h2 class="mb-0">{{ $timeSlotsData['tomorrow']['total_booked'] }}</h2>
                            <small>{{ __('messages.booked_appointments') }}</small>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-calendar-plus"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card bg-gradient-info text-white shadow-lg stats-card">
                <div class="card-body text-center">
                    <div class="stats-content">
                        <div class="stats-info">
                            <h5 class="card-title mb-1">{{ __('messages.day_after') }}</h5>
                            <h2 class="mb-0">{{ $timeSlotsData['day_after']['total_booked'] }}</h2>
                            <small>{{ __('messages.booked_appointments') }}</small>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-calendar-week"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Slots Display -->
    <div class="row g-3" id="timeSlotsContainer">
        @foreach(['today', 'tomorrow', 'day_after'] as $dayKey)
            @php $dayData = $timeSlotsData[$dayKey]; @endphp
            <div class="col-xl-4 col-lg-6 col-md-12 mb-4">
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
                                        <i class="bi bi-x-circle"></i> {{ $dayData['total_booked'] }} {{ __('messages.booked') }}
                                    </span>
                                    <span class="badge bg-light text-dark">
                                        <i class="bi bi-check-circle"></i> {{ $dayData['total_available'] }} {{ __('messages.available') }}
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
                                        data-order-id="{{ $hourData['order']->id ?? '' }}"
                                        data-customer-name="{{ $hourData['order']->customer->name ?? 'عميل' }}"
                                        @if($hourData['is_booked'])
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top"
                                            title="{{ __('messages.booked') }} - {{ $hourData['order']->customer->name ?? __('messages.customer') }} - {{ __('messages.' . $hourData['order']->status) }}"
                                        @elseif($hourData['is_unavailable'])
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top"
                                            title="{{ __('messages.unavailable') }} - {{ __('messages.click_to_enable') }}"
                                        @else
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top"
                                            title="{{ __('messages.available') }} - {{ __('messages.click_to_book') }}"
                                        @endif
                                        onclick="handleTimeSlotClick('{{ $dayKey }}', {{ $hourData['hour'] }}, {{ $hourData['is_booked'] ? 'true' : 'false' }}, {{ $hourData['is_unavailable'] ? 'true' : 'false' }}, '{{ $dayData['date_string'] }}')">
                                        <div class="text-center p-2">
                                            <div class="fw-bold {{ $hourData['is_booked'] ? 'text-danger' : ($hourData['is_unavailable'] ? 'text-warning' : 'text-success') }} fs-6">
                                                {{ $hourData['label'] }}
                                            </div>
                                            @if($hourData['is_booked'])
                                                <small class="text-danger d-block">
                                                    <i class="bi bi-x-circle"></i> {{ __('messages.booked') }}
                                                </small>
                                                @if($hourData['order'])
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">
                                                        {{ $hourData['order']->customer->name ?? __('messages.customer') }}
                                                    </small>
                                                @endif
                                            @elseif($hourData['is_unavailable'])
                                                <small class="text-warning d-block">
                                                    <i class="bi bi-power"></i> {{ __('messages.off') }}
                                                </small>
                                                <small class="text-muted d-block" style="font-size: 0.6rem;">
                                                    {{ __('messages.click_to_enable') }}
                                                </small>
                                            @else
                                                <small class="text-success d-block">
                                                    <i class="bi bi-check-circle"></i> {{ __('messages.available') }}
                                                </small>
                                            @endif
                                        </div>
                                        
                                        <!-- Toggle Button for Admin -->
                                        @if(!$hourData['is_booked'])
                                            <div class="toggle-btn-container">
                                                <button class="btn btn-sm toggle-btn {{ $hourData['is_unavailable'] ? 'btn-warning' : 'btn-success' }}"
                                                        onclick="event.stopPropagation(); toggleTimeSlot({{ $hourData['hour'] }}, '{{ $dayData['date_string'] }}')"
                                                        data-bs-toggle="tooltip" 
                                                        title="{{ $hourData['is_unavailable'] ? __('messages.enable_hour') : __('messages.disable_hour') }}">
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
                    <div class="table-header">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul text-primary"></i>
                            {{ __('messages.booked_orders_details') }}
                        </h5>
                        <div class="table-controls">
                            <div class="table-filters">
                            <select class="form-select form-select-sm" id="statusFilter" onchange="filterOrders()">
                                <option value="">{{ __('messages.all_statuses') }}</option>
                                <option value="pending">{{ __('messages.pending') }}</option>
                                <option value="accepted">{{ __('messages.accepted') }}</option>
                                <option value="in_progress">{{ __('messages.in_progress') }}</option>
                                <option value="completed">{{ __('messages.completed') }}</option>
                                <option value="cancelled">{{ __('messages.cancelled') }}</option>
                            </select>
                            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="{{ __('messages.search') }}" onkeyup="searchOrders()">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="ordersTable">
                            <thead class="table-dark">
                                <tr>
                                    <th class="d-none d-md-table-cell">{{ __('messages.date') }}</th>
                                    <th>{{ __('messages.time') }}</th>
                                    <th>{{ __('messages.order_id') }}</th>
                                    <th>{{ __('messages.customer') }}</th>
                                    <th class="d-none d-lg-table-cell">{{ __('messages.phone') }}</th>
                                    <th class="d-none d-xl-table-cell">{{ __('messages.services') }}</th>
                                    <th>{{ __('messages.amount') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(['today', 'tomorrow', 'day_after'] as $dayKey)
                                    @foreach($timeSlotsData[$dayKey]['orders'] as $order)
                                        <tr data-status="{{ $order->status }}" data-customer="{{ $order->customer->name ?? '' }}" data-order-id="{{ $order->id }}" data-customer-name="{{ $order->customer->name ?? 'عميل' }}">
                                            <td class="d-none d-md-table-cell">
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
                                                <span class="order-id-badge">
                                                    #{{ $order->id }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="customer-info">
                                                    <div class="customer-avatar">
                                                        {{ substr($order->customer->name ?? 'ع', 0, 1) }}
                                                    </div>
                                                    <div class="customer-details">
                                                        <div class="customer-name">{{ $order->customer->name ?? 'غير محدد' }}</div>
                                                        <small class="customer-email d-none d-lg-block">{{ $order->customer->email ?? '' }}</small>
                                                        <small class="customer-phone d-md-none">{{ $order->customer->phone ?? 'غير محدد' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="d-none d-lg-table-cell">{{ $order->customer->phone ?? 'غير محدد' }}</td>
                                            <td class="d-none d-xl-table-cell">
                                                @foreach($order->services as $service)
                                                    <span class="badge bg-secondary me-1 mb-1">{{ $service->name }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <span class="amount">{{ number_format($order->total, 2) }} AED</span>
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
                                                    {{ __('messages.' . $order->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-outline-info btn-sm" 
                                                            onclick="viewOrderDetails({{ $order->id }})"
                                                            data-bs-toggle="tooltip" title="{{ __('messages.view_details') }}">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning btn-sm" 
                                                            onclick="editOrderStatus({{ $order->id }})"
                                                            data-bs-toggle="tooltip" title="{{ __('messages.edit_status') }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm" 
                                                            onclick="cancelOrder({{ $order->id }})"
                                                            data-bs-toggle="tooltip" title="{{ __('messages.cancel_order') }}">
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
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.order_details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ __('messages.loading') }}</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.update_order_status') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="statusUpdateForm">
                    <input type="hidden" id="orderId" name="order_id">
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">{{ __('messages.new_status') }}</label>
                        <select class="form-select form-select-lg" id="newStatus" name="status" required>
                            <option value="pending">{{ __('messages.pending') }}</option>
                            <option value="accepted">{{ __('messages.accepted') }}</option>
                            <option value="in_progress">{{ __('messages.in_progress') }}</option>
                            <option value="completed">{{ __('messages.completed') }}</option>
                            <option value="cancelled">{{ __('messages.cancelled') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="statusNotes" class="form-label">{{ __('messages.notes_optional') }}</label>
                        <textarea class="form-control" id="statusNotes" name="notes" rows="4"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="updateOrderStatus()">{{ __('messages.update') }}</button>
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

/* ===== RESPONSIVE DESIGN ===== */

/* Header Section Responsive */
.header-section {
    padding: 1rem 0;
}

.header-content {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.header-text h2 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.header-text p {
    font-size: 0.9rem;
}

.header-actions {
    width: 100%;
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: flex-start;
}

.action-buttons .btn {
    flex: 1;
    min-width: 120px;
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

.action-buttons .btn-group {
    flex: 1;
    min-width: 140px;
}

/* Stats Cards Responsive */
.stats-card {
    margin-bottom: 1rem;
}

.stats-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 1rem;
}

.stats-info h5 {
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

.stats-info h2 {
    font-size: 2rem;
    margin-bottom: 0.25rem;
}

.stats-info small {
    font-size: 0.8rem;
}

.stats-icon {
    font-size: 2.5rem;
    opacity: 0.8;
}

/* Time Slots Responsive */
.time-slot-day-card {
    margin-bottom: 1.5rem;
}

.day-header {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1rem;
}

.day-info h5 {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.day-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.day-badges .badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.day-icon {
    font-size: 1.5rem;
    opacity: 0.8;
    text-align: center;
}

.time-slots-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    gap: 0.5rem;
    padding: 0.75rem;
}

.time-slot-item {
    width: 100%;
}

.time-slot-card {
    min-height: 70px;
    padding: 0.5rem;
    border-radius: 8px;
}

.time-slot-content {
    text-align: center;
    padding: 0.25rem;
}

.time-label {
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.time-status {
    font-size: 0.7rem;
    margin-bottom: 0.125rem;
}

.time-hint {
    font-size: 0.6rem;
    color: #6c757d;
}

.customer-name {
    font-size: 0.65rem;
    color: #6c757d;
    margin-top: 0.125rem;
}

.toggle-btn-container {
    position: absolute;
    top: 4px;
    right: 4px;
}

.toggle-btn {
    width: 20px;
    height: 20px;
    font-size: 10px;
}

/* Table Responsive */
.table-header {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1rem;
}

.table-controls {
    width: 100%;
}

.table-filters {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.table-filters .form-select,
.table-filters .form-control {
    width: 100%;
}

.customer-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.customer-avatar {
    width: 30px;
    height: 30px;
    background-color: #007bff;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
}

.customer-details {
    flex: 1;
    min-width: 0;
}

.customer-name {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.125rem;
}

.customer-email,
.customer-phone {
    font-size: 0.75rem;
    color: #6c757d;
}

.amount {
    font-weight: 600;
    color: #28a745;
    font-size: 0.9rem;
}

.order-id-badge {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    font-size: 0.85rem;
    padding: 0.4rem 0.6rem;
    border-radius: 6px;
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);
    transition: all 0.3s ease;
}

.order-id-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(23, 162, 184, 0.4);
}

.action-buttons {
    display: flex;
    gap: 0.25rem;
    flex-wrap: wrap;
}

.action-buttons .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

/* Modal Responsive */
.modal-dialog {
    margin: 0.5rem;
    max-width: calc(100% - 1rem);
}

.modal-content {
    border-radius: 12px;
}

.modal-header {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.modal-body {
    padding: 1rem;
}

.modal-footer {
    padding: 1rem;
    border-top: 1px solid #dee2e6;
}

/* Status Update Modal Specific Styles */
#statusUpdateModal .modal-dialog {
    max-width: 600px;
}

#statusUpdateModal .form-select-lg {
    font-size: 1.1rem;
    padding: 0.75rem 1rem;
    min-height: 3rem;
}

#statusUpdateModal .form-select option {
    padding: 0.5rem 1rem;
    font-size: 1rem;
    white-space: nowrap;
}

#statusUpdateModal .form-label {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

#statusUpdateModal .form-control {
    font-size: 1rem;
    padding: 0.75rem;
}

#statusUpdateModal .btn {
    font-size: 1rem;
    padding: 0.75rem 1.5rem;
}

/* Mobile Specific Styles */
@media (max-width: 576px) {
    .container-fluid {
        padding: 0.5rem;
    }
    
    .header-text h2 {
        font-size: 1.25rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .action-buttons .btn,
    .action-buttons .btn-group {
        width: 100%;
        min-width: auto;
    }
    
    .stats-content {
        padding: 1rem 0.5rem;
    }
    
    .stats-info h2 {
        font-size: 1.75rem;
    }
    
    .stats-icon {
        font-size: 2rem;
    }
    
    .time-slots-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 0.375rem;
        padding: 0.5rem;
    }
    
    .time-slot-card {
        min-height: 60px;
        padding: 0.375rem;
    }
    
    .time-label {
        font-size: 0.75rem;
    }
    
    .time-status {
        font-size: 0.65rem;
    }
    
    .time-hint {
        font-size: 0.55rem;
    }
    
    .toggle-btn {
        width: 18px;
        height: 18px;
        font-size: 9px;
    }
    
    .table-header {
        padding: 0.75rem;
    }
    
    .table-filters {
        gap: 0.375rem;
    }
    
    .customer-avatar {
        width: 25px;
        height: 25px;
        font-size: 0.7rem;
    }
    
    .customer-name {
        font-size: 0.8rem;
    }
    
    .amount {
        font-size: 0.8rem;
    }
    
    .order-id-badge {
        font-size: 0.75rem;
        padding: 0.3rem 0.5rem;
    }
    
    .action-buttons .btn {
        padding: 0.2rem 0.4rem;
        font-size: 0.7rem;
    }
    
    .modal-dialog {
        margin: 0.25rem;
        max-width: calc(100% - 0.5rem);
    }
    
    .modal-header,
    .modal-body,
    .modal-footer {
        padding: 0.75rem;
    }
    
    /* Status Update Modal Mobile */
    #statusUpdateModal .modal-dialog {
        margin: 0.25rem;
        max-width: calc(100% - 0.5rem);
    }
    
    #statusUpdateModal .form-select-lg {
        font-size: 1rem;
        padding: 0.5rem 0.75rem;
        min-height: 2.5rem;
    }
    
    #statusUpdateModal .form-select option {
        padding: 0.375rem 0.75rem;
        font-size: 0.9rem;
    }
    
    #statusUpdateModal .btn {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
}

/* Tablet Specific Styles */
@media (min-width: 577px) and (max-width: 768px) {
    .header-content {
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-start;
    }
    
    .header-actions {
        width: auto;
    }
    
    .action-buttons {
        flex-direction: row;
        flex-wrap: wrap;
    }
    
    .stats-content {
        flex-direction: row;
        justify-content: space-between;
        text-align: left;
    }
    
    .time-slots-grid {
        grid-template-columns: repeat(4, 1fr);
    }
    
    .table-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
    
    .table-filters {
        flex-direction: row;
        gap: 0.5rem;
    }
    
    .table-filters .form-select,
    .table-filters .form-control {
        width: auto;
        min-width: 150px;
    }
}

/* Desktop Specific Styles */
@media (min-width: 769px) {
    .header-content {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
    
    .header-actions {
        width: auto;
    }
    
    .action-buttons {
        flex-direction: row;
        gap: 0.5rem;
    }
    
    .action-buttons .btn {
        flex: none;
        min-width: auto;
    }
    
    .stats-content {
        flex-direction: row;
        justify-content: space-between;
        text-align: left;
    }
    
    .day-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
    
    .time-slots-grid {
        grid-template-columns: repeat(5, 1fr);
        gap: 0.5rem;
    }
    
    .time-slot-card {
        min-height: 80px;
    }
    
    .time-label {
        font-size: 0.9rem;
    }
    
    .time-status {
        font-size: 0.75rem;
    }
    
    .time-hint {
        font-size: 0.65rem;
    }
    
    .toggle-btn {
        width: 24px;
        height: 24px;
        font-size: 12px;
    }
    
    .table-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
    
    .table-filters {
        flex-direction: row;
        gap: 0.5rem;
    }
    
    .table-filters .form-select,
    .table-filters .form-control {
        width: auto;
        min-width: 150px;
    }
    
    .modal-dialog {
        margin: 1.75rem auto;
        max-width: 500px;
    }
}

/* Large Desktop Specific Styles */
@media (min-width: 1200px) {
    .time-slots-grid {
        grid-template-columns: repeat(6, 1fr);
    }
    
    .time-slot-card {
        min-height: 85px;
    }
    
    .modal-dialog {
        max-width: 600px;
    }
}

/* Extra Large Desktop Specific Styles */
@media (min-width: 1400px) {
    .time-slots-grid {
        grid-template-columns: repeat(7, 1fr);
    }
    
    .time-slot-card {
        min-height: 90px;
    }
    
    .modal-dialog {
        max-width: 800px;
    }
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
    .time-slot-card:hover {
        transform: none;
    }
    
    .time-slot-card:active {
        transform: scale(0.98);
    }
    
    .btn:hover {
        transform: none;
    }
    
    .btn:active {
        transform: scale(0.98);
    }
    
    .toggle-btn:hover {
        transform: none;
    }
    
    .toggle-btn:active {
        transform: scale(0.9);
    }
    
    /* Increase touch targets */
    .time-slot-card {
        min-height: 80px;
    }
    
    .toggle-btn {
        width: 28px;
        height: 28px;
        font-size: 14px;
    }
    
    .action-buttons .btn {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
}

/* High DPI Display Optimizations */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .time-slot-card {
        border-width: 1px;
    }
    
    .card {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .btn {
        border-width: 1px;
    }
}

/* Print Styles */
@media print {
    .header-actions,
    .action-buttons,
    .toggle-btn-container,
    .action-buttons,
    .modal {
        display: none !important;
    }
    
    .time-slot-card {
        border: 1px solid #000 !important;
        background: white !important;
        color: black !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
    
    .table {
        font-size: 0.8rem;
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
    backdrop-filter: blur(10px);
    border-left: 4px solid;
}

.alert-success {
    background: linear-gradient(135deg, rgba(212, 237, 218, 0.95), rgba(195, 230, 203, 0.95));
    color: #155724;
    border-left-color: #28a745;
}

.alert-danger {
    background: linear-gradient(135deg, rgba(248, 215, 218, 0.95), rgba(245, 198, 203, 0.95));
    color: #721c24;
    border-left-color: #dc3545;
}

.alert-warning {
    background: linear-gradient(135deg, rgba(255, 243, 205, 0.95), rgba(255, 234, 167, 0.95));
    color: #856404;
    border-left-color: #ffc107;
}

.alert-info {
    background: linear-gradient(135deg, rgba(209, 236, 241, 0.95), rgba(190, 229, 235, 0.95));
    color: #0c5460;
    border-left-color: #17a2b8;
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

/* Table Row Animation */
.table tbody tr {
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
    transform: translateX(2px);
}

/* Status Badge Animation */
.badge {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.badge::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.badge:hover::before {
    left: 100%;
}

/* Real-time Indicator */
#realTimeIndicator {
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
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
    
    .table tbody tr:hover {
        transform: none;
    }
}
</style>

<script>
// Global variables
let currentOrderId = null;
let refreshInterval = null;
let currentLocale = '{{ app()->getLocale() }}';

// Translation helper function
function t(key) {
    const translations = {
        'ar': {
            'refresh_btn_text': 'تحديث',
            'updating': 'جاري التحديث...',
            'refresh_success': 'تم تحديث البيانات بنجاح',
            'refresh_error': 'حدث خطأ في تحديث البيانات',
            'status_updated_successfully': 'تم تحديث حالة الطلب بنجاح',
            'status_update_error': 'حدث خطأ في تحديث الحالة',
            'order_cancelled_successfully': 'تم إلغاء الطلب بنجاح',
            'order_cancel_error': 'حدث خطأ في إلغاء الطلب',
            'loading': 'جاري التحميل...',
            'updating': 'جاري التحديث...',
            'booked': 'محجوز',
            'available': 'متاح',
            'unavailable': 'غير متاح',
            'off': 'OFF',
            'click_to_enable': 'اضغط للتفعيل',
            'click_to_book': 'اضغط للحجز',
            'enable_hour': 'تفعيل الساعة',
            'disable_hour': 'إيقاف الساعة',
            'confirm_enable_hour': 'هل تريد تفعيل الساعة',
            'confirm_disable_hour': 'هل تريد إيقاف الساعة',
            'hour_available': 'متاحة للحجز',
            'hour_unavailable': 'ولن تكون متاحة للحجز',
            'hour_enabled_successfully': 'تم تفعيل الساعة بنجاح',
            'hour_disabled_successfully': 'تم إيقاف الساعة بنجاح',
            'hour_toggle_error': 'حدث خطأ في تغيير حالة الساعة',
            'real_time_updates_active': 'تحديث تلقائي نشط',
            'order_status_changed': 'تم تحديث حالة الطلب بنجاح!',
            'order_now': 'أصبح الآن',
            'customer_notified': 'تم إلغاؤه وتم إشعار العميل',
            'enable_all_hours_for_date': 'هل تريد تفعيل جميع الساعات لليوم',
            'disable_all_hours_for_date': 'هل تريد إيقاف جميع الساعات لليوم',
            'all_hours_will_be': 'ستكون جميع الساعات',
            'for_receiving_orders': 'للاستقبال الطلبات',
            'not_available_for_booking': 'ولن تكون متاحة للحجز',
            'hours_from_10_to_11': 'سيتم تفعيل الساعات من 10:00 صباحاً حتى 11:00 مساءً',
            'processing_all_hours': 'جاري المعالجة...',
            'enabling_hours': 'تفعيل الساعات',
            'disabling_hours': 'إيقاف الساعات',
            'hours_processed': 'تم تفعيل جميع الساعات لليوم',
            'most_hours_processed': 'تم تفعيل معظم الساعات، حدثت بعض الأخطاء البسيطة',
            'advanced_hours_management': 'إدارة الساعات المتقدمة',
            'available_slots': 'الساعات المتاحة',
            'unavailable_slots': 'الساعات غير المتاحة',
            'save_changes': 'حفظ التغييرات',
            'changes_saved_successfully': 'تم حفظ التغييرات بنجاح',
            'confirm_cancel': 'هل أنت متأكد من إلغاء هذا الطلب؟',
            'order_cancelled_successfully': 'تم إلغاء الطلب بنجاح',
            'order_cancel_error': 'حدث خطأ في إلغاء الطلب',
            'status_updated_successfully': 'تم تحديث حالة الطلب بنجاح',
            'status_update_error': 'حدث خطأ في تحديث الحالة',
            'refresh_success': 'تم تحديث البيانات بنجاح',
            'refresh_error': 'حدث خطأ في تحديث البيانات',
            'loading': 'جاري التحميل...',
            'updating': 'جاري التحديث...',
            'booked': 'محجوز',
            'available': 'متاح',
            'unavailable': 'غير متاح',
            'off': 'OFF',
            'click_to_enable': 'اضغط للتفعيل',
            'click_to_book': 'اضغط للحجز',
            'enable_hour': 'تفعيل الساعة',
            'disable_hour': 'إيقاف الساعة',
            'confirm_enable_hour': 'هل تريد تفعيل الساعة',
            'confirm_disable_hour': 'هل تريد إيقاف الساعة',
            'hour_available': 'متاحة للحجز',
            'hour_unavailable': 'ولن تكون متاحة للحجز',
            'hour_enabled_successfully': 'تم تفعيل الساعة بنجاح',
            'hour_disabled_successfully': 'تم إيقاف الساعة بنجاح',
            'hour_toggle_error': 'حدث خطأ في تغيير حالة الساعة',
            'real_time_updates_active': 'تحديث تلقائي نشط',
            'order_status_changed': 'تم تحديث حالة الطلب بنجاح!',
            'order_now': 'أصبح الآن',
            'customer_notified': 'تم إلغاؤه وتم إشعار العميل',
            'enable_all_hours_for_date': 'هل تريد تفعيل جميع الساعات لليوم',
            'disable_all_hours_for_date': 'هل تريد إيقاف جميع الساعات لليوم',
            'all_hours_will_be': 'ستكون جميع الساعات',
            'for_receiving_orders': 'للاستقبال الطلبات',
            'not_available_for_booking': 'ولن تكون متاحة للحجز',
            'hours_from_10_to_11': 'سيتم تفعيل الساعات من 10:00 صباحاً حتى 11:00 مساءً',
            'processing_all_hours': 'جاري المعالجة...',
            'enabling_hours': 'تفعيل الساعات',
            'disabling_hours': 'إيقاف الساعات',
            'hours_processed': 'تم تفعيل جميع الساعات لليوم',
            'most_hours_processed': 'تم تفعيل معظم الساعات، حدثت بعض الأخطاء البسيطة',
            'advanced_hours_management': 'إدارة الساعات المتقدمة',
            'available_slots': 'الساعات المتاحة',
            'unavailable_slots': 'الساعات غير المتاحة',
            'save_changes': 'حفظ التغييرات',
            'changes_saved_successfully': 'تم حفظ التغييرات بنجاح'
        },
        'en': {
            'refresh_btn_text': 'Refresh',
            'updating': 'Updating...',
            'refresh_success': 'Data updated successfully',
            'refresh_error': 'Error occurred while updating data',
            'status_updated_successfully': 'Order status updated successfully',
            'status_update_error': 'Error occurred while updating status',
            'order_cancelled_successfully': 'Order cancelled successfully',
            'order_cancel_error': 'Error occurred while cancelling order',
            'loading': 'Loading...',
            'updating': 'Updating...',
            'booked': 'Booked',
            'available': 'Available',
            'unavailable': 'Unavailable',
            'off': 'OFF',
            'click_to_enable': 'Click to Enable',
            'click_to_book': 'Click to Book',
            'enable_hour': 'Enable Hour',
            'disable_hour': 'Disable Hour',
            'confirm_enable_hour': 'Do you want to enable hour',
            'confirm_disable_hour': 'Do you want to disable hour',
            'hour_available': 'available for booking',
            'hour_unavailable': 'and will not be available for booking',
            'hour_enabled_successfully': 'Hour enabled successfully',
            'hour_disabled_successfully': 'Hour disabled successfully',
            'hour_toggle_error': 'Error occurred while changing hour status',
            'real_time_updates_active': 'Real-time updates active',
            'order_status_changed': 'Order status updated successfully!',
            'order_now': 'is now',
            'customer_notified': 'has been cancelled and customer has been notified',
            'enable_all_hours_for_date': 'Do you want to enable all hours for',
            'disable_all_hours_for_date': 'Do you want to disable all hours for',
            'all_hours_will_be': 'All hours will be',
            'for_receiving_orders': 'for receiving orders',
            'not_available_for_booking': 'and will not be available for booking',
            'hours_from_10_to_11': 'Hours will be enabled from 10:00 AM to 11:00 PM',
            'processing_all_hours': 'Processing...',
            'enabling_hours': 'Enabling Hours',
            'disabling_hours': 'Disabling Hours',
            'hours_processed': 'All hours enabled for',
            'most_hours_processed': 'Most hours processed, some minor errors occurred',
            'advanced_hours_management': 'Advanced Hours Management',
            'available_slots': 'Available Slots',
            'unavailable_slots': 'Unavailable Slots',
            'save_changes': 'Save Changes',
            'changes_saved_successfully': 'Changes saved successfully',
            'confirm_cancel': 'Are you sure you want to cancel this order?',
            'order_cancelled_successfully': 'Order cancelled successfully',
            'order_cancel_error': 'Error occurred while cancelling order',
            'status_updated_successfully': 'Order status updated successfully',
            'status_update_error': 'Error occurred while updating status',
            'refresh_success': 'Data updated successfully',
            'refresh_error': 'Error occurred while updating data',
            'loading': 'Loading...',
            'updating': 'Updating...',
            'booked': 'Booked',
            'available': 'Available',
            'unavailable': 'Unavailable',
            'off': 'OFF',
            'click_to_enable': 'Click to Enable',
            'click_to_book': 'Click to Book',
            'enable_hour': 'Enable Hour',
            'disable_hour': 'Disable Hour',
            'confirm_enable_hour': 'Do you want to enable hour',
            'confirm_disable_hour': 'Do you want to disable hour',
            'hour_available': 'available for booking',
            'hour_unavailable': 'and will not be available for booking',
            'hour_enabled_successfully': 'Hour enabled successfully',
            'hour_disabled_successfully': 'Hour disabled successfully',
            'hour_toggle_error': 'Error occurred while changing hour status',
            'real_time_updates_active': 'Real-time updates active',
            'order_status_changed': 'Order status updated successfully!',
            'order_now': 'is now',
            'customer_notified': 'has been cancelled and customer has been notified',
            'enable_all_hours_for_date': 'Do you want to enable all hours for',
            'disable_all_hours_for_date': 'Do you want to disable all hours for',
            'all_hours_will_be': 'All hours will be',
            'for_receiving_orders': 'for receiving orders',
            'not_available_for_booking': 'and will not be available for booking',
            'hours_from_10_to_11': 'Hours will be enabled from 10:00 AM to 11:00 PM',
            'processing_all_hours': 'Processing...',
            'enabling_hours': 'Enabling Hours',
            'disabling_hours': 'Disabling Hours',
            'hours_processed': 'All hours enabled for',
            'most_hours_processed': 'Most hours processed, some minor errors occurred',
            'advanced_hours_management': 'Advanced Hours Management',
            'available_slots': 'Available Slots',
            'unavailable_slots': 'Unavailable Slots',
            'save_changes': 'Save Changes',
            'changes_saved_successfully': 'Changes saved successfully'
        }
    };
    
    return translations[currentLocale] && translations[currentLocale][key] ? translations[currentLocale][key] : key;
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
    startSmartAutoRefresh(); // Use smart auto refresh instead
    initializeSoundEffects();
    initializeRealTimeUpdates();
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

// Initialize real-time updates
function initializeRealTimeUpdates() {
    // Visual indicators removed - no longer showing real-time indicator
    
    // Listen for visibility changes to pause/resume updates
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startSmartAutoRefresh();
            // Refresh immediately when page becomes visible
            setTimeout(() => {
                refreshTimeSlots(true);
            }, 1000);
        }
    });
}

// Real-time indicators function removed - no longer needed

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

// Enhanced auto refresh with smart detection
function startSmartAutoRefresh() {
    refreshInterval = setInterval(function() {
        // Only refresh if no modals are open and user is not actively editing
        const openModals = document.querySelectorAll('.modal.show');
        const activeInputs = document.querySelectorAll('input:focus, textarea:focus, select:focus');
        
        if (openModals.length === 0 && activeInputs.length === 0) {
            refreshTimeSlots(true);
        }
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
    console.log('refreshTimeSlots called with silent:', silent);
    
    if (!silent) {
        const refreshBtn = document.getElementById('refreshBtn');
        refreshBtn.innerHTML = `<i class="bi bi-arrow-clockwise spin"></i> ${t('updating')}`;
        refreshBtn.disabled = true;
    }
    
    console.log('Making fetch request to:', '{{ route("admin.orders.time-slots") }}');
    fetch('{{ route("admin.orders.time-slots") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        console.log('Fetch response received:', response);
        return response.json();
    })
    .then(data => {
        console.log('Parsed data:', data);
        if (data.success) {
            console.log('Data is successful, calling updateTimeSlotsDisplay...');
            updateTimeSlotsDisplay(data.data);
            if (!silent) {
                showNotification(t('refresh_success'), 'success');
            }
        } else {
            console.error('Data success is false:', data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (!silent) {
            showNotification(t('refresh_error'), 'error');
        }
    })
    .finally(() => {
        if (!silent) {
            const refreshBtn = document.getElementById('refreshBtn');
            refreshBtn.innerHTML = `<i class="bi bi-arrow-clockwise"></i> ${t('refresh_btn_text')}`;
            refreshBtn.disabled = false;
        }
    });
}

// Update time slots display
function updateTimeSlotsDisplay(data) {
    console.log('updateTimeSlotsDisplay called with data:', data);
    
    // Update stats cards
    const statsCard1 = document.querySelector('.col-xl-4:nth-child(1) h2');
    const statsCard2 = document.querySelector('.col-xl-4:nth-child(2) h2');
    const statsCard3 = document.querySelector('.col-xl-4:nth-child(3) h2');
    
    console.log('Stats cards found:', {statsCard1, statsCard2, statsCard3});
    
    if (statsCard1) statsCard1.textContent = data.today.total_booked;
    if (statsCard2) statsCard2.textContent = data.tomorrow.total_booked;
    if (statsCard3) statsCard3.textContent = data.day_after.total_booked;
    
    // Update time slots
    ['today', 'tomorrow', 'day_after'].forEach(dayKey => {
        const dayData = data[dayKey];
        console.log(`Processing ${dayKey}:`, dayData);
        
        const dayContainer = document.querySelector(`[data-day="${dayKey}"]`).closest('.col-xl-4');
        console.log(`Day container for ${dayKey}:`, dayContainer);
        
        // Update badges
        const badges = dayContainer.querySelectorAll('.badge');
        badges[0].innerHTML = `<i class="bi bi-x-circle"></i> ${dayData.total_booked} ${t('booked')}`;
        badges[1].innerHTML = `<i class="bi bi-check-circle"></i> ${dayData.total_available} ${t('available')}`;
        
        // Update time slots
        dayData.hours_data.forEach(hourData => {
            const slotElement = dayContainer.querySelector(`[data-hour="${hourData.hour}"]`);
            if (slotElement) {
                const isBooked = hourData.is_booked;
                const isUnavailable = hourData.is_unavailable;
                
                // Update slot classes and styling
                if (isBooked) {
                    // Check if order is completed
                    const orderStatus = hourData.order?.status;
                    if (orderStatus === 'completed') {
                        slotElement.className = 'time-slot-card completed border-success';
                    } else {
                        slotElement.className = 'time-slot-card booked border-danger';
                    }
                } else if (isUnavailable) {
                    slotElement.className = 'time-slot-card unavailable border-warning';
                } else {
                    slotElement.className = 'time-slot-card available border-success';
                }
                
                // Update time text color immediately
                const timeText = slotElement.querySelector('.fw-bold');
                if (timeText) {
                    if (isBooked) {
                        const orderStatus = hourData.order?.status;
                        if (orderStatus === 'completed') {
                            timeText.className = 'fw-bold text-success fs-6';
                        } else {
                            timeText.className = 'fw-bold text-danger fs-6';
                        }
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
                    const orderStatus = hourData.order?.status;
                    if (orderStatus === 'completed') {
                        statusText.innerHTML = `<i class="bi bi-check-circle"></i> ${t('completed')}`;
                        statusText.className = 'text-success d-block';
                    } else {
                        statusText.innerHTML = `<i class="bi bi-x-circle"></i> ${t('booked')}`;
                        statusText.className = 'text-danger d-block';
                    }
                    
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
                        instructionSpan.textContent = t('click_to_enable');
                        instructionSpan.style.fontSize = '0.6rem';
                    } else {
                        const newInstructionSpan = document.createElement('small');
                        newInstructionSpan.className = 'text-muted d-block';
                        newInstructionSpan.style.fontSize = '0.6rem';
                        newInstructionSpan.textContent = t('click_to_enable');
                        statusText.parentNode.appendChild(newInstructionSpan);
                    }
                    
                    // Update toggle button
                    if (toggleBtn) {
                        toggleBtn.className = 'btn btn-sm toggle-btn btn-warning';
                        toggleBtn.innerHTML = '<i class="bi bi-power"></i>';
                        toggleBtn.title = t('enable_hour');
                    }
                    
                } else {
                    // Available slot
                    statusText.innerHTML = `<i class="bi bi-check-circle"></i> ${t('available')}`;
                    statusText.className = 'text-success d-block';
                    
                    // Remove instruction text if exists
                    const instructionSpan = slotElement.querySelector('.text-muted');
                    if (instructionSpan && instructionSpan.textContent === t('click_to_enable')) {
                        instructionSpan.remove();
                    }
                    
                    // Update toggle button
                    if (toggleBtn) {
                        toggleBtn.className = 'btn btn-sm toggle-btn btn-success';
                        toggleBtn.innerHTML = '<i class="bi bi-check-circle"></i>';
                        toggleBtn.title = t('disable_hour');
                    }
                }
                
                // Update tooltip
                if (isBooked) {
                    const orderStatus = hourData.order?.status;
                    if (orderStatus === 'completed') {
                        slotElement.setAttribute('data-bs-original-title', `${t('completed')} - ${hourData.order?.customer?.name || t('customer')}`);
                    } else {
                        slotElement.setAttribute('data-bs-original-title', `${t('booked')} - ${hourData.order?.customer?.name || t('customer')}`);
                    }
                } else if (isUnavailable) {
                    slotElement.setAttribute('data-bs-original-title', `${t('unavailable')} - ${t('click_to_enable')}`);
                } else {
                    slotElement.setAttribute('data-bs-original-title', `${t('available')} - ${t('click_to_book')}`);
                }
            }
        });
    });
    
    // Update orders table
    updateOrdersTable(data);
    
    // Reinitialize tooltips after update
    setTimeout(() => {
        initializeTooltips();
    }, 100);
}

// Update orders table with fresh data
function updateOrdersTable(data) {
    console.log('updateOrdersTable called with data:', data);
    
    const tbody = document.querySelector('#ordersTable tbody');
    console.log('Table tbody found:', tbody);
    if (!tbody) {
        console.error('Table tbody not found!');
        return;
    }
    
    // Clear existing rows
    tbody.innerHTML = '';
    
    // Add new rows
    ['today', 'tomorrow', 'day_after'].forEach(dayKey => {
        const dayData = data[dayKey];
        dayData.orders.forEach(order => {
            // Skip cancelled orders but keep completed orders
            if (order.status === 'cancelled') {
                return;
            }
            const row = document.createElement('tr');
            row.setAttribute('data-status', order.status);
            row.setAttribute('data-customer', order.customer?.name || '');
            row.setAttribute('data-order-id', order.id);
            row.setAttribute('data-customer-name', order.customer?.name || 'عميل');
            
            const badgeClass = {
                'today': 'primary',
                'tomorrow': 'success', 
                'day_after': 'info'
            }[dayKey];
            
            const statusBadgeClass = {
                'pending': 'warning',
                'accepted': 'info',
                'in_progress': 'primary',
                'completed': 'success',
                'cancelled': 'danger'
            }[order.status] || 'secondary';
            
            row.innerHTML = `
                <td class="d-none d-md-table-cell">
                    <span class="badge bg-${badgeClass}">${dayData.label}</span>
                </td>
                <td>
                    <span class="badge bg-secondary">${new Date(order.scheduled_at).toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true})}</span>
                </td>
                <td>
                    <span class="order-id-badge">
                        #${order.id}
                    </span>
                </td>
                <td>
                    <div class="customer-info">
                        <div class="customer-avatar">${(order.customer?.name || 'ع').charAt(0)}</div>
                        <div class="customer-details">
                            <div class="customer-name">${order.customer?.name || 'غير محدد'}</div>
                            <small class="customer-email d-none d-lg-block">${order.customer?.email || ''}</small>
                            <small class="customer-phone d-md-none">${order.customer?.phone || 'غير محدد'}</small>
                        </div>
                    </div>
                </td>
                <td class="d-none d-lg-table-cell">${order.customer?.phone || 'غير محدد'}</td>
                <td class="d-none d-xl-table-cell">
                    ${order.services?.map(service => `<span class="badge bg-secondary me-1 mb-1">${service.name}</span>`).join('') || ''}
                </td>
                <td>
                    <span class="amount">${parseFloat(order.total).toFixed(2)} AED</span>
                </td>
                <td>
                    <span class="badge bg-${statusBadgeClass}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-outline-info btn-sm" onclick="viewOrderDetails(${order.id})" data-bs-toggle="tooltip" title="${t('view_details')}">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-warning btn-sm" onclick="editOrderStatus(${order.id})" data-bs-toggle="tooltip" title="${t('edit_status')}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="cancelOrder(${order.id})" data-bs-toggle="tooltip" title="${t('cancel_order')}">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </td>
            `;
            
            tbody.appendChild(row);
        });
    });
    
    // Reinitialize tooltips for action buttons
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
        if (confirm(`${t('confirm_enable_hour')} ${hour}:00 ${t('for')} ${date}?`)) {
            toggleTimeSlot(hour, date);
        }
    } else {
        // Show available slot info
        showNotification(`${t('hour')} ${hour}:00 ${t('hour_available')} ${t('for')} ${date}`, 'info');
    }
}

// View order details
function viewOrderDetails(orderId) {
    console.log('View order details called with ID:', orderId);
    currentOrderId = orderId;
    showModal('orderDetailsModal');
    
    // Reset content to loading state
    const contentElement = document.getElementById('orderDetailsContent');
    if (contentElement) {
        contentElement.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">جاري التحميل...</span>
                </div>
                <p class="mt-2">جاري تحميل تفاصيل الطلب...</p>
            </div>
        `;
    }
    
    // Load order details via AJAX
    fetch(`/admin/orders/${orderId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success && contentElement) {
            contentElement.innerHTML = data.html;
        } else {
            if (contentElement) {
                contentElement.innerHTML = `<div class="alert alert-danger">خطأ في تحميل تفاصيل الطلب</div>`;
            }
        }
    })
    .catch(error => {
        console.error('Error loading order details:', error);
        if (contentElement) {
            contentElement.innerHTML = `<div class="alert alert-danger">حدث خطأ في تحميل تفاصيل الطلب</div>`;
        }
    });
}

// Edit order status
function editOrderStatus(orderId) {
    console.log('Edit order status called with ID:', orderId);
    currentOrderId = orderId;
    document.getElementById('orderId').value = orderId;
    showModal('statusUpdateModal');
}

// Update order status
function updateOrderStatus() {
    try {
        if (!currentOrderId) {
            showNotification('لم يتم تحديد الطلب', 'error');
            return;
        }
        
        const formElement = document.getElementById('statusUpdateForm');
        const statusElement = document.getElementById('newStatus');
        
        if (!formElement || !statusElement) {
            showNotification('خطأ في العثور على نموذج التحديث', 'error');
            return;
        }
        
        const formData = new FormData(formElement);
        const newStatus = statusElement.value;
        const statusText = statusElement.selectedOptions[0]?.text || newStatus;
        
        // Show loading state
        const updateBtn = document.querySelector('#statusUpdateModal .btn-warning');
        if (!updateBtn) {
            showNotification('خطأ في العثور على زر التحديث', 'error');
            return;
        }
        
        const originalBtnText = updateBtn.innerHTML;
        updateBtn.innerHTML = '<i class="bi bi-hourglass-split spin"></i> جاري التحديث...';
        updateBtn.disabled = true;
        
        fetch(`/admin/orders/${currentOrderId}/status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success notification with animation
                showStatusUpdateSuccessNotification(currentOrderId, statusText);
                
                // Update the UI immediately
                updateOrderStatusInUI(currentOrderId, newStatus, statusText);
                
                // Close modal
                hideModal('statusUpdateModal');
                
                // Refresh data after a short delay to ensure consistency
                setTimeout(() => {
                    refreshTimeSlots(true);
                }, 1000);
            } else {
                showNotification(data.message || 'حدث خطأ في تحديث الحالة', 'error');
            }
        })
        .catch(error => {
            console.error('Error updating order status:', error);
            showNotification('حدث خطأ في تحديث الحالة', 'error');
        })
        .finally(() => {
            // Restore button state
            updateBtn.innerHTML = originalBtnText;
            updateBtn.disabled = false;
        });
    } catch (error) {
        console.error('Error in updateOrderStatus:', error);
        showNotification('حدث خطأ غير متوقع في تحديث الحالة', 'error');
    }
}

// Show success notification for status update
function showStatusUpdateSuccessNotification(orderId, statusText) {
    // Play success sound
    playSuccessSound();
    
    const notification = document.createElement('div');
    notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 400px; border-left: 4px solid #28a745;';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 1.5rem;"></i>
            </div>
            <div class="flex-grow-1">
                <strong>تم تحديث حالة الطلب بنجاح!</strong><br>
                <small>الطلب #${orderId} أصبح الآن: <span class="badge bg-success">${statusText}</span></small>
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
    }, 5000);
}

// Update order status in UI immediately
function updateOrderStatusInUI(orderId, newStatus, statusText) {
    // Find the order row in the table
    const orderRow = document.querySelector(`tr[data-order-id="${orderId}"]`);
    if (!orderRow) {
        // Try to find by order ID in the table
        const rows = document.querySelectorAll('#ordersTable tbody tr');
        for (let row of rows) {
            const editBtn = row.querySelector(`button[onclick*="editOrderStatus(${orderId})"]`);
            if (editBtn) {
                orderRow = row;
                break;
            }
        }
    }
    
    if (orderRow) {
        // Update the status badge
        const statusCell = orderRow.querySelector('td:nth-child(7)'); // Status column
        if (statusCell) {
            const badgeClass = getStatusBadgeClass(newStatus);
            statusCell.innerHTML = `<span class="badge bg-${badgeClass}">${statusText}</span>`;
            
            // Update data attribute
            orderRow.setAttribute('data-status', newStatus);
            
            // Add visual feedback animation
            statusCell.style.transform = 'scale(1.1)';
            statusCell.style.transition = 'transform 0.3s ease';
            
            setTimeout(() => {
                statusCell.style.transform = 'scale(1)';
            }, 300);
            
            // If status is completed, add special styling
            if (newStatus === 'completed') {
                orderRow.style.backgroundColor = '#f8f9fa';
                orderRow.style.borderLeft = '4px solid #28a745';
            }
        }
        
        // Update time slot card if it exists
        updateTimeSlotCardStatus(orderId, newStatus);
        
        // Add success animation to the entire row
        orderRow.style.backgroundColor = '#d4edda';
        orderRow.style.transition = 'background-color 0.5s ease';
        
        setTimeout(() => {
            orderRow.style.backgroundColor = '';
        }, 2000);
    }
}

// Get status badge class based on status
function getStatusBadgeClass(status) {
    const badgeClasses = {
        'pending': 'warning',
        'accepted': 'info',
        'in_progress': 'primary',
        'completed': 'success',
        'cancelled': 'danger'
    };
    return badgeClasses[status] || 'secondary';
}

// Update time slot card status
function updateTimeSlotCardStatus(orderId, newStatus) {
    // Find the time slot card that contains this order
    const timeSlotCards = document.querySelectorAll('.time-slot-card');
    timeSlotCards.forEach(card => {
        const cardOrderId = card.getAttribute('data-order-id');
        if (cardOrderId == orderId) {
            // Update tooltip with new status
            const customerName = card.getAttribute('data-customer-name') || 'عميل';
            card.setAttribute('data-bs-original-title', `${t('booked')} - ${customerName} - ${newStatus}`);
            
            // Update customer name display if exists
            const customerSpan = card.querySelector('.text-muted');
            if (customerSpan && customerSpan.textContent !== t('click_to_enable')) {
                customerSpan.textContent = customerName;
            }
            
            // Add visual feedback
            card.style.transform = 'scale(1.02)';
            card.style.transition = 'transform 0.3s ease';
            
            setTimeout(() => {
                card.style.transform = 'scale(1)';
            }, 300);
        }
    });
}

// Cancel order
function cancelOrder(orderId) {
    // Show enhanced confirmation modal
    showCancelOrderConfirmationModal(orderId);
}

// Show cancel order confirmation modal
function showCancelOrderConfirmationModal(orderId) {
    const modalHtml = `
        <div class="modal fade" id="cancelOrderConfirmModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-danger text-white border-0">
                        <h5 class="modal-title d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            تأكيد إلغاء الطلب
                        </h5>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="mb-3">
                            <i class="bi bi-x-circle text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="mb-3">هل أنت متأكد من إلغاء الطلب #<strong>${orderId}</strong>؟</h6>
                        <p class="text-muted mb-0">
                            سيتم إلغاء الطلب نهائياً ولن يمكن استرداده
                        </p>
                        <div class="alert alert-warning mt-3">
                            <small><i class="bi bi-info-circle me-1"></i> سيتم إشعار العميل بإلغاء الطلب</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> إلغاء
                        </button>
                        <button type="button" class="btn btn-danger" onclick="confirmCancelOrder(${orderId})">
                            <i class="bi bi-trash me-1"></i> تأكيد الإلغاء
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('cancelOrderConfirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('cancelOrderConfirmModal'));
    modal.show();
}

// Confirm cancel order
function confirmCancelOrder(orderId) {
    // Hide modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('cancelOrderConfirmModal'));
    if (modal) {
        modal.hide();
    }
    
    // Show loading notification
    showNotification(`جاري إلغاء الطلب #${orderId}...`, 'info');
    
    fetch(`/admin/orders/${orderId}/cancel`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success notification
            showCancelOrderSuccessNotification(orderId);
            
            // Update UI immediately
            updateOrderStatusInUI(orderId, 'cancelled', 'ملغي');
            
            // Refresh data after a short delay
            setTimeout(() => {
                refreshTimeSlots(true);
            }, 1000);
        } else {
            showNotification(t('order_cancel_error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(t('order_cancel_error'), 'error');
    })
    .finally(() => {
        // Clean up modal
        setTimeout(() => {
            const modalElement = document.getElementById('cancelOrderConfirmModal');
            if (modalElement) {
                modalElement.remove();
            }
        }, 300);
    });
}

// Show cancel order success notification
function showCancelOrderSuccessNotification(orderId) {
    const notification = document.createElement('div');
    notification.className = 'alert alert-warning alert-dismissible fade show position-fixed';
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 400px; border-left: 4px solid #ffc107;';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i class="bi bi-check-circle-fill text-warning" style="font-size: 1.5rem;"></i>
            </div>
            <div class="flex-grow-1">
                <strong>تم إلغاء الطلب بنجاح!</strong><br>
                <small>الطلب #${orderId} تم إلغاؤه وتم إشعار العميل</small>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Add animation
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
    }, 5000);
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
    const actionText = isCurrentlyUnavailable ? 'متاحة' : 'غير متاحة';
    
    // Create modal HTML
    const modalHtml = `
        <div class="modal" id="timeSlotConfirmModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-${actionColor} text-white">
                        <h5 class="modal-title d-flex align-items-center">
                            <i class="bi ${actionIcon} me-2"></i>
                            تأكيد ${action} الساعة
                        </h5>
                        <button type="button" class="btn-close btn-close-white" onclick="hideTimeSlotModal()">&times;</button>
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
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary me-2" onclick="hideTimeSlotModal()">
                            <i class="bi bi-x-circle me-1"></i> إلغاء
                        </button>
                        <button type="button" class="btn btn-${actionColor}" onclick="confirmTimeSlotAction()">
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
    showModal('timeSlotConfirmModal');
}

// Hide time slot modal
function hideTimeSlotModal() {
    hideModal('timeSlotConfirmModal');
    setTimeout(() => {
        const modalElement = document.getElementById('timeSlotConfirmModal');
        if (modalElement) {
            modalElement.remove();
        }
    }, 300);
}

// Confirm time slot action
function confirmTimeSlotAction() {
    if (window.timeSlotConfirmCallback) {
        window.timeSlotConfirmCallback();
    }
    hideTimeSlotModal();
}

// Confirm time slot toggle (legacy function)
function confirmTimeSlotToggle() {
    confirmTimeSlotAction();
}

// Perform the actual time slot toggle
function performTimeSlotToggle(hour, date, toggleBtn, originalContent) {
    // Show loading state
    toggleBtn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
    toggleBtn.disabled = true;
    
    // Show loading notification
    showNotification(`جاري ${toggleBtn.classList.contains('btn-warning') ? t('enabling_hours') : t('disabling_hours')} ${t('hour')} ${hour}:00...`, 'info');
    
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
            showNotification(data.message || t('hour_toggle_error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(t('hour_toggle_error'), 'error');
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
            showNotification(data.message || t('hour_toggle_error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(t('hour_toggle_error'), 'error');
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
            toggleBtn.title = t('disable_hour');
        }
        
        if (statusText) {
            statusText.innerHTML = `<i class="bi bi-check-circle"></i> ${t('available')}`;
            statusText.className = 'text-success d-block';
        }
        
        // Remove instruction text if exists
        const instructionSpan = slotElement.querySelector('.text-muted');
        if (instructionSpan && instructionSpan.textContent === t('click_to_enable')) {
            instructionSpan.remove();
        }
        
        // Update tooltip
        slotElement.setAttribute('data-bs-original-title', `${t('available')} - ${t('click_to_book')}`);
        
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
    
    // Create modal HTML
    const modalHtml = `
        <div class="modal" id="bulkActionConfirmModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-${actionColor} text-white">
                        <h5 class="modal-title d-flex align-items-center">
                            <i class="bi ${actionIcon} me-2"></i>
                            تأكيد ${action} جميع الساعات
                        </h5>
                        <button type="button" class="btn-close btn-close-white" onclick="hideBulkActionModal()">&times;</button>
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
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary me-2" onclick="hideBulkActionModal()">
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
    showModal('bulkActionConfirmModal');
}

// Hide bulk action modal
function hideBulkActionModal() {
    hideModal('bulkActionConfirmModal');
    setTimeout(() => {
        const modalElement = document.getElementById('bulkActionConfirmModal');
        if (modalElement) {
            modalElement.remove();
        }
    }, 300);
}

// Confirm bulk action
function confirmBulkAction() {
    if (window.bulkActionConfirmCallback) {
        window.bulkActionConfirmCallback();
    }
    hideBulkActionModal();
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
                    console.log('Calling refreshTimeSlots after successful bulk action...');
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
                    console.log('Calling refreshTimeSlots after bulk action with errors...');
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

// Simple modal functions
function showModal(modalId) {
    console.log('Attempting to show modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        console.log('Modal shown successfully:', modalId);
    } else {
        console.error('Modal not found:', modalId);
    }
}

function hideModal(modalId) {
    console.log('Attempting to hide modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');
        console.log('Modal hidden successfully:', modalId);
    } else {
        console.error('Modal not found:', modalId);
    }
}

// Add spin animation and modal enhancements
const style = document.createElement('style');
style.textContent = `
    .spin {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    /* Simple Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    
    .modal.show {
        display: block !important;
    }
    
    .modal-dialog {
        position: relative;
        width: auto;
        margin: 1.75rem auto;
        max-width: 500px;
    }
    
    .modal-content {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        background-color: #fff;
        border: 1px solid rgba(0,0,0,.2);
        border-radius: 0.3rem;
        box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,.5);
    }
    
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
    }
    
    .modal-body {
        position: relative;
        flex: 1 1 auto;
        padding: 1rem;
    }
    
    .modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 1rem;
        border-top: 1px solid #dee2e6;
    }
    
    .btn-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
    }
    
    .spinner-border {
        width: 2rem;
        height: 2rem;
    }
    
    /* Completed orders styling */
    .time-slot-card.completed {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border: 2px solid #28a745 !important;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
    }
    
    .time-slot-card.completed:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
    }
    
    /* Beautiful Modal Styles */
    .modal-content {
        border-radius: 15px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
    }
    
    .modal-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
    }
    
    .modal-body {
        padding: 2rem;
        background: rgba(255, 255, 255, 0.95);
    }
    
    .modal-footer {
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        background: rgba(255, 255, 255, 0.95);
    }
    
    .btn-close-white {
        color: white;
        opacity: 0.8;
    }
    
    .btn-close-white:hover {
        opacity: 1;
    }
    
    .modal.show {
        display: block !important;
        animation: modalFadeIn 0.3s ease;
    }
    
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
`;
document.head.appendChild(style);

// Simple event listeners for modal close buttons
document.addEventListener('DOMContentLoaded', function() {
    console.log('Setting up modal event listeners');
    
    // Test modal functionality
    console.log('Testing modal elements:');
    console.log('orderDetailsModal:', document.getElementById('orderDetailsModal'));
    console.log('statusUpdateModal:', document.getElementById('statusUpdateModal'));
    console.log('cancelOrderModal:', document.getElementById('cancelOrderModal'));
    
    // Add click listeners for modal close buttons
    const closeButtons = document.querySelectorAll('[data-bs-dismiss="modal"]');
    console.log('Found close buttons:', closeButtons.length);
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('Close button clicked');
            const modal = this.closest('.modal');
            if (modal) {
                hideModal(modal.id);
            }
        });
    });
    
    // Add ESC key listener
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                hideModal(openModal.id);
            }
        }
    });
    
    // Test function
    window.testModal = function() {
        console.log('Testing modal...');
        showModal('orderDetailsModal');
    };
    
    // Test all functions
    window.testAllFunctions = function() {
        console.log('Testing all functions...');
        console.log('viewOrderDetails:', typeof viewOrderDetails);
        console.log('editOrderStatus:', typeof editOrderStatus);
        console.log('cancelOrder:', typeof cancelOrder);
        console.log('showModal:', typeof showModal);
        console.log('hideModal:', typeof hideModal);
    };
    
});

// Cancel order function
function cancelOrder(orderId) {
    console.log('Cancel order called with ID:', orderId);
    currentOrderId = orderId;
    showModal('cancelOrderModal');
}

// Confirm cancel order
function confirmCancelOrder() {
    if (!currentOrderId) {
        showNotification('لم يتم تحديد الطلب', 'error');
        return;
    }
    
    try {
        // Show loading state
        const cancelBtn = document.querySelector('#cancelOrderModal .btn-danger');
        if (!cancelBtn) {
            showNotification('خطأ في العثور على زر الإلغاء', 'error');
            return;
        }
        
        const originalBtnText = cancelBtn.innerHTML;
        cancelBtn.innerHTML = '<i class="bi bi-hourglass-split spin"></i> جاري الإلغاء...';
        cancelBtn.disabled = true;
        
        fetch(`/admin/orders/${currentOrderId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('تم إلغاء الطلب بنجاح', 'success');
                
                // Close modal
                hideModal('cancelOrderModal');
                
                // Refresh data
                setTimeout(() => {
                    refreshTimeSlots(true);
                }, 1000);
            } else {
                showNotification(data.message || 'حدث خطأ في إلغاء الطلب', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('حدث خطأ في إلغاء الطلب', 'error');
        })
        .finally(() => {
            // Restore button state
            cancelBtn.innerHTML = originalBtnText;
            cancelBtn.disabled = false;
        });
    } catch (error) {
        console.error('Error in confirmCancelOrder:', error);
        showNotification('حدث خطأ غير متوقع', 'error');
    }
}

// Update time slot card status
function updateTimeSlotCardStatus(orderId, newStatus) {
    // Find the time slot card that contains this order
    const timeSlotCards = document.querySelectorAll('.time-slot-card');
    timeSlotCards.forEach(card => {
        const cardOrderId = card.getAttribute('data-order-id');
        if (cardOrderId == orderId) {
            if (newStatus === 'completed') {
                card.className = 'time-slot-card completed border-success';
                
                // Update status text
                const statusText = card.querySelector('small');
                if (statusText) {
                    statusText.innerHTML = `<i class="bi bi-check-circle"></i> ${t('completed')}`;
                    statusText.className = 'text-success d-block';
                }
                
                // Update time text color
                const timeText = card.querySelector('.fw-bold');
                if (timeText) {
                    timeText.className = 'fw-bold text-success fs-6';
                }
                
                // Update tooltip
                card.setAttribute('data-bs-original-title', `${t('completed')} - ${card.getAttribute('data-customer-name') || t('customer')}`);
            }
        }
    });
}

</script>

<!-- Order Details Modal -->
<div class="modal" id="orderDetailsModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل الطلب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="orderDetailsContent" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                    <p class="mt-2">جاري تحميل تفاصيل الطلب...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal" id="statusUpdateModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تحديث حالة الطلب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="statusUpdateForm">
                    <input type="hidden" id="orderId" name="order_id">
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">الحالة الجديدة</label>
                        <select class="form-select" id="newStatus" name="status" required>
                            <option value="pending">في الانتظار</option>
                            <option value="accepted">مقبول</option>
                            <option value="in_progress">قيد التنفيذ</option>
                            <option value="completed">مكتمل</option>
                            <option value="cancelled">ملغي</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        سيتم إشعار العميل بالتغيير في حالة الطلب
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-warning" onclick="updateOrderStatus()">تحديث الحالة</button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Order Modal -->
<div class="modal" id="cancelOrderModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إلغاء الطلب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                </div>
                <h6 class="mb-3">هل أنت متأكد من إلغاء هذا الطلب؟</h6>
                <p class="text-muted mb-0">
                    سيتم إلغاء الطلب وإشعار العميل بالتغيير. لا يمكن التراجع عن هذا الإجراء.
                </p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-danger" onclick="confirmCancelOrder()">تأكيد الإلغاء</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Confirmation Modal -->
<div class="modal" id="bulkActionConfirmModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تأكيد الإجراء الجماعي</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="bi bi-gear text-info" style="font-size: 3rem;"></i>
                </div>
                <h6 class="mb-3" id="bulkActionTitle">تأكيد الإجراء</h6>
                <p class="text-muted mb-0" id="bulkActionDescription">
                    سيتم تطبيق هذا الإجراء على جميع الساعات المحددة
                </p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-info" onclick="confirmBulkAction()">تأكيد</button>
            </div>
        </div>
    </div>
</div>

@endsection
