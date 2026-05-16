@extends('admin.layout')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-person-badge text-primary"></i>
                        {{ __('messages.customer_details') }}
                    </h2>
                    <p class="text-muted mb-0">{{ $user->name }}</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('admin.users.customers') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right"></i> {{ __('messages.back_to_users') }}
                    </a>
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil"></i> {{ __('messages.edit') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <div class="text-muted small">{{ __('messages.name') }}</div>
                            <div class="fw-bold">{{ $user->name }}</div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-muted small">{{ __('messages.email') }}</div>
                            <div>{{ $user->email ?? __('messages.not_specified') }}</div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-muted small">{{ __('messages.phone_number') }}</div>
                            <div>{{ $user->phone ?? __('messages.not_specified') }}</div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-muted small">{{ __('messages.role') }}</div>
                            <span class="badge bg-{{ $user->role === 'customer' ? 'success' : 'secondary' }}">
                                {{ __('messages.' . $user->role) }}
                            </span>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-muted small">{{ __('messages.registration_date') }}</div>
                            <div>{{ optional($user->created_at)->format('Y-m-d H:i') }}</div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-muted small">{{ __('messages.cars_count') }}</div>
                            <div class="fs-5 fw-bold text-primary">{{ $user->cars->count() }}</div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-muted small">{{ __('messages.addresses_count') }}</div>
                            <div class="fs-5 fw-bold text-info">{{ $user->addresses->count() }}</div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-muted small">{{ __('messages.orders_count') }}</div>
                            <div class="fs-5 fw-bold text-warning">{{ $user->customerOrders->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-car-front"></i> {{ __('messages.saved_cars') }}</h5>
                </div>
                <div class="card-body p-0">
                    @if($user->cars->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('messages.make') }}</th>
                                        <th>{{ __('messages.model') }}</th>
                                        <th>{{ __('messages.year') }}</th>
                                        <th>{{ __('messages.car_type') }}</th>
                                        <th>{{ __('messages.color') }}</th>
                                        <th>{{ __('messages.license_plate') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->cars as $car)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $car->brand->name ?? __('messages.not_specified') }}</td>
                                        <td>{{ $car->model->name ?? __('messages.not_specified') }}</td>
                                        <td>{{ $car->year->year ?? __('messages.not_specified') }}</td>
                                        <td>{{ $car->car_type ?? __('messages.not_specified') }}</td>
                                        <td>{{ $car->color ?? __('messages.not_specified') }}</td>
                                        <td>{{ $car->license_plate ?? __('messages.not_specified') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">
                            <i class="bi bi-car-front fs-1 d-block mb-2"></i>
                            {{ __('messages.no_cars') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-geo-alt"></i> {{ __('messages.saved_addresses') }}</h5>
                </div>
                <div class="card-body p-0">
                    @if($user->addresses->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('messages.label') }}</th>
                                        <th>{{ __('messages.address') }}</th>
                                        <th>{{ __('messages.street') }}</th>
                                        <th>{{ __('messages.building') }}</th>
                                        <th>{{ __('messages.floor') }}</th>
                                        <th>{{ __('messages.apartment') }}</th>
                                        <th>{{ __('messages.notes') }}</th>
                                        <th>{{ __('messages.coordinates') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->addresses as $address)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $address->label ?? __('messages.not_specified') }}</td>
                                        <td>{{ $address->address ?? __('messages.not_specified') }}</td>
                                        <td>{{ $address->street ?? '-' }}</td>
                                        <td>{{ $address->building ?? '-' }}</td>
                                        <td>{{ $address->floor ?? '-' }}</td>
                                        <td>{{ $address->apartment ?? '-' }}</td>
                                        <td>{{ $address->notes ?? '-' }}</td>
                                        <td>
                                            @if($address->latitude && $address->longitude)
                                                <a href="https://www.google.com/maps?q={{ $address->latitude }},{{ $address->longitude }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-map"></i> {{ __('messages.view_on_map') }}
                                                </a>
                                                <small class="d-block text-muted">{{ $address->latitude }}, {{ $address->longitude }}</small>
                                            @else
                                                {{ __('messages.not_specified') }}
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">
                            <i class="bi bi-geo-alt fs-1 d-block mb-2"></i>
                            {{ __('messages.no_addresses') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> {{ __('messages.customer_orders') }}</h5>
                </div>
                <div class="card-body p-0">
                    @if($user->customerOrders->isNotEmpty())
                        <div class="accordion" id="customerOrdersAccordion">
                            @foreach($user->customerOrders as $order)
                            @php
                                $badgeClass = match($order->status) {
                                    'pending' => 'warning',
                                    'accepted' => 'info',
                                    'in_progress' => 'primary',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                };
                                $statusLabel = __('messages.' . $order->status);
                                if ($statusLabel === 'messages.' . $order->status) {
                                    $statusLabel = ucfirst(str_replace('_', ' ', $order->status));
                                }
                            @endphp
                            <div class="accordion-item border-0 border-bottom">
                                <h2 class="accordion-header" id="orderHeading{{ $order->id }}">
                                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#orderCollapse{{ $order->id }}">
                                        <span class="me-2 fw-bold">#{{ $order->id }}</span>
                                        <span class="badge bg-{{ $badgeClass }} me-2">{{ $statusLabel }}</span>
                                        <span class="text-muted me-2">
                                            {{ $order->scheduled_at ? \Carbon\Carbon::parse($order->scheduled_at)->format('Y-m-d h:i A') : __('messages.not_specified') }}
                                        </span>
                                        <span class="fw-bold text-success">{{ number_format($order->total, 2) }} {{ __('messages.currency') }}</span>
                                    </button>
                                </h2>
                                <div id="orderCollapse{{ $order->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#customerOrdersAccordion">
                                    <div class="accordion-body">
                                        <div class="d-flex justify-content-end mb-3">
                                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bi bi-box-arrow-up-right"></i> {{ __('messages.view_details') }}
                                            </a>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <h6 class="text-primary"><i class="bi bi-calendar"></i> {{ __('messages.appointment_details') }}</h6>
                                                <table class="table table-sm table-borderless">
                                                    <tr>
                                                        <td class="text-muted" width="40%">{{ __('messages.date') }}</td>
                                                        <td>{{ $order->scheduled_at ? \Carbon\Carbon::parse($order->scheduled_at)->format('Y-m-d') : '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">{{ __('messages.time') }}</td>
                                                        <td>{{ $order->scheduled_at ? \Carbon\Carbon::parse($order->scheduled_at)->format('h:i A') : '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">{{ __('messages.status') }}</td>
                                                        <td><span class="badge bg-{{ $badgeClass }}">{{ $statusLabel }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">{{ __('messages.payment_status') }}</td>
                                                        <td>{{ $order->payment_status ?? __('messages.not_specified') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">{{ __('messages.provider') }}</td>
                                                        <td>{{ $order->provider->name ?? __('messages.not_specified') }}</td>
                                                    </tr>
                                                </table>
                                            </div>

                                            <div class="col-md-6">
                                                <h6 class="text-info"><i class="bi bi-geo-alt"></i> {{ __('messages.location_information') }}</h6>
                                                <table class="table table-sm table-borderless">
                                                    <tr>
                                                        <td class="text-muted" width="40%">{{ __('messages.address') }}</td>
                                                        <td>{{ $order->address ?? __('messages.not_specified') }}</td>
                                                    </tr>
                                                    @if($order->street)
                                                    <tr>
                                                        <td class="text-muted">{{ __('messages.street') }}</td>
                                                        <td>{{ $order->street }}</td>
                                                    </tr>
                                                    @endif
                                                    @if($order->building)
                                                    <tr>
                                                        <td class="text-muted">{{ __('messages.building') }}</td>
                                                        <td>{{ $order->building }}</td>
                                                    </tr>
                                                    @endif
                                                    @if($order->floor)
                                                    <tr>
                                                        <td class="text-muted">{{ __('messages.floor') }}</td>
                                                        <td>{{ $order->floor }}</td>
                                                    </tr>
                                                    @endif
                                                    @if($order->apartment)
                                                    <tr>
                                                        <td class="text-muted">{{ __('messages.apartment') }}</td>
                                                        <td>{{ $order->apartment }}</td>
                                                    </tr>
                                                    @endif
                                                    @if($order->latitude && $order->longitude)
                                                    <tr>
                                                        <td class="text-muted">{{ __('messages.coordinates') }}</td>
                                                        <td>
                                                            <a href="https://www.google.com/maps?q={{ $order->latitude }},{{ $order->longitude }}" target="_blank" rel="noopener">
                                                                {{ __('messages.view_on_map') }}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                </table>
                                            </div>

                                            <div class="col-12">
                                                <h6 class="text-success"><i class="bi bi-car-front"></i> {{ __('messages.car_information') }}</h6>
                                                @if($order->orderCars->isNotEmpty())
                                                    @foreach($order->orderCars as $orderCar)
                                                        @if($orderCar->car)
                                                        <div class="border rounded p-3 mb-2 bg-light">
                                                            <div class="row">
                                                                <div class="col-md-3"><strong>{{ __('messages.make') }}:</strong> {{ $orderCar->car->brand->name ?? '-' }}</div>
                                                                <div class="col-md-3"><strong>{{ __('messages.model') }}:</strong> {{ $orderCar->car->model->name ?? '-' }}</div>
                                                                <div class="col-md-2"><strong>{{ __('messages.year') }}:</strong> {{ $orderCar->car->year->year ?? '-' }}</div>
                                                                <div class="col-md-2"><strong>{{ __('messages.color') }}:</strong> {{ $orderCar->car->color ?? '-' }}</div>
                                                                <div class="col-md-2"><strong>{{ __('messages.license_plate') }}:</strong> {{ $orderCar->car->license_plate ?? '-' }}</div>
                                                            </div>
                                                            @if($orderCar->services->isNotEmpty())
                                                            <div class="mt-2">
                                                                <strong>{{ __('messages.required_services') }}:</strong>
                                                                @foreach($orderCar->services as $service)
                                                                    <span class="badge bg-secondary me-1">{{ $service->name }} ({{ number_format($service->price, 2) }})</span>
                                                                @endforeach
                                                            </div>
                                                            @endif
                                                        </div>
                                                        @endif
                                                    @endforeach
                                                @elseif($order->car)
                                                    <div class="border rounded p-3 bg-light">
                                                        <div class="row">
                                                            <div class="col-md-3"><strong>{{ __('messages.make') }}:</strong> {{ $order->car->brand->name ?? '-' }}</div>
                                                            <div class="col-md-3"><strong>{{ __('messages.model') }}:</strong> {{ $order->car->model->name ?? '-' }}</div>
                                                            <div class="col-md-2"><strong>{{ __('messages.year') }}:</strong> {{ $order->car->year->year ?? '-' }}</div>
                                                            <div class="col-md-2"><strong>{{ __('messages.color') }}:</strong> {{ $order->car->color ?? '-' }}</div>
                                                            <div class="col-md-2"><strong>{{ __('messages.license_plate') }}:</strong> {{ $order->car->license_plate ?? '-' }}</div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <p class="text-muted mb-0">{{ __('messages.no_car_info') }}</p>
                                                @endif
                                            </div>

                                            <div class="col-12">
                                                <h6 class="text-secondary"><i class="bi bi-list-check"></i> {{ __('messages.required_services') }}</h6>
                                                @if($order->services->isNotEmpty())
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($order->services as $service)
                                                            <span class="badge bg-primary fs-6">{{ $service->name }} — {{ number_format($service->price, 2) }} {{ __('messages.currency') }}</span>
                                                        @endforeach
                                                    </div>
                                                    <div class="mt-2">
                                                        <strong>{{ __('messages.total') }}:</strong>
                                                        <span class="fs-5 text-success">{{ number_format($order->total, 2) }} {{ __('messages.currency') }}</span>
                                                    </div>
                                                @else
                                                    <p class="text-muted mb-0">{{ __('messages.no_services') }}</p>
                                                @endif
                                            </div>

                                            @if($order->admin_notes)
                                            <div class="col-12">
                                                <h6><i class="bi bi-chat-text"></i> {{ __('messages.admin_notes') }}</h6>
                                                <p class="mb-0">{{ $order->admin_notes }}</p>
                                            </div>
                                            @endif

                                            <div class="col-12">
                                                <small class="text-muted">
                                                    {{ __('messages.creation_date') }}: {{ $order->created_at->format('Y-m-d H:i') }}
                                                    | {{ __('messages.last_update') }}: {{ $order->updated_at->format('Y-m-d H:i') }}
                                                    @if($order->cancelled_at)
                                                        | {{ __('messages.cancellation_date') }}: {{ \Carbon\Carbon::parse($order->cancelled_at)->format('Y-m-d H:i') }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">
                            <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                            {{ __('messages.no_customer_orders') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
