@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">التقييمات / Ratings</h3>
        <div class="text-muted">
            <i class="fas fa-star text-warning"></i> 
            إجمالي التقييمات: {{ $ratings->total() }}
        </div>
    </div>

    @if($ratings->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover text-center">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">النجوم</th>
                        <th class="text-center">التعليق</th>
                        <th class="text-center">رقم الطلب</th>
                        <th class="text-center">الخدمات</th>
                        <th class="text-center">اسم العامل</th>
                        <th class="text-center">اسم العميل</th>
                        <th class="text-center">تاريخ الطلب</th>
                        <th class="text-center">الوقت المجدول</th>
                        <th class="text-center">تاريخ التقييم</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ratings as $rating)
                        <tr>
                            <td class="text-center">{{ $rating->id }}</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $rating->rating)
                                            <i class="fas fa-star text-warning"></i>
                                        @else
                                            <i class="far fa-star text-secondary"></i>
                                        @endif
                                    @endfor
                                    <span class="ms-2 text-muted">({{ $rating->rating }}/5)</span>
                                </div>
                            </td>
                            <td class="text-center" style="max-width: 400px; white-space: normal; word-wrap: break-word;">
                                @if($rating->comment)
                                    <div style="text-align: right; padding: 5px;">
                                        {{ $rating->comment }}
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($rating->order)
                                    <a href="{{ route('admin.orders.show', $rating->order->id) }}" 
                                       class="text-decoration-none fw-bold">
                                        #{{ $rating->order->id }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center" style="min-width: 200px; white-space: normal;">
                                @php
                                    // جمع جميع الخدمات من مصادر مختلفة
                                    $allServices = collect();
                                    
                                    // الخدمات المباشرة للطلب (للطلبات العادية)
                                    if ($rating->order && $rating->order->services && $rating->order->services->count() > 0) {
                                        $allServices = $allServices->merge($rating->order->services);
                                    }
                                    
                                    // الخدمات من OrderCars (للطلبات متعددة السيارات)
                                    if ($rating->order && $rating->order->orderCars && $rating->order->orderCars->count() > 0) {
                                        foreach ($rating->order->orderCars as $orderCar) {
                                            if ($orderCar->services && $orderCar->services->count() > 0) {
                                                $allServices = $allServices->merge($orderCar->services);
                                            }
                                        }
                                    }
                                    
                                    // عدم إزالة التكرارات - عرض الخدمة المكررة لكل سيارة
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
                            <td class="text-center">
                                @if($rating->order && $rating->order->assignedUser)
                                    <span class="badge bg-info">
                                        {{ $rating->order->assignedUser->name }}
                                    </span>
                                @else
                                    <span class="text-muted">غير محدد</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($rating->order && $rating->order->customer)
                                    {{ $rating->order->customer->name }}
                                @elseif($rating->user)
                                    {{ $rating->user->name }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($rating->order)
                                    {{ $rating->order->created_at ? \Carbon\Carbon::parse($rating->order->created_at)->format('Y-m-d H:i') : '-' }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($rating->order && $rating->order->scheduled_at)
                                    {{ \Carbon\Carbon::parse($rating->order->scheduled_at)->format('Y-m-d H:i') }}
                                @else
                                    <span class="text-muted">غير محدد</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{ \Carbon\Carbon::parse($rating->created_at)->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex flex-column align-items-center gap-2 mt-4">
            <div class="text-muted small">
                عرض {{ $ratings->firstItem() ?? 0 }} إلى {{ $ratings->lastItem() ?? 0 }} من {{ $ratings->total() }} نتيجة
            </div>
            <nav aria-label="تنقل التقييمات" class="d-flex justify-content-center">
                {{ $ratings->withQueryString()->links('pagination::bootstrap-5') }}
            </nav>
        </div>
    @else
        <div class="alert alert-info text-center mt-5">
            <i class="fas fa-info-circle fa-3x mb-3"></i>
            <h5>لا توجد تقييمات حالياً</h5>
            <p class="mb-0">لم يتم إضافة أي تقييمات بعد.</p>
        </div>
    @endif
</div>

<style>
    .table th {
        white-space: nowrap;
        font-weight: 600;
        text-align: center;
    }
    .table td {
        vertical-align: middle;
        text-align: center;
    }
</style>
@endsection
