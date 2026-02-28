@extends('admin.layout')

@section('content')
<div class="container-fluid mt-4">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                <div class="flex-grow-1">
                    <h2 class="mb-1">
                        <i class="bi bi-people text-primary"></i>
                        {{ __('messages.user_list') }}
                    </h2>
                    <p class="text-muted mb-0">{{ __('messages.manage_users_description') ?? 'إدارة المستخدمين في النظام' }}</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> <span class="d-none d-sm-inline">{{ __('messages.add_user') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                            <i class="bi bi-people"></i> {{ __('messages.all') }}
                        </a>
                        <a href="{{ route('admin.users.customers') }}" class="btn btn-outline-success {{ request()->routeIs('admin.users.customers') ? 'active' : '' }}">
                            <i class="bi bi-person"></i> {{ __('messages.customers') }}
                        </a>
                        <a href="{{ route('admin.users.providers') }}" class="btn btn-outline-warning {{ request()->routeIs('admin.users.providers') ? 'active' : '' }}">
                            <i class="bi bi-person-gear"></i> {{ __('messages.providers') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="usersTable">
                            <thead class="table-dark">
                                <tr>
                                    <th class="d-none d-sm-table-cell">#</th>
                                    <th>{{ __('messages.name') }}</th>
                                    <th class="d-none d-md-table-cell">{{ __('messages.email') }}</th>
                                    <th class="d-none d-lg-table-cell">{{ __('messages.phone') }}</th>
                                    <th>{{ __('messages.role') }}</th>
                                    <th class="d-none d-xl-table-cell">{{ __('messages.registration_date') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                <tr>
                                    <td class="d-none d-sm-table-cell">
                                        @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                            {{ $users->firstItem() + $loop->index }}
                                        @else
                                            {{ $loop->iteration }}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2 d-none d-sm-flex">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $user->name }}</div>
                                                <small class="text-muted d-md-none">{{ $user->email ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $user->email ?? '-' }}</td>
                                    <td class="d-none d-lg-table-cell">{{ $user->phone ?? '-' }}</td>
                                    <td>
                                        <form action="{{ route('admin.users.updateRole', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <select name="role" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 100px;">
                                                <option value="customer" {{ $user->role === 'customer' ? 'selected' : '' }}>{{ __('messages.customer') }}</option>
                                                <option value="provider" {{ $user->role === 'provider' ? 'selected' : '' }}>{{ __('messages.provider') }}</option>
                                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>{{ __('messages.admin') }}</option>
                                                <option value="worker" {{ $user->role === 'worker' ? 'selected' : '' }}>{{ __('messages.worker') ?? 'Worker' }}</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="d-none d-xl-table-cell">{{ optional($user->created_at)->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="{{ __('messages.edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-info" onclick="viewUserDetails({{ $user->id }})" data-bs-toggle="tooltip" title="{{ __('messages.view_details') }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-people fs-1 d-block mb-2"></i>
                                            {{ __('messages.no_users') }}
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div class="d-flex justify-content-center mt-3 mb-3">
                                {{ $users->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// View user details function
function viewUserDetails(userId) {
    // You can implement a modal or redirect to user details page
    window.location.href = `/admin/users/${userId}`;
}

// Add loading state to role change forms
document.addEventListener('DOMContentLoaded', function() {
    const roleSelects = document.querySelectorAll('select[name="role"]');
    roleSelects.forEach(select => {
        select.addEventListener('change', function() {
            const form = this.closest('form');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            // Add loading state
            this.disabled = true;
            this.style.opacity = '0.6';
            
            // Submit form
            form.submit();
        });
    });
});
</script>
@endsection
