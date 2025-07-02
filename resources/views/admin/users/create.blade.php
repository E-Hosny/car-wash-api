@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4 text-center">{{ __('messages.add_user') ?? 'Add New User' }}</h3>
    <form action="{{ route('admin.users.store') }}" method="POST" class="mx-auto" style="max-width: 500px;">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">{{ __('messages.name') }}</label>
            <input type="text" name="name" id="name" class="form-control" required value="{{ old('name') }}">
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('messages.email') }}</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
            @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">{{ __('messages.phone') }}</label>
            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
            @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">{{ __('messages.password') }}</label>
            <input type="password" name="password" id="password" class="form-control" required>
            @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">{{ __('messages.password_confirmation') }}</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">{{ __('messages.role') }}</label>
            <select name="role" id="role" class="form-select" required>
                <option value="customer" {{ old('role') == 'customer' ? 'selected' : '' }}>{{ __('messages.customer') }}</option>
                <option value="provider" {{ old('role') == 'provider' ? 'selected' : '' }}>{{ __('messages.provider') }}</option>
                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>{{ __('messages.admin') }}</option>
                <option value="worker" {{ old('role') == 'worker' ? 'selected' : '' }}>{{ __('messages.worker') }}</option>
            </select>
            @error('role')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary w-100">{{ __('messages.save') }}</button>
    </form>
</div>
@endsection 