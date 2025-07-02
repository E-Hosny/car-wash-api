@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4 text-center">{{ __('messages.edit') }} {{ $user->name }}</h3>
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="mx-auto" style="max-width: 500px;">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">{{ __('messages.name') }}</label>
            <input type="text" name="name" id="name" class="form-control" required value="{{ old('name', $user->name) }}">
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('messages.email') }}</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}">
            @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">{{ __('messages.phone') }}</label>
            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
            @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">{{ __('messages.password') ?? 'Password' }}</label>
            <input type="password" name="password" id="password" class="form-control">
            <small class="text-muted">{{ __('messages.leave_blank_to_keep') ?? 'Leave blank to keep current password' }}</small>
            @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">{{ __('messages.password_confirmation') ?? 'Confirm Password' }}</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">{{ __('messages.role') }}</label>
            <select name="role" id="role" class="form-select" required>
                <option value="customer" {{ old('role', $user->role) == 'customer' ? 'selected' : '' }}>{{ __('messages.customer') }}</option>
                <option value="provider" {{ old('role', $user->role) == 'provider' ? 'selected' : '' }}>{{ __('messages.provider') }}</option>
                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>{{ __('messages.admin') }}</option>
                <option value="worker" {{ old('role', $user->role) == 'worker' ? 'selected' : '' }}>{{ __('messages.worker') ?? 'Worker' }}</option>
            </select>
            @error('role')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary w-100">{{ __('messages.update') }}</button>
    </form>
</div>
@endsection 