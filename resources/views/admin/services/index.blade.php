@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h3 class="mb-3">{{ __('messages.services') }}</h3>

    <a href="{{ route('admin.services.create') }}" class="btn btn-success mb-3">{{ __('messages.add_service') }}</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered text-center">
        <thead>
            <tr>
                <th>{{ __('messages.name') }}</th>
                <th>{{ __('messages.description') }}</th>
                <th>{{ __('messages.price') }}</th>
                <th>{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($services as $service)
                <tr>
                    <td>{{ $service->name }}</td>
                    <td>{{ $service->description }}</td>
                    <td>{{ $service->price }}</td>
                    <td>
                        <a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-primary btn-sm">{{ __('messages.edit') }}</a>
                        <form action="{{ route('admin.services.delete', $service->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-danger btn-sm" onclick="return confirm('{{ __('messages.confirm_delete') }}')">{{ __('messages.delete') }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
