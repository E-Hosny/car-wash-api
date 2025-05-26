@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4 text-center">{{ __('messages.order_list') }}</h3>

    <table class="table table-bordered table-striped text-center">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>{{ __('messages.customer') }}</th>
                <th>{{ __('messages.provider') }}</th>
                <th>{{ __('messages.location') }}</th>
                <th>{{ __('messages.services') }}</th>
                <th>{{ __('messages.status') }}</th>
                <th>{{ __('messages.date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->customer->name ?? '-' }}</td>
                    <td>{{ $order->provider->name ?? '-' }}</td>
                    <td>{{ $order->location }}</td>
                    <td>
                        <ul class="list-unstyled m-0">
                            @foreach($order->services as $service)
                                <li>🧼 {{ $service->name }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td>
                        @php
                            $badge = match($order->status) {
                                'pending' => 'secondary',
                                'accepted' => 'info',
                                'in_progress' => 'warning',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'dark'
                            };
                        @endphp
                        <span class="badge bg-{{ $badge }}">{{ __('messages.' . $order->status) }}</span>
                    </td>
                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">{{ __('messages.no_orders') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
