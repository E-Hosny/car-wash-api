@extends('admin.layout')

@section('title', 'User Package Subscription Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Subscription Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.user-packages.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('admin.user-packages.edit', $userPackage->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- User Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">User Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Name:</strong></td>
                                            <td>{{ $userPackage->user->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $userPackage->user->email }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone:</strong></td>
                                            <td>{{ $userPackage->user->phone ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Role:</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $userPackage->user->role === 'admin' ? 'danger' : 'info' }}">
                                                    {{ ucfirst($userPackage->user->role) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Joined:</strong></td>
                                            <td>{{ $userPackage->user->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Package Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Package Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Package Name:</strong></td>
                                            <td>{{ $userPackage->package->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Description:</strong></td>
                                            <td>{{ $userPackage->package->description ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Original Price:</strong></td>
                                            <td>${{ number_format($userPackage->package->price, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Original Points:</strong></td>
                                            <td>{{ $userPackage->package->points }} points</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Package Status:</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $userPackage->package->is_active ? 'success' : 'secondary' }}">
                                                    {{ $userPackage->package->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- Subscription Details -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Subscription Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info">
                                                    <i class="fas fa-coins"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Points</span>
                                                    <span class="info-box-number">{{ $userPackage->total_points }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success">
                                                    <i class="fas fa-check-circle"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Remaining Points</span>
                                                    <span class="info-box-number">{{ $userPackage->remaining_points }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Paid Amount</span>
                                                    <span class="info-box-number">${{ number_format($userPackage->paid_amount, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-{{ $userPackage->status === 'active' ? 'success' : 'danger' }}">
                                                    <i class="fas fa-{{ $userPackage->status === 'active' ? 'play' : 'pause' }}"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Status</span>
                                                    <span class="info-box-number">{{ ucfirst($userPackage->status) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Subscription ID:</strong></td>
                                                    <td>#{{ $userPackage->id }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Purchased At:</strong></td>
                                                    <td>{{ $userPackage->purchased_at ? $userPackage->purchased_at->format('M d, Y H:i') : 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Created At:</strong></td>
                                                    <td>{{ $userPackage->created_at->format('M d, Y H:i') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Updated At:</strong></td>
                                                    <td>{{ $userPackage->updated_at->format('M d, Y H:i') }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Expires At:</strong></td>
                                                    <td>
                                                        @if($userPackage->expires_at)
                                                            {{ $userPackage->expires_at->format('M d, Y') }}
                                                            @if($userPackage->expires_at < now())
                                                                <br><span class="text-danger">Expired</span>
                                                            @else
                                                                <br><span class="text-success">Valid</span>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">No expiry date</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Payment Intent ID:</strong></td>
                                                    <td>{{ $userPackage->payment_intent_id ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Points Used:</strong></td>
                                                    <td>{{ $userPackage->total_points - $userPackage->remaining_points }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Usage Percentage:</strong></td>
                                                    <td>
                                                        @if($userPackage->total_points > 0)
                                                            {{ round((($userPackage->total_points - $userPackage->remaining_points) / $userPackage->total_points) * 100, 1) }}%
                                                        @else
                                                            0%
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group">
                                        @if($userPackage->status !== 'active')
                                            <form method="POST" action="{{ route('admin.user-packages.activate', $userPackage->id) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-play"></i> Activate Subscription
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.user-packages.deactivate', $userPackage->id) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary">
                                                    <i class="fas fa-pause"></i> Deactivate Subscription
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <form method="POST" action="{{ route('admin.user-packages.extend', $userPackage->id) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-calendar-plus"></i> Extend 1 Year
                                            </button>
                                        </form>
                                        
                                        <form method="POST" action="{{ route('admin.user-packages.destroy', $userPackage->id) }}" 
                                              style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this subscription?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash"></i> Delete Subscription
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 