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
		<button type="submit" class="btn btn-primary mt-3">حفظ</button>
	</form>
</div>
@endsection 