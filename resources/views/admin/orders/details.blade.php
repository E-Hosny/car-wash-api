<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-person"></i> معلومات العميل</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <strong>الاسم:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ $order->customer->name ?? 'غير محدد' }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong>البريد الإلكتروني:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ $order->customer->email ?? 'غير محدد' }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong>رقم الهاتف:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ $order->customer->phone ?? 'غير محدد' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-calendar"></i> تفاصيل الموعد</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <strong>التاريخ:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ \Carbon\Carbon::parse($order->scheduled_at)->format('Y-m-d') }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong>الوقت:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ \Carbon\Carbon::parse($order->scheduled_at)->format('h:i A') }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong>الحالة:</strong>
                    </div>
                    <div class="col-sm-8">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-car-front"></i> معلومات السيارة</h6>
            </div>
            <div class="card-body">
                @if($order->car)
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>الماركة:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->car->brand->name ?? 'غير محدد' }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>الموديل:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->car->model->name ?? 'غير محدد' }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>السنة:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->car->year->year ?? 'غير محدد' }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>اللون:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->car->color ?? 'غير محدد' }}
                        </div>
                    </div>
                    @if($order->car->license_plate)
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                <strong>رقم اللوحة:</strong>
                            </div>
                            <div class="col-sm-8">
                                {{ $order->car->license_plate }}
                            </div>
                        </div>
                    @endif
                @else
                    <p class="text-muted">لا توجد معلومات سيارة</p>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-geo-alt"></i> معلومات الموقع</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <strong>العنوان:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ $order->address ?? 'غير محدد' }}
                    </div>
                </div>
                @if($order->street)
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>الشارع:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->street }}
                        </div>
                    </div>
                @endif
                @if($order->building)
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>المبنى:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->building }}
                        </div>
                    </div>
                @endif
                @if($order->floor)
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>الطابق:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->floor }}
                        </div>
                    </div>
                @endif
                @if($order->apartment)
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>الشقة:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->apartment }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="bi bi-list-check"></i> الخدمات المطلوبة</h6>
            </div>
            <div class="card-body">
                @if($order->services->count() > 0)
                    <div class="row">
                        @foreach($order->services as $service)
                            <div class="col-md-6 mb-2">
                                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                    <span>{{ $service->name }}</span>
                                    <span class="badge bg-primary">{{ number_format($service->price, 2) }} AED</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>المجموع:</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="fs-5 fw-bold text-success">{{ number_format($order->total, 2) }} AED</span>
                        </div>
                    </div>
                @else
                    <p class="text-muted">لا توجد خدمات محددة</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if($order->admin_notes)
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0"><i class="bi bi-chat-text"></i> ملاحظات الأدمن</h6>
            </div>
            <div class="card-body">
                <p>{{ $order->admin_notes }}</p>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> سجل الطلب</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>تاريخ الإنشاء:</strong>
                        {{ $order->created_at->format('Y-m-d H:i') }}
                    </div>
                    <div class="col-md-6">
                        <strong>آخر تحديث:</strong>
                        {{ $order->updated_at->format('Y-m-d H:i') }}
                    </div>
                </div>
                @if($order->cancelled_at)
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>تاريخ الإلغاء:</strong>
                            {{ \Carbon\Carbon::parse($order->cancelled_at)->format('Y-m-d H:i') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
