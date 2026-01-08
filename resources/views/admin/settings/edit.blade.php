@extends('admin.layout')

@section('content')
<div class="container mt-4">
	<h2>إعدادات التطبيق</h2>
	@if(session('success'))
		<div class="alert alert-success">{{ session('success') }}</div>
	@endif
	<form method="POST" action="{{ route('admin.settings.update') }}">
		@csrf
		<div class="form-check form-switch mb-3">
			<input class="form-check-input" type="checkbox" id="packages_enabled" name="packages_enabled" value="1" {{ $packagesEnabled ? 'checked' : '' }}>
			<label class="form-check-label" for="packages_enabled">تفعيل الباقات (Packages)</label>
		</div>
		<div class="mb-3">
			<label for="max_slots_per_hour" class="form-label">{{ __('messages.max_slots_per_hour_label') }}</label>
			<input type="number" class="form-control" id="max_slots_per_hour" name="max_slots_per_hour" value="{{ $maxSlotsPerHour }}" min="1" max="10" required>
			<small class="form-text text-muted">{{ __('messages.max_slots_per_hour_help') }}</small>
		</div>
		<div class="mb-3">
			<label for="support_whatsapp" class="form-label">رقم واتساب الدعم</label>
			<input type="text" class="form-control" id="support_whatsapp" name="support_whatsapp" value="{{ $supportWhatsapp }}" placeholder="966542327025" required>
			<small class="form-text text-muted">أدخل رقم واتساب الدعم (بدون + في البداية، مثال: 966542327025)</small>
		</div>
		<div class="mb-3">
			<label for="minimum_booking_advance_minutes" class="form-label">{{ __('messages.minimum_booking_advance_minutes_label') }}</label>
			<input type="number" class="form-control" id="minimum_booking_advance_minutes" name="minimum_booking_advance_minutes" value="{{ $minimumBookingAdvance }}" min="1" max="1440" required>
			<small class="form-text text-muted">{{ __('messages.minimum_booking_advance_minutes_help') }}</small>
		</div>
		
		<hr class="my-4">
		<h4 class="mb-3">الحدود الجغرافية</h4>
		<div class="alert alert-info">
			<small>حدد الحدود الجغرافية للمنطقة المسموح بالحجز فيها. سيتم رفض أي طلب خارج هذه الحدود.</small>
		</div>
		
		<div class="row">
			<div class="col-md-6 mb-3">
				<label for="dubai_min_latitude" class="form-label">الحد الأدنى للخط العرض (Latitude)</label>
				<input type="number" step="0.0001" class="form-control @error('dubai_min_latitude') is-invalid @enderror" 
					id="dubai_min_latitude" name="dubai_min_latitude" 
					value="{{ old('dubai_min_latitude', $dubaiMinLat) }}" 
					min="-90" max="90" required>
				@error('dubai_min_latitude')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
				<small class="form-text text-muted">يجب أن يكون أقل من الحد الأقصى</small>
			</div>
			<div class="col-md-6 mb-3">
				<label for="dubai_max_latitude" class="form-label">الحد الأقصى للخط العرض (Latitude)</label>
				<input type="number" step="0.0001" class="form-control @error('dubai_max_latitude') is-invalid @enderror" 
					id="dubai_max_latitude" name="dubai_max_latitude" 
					value="{{ old('dubai_max_latitude', $dubaiMaxLat) }}" 
					min="-90" max="90" required>
				@error('dubai_max_latitude')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
				<small class="form-text text-muted">يجب أن يكون أكبر من الحد الأدنى</small>
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-6 mb-3">
				<label for="dubai_min_longitude" class="form-label">الحد الأدنى للخط الطول (Longitude)</label>
				<input type="number" step="0.0001" class="form-control @error('dubai_min_longitude') is-invalid @enderror" 
					id="dubai_min_longitude" name="dubai_min_longitude" 
					value="{{ old('dubai_min_longitude', $dubaiMinLng) }}" 
					min="-180" max="180" required>
				@error('dubai_min_longitude')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
				<small class="form-text text-muted">يجب أن يكون أقل من الحد الأقصى</small>
			</div>
			<div class="col-md-6 mb-3">
				<label for="dubai_max_longitude" class="form-label">الحد الأقصى للخط الطول (Longitude)</label>
				<input type="number" step="0.0001" class="form-control @error('dubai_max_longitude') is-invalid @enderror" 
					id="dubai_max_longitude" name="dubai_max_longitude" 
					value="{{ old('dubai_max_longitude', $dubaiMaxLng) }}" 
					min="-180" max="180" required>
				@error('dubai_max_longitude')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
				<small class="form-text text-muted">يجب أن يكون أكبر من الحد الأدنى</small>
			</div>
		</div>
		
		<button type="submit" class="btn btn-primary mt-3">حفظ</button>
	</form>
</div>
@endsection 