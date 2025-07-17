@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> {{ __('packages.package_statistics') }}
                    </h3>
                    <div>
                        <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('packages.back_to_packages') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="display-4">{{ $totalPackages }}</h3>
                                            <p class="mb-0">{{ __('packages.total_packages') }}</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-box fa-3x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="display-4">{{ $activePackages }}</h3>
                                            <p class="mb-0">{{ __('packages.active_packages') }}</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-check-circle fa-3x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-warning text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="display-4">{{ $totalPurchases }}</h3>
                                            <p class="mb-0">{{ __('packages.total_purchases') }}</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-shopping-cart fa-3x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="display-4">{{ $activeSubscriptions }}</h3>
                                            <p class="mb-0">{{ __('packages.active_subscriptions') }}</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-users fa-3x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue and Top Packages -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-money-bill-wave"></i> {{ __('packages.total_revenue') }}
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    <div class="display-3 text-success mb-3">
                                        {{ number_format($totalRevenue, 2) }}
                                    </div>
                                    <p class="text-muted mb-0">{{ __('packages.currency') }}</p>
                                    <div class="mt-3">
                                        <span class="badge bg-success fs-6">
                                            <i class="fas fa-chart-line"></i> {{ __('packages.total_revenue') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-trophy"></i> {{ __('packages.top_packages') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($topPackages->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>{{ __('packages.package') }}</th>
                                                        <th>{{ __('packages.subscribers_count') }}</th>
                                                        <th>{{ __('packages.percentage') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($topPackages as $index => $package)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                @if($index < 3)
                                                                    <span class="badge bg-warning me-2">
                                                                        #{{ $index + 1 }}
                                                                    </span>
                                                                @endif
                                                                <strong>{{ $package->name }}</strong>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-primary fs-6">
                                                                {{ $package->user_packages_count }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @php
                                                                $percentage = $totalPurchases > 0 ? 
                                                                    round(($package->user_packages_count / $totalPurchases) * 100, 1) : 0;
                                                            @endphp
                                                            <div class="progress" style="height: 20px;">
                                                                <div class="progress-bar bg-success" 
                                                                     style="width: {{ $percentage }}%">
                                                                    {{ $percentage }}%
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                            <h5>{{ __('packages.no_data') }}</h5>
                                            <p>{{ __('packages.no_purchases_yet') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Statistics -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle"></i> {{ __('packages.additional_info') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 text-center mb-3">
                                            <div class="border rounded p-3">
                                                <i class="fas fa-percentage fa-2x text-primary mb-2"></i>
                                                <h5>{{ $totalPackages > 0 ? round(($activePackages / $totalPackages) * 100, 1) : 0 }}%</h5>
                                                <small class="text-muted">{{ __('packages.active_packages_percentage') }}</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center mb-3">
                                            <div class="border rounded p-3">
                                                <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                                                <h5>{{ $totalPurchases > 0 ? round(($activeSubscriptions / $totalPurchases) * 100, 1) : 0 }}%</h5>
                                                <small class="text-muted">{{ __('packages.active_subscriptions_percentage') }}</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center mb-3">
                                            <div class="border rounded p-3">
                                                <i class="fas fa-calculator fa-2x text-info mb-2"></i>
                                                <h5>{{ $totalPurchases > 0 ? round($totalRevenue / $totalPurchases, 2) : 0 }}</h5>
                                                <small class="text-muted">{{ __('packages.average_revenue_per_subscriber') }}</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center mb-3">
                                            <div class="border rounded p-3">
                                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                                <h5>{{ now()->format('Y-m-d') }}</h5>
                                                <small class="text-muted">{{ __('packages.last_update_date') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.packages.index') }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-list"></i> {{ __('packages.view_all_packages') }}
                                </a>
                                <a href="{{ route('admin.packages.create') }}" class="btn btn-success btn-lg">
                                    <i class="fas fa-plus"></i> {{ __('packages.add_new_package') }}
                                </a>
                                <button type="button" class="btn btn-info btn-lg" onclick="window.print()">
                                    <i class="fas fa-print"></i> {{ __('packages.print_report') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-header .btn {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .card-header {
        background: none !important;
        border-bottom: 2px solid #000 !important;
    }
}

.opacity-75 {
    opacity: 0.75;
}

.display-4 {
    font-size: 2.5rem;
    font-weight: 300;
    line-height: 1.2;
}

.display-3 {
    font-size: 3.5rem;
    font-weight: 300;
    line-height: 1.2;
}
</style>

<script>
// Auto-refresh statistics every 30 seconds
setTimeout(function() {
    window.location.reload();
}, 30000);

// Add animation to statistics cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.card');
    cards.forEach(function(card, index) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(function() {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endsection 