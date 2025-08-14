@extends('admin.layout')

@section('content')
<div class="container mt-4">
	<h2>إعدادات التطبيق</h2>
	@if(session('success'))
		<div class="alert alert-success">{{ session('success') }}</div>
	@endif
	<form method="POST" action="{{ route('admin.settings.update') }}">
		@csrf
		<div class="form-check form-switch">
			<input class="form-check-input" type="checkbox" id="packages_enabled" name="packages_enabled" value="1" {{ $packagesEnabled ? 'checked' : '' }}>
			<label class="form-check-label" for="packages_enabled">تفعيل الباقات (Packages)</label>
		</div>
		<button type="submit" class="btn btn-primary mt-3">حفظ</button>
	</form>
</div>
@endsection 