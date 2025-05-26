@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h3 class="mb-3">{{ __('messages.edit_service') }}</h3>

    <form method="POST" action="{{ route('admin.services.update', $service->id) }}">
        @csrf

        <div class="mb-3">
            <label>{{ __('messages.name') }}:</label>
            <input type="text" name="name" class="form-control" value="{{ $service->name }}" required>
        </div>

        <div class="mb-3">
            <label>{{ __('messages.description') }}:</label>
            <textarea name="description" class="form-control">{{ $service->description }}</textarea>
        </div>

        <div class="mb-3">
            <label>{{ __('messages.price') }}:</label>
            <input type="number" name="price" step="0.01" class="form-control" value="{{ $service->price }}" required>
        </div>

        <button class="btn btn-primary">{{ __('messages.update') }}</button>
    </form>
</div>
@endsection
