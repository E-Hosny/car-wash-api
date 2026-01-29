@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4 text-center">{{ __('messages.order_list') }}</h3>

    @if(isset($serviceStats) && count($serviceStats) > 0)
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie"></i> إحصائيات الخدمات / Services Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <strong>إجمالي الطلبات:</strong> <span class="badge bg-secondary">{{ $totalOrders }}</span>
                    </div>
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">الخدمة / Service</th>
                                        <th class="text-center">عدد المرات / Count</th>
                                        <th class="text-center">النسبة المئوية / Percentage</th>
                                        <th class="text-center">شريط التقدم / Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($serviceStats as $stat)
                                        <tr>
                                            <td class="text-center">
                                                <strong>🧼 {{ $stat['name'] }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">{{ $stat['count'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success">{{ $stat['percentage'] }}%</span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated 
                                                        @if($stat['percentage'] >= 50) bg-success
                                                        @elseif($stat['percentage'] >= 25) bg-warning
                                                        @else bg-info
                                                        @endif" 
                                                        role="progressbar" 
                                                        style="width: {{ $stat['percentage'] }}%"
                                                        aria-valuenow="{{ $stat['percentage'] }}" 
                                                        aria-valuemin="0" 
                                                        aria-valuemax="100">
                                                        {{ $stat['percentage'] }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <table class="table table-bordered table-striped text-center">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>{{ __('messages.customer') }}</th>
                <th>{{ __('messages.provider') }}</th>
                <th>{{ __('messages.location') }}</th>
                <th>{{ __('messages.services') }}</th>
                <th>{{ __('messages.total') }}</th>
                <th>{{ __('messages.status') }}</th>
                <th>{{ __('messages.scheduled_at') }}</th>
                <th>{{ __('messages.date') }}</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->customer->name ?? '-' }}</td>
                    <td>{{ $order->provider->name ?? '-' }}</td>
                    <td>{{ $order->address ?? 'غير محدد' }}</td>
                    <td style="min-width: 200px; white-space: normal;">
                        @php
                            // جمع جميع الخدمات من مصادر مختلفة
                            $allServices = collect();
                            
                            // الخدمات المباشرة للطلب (للطلبات العادية)
                            if ($order->services && $order->services->count() > 0) {
                                $allServices = $allServices->merge($order->services);
                            }
                            
                            // الخدمات من OrderCars (للطلبات متعددة السيارات)
                            if ($order->orderCars && $order->orderCars->count() > 0) {
                                foreach ($order->orderCars as $orderCar) {
                                    if ($orderCar->services && $orderCar->services->count() > 0) {
                                        $allServices = $allServices->merge($orderCar->services);
                                    }
                                }
                            }
                            
                            // إزالة التكرارات بناءً على service ID
                            $allServices = $allServices->unique('id');
                        @endphp
                        
                        @if($allServices->count() > 0)
                            <div class="d-flex flex-wrap gap-2 justify-content-center">
                                @foreach($allServices as $service)
                                    <span class="text-dark">🧼 {{ $service->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ number_format($order->total, 2) }} AED</td>

                    <td>
                        <select class="form-select form-select-sm status-select" 
                                data-order-id="{{ $order->id }}" 
                                data-current-status="{{ $order->status }}"
                                style="min-width: 140px;">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>
                                {{ __('messages.pending') }}
                            </option>
                            <option value="accepted" {{ $order->status === 'accepted' ? 'selected' : '' }}>
                                {{ __('messages.accepted') }}
                            </option>
                            <option value="in_progress" {{ $order->status === 'in_progress' ? 'selected' : '' }}>
                                {{ __('messages.in_progress') }}
                            </option>
                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>
                                {{ __('messages.completed') }}
                            </option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>
                                {{ __('messages.cancelled') }}
                            </option>
                        </select>
                    </td>
                    <td>
                        @if($order->scheduled_at)
                            {{ \Carbon\Carbon::parse($order->scheduled_at)->format('Y-m-d H:i') }}
                        @else
                            <span class="text-muted">غير محدد</span>
                        @endif
                    </td>
                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                    <td>
                        @if($order->status !== 'completed')
                            <button class="btn btn-success btn-sm mark-completed-btn" 
                                    data-order-id="{{ $order->id }}"
                                    title="Mark as Completed (Test Rating Notification)">
                                <i class="bi bi-check-circle"></i> Mark Completed
                            </button>
                        @else
                            <span class="badge bg-success">Completed</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">{{ __('messages.no_orders') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<style>
    .status-select {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .status-select:hover {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    .status-select:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .status-updating {
        position: relative;
    }
    .status-updating::after {
        content: '';
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 12px;
        height: 12px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #0d6efd;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: translateY(-50%) rotate(0deg); }
        100% { transform: translateY(-50%) rotate(360deg); }
    }
    .alert-message {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1050;
        min-width: 300px;
        animation: slideIn 0.3s ease;
    }
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelects = document.querySelectorAll('.status-select');
    
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const orderId = this.getAttribute('data-order-id');
            const currentStatus = this.getAttribute('data-current-status');
            const newStatus = this.value;
            
            // إذا كانت الحالة نفسها، لا تفعل شيء
            if (newStatus === currentStatus) {
                return;
            }
            
            // تعطيل القائمة المنسدلة أثناء التحديث
            this.disabled = true;
            this.classList.add('status-updating');
            
            // إرسال طلب AJAX
            fetch(`/admin/orders/${orderId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: newStatus,
                    notes: ''
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // تحديث الحالة الحالية
                    select.setAttribute('data-current-status', newStatus);
                    
                    // عرض رسالة نجاح
                    showAlert('success', data.message || 'تم تحديث حالة الطلب بنجاح');
                } else {
                    // إرجاع القائمة المنسدلة للحالة السابقة
                    select.value = currentStatus;
                    showAlert('danger', data.message || 'حدث خطأ في تحديث الحالة');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // إرجاع القائمة المنسدلة للحالة السابقة
                select.value = currentStatus;
                showAlert('danger', 'حدث خطأ في تحديث الحالة');
            })
            .finally(() => {
                // إعادة تفعيل القائمة المنسدلة
                select.disabled = false;
                select.classList.remove('status-updating');
            });
        });
    });
    
    function showAlert(type, message) {
        // إزالة أي رسالة سابقة
        const existingAlert = document.querySelector('.alert-message');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // إنشاء رسالة جديدة
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show alert-message`;
        alert.setAttribute('role', 'alert');
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(alert);
        
        // إزالة الرسالة تلقائياً بعد 3 ثوان
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 300);
        }, 3000);
    }
    
    // Handle "Mark as Completed" button clicks
    document.querySelectorAll('.mark-completed-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            const button = this;
            
            if (!confirm('Are you sure you want to mark this order as completed? This will send a rating notification to the customer.')) {
                return;
            }
            
            // Disable button and show loading
            button.disabled = true;
            const originalText = button.innerHTML;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
            
            // Send request to mark as completed
            fetch(`/admin/orders/${orderId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: 'completed',
                    notes: 'Marked as completed for testing'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Order marked as completed! Rating notification sent.');
                    // Reload page after 1 second to show updated status
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    button.disabled = false;
                    button.innerHTML = originalText;
                    showAlert('danger', data.message || 'Error updating order status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.disabled = false;
                button.innerHTML = originalText;
                showAlert('danger', 'Error updating order status');
            });
        });
    });
});
</script>
@endsection
