@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">إحصائيات الخدمات / Services Statistics</h3>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.order_list') }}
        </a>
    </div>

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
                    @if(count($serviceStats) > 0)
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
                    @else
                        <p class="text-muted mb-0">لا توجد طلبات لعرض إحصائيات الخدمات.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="fas fa-user-check"></i> Retention / نسبة العملاء العائدين
            </h5>
            <button type="button" class="btn btn-light btn-sm" id="toggleRetentionDetails" aria-expanded="false">
                <i class="fas fa-list"></i> التفاصيل
            </button>
        </div>
        <div class="card-body">
            <p class="mb-0">
                <strong>نسبة العملاء الذين لديهم أكثر من طلب واحد:</strong>
                <span class="badge bg-info fs-6">{{ $retentionPercentage }}%</span>
                ({{ $retentionCount }} من أصل {{ $totalUniqueCustomers }} عميل)
            </p>
            <div id="retentionDetailsSection" class="mt-4 collapse">
                <h6 class="mb-3">العملاء الذين لديهم أكثر من طلب:</h6>
                @if(count($retentionDetails) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">العميل / Customer</th>
                                    <th class="text-center">الهاتف / Phone</th>
                                    <th class="text-center">البريد / Email</th>
                                    <th class="text-center">عدد الطلبات / Orders Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($retentionDetails as $index => $row)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td class="text-center">{{ $row['name'] }}</td>
                                        <td class="text-center">{{ $row['phone'] }}</td>
                                        <td class="text-center">{{ $row['email'] }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $row['orders_count'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">لا يوجد عملاء لديهم أكثر من طلب واحد.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('toggleRetentionDetails');
    var section = document.getElementById('retentionDetailsSection');
    if (btn && section) {
        btn.addEventListener('click', function() {
            var expanded = section.classList.toggle('show');
            btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });
    }
});
</script>
@endsection
