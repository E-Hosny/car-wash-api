@extends('admin.layout')

@section('title', 'User Package Subscriptions')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">@lang('packages.user_package_subscriptions')</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.user-packages.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> @lang('packages.add_new_subscription')
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('admin.user-packages.filter') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <select name="package_id" class="form-control">
                                    <option value="">@lang('packages.all_packages')</option>
                                    @foreach($packages as $package)
                                        <option value="{{ $package->id }}" {{ request('package_id') == $package->id ? 'selected' : '' }}>
                                            {{ $package->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">@lang('packages.all_status')</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>@lang('packages.active')</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>@lang('packages.inactive')</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>@lang('packages.expired')</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="user_id" class="form-control">
                                    <option value="">@lang('packages.all_users')</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" placeholder="@lang('packages.from_date')" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_to" class="form-control" placeholder="@lang('packages.to_date')" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info">@lang('packages.filter')</button>
                                <a href="{{ route('admin.user-packages.index') }}" class="btn btn-secondary">@lang('packages.clear')</a>
                            </div>
                        </div>
                    </form>

                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">@lang('packages.total_subscriptions')</span>
                                    <span class="info-box-number">{{ $userPackages->total() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">@lang('packages.active_subscriptions')</span>
                                    <span class="info-box-number">{{ $userPackages->where('status', 'active')->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">@lang('packages.expired_subscriptions')</span>
                                    <span class="info-box-number">{{ $userPackages->where('status', 'expired')->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-coins"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">@lang('packages.total_revenue')</span>
                                    <span class="info-box-number">{{ number_format($userPackages->sum('paid_amount'), 2) }} {{ __('packages.currency') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subscriptions Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>@lang('packages.id')</th>
                                    <th>@lang('packages.name')</th>
                                    <th>@lang('packages.package')</th>
                                    <th>@lang('packages.status')</th>
                                    <th>@lang('packages.points')</th>
                                    <th>@lang('packages.expires_at')</th>
                                    <th>@lang('packages.purchased_at')</th>
                                    <th>@lang('packages.actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($userPackages as $userPackage)
                                <tr>
                                    <td>{{ $userPackage->id }}</td>
                                    <td>
                                        <strong>{{ $userPackage->user->name }}</strong><br>
                                        <small class="text-muted">{{ $userPackage->user->email }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $userPackage->package->name }}</strong><br>
                                        <small class="text-muted">{{ $userPackage->paid_amount }} {{ __('packages.currency') }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $status = $userPackage->status ?? 'غير محدد';
                                        @endphp
                                        @if($status === 'active')
                                            <span class="badge bg-success text-white">@lang('packages.active')</span>
                                        @elseif($status === 'inactive')
                                            <span class="badge bg-secondary text-white">@lang('packages.inactive')</span>
                                        @elseif($status === 'expired')
                                            <span class="badge bg-danger text-white">@lang('packages.expired')</span>
                                        @elseif($status === 'cancelled')
                                            <span class="badge bg-dark text-white">@lang('packages.cancelled')</span>
                                        @else
                                            <span class="badge bg-light text-dark">@lang('packages.status') - @lang('packages.inactive')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $remaining = $userPackage->remaining_points ?? 0;
                                            $total = $userPackage->total_points ?? 0;
                                        @endphp
                                        <span class="badge bg-info text-dark">{{ $remaining }}/{{ $total }}</span>
                                    </td>
                                    <td>
                                        @if($userPackage->expires_at)
                                            {{ \Carbon\Carbon::parse($userPackage->expires_at)->format('M d, Y') }}
                                            @if($userPackage->expires_at < now())
                                                <br><small class="text-danger">@lang('packages.expired')</small>
                                            @endif
                                        @else
                                            <span class="text-muted">@lang('packages.no_expiry',[],'messages')</span>
                                        @endif
                                    </td>
                                    <td>{{ $userPackage->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Actions">
                                            <a href="{{ route('admin.user-packages.show', $userPackage->id) }}" class="btn btn-info btn-sm mx-1" title="@lang('packages.view_package')">
                                                <i class="fas fa-eye"></i> @lang('packages.view_package')
                                            </a>
                                            <a href="{{ route('admin.user-packages.edit', $userPackage->id) }}" class="btn btn-warning btn-sm mx-1" title="@lang('packages.edit')">
                                                <i class="fas fa-edit"></i> @lang('packages.edit')
                                            </a>
                                            @if($userPackage->status !== 'active')
                                                <form method="POST" action="{{ route('admin.user-packages.activate', $userPackage->id) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm mx-1" title="@lang('packages.activate')">
                                                        <i class="fas fa-play"></i> @lang('packages.activate')
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.user-packages.deactivate', $userPackage->id) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-secondary btn-sm mx-1" title="@lang('packages.deactivate')">
                                                        <i class="fas fa-pause"></i> @lang('packages.deactivate')
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.user-packages.extend', $userPackage->id) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-sm mx-1" title="@lang('packages.extend')">
                                                    <i class="fas fa-calendar-plus"></i> @lang('packages.extend',[],'messages')
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.user-packages.destroy', $userPackage->id) }}" style="display: inline;" onsubmit="return confirm('@lang('packages.confirm_delete')')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm mx-1" title="@lang('packages.delete')">
                                                    <i class="fas fa-trash"></i> @lang('packages.delete')
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">@lang('packages.no_packages_found')</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $userPackages->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 