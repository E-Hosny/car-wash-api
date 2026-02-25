@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4 text-center">احصائيات العمال</h3>

    <div class="mb-3">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            ← العودة إلى قائمة الطلبات
        </a>
    </div>

    <form method="GET" action="{{ route('admin.orders.workers-statistics') }}" class="card p-3 mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="from_date" class="form-label">من تاريخ</label>
                <input type="date" id="from_date" name="from_date" class="form-control"
                       value="{{ $fromDate }}">
            </div>
            <div class="col-md-4">
                <label for="to_date" class="form-label">إلى تاريخ</label>
                <input type="date" id="to_date" name="to_date" class="form-control"
                       value="{{ $toDate }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    تطبيق الفلتر
                </button>
            </div>
        </div>
        <div class="mt-3 text-muted">
            المدة الحالية: من <strong>{{ $fromDate }}</strong> إلى <strong>{{ $toDate }}</strong>
        </div>
    </form>

    <table class="table table-bordered table-striped text-center">
        <thead class="table-dark">
            <tr>
                <th>إجمالي المبالغ (AED)</th>
                <th>عدد الطلبات</th>
                <th>الهاتف</th>
                <th>العامل</th>
                <th>#</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stats as $index => $row)
                @php
                    $provider = $providers->get($row->provider_id);
                @endphp
                <tr>
                    <td>{{ number_format($row->total_amount ?? 0, 2) }}</td>
                    <td>{{ $row->orders_count }}</td>
                    <td>{{ $provider->phone ?? '-' }}</td>
                    <td>{{ $provider->name ?? '-' }}</td>
                    <td>{{ $index + 1 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">لا توجد بيانات في هذه المدة.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

