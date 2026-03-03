@extends('admin.layout')

@section('content')
<div class="container mt-4">
	<h2>ربط Slots بالعمال</h2>
	<p class="text-muted">عند حجز العميل لوقت معيّن، يُحدد النظام تلقائياً أحد الـ Slots (مثلاً 1 أو 2). إذا ربطت كل Slot بعامل هنا، سيتم توجيه الطلب تلقائياً لذلك العامل مع إرسال إشعار واتساب وإشعار التطبيق دون الحاجة للرئيسي.</p>
	@if(session('success'))
		<div class="alert alert-success">{{ session('success') }}</div>
	@endif
	<form method="POST" action="{{ route('admin.settings.slot-workers.update') }}">
		@csrf
		@for($i = 1; $i <= $maxSlotsPerHour; $i++)
		<div class="mb-3">
			<label for="slot_{{ $i }}_worker_id" class="form-label">Slot {{ $i }}</label>
			<select class="form-select" id="slot_{{ $i }}_worker_id" name="slot_{{ $i }}_worker_id">
				<option value="">— لا توجيه تلقائي —</option>
				@foreach($workers as $worker)
					<option value="{{ $worker->id }}" {{ (isset($slotWorkerIds[(string)$i]) && (int)$slotWorkerIds[(string)$i] === (int)$worker->id) ? 'selected' : '' }}>
						{{ $worker->name }} @if($worker->phone)({{ $worker->phone }})@endif
					</option>
				@endforeach
			</select>
		</div>
		@endfor
		<button type="submit" class="btn btn-primary">حفظ</button>
		<a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">العودة للإعدادات</a>
	</form>
</div>
@endsection
