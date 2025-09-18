<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-person"></i> {{ __('messages.customer_information') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <strong>{{ __('messages.name') }}:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ $order->customer->name ?? __('messages.not_specified') }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong>{{ __('messages.email') }}:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ $order->customer->email ?? __('messages.not_specified') }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong>{{ __('messages.phone_number') }}:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ $order->customer->phone ?? __('messages.not_specified') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-calendar"></i> {{ __('messages.appointment_details') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <strong>{{ __('messages.date') }}:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ \Carbon\Carbon::parse($order->scheduled_at)->format('Y-m-d') }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong>{{ __('messages.time') }}:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ \Carbon\Carbon::parse($order->scheduled_at)->format('h:i A') }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong>{{ __('messages.status') }}:</strong>
                    </div>
                    <div class="col-sm-8">
                        @php
                            $badgeClass = match($order->status) {
                                'pending' => 'warning',
                                'accepted' => 'info',
                                'in_progress' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge bg-{{ $badgeClass }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-car-front"></i> {{ __('messages.car_information') }}</h6>
            </div>
            <div class="card-body">
                @if($order->car)
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>{{ __('messages.make') }}:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->car->brand->name ?? __('messages.not_specified') }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>{{ __('messages.model') }}:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->car->model->name ?? __('messages.not_specified') }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>{{ __('messages.year') }}:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->car->year->year ?? __('messages.not_specified') }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>{{ __('messages.color') }}:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->car->color ?? __('messages.not_specified') }}
                        </div>
                    </div>
                    @if($order->car->license_plate)
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                <strong>{{ __('messages.license_plate') }}:</strong>
                            </div>
                            <div class="col-sm-8">
                                {{ $order->car->license_plate }}
                            </div>
                        </div>
                    @endif
                @else
                    <p class="text-muted">{{ __('messages.no_car_info') }}</p>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-geo-alt"></i> {{ __('messages.location_information') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <strong>{{ __('messages.address') }}:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ $order->address ?? __('messages.not_specified') }}
                    </div>
                </div>
                @if($order->street)
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>{{ __('messages.street') }}:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->street }}
                        </div>
                    </div>
                @endif
                @if($order->building)
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>{{ __('messages.building') }}:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->building }}
                        </div>
                    </div>
                @endif
                @if($order->floor)
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>{{ __('messages.floor') }}:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->floor }}
                        </div>
                    </div>
                @endif
                @if($order->apartment)
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>{{ __('messages.apartment') }}:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->apartment }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="bi bi-list-check"></i> {{ __('messages.required_services') }}</h6>
            </div>
            <div class="card-body">
                @if($order->services->count() > 0)
                    <div class="row">
                        @foreach($order->services as $service)
                            <div class="col-md-6 mb-2">
                                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                    <span>{{ $service->name }}</span>
                                    <span class="badge bg-primary">{{ number_format($service->price, 2) }} AED</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ __('messages.total') }}:</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="fs-5 fw-bold text-success">{{ number_format($order->total, 2) }} AED</span>
                        </div>
                    </div>
                @else
                    <p class="text-muted">{{ __('messages.no_services') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if($order->admin_notes)
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0"><i class="bi bi-chat-text"></i> {{ __('messages.admin_notes') }}</h6>
            </div>
            <div class="card-body">
                <p>{{ $order->admin_notes }}</p>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> {{ __('messages.order_log') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>{{ __('messages.creation_date') }}:</strong>
                        {{ $order->created_at->format('Y-m-d H:i') }}
                    </div>
                    <div class="col-md-6">
                        <strong>{{ __('messages.last_update') }}:</strong>
                        {{ $order->updated_at->format('Y-m-d H:i') }}
                    </div>
                </div>
                @if($order->cancelled_at)
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>تاريخ الإلغاء:</strong>
                            {{ \Carbon\Carbon::parse($order->cancelled_at)->format('Y-m-d H:i') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
