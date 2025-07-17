@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('packages.packages') }}</h3>
                    <div>
                        <a href="{{ route('admin.packages.statistics') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> {{ __('packages.view_statistics') }}
                        </a>
                        <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ __('packages.add_package') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ __('packages.id') }}</th>
                                    <th>{{ __('packages.image') }}</th>
                                    <th>{{ __('packages.name') }}</th>
                                    <th>{{ __('packages.description') }}</th>
                                    <th>{{ __('packages.price') }}</th>
                                    <th>{{ __('packages.points') }}</th>
                                    <th>{{ __('packages.subscribers_count') }}</th>
                                    <th>{{ __('packages.status') }}</th>
                                    <th>{{ __('packages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($packages as $package)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ $package->id }}</span></td>
                                    <td>
                                        @if($package->image)
                                            <img src="{{ asset('storage/' . $package->image) }}" 
                                                 alt="{{ $package->name }}" 
                                                 class="img-thumbnail"
                                                 style="width: 60px; height: 60px; object-fit: cover;"
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#imageModal{{ $package->id }}"
                                                 style="cursor: pointer;">
                                        @else
                                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $package->name }}</strong>
                                    </td>
                                    <td>
                                        <span title="{{ $package->description }}">
                                            {{ Str::limit($package->description, 50) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success fs-6">
                                            {{ $package->price }} {{ __('packages.currency') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info fs-6">
                                            {{ $package->points }} {{ __('packages.points_unit') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary fs-6">
                                            {{ $package->user_packages_count }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($package->is_active)
                                            <span class="badge bg-success fs-6">
                                                <i class="fas fa-check-circle"></i> {{ __('packages.active') }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger fs-6">
                                                <i class="fas fa-times-circle"></i> {{ __('packages.inactive') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="{{ __('packages.actions') }}">
                                            <a href="{{ route('admin.packages.edit', $package->id) }}" 
                                               class="btn btn-sm btn-primary" 
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top" 
                                               title="{{ __('packages.edit_package') }}">
                                                <i class="fas fa-edit me-1"></i>
                                                <span class="d-none d-lg-inline">{{ __('packages.edit') }}</span>
                                            </a>
                                            
                                            <form action="{{ route('admin.packages.toggle-status', $package->id) }}" 
                                                  method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm {{ $package->is_active ? 'btn-warning' : 'btn-success' }}"
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-placement="top" 
                                                        title="{{ $package->is_active ? __('packages.deactivate') : __('packages.activate') }}"
                                                        onclick="return confirm('{{ $package->is_active ? __('packages.confirm_deactivate') : __('packages.confirm_activate') }}')">
                                                    @if($package->is_active)
                                                        <i class="fas fa-eye-slash me-1"></i>
                                                        <span class="d-none d-lg-inline">{{ __('packages.deactivate') }}</span>
                                                    @else
                                                        <i class="fas fa-eye me-1"></i>
                                                        <span class="d-none d-lg-inline">{{ __('packages.activate') }}</span>
                                                    @endif
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.packages.delete', $package->id) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirmDelete('{{ $package->name }}')">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger"
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-placement="top" 
                                                        title="{{ __('packages.delete_package') }}">
                                                    <i class="fas fa-trash me-1"></i>
                                                    <span class="d-none d-lg-inline">{{ __('packages.delete') }}</span>
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <!-- Mobile Actions -->
                                        <div class="d-lg-none mt-2">
                                            <div class="d-grid gap-1">
                                                <a href="{{ route('admin.packages.edit', $package->id) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit me-1"></i> {{ __('packages.edit_package') }}
                                                </a>
                                                
                                                <form action="{{ route('admin.packages.toggle-status', $package->id) }}" 
                                                      method="POST">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm {{ $package->is_active ? 'btn-warning' : 'btn-success' }} w-100"
                                                            onclick="return confirm('{{ $package->is_active ? __('packages.confirm_deactivate') : __('packages.confirm_activate') }}')">
                                                        @if($package->is_active)
                                                            <i class="fas fa-eye-slash me-1"></i> {{ __('packages.deactivate') }}
                                                        @else
                                                            <i class="fas fa-eye me-1"></i> {{ __('packages.activate') }}
                                                        @endif
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.packages.delete', $package->id) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirmDelete('{{ $package->name }}')">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-danger w-100">
                                                        <i class="fas fa-trash me-1"></i> {{ __('packages.delete_package') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Image Modal -->
                                @if($package->image)
                                <div class="modal fade" id="imageModal{{ $package->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ $package->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="{{ asset('storage/' . $package->image) }}" 
                                                     alt="{{ $package->name }}" 
                                                     class="img-fluid">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-box fa-3x mb-3"></i>
                                            <h5>{{ __('packages.no_packages_found') }}</h5>
                                            <p>{{ __('packages.no_packages_available') }}</p>
                                            <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> {{ __('packages.add_first_package') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($packages->count() > 0)
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    {{ __('packages.total_packages') }}: <strong>{{ $packages->count() }}</strong>
                                </p>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="{{ route('admin.packages.statistics') }}" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-chart-line"></i> {{ __('packages.view_detailed_statistics') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* تحسين مظهر أزرار الإجراءات */
.btn-group .btn {
    border-radius: 6px !important;
    margin: 0 2px;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-group .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* ألوان مخصصة للأزرار */
.btn-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border: none;
}

.btn-warning {
    background: linear-gradient(45deg, #ffc107, #e0a800);
    border: none;
    color: #000;
}

.btn-success {
    background: linear-gradient(45deg, #28a745, #1e7e34);
    border: none;
}

.btn-danger {
    background: linear-gradient(45deg, #dc3545, #c82333);
    border: none;
}

/* تحسين مظهر الأزرار على الجوال */
@media (max-width: 991px) {
    .d-grid .btn {
        border-radius: 8px !important;
        padding: 10px 15px;
        font-size: 14px;
        margin-bottom: 5px;
    }
}

/* تحسين مظهر الجدول */
.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,0.05);
    transform: scale(1.01);
    transition: all 0.2s ease;
}

/* تحسين مظهر البطاقات */
.badge {
    font-size: 0.85em;
    padding: 6px 10px;
    border-radius: 20px;
}

/* تحسين مظهر الصور */
.img-thumbnail {
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
}

.img-thumbnail:hover {
    border-color: #007bff;
    transform: scale(1.1);
    cursor: pointer;
}

/* تحسين مظهر الأيقونات */
.fas {
    font-size: 14px;
}

/* تحسين مظهر tooltips */
.tooltip {
    font-size: 12px;
}

.tooltip-inner {
    background-color: #333;
    border-radius: 6px;
    padding: 8px 12px;
}

/* تحسين مظهر رسائل التأكيد */
.alert {
    border-radius: 10px;
    border: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

/* تحسين مظهر الأزرار المحملة */
.btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* تحسين مظهر الجدول على الشاشات الصغيرة */
@media (max-width: 768px) {
    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .table th,
    .table td {
        padding: 12px 8px;
        vertical-align: middle;
    }
}
</style>

<script>
function confirmDelete(packageName) {
    return confirm(`{{ __('packages.confirm_delete_package') }} "${packageName}"?\n\n{{ __('packages.delete_warning') }}`);
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Add loading state to buttons
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __('packages.loading') }}...';
                submitBtn.disabled = true;
            }
        });
    });
    
    // Add hover effects to action buttons
    const actionButtons = document.querySelectorAll('.btn-group .btn');
    actionButtons.forEach(function(btn) {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>
@endsection 