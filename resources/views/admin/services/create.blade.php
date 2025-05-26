@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h3 class="mb-3">{{ __('messages.add_service') }}</h3>

    <form method="POST" action="{{ route('admin.services.store') }}">
        @csrf

        <div class="mb-3">
            <label>{{ __('messages.name') }}:</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>{{ __('messages.description') }}:</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label>{{ __('messages.price') }}:</label>
            <input type="number" name="price" step="0.01" class="form-control" required>
        </div>

        <button class="btn btn-success">{{ __('messages.save') }}</button>
    </form>
</div>
@endsection
