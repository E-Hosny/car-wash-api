@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">تاريخ تغييرات حالة الطلب #{{ $order->id }}</h3>
        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> العودة إلى تفاصيل الطلب
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">معلومات الطلب</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>رقم الطلب:</strong> #{{ $order->id }}</p>
                    <p><strong>العميل:</strong> {{ $order->customer->name ?? '-' }}</p>
                    <p><strong>الحالة الحالية:</strong> 
                        <span class="badge 
                            @if($order->status === 'completed') bg-success
                            @elseif($order->status === 'cancelled') bg-danger
                            @elseif($order->status === 'in_progress') bg-warning
                            @elseif($order->status === 'accepted') bg-info
                            @else bg-secondary
                            @endif">
                            {{ $order->status }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>تاريخ الإنشاء:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</p>
                    <p><strong>الوقت المجدول:</strong> 
                        {{ $order->scheduled_at ? \Carbon\Carbon::parse($order->scheduled_at)->format('Y-m-d H:i') : 'غير محدد' }}
                    </p>
                    <p><strong>إجمالي السعر:</strong> {{ number_format($order->total, 2) }} AED</p>
                </div>
            </div>
        </div>
    </div>

    @if($history->count() > 0)
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-history"></i> سجل تغييرات الحالة
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">الحالة السابقة</th>
                                <th class="text-center">الحالة الجديدة</th>
                                <th class="text-center">المستخدم</th>
                                <th class="text-center">التاريخ والوقت</th>
                                <th class="text-center">IP Address</th>
                                <th class="text-center">الملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $index => $record)
                                <tr>
                                    <td class="text-center">{{ $history->count() - $index }}</td>
                                    <td class="text-center">
                                        @if($record->previous_status)
                                            <span class="badge bg-secondary">{{ $record->previous_status }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge 
                                            @if($record->new_status === 'completed') bg-success
                                            @elseif($record->new_status === 'cancelled') bg-danger
                                            @elseif($record->new_status === 'in_progress') bg-warning
                                            @elseif($record->new_status === 'accepted') bg-info
                                            @else bg-secondary
                                            @endif">
                                            {{ $record->new_status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($record->changedBy)
                                            {{ $record->changedBy->name }}
                                            <br>
                                            <small class="text-muted">(ID: {{ $record->changedBy->id }})</small>
                                        @else
                                            <span class="text-muted">غير معروف</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $record->created_at->format('Y-m-d H:i:s') }}
                                        <br>
                                        <small class="text-muted">{{ $record->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if($record->ip_address)
                                            <code>{{ $record->ip_address }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($record->notes)
                                            <span class="text-dark">{{ $record->notes }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info text-center mt-5">
            <i class="fas fa-info-circle fa-3x mb-3"></i>
            <h5>لا يوجد سجل لتغييرات الحالة</h5>
            <p class="mb-0">لم يتم تسجيل أي تغييرات في حالة هذا الطلب بعد.</p>
        </div>
    @endif
</div>

<style>
    .table th {
        white-space: nowrap;
        font-weight: 600;
    }
    .table td {
        vertical-align: middle;
    }
</style>
@endsection
