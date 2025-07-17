@extends('admin.layout')

@section('title', 'Create User Package Subscription')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create New User Package Subscription</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.user-packages.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.user-packages.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_id">User <span class="text-danger">*</span></label>
                                    <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                        <option value="">Select User</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
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
                                            <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
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
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total_points">Total Points</label>
                                    <input type="number" name="total_points" id="total_points" class="form-control @error('total_points') is-invalid @enderror" 
                                           value="{{ old('total_points') }}" min="0">
                                    @error('total_points')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="remaining_points">Remaining Points</label>
                                    <input type="number" name="remaining_points" id="remaining_points" class="form-control @error('remaining_points') is-invalid @enderror" 
                                           value="{{ old('remaining_points') }}" min="0">
                                    @error('remaining_points')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="expires_at">Expires At</label>
                                    <input type="date" name="expires_at" id="expires_at" class="form-control @error('expires_at') is-invalid @enderror" 
                                           value="{{ old('expires_at') }}">
                                    @error('expires_at')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="paid_amount">Paid Amount</label>
                                    <input type="number" name="paid_amount" id="paid_amount" class="form-control @error('paid_amount') is-invalid @enderror" 
                                           value="{{ old('paid_amount') }}" step="0.01" min="0">
                                    @error('paid_amount')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Subscription
                            </button>
                            <a href="{{ route('admin.user-packages.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill total points when package is selected
    const packageSelect = document.getElementById('package_id');
    const totalPointsInput = document.getElementById('total_points');
    const remainingPointsInput = document.getElementById('remaining_points');
    
    packageSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const packageText = selectedOption.text;
        
        // Extract points from package text (assuming format: "Package Name - $Price (X points)")
        const pointsMatch = packageText.match(/\((\d+) points\)/);
        if (pointsMatch) {
            const points = pointsMatch[1];
            totalPointsInput.value = points;
            remainingPointsInput.value = points;
        }
    });
});
</script>
@endsection 