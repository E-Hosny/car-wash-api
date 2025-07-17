@extends('admin.layout')

@section('title', 'Edit User Package Subscription')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit User Package Subscription</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.user-packages.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.user-packages.update', $userPackage->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_id">User <span class="text-danger">*</span></label>
                                    <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                        <option value="">Select User</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" 
                                                    {{ old('user_id', $userPackage->user_id) == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="package_id">Package <span class="text-danger">*</span></label>
                                    <select name="package_id" id="package_id" class="form-control @error('package_id') is-invalid @enderror" required>
                                        <option value="">Select Package</option>
                                        @foreach($packages as $package)
                                            <option value="{{ $package->id }}" 
                                                    data-points="{{ $package->points }}"
                                                    data-price="{{ $package->price }}"
                                                    {{ old('package_id', $userPackage->package_id) == $package->id ? 'selected' : '' }}>
                                                {{ $package->name }} - ${{ $package->price }} ({{ $package->points }} points)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('package_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="active" {{ old('status', $userPackage->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $userPackage->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="expired" {{ old('status', $userPackage->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="remaining_points">Remaining Points <span class="text-danger">*</span></label>
                                    <input type="number" name="remaining_points" id="remaining_points" 
                                           class="form-control @error('remaining_points') is-invalid @enderror" 
                                           value="{{ old('remaining_points', $userPackage->remaining_points) }}" min="0" required>
                                    @error('remaining_points')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="expires_at">Expires At</label>
                                    <input type="date" name="expires_at" id="expires_at" 
                                           class="form-control @error('expires_at') is-invalid @enderror" 
                                           value="{{ old('expires_at', $userPackage->expires_at ? $userPackage->expires_at->format('Y-m-d') : '') }}">
                                    @error('expires_at')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea name="notes" id="notes" rows="3" 
                                      class="form-control @error('notes') is-invalid @enderror" 
                                      placeholder="Optional notes about this subscription">{{ old('notes', $userPackage->notes) }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Current Subscription Info -->
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Current Subscription Information</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Total Points:</strong> {{ $userPackage->total_points }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Paid Amount:</strong> ${{ $userPackage->paid_amount }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Created:</strong> {{ $userPackage->created_at->format('M d, Y') }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Last Updated:</strong> {{ $userPackage->updated_at->format('M d, Y') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Subscription
                            </button>
                            <a href="{{ route('admin.user-packages.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const packageSelect = document.getElementById('package_id');
    const remainingPointsInput = document.getElementById('remaining_points');
    
    packageSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const points = selectedOption.getAttribute('data-points');
            remainingPointsInput.max = points;
        } else {
            remainingPointsInput.max = '';
        }
    });
});
</script>
@endpush
@endsection 