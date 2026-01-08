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
		<h4 class="mb-3">الحدود الجغرافية (قديم - للتوافق فقط)</h4>
		<div class="alert alert-warning">
			<small>هذه الحقول للتوافق مع البيانات القديمة فقط. يرجى استخدام قسم "إدارة الحدود الجغرافية" أدناه.</small>
		</div>
		
		<div class="row">
			<div class="col-md-6 mb-3">
				<label for="dubai_min_latitude" class="form-label">الحد الأدنى للخط العرض (Latitude)</label>
				<input type="number" step="0.0001" class="form-control @error('dubai_min_latitude') is-invalid @enderror" 
					id="dubai_min_latitude" name="dubai_min_latitude" 
					value="{{ old('dubai_min_latitude', $dubaiMinLat) }}" 
					min="-90" max="90">
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
					min="-90" max="90">
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
					min="-180" max="180">
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
					min="-180" max="180">
				@error('dubai_max_longitude')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
				<small class="form-text text-muted">يجب أن يكون أكبر من الحد الأدنى</small>
			</div>
		</div>
		
		<button type="submit" class="btn btn-primary mt-3">حفظ الإعدادات</button>
	</form>

	<hr class="my-5">
	
	<!-- إدارة الحدود الجغرافية المتعددة -->
	<div class="mt-4">
		<h4 class="mb-3">إدارة الحدود الجغرافية</h4>
		<div class="alert alert-info">
			<small>يمكنك إضافة حدود جغرافية متعددة. سيتم قبول أي طلب يقع ضمن أي من هذه الحدود.</small>
		</div>

		<!-- زر إضافة حد جديد -->
		<button type="button" class="btn btn-success mb-3" onclick="toggleAddForm()">
			+ إضافة حد جديد
		</button>
		
		<!-- Form إضافة حد جديد (مخفي افتراضياً) -->
		<div id="addBoundForm" style="display: none;" class="card mb-4">
			<div class="card-header bg-success text-white">
				<h5 class="mb-0">إضافة حد جغرافي جديد</h5>
			</div>
			<div class="card-body">
				<form method="POST" action="{{ route('admin.settings.bounds.store') }}">
					@csrf
					<div class="mb-3">
						<label for="name" class="form-label">اسم الحد</label>
						<input type="text" class="form-control @error('name') is-invalid @enderror" 
							id="name" name="name" value="{{ old('name') }}" required>
						@error('name')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label for="min_latitude" class="form-label">الحد الأدنى للخط العرض</label>
							<input type="number" step="0.000001" class="form-control @error('min_latitude') is-invalid @enderror" 
								id="min_latitude" name="min_latitude" value="{{ old('min_latitude') }}" 
								min="-90" max="90" required>
							@error('min_latitude')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
						<div class="col-md-6 mb-3">
							<label for="max_latitude" class="form-label">الحد الأقصى للخط العرض</label>
							<input type="number" step="0.000001" class="form-control @error('max_latitude') is-invalid @enderror" 
								id="max_latitude" name="max_latitude" value="{{ old('max_latitude') }}" 
								min="-90" max="90" required>
							@error('max_latitude')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label for="min_longitude" class="form-label">الحد الأدنى للخط الطول</label>
							<input type="number" step="0.000001" class="form-control @error('min_longitude') is-invalid @enderror" 
								id="min_longitude" name="min_longitude" value="{{ old('min_longitude') }}" 
								min="-180" max="180" required>
							@error('min_longitude')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
						<div class="col-md-6 mb-3">
							<label for="max_longitude" class="form-label">الحد الأقصى للخط الطول</label>
							<input type="number" step="0.000001" class="form-control @error('max_longitude') is-invalid @enderror" 
								id="max_longitude" name="max_longitude" value="{{ old('max_longitude') }}" 
								min="-180" max="180" required>
							@error('max_longitude')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
					</div>
					<div class="d-flex justify-content-end gap-2">
						<button type="button" class="btn btn-secondary" onclick="toggleAddForm()">إلغاء</button>
						<button type="submit" class="btn btn-primary">إضافة</button>
					</div>
				</form>
			</div>
		</div>

		<!-- جدول الحدود -->
		@if($geographicalBounds && $geographicalBounds->count() > 0)
		<div class="table-responsive">
			<table class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>الاسم</th>
						<th>الحد الأدنى للعرض</th>
						<th>الحد الأقصى للعرض</th>
						<th>الحد الأدنى للطول</th>
						<th>الحد الأقصى للطول</th>
						<th>الإجراءات</th>
					</tr>
				</thead>
				<tbody>
					@foreach($geographicalBounds as $bound)
					<tr>
						<td>{{ $bound->name }}</td>
						<td>{{ $bound->min_latitude }}</td>
						<td>{{ $bound->max_latitude }}</td>
						<td>{{ $bound->min_longitude }}</td>
						<td>{{ $bound->max_longitude }}</td>
						<td>
							<button type="button" class="btn btn-sm btn-primary" 
								onclick="toggleEditForm({{ $bound->id }}, '{{ $bound->name }}', {{ $bound->min_latitude }}, {{ $bound->max_latitude }}, {{ $bound->min_longitude }}, {{ $bound->max_longitude }})">
								تعديل
							</button>
							<form method="POST" action="{{ route('admin.settings.bounds.destroy', $bound->id) }}" 
								style="display: inline-block;" 
								onsubmit="return confirm('هل أنت متأكد من حذف هذا الحد؟');">
								@csrf
								@method('DELETE')
								<button type="submit" class="btn btn-sm btn-danger">حذف</button>
							</form>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		@else
		<div class="alert alert-warning">
			لا توجد حدود جغرافية محددة. سيتم استخدام القيم الافتراضية من الإعدادات القديمة.
		</div>
		@endif
	</div>

	<!-- Form تعديل حد (مخفي افتراضياً) -->
	<div id="editBoundForm" style="display: none;" class="card mb-4">
		<div class="card-header bg-primary text-white">
			<h5 class="mb-0">تعديل حد جغرافي</h5>
		</div>
		<div class="card-body">
			<form method="POST" id="editBoundFormElement">
				@csrf
				<div class="mb-3">
					<label for="edit_name" class="form-label">اسم الحد</label>
					<input type="text" class="form-control" id="edit_name" name="name" required>
				</div>
				<div class="row">
					<div class="col-md-6 mb-3">
						<label for="edit_min_latitude" class="form-label">الحد الأدنى للخط العرض</label>
						<input type="number" step="0.000001" class="form-control" 
							id="edit_min_latitude" name="min_latitude" min="-90" max="90" required>
					</div>
					<div class="col-md-6 mb-3">
						<label for="edit_max_latitude" class="form-label">الحد الأقصى للخط العرض</label>
						<input type="number" step="0.000001" class="form-control" 
							id="edit_max_latitude" name="max_latitude" min="-90" max="90" required>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 mb-3">
						<label for="edit_min_longitude" class="form-label">الحد الأدنى للخط الطول</label>
						<input type="number" step="0.000001" class="form-control" 
							id="edit_min_longitude" name="min_longitude" min="-180" max="180" required>
					</div>
					<div class="col-md-6 mb-3">
						<label for="edit_max_longitude" class="form-label">الحد الأقصى للخط الطول</label>
						<input type="number" step="0.000001" class="form-control" 
							id="edit_max_longitude" name="max_longitude" min="-180" max="180" required>
					</div>
				</div>
				<div class="d-flex justify-content-end gap-2">
					<button type="button" class="btn btn-secondary" onclick="toggleEditForm()">إلغاء</button>
					<button type="submit" class="btn btn-primary">حفظ التعديلات</button>
				</div>
			</form>
		</div>
	</div>

	<script>
		// دوال بسيطة لإظهار/إخفاء النماذج
		function toggleAddForm() {
			var form = document.getElementById('addBoundForm');
			if (form) {
				if (form.style.display === 'none') {
					form.style.display = 'block';
					// إخفاء form التعديل إذا كان ظاهراً
					var editForm = document.getElementById('editBoundForm');
					if (editForm) {
						editForm.style.display = 'none';
					}
				} else {
					form.style.display = 'none';
				}
			}
		}
		
		function toggleEditForm(id, name, minLat, maxLat, minLng, maxLng) {
			var form = document.getElementById('editBoundForm');
			if (!form) return;
			
			if (form.style.display === 'none' || !id) {
				// ملء البيانات إذا تم تمريرها
				if (id && name !== undefined) {
					document.getElementById('edit_name').value = name || '';
					document.getElementById('edit_min_latitude').value = minLat || '';
					document.getElementById('edit_max_latitude').value = maxLat || '';
					document.getElementById('edit_min_longitude').value = minLng || '';
					document.getElementById('edit_max_longitude').value = maxLng || '';
					
					var formElement = document.getElementById('editBoundFormElement');
					if (formElement) {
						formElement.action = '{{ route("admin.settings.bounds.update", ":id") }}'.replace(':id', id);
					}
				}
				
				form.style.display = 'block';
				// إخفاء form الإضافة إذا كان ظاهراً
				var addForm = document.getElementById('addBoundForm');
				if (addForm) {
					addForm.style.display = 'none';
				}
			} else {
				form.style.display = 'none';
			}
		}
		
		// جعل الدوال متاحة بشكل global
		window.toggleAddForm = toggleAddForm;
		window.toggleEditForm = toggleEditForm;
	</script>
</div>
@endsection
