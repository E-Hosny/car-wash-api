@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h2 class="text-center mb-4">{{ __('messages.dashboard') }}</h2>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row text-center g-4">
        <div class="col-md-4">
            <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
                <div class="card border-primary shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-people"></i> {{ __('messages.users') }}</h5>
                        <h2 class="text-primary">{{ $total_users }}</h2>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('admin.users.customers') }}" class="text-decoration-none">
                <div class="card border-success shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-person-check"></i> {{ __('messages.customers') }}</h5>
                        <h2 class="text-success">{{ $total_customers }}</h2>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('admin.users.providers') }}" class="text-decoration-none">
                <div class="card border-warning shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-person-gear"></i> {{ __('messages.providers') }}</h5>
                        <h2 class="text-warning">{{ $total_providers }}</h2>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="{{ route('admin.orders.index') }}" class="text-decoration-none">
                <div class="card border-danger shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-box"></i> {{ __('messages.orders') }}</h5>
                        <h2 class="text-danger">{{ $total_orders }}</h2>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="{{ route('admin.services.index') }}" class="text-decoration-none">
                <div class="card border-info shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-stars"></i> {{ __('messages.services') }}</h5>
                        <h2 class="text-info">{{ $total_services }}</h2>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- OneSignal Test Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-secondary shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bell"></i> OneSignal Push Notification Test
                    </h5>
                </div>
                <div class="card-body">
                    <form id="onesignalTestForm" action="{{ route('admin.onesignal.test.send') }}" method="POST">
                        @csrf
                        <button type="submit" id="onesignalTestBtn" class="btn btn-primary btn-lg">
                            <i class="bi bi-send"></i> Send Push Test
                        </button>
                    </form>

                    @if(session('onesignal_last_response'))
                        <div class="mt-4">
                            <h6 class="mb-2">Last Response (Debug):</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <pre id="onesignalResponse" class="mb-0" style="max-height: 400px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word;">{{ json_encode(session('onesignal_last_response'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('onesignalTestForm');
    const btn = document.getElementById('onesignalTestBtn');
    
    if (form && btn) {
        form.addEventListener('submit', function(e) {
            // Disable button and show loading state
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';
            
            // Form will submit normally, button will be re-enabled on page reload
        });
    }
});
</script>
@endsection
