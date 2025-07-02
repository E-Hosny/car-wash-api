@extends('admin.layout')

@section('content')
<div class="container mt-4">
        @if (session('success'))
            <div class="alert alert-success text-center">{{ session('success') }}</div>
        @endif

    <h3 class="mb-4 text-center">{{ __('messages.user_list') }}</h3>

    <div class="mb-3 text-center">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">{{ __('messages.all') }}</a>
        <a href="{{ route('admin.users.customers') }}" class="btn btn-success btn-sm">{{ __('messages.customers') }}</a>
        <a href="{{ route('admin.users.providers') }}" class="btn btn-warning btn-sm">{{ __('messages.providers') }}</a>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">{{ __('messages.add_user') }}</a>
    </div>

    <table class="table table-bordered table-striped text-center">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>{{ __('messages.name') }}</th>
                <th>{{ __('messages.email') }}</th>
                <th>{{ __('messages.phone') }}</th>
                <th>{{ __('messages.role') }}</th>
                <th>{{ __('messages.registration_date') }}</th>
                <th>{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email ?? '-' }}</td>
                <td>{{ $user->phone ?? '-' }}</td>
                <td>
                <form action="{{ route('admin.users.updateRole', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <select name="role" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                        <option value="customer" {{ $user->role === 'customer' ? 'selected' : '' }}>{{ __('messages.customer') }}</option>
                        <option value="provider" {{ $user->role === 'provider' ? 'selected' : '' }}>{{ __('messages.provider') }}</option>
                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>{{ __('messages.admin') }}</option>
                        <option value="worker" {{ $user->role === 'worker' ? 'selected' : '' }}>{{ __('messages.worker') ?? 'Worker' }}</option>
                    </select>
                </form>
            </td>
                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                <td>
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-info">{{ __('messages.edit') }}</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">{{ __('messages.no_users') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
