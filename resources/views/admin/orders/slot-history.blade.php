@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">تاريخ تغييرات الـ Slots</h3>
        <a href="{{ route('admin.orders.time-slots') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> العودة إلى Time Slots
        </a>
    </div>

    <!-- فلترة -->
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">فلترة النتائج</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.slots.history') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="slot_type" class="form-label">نوع الـ Slot</label>
                    <select name="slot_type" id="slot_type" class="form-select">
                        <option value="">الكل</option>
                        <option value="hour_slot_instance" {{ request('slot_type') === 'hour_slot_instance' ? 'selected' : '' }}>Hour Slot Instance</option>
                        <option value="daily_time_slot" {{ request('slot_type') === 'daily_time_slot' ? 'selected' : '' }}>Daily Time Slot</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date" class="form-label">التاريخ</label>
                    <input type="date" name="date" id="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-3">
                    <label for="hour" class="form-label">الساعة</label>
                    <input type="number" name="hour" id="hour" class="form-control" min="10" max="23" value="{{ request('hour') }}" placeholder="10-23">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> بحث
                    </button>
                    <a href="{{ route('admin.slots.history') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> إعادة تعيين
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if($history->count() > 0)
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-history"></i> سجل تغييرات الـ Slots
                    <span class="badge bg-light text-dark ms-2">{{ $history->total() }}</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">النوع</th>
                                <th class="text-center">التاريخ</th>
                                <th class="text-center">الساعة</th>
                                <th class="text-center">Slot Index</th>
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
                                    <td class="text-center">{{ $history->firstItem() + $index }}</td>
                                    <td class="text-center">
                                        @if($record->slot_type === 'hour_slot_instance')
                                            <span class="badge bg-info">Hour Slot</span>
                                        @else
                                            <span class="badge bg-primary">Daily Time Slot</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $record->date->format('Y-m-d') }}
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ $record->hour }}:00</strong>
                                        @php
                                            $period = $record->hour < 12 ? 'AM' : 'PM';
                                            $displayHour = $record->hour > 12 ? $record->hour - 12 : $record->hour;
                                            if ($record->hour == 12) $displayHour = 12;
                                        @endphp
                                        <br>
                                        <small class="text-muted">({{ $displayHour }}:00 {{ $period }})</small>
                                    </td>
                                    <td class="text-center">
                                        @if($record->slot_index)
                                            <span class="badge bg-secondary">#{{ $record->slot_index }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($record->slot_type === 'hour_slot_instance')
                                            <span class="badge 
                                                @if($record->previous_status === 'available') bg-success
                                                @elseif($record->previous_status === 'disabled') bg-danger
                                                @elseif($record->previous_status === 'booked') bg-warning
                                                @else bg-secondary
                                                @endif">
                                                {{ $record->previous_status }}
                                            </span>
                                        @else
                                            <span class="badge {{ $record->previous_status === 'true' ? 'bg-success' : 'bg-danger' }}">
                                                {{ $record->previous_status === 'true' ? 'ON' : 'OFF' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($record->slot_type === 'hour_slot_instance')
                                            <span class="badge 
                                                @if($record->new_status === 'available') bg-success
                                                @elseif($record->new_status === 'disabled') bg-danger
                                                @elseif($record->new_status === 'booked') bg-warning
                                                @else bg-secondary
                                                @endif">
                                                {{ $record->new_status }}
                                            </span>
                                        @else
                                            <span class="badge {{ $record->new_status === 'true' ? 'bg-success' : 'bg-danger' }}">
                                                {{ $record->new_status === 'true' ? 'ON' : 'OFF' }}
                                            </span>
                                        @endif
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

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $history->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info text-center mt-5">
            <i class="fas fa-info-circle fa-3x mb-3"></i>
            <h5>لا يوجد سجل لتغييرات الـ Slots</h5>
            <p class="mb-0">لم يتم تسجيل أي تغييرات في الـ slots بعد.</p>
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
