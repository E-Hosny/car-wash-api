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
		<h4 class="mb-3">تحديث التطبيق (إجباري)</h4>
		<div class="alert alert-info">
			<small>إذا حددت إصداراً أدنى، سيُطلب من المستخدمين الذين لديهم إصدار أقدم تحديث التطبيق قبل الاستمرار. اترك الحقل فارغاً لعدم فرض تحديث.</small>
		</div>
		<div class="row">
			<div class="col-md-6 mb-3">
				<label for="min_android_version" class="form-label">أقل إصدار أندرويد (مثال: 1.2.6)</label>
				<input type="text" class="form-control" id="min_android_version" name="min_android_version" value="{{ old('min_android_version', $minAndroidVersion) }}" placeholder="1.2.6">
			</div>
			<div class="col-md-6 mb-3">
				<label for="min_ios_version" class="form-label">أقل إصدار آيفون (مثال: 1.2.6)</label>
				<input type="text" class="form-control" id="min_ios_version" name="min_ios_version" value="{{ old('min_ios_version', $minIosVersion) }}" placeholder="1.2.6">
			</div>
		</div>
		<div class="row">
			<div class="col-md-6 mb-3">
				<label for="android_store_url" class="form-label">رابط تطبيق أندرويد (Google Play)</label>
				<input type="url" class="form-control" id="android_store_url" name="android_store_url" value="{{ old('android_store_url', $androidStoreUrl) }}" placeholder="https://play.google.com/store/apps/details?id=...">
			</div>
			<div class="col-md-6 mb-3">
				<label for="ios_store_url" class="form-label">رابط تطبيق آيفون (App Store)</label>
				<input type="url" class="form-control" id="ios_store_url" name="ios_store_url" value="{{ old('ios_store_url', $iosStoreUrl) }}" placeholder="https://apps.apple.com/app/id...">
			</div>
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
	
	<!-- قسم الخريطة لرسم المناطق -->
	<div class="card mb-4 mt-4">
		<div class="card-header bg-info text-white">
			<h5 class="mb-0"><i class="fas fa-map"></i> رسم المنطقة على الخريطة</h5>
		</div>
		<div class="card-body">
			<div class="mb-3">
				<button type="button" class="btn btn-primary" id="startDrawing">
					<i class="fas fa-draw-polygon"></i> ابدأ الرسم
				</button>
				<button type="button" class="btn btn-secondary" id="clearDrawing" style="display:none;">
					<i class="fas fa-eraser"></i> مسح الرسم
				</button>
				<button type="button" class="btn btn-success" id="applyBounds" style="display:none;">
					<i class="fas fa-check"></i> تطبيق الحدود على الحقول
				</button>
			</div>
			<div id="map" style="height: 500px; width: 100%; border: 1px solid #ddd; border-radius: 5px;"></div>
			<div class="alert alert-info mt-2 mb-0">
				<small>
					<i class="fas fa-info-circle"></i> <strong>خطوات الاستخدام:</strong><br>
					1. اضغط "ابدأ الرسم" ثم ارسم المضلع على الخريطة<br>
					2. بعد الانتهاء، اضغط "تطبيق الحدود على الحقول"<br>
					3. أدخل اسم الحد في النموذج الذي سيظهر تلقائياً<br>
					4. اضغط "إضافة" لحفظ الحد الجغرافي
				</small>
			</div>
			
			<!-- قسم عرض نقاط المضلع -->
			<div id="polygonPointsSection" style="display: none;" class="mt-3">
				<div class="card border-info">
					<div class="card-header bg-light">
						<h6 class="mb-0"><i class="fas fa-list"></i> نقاط المضلع المرسوم</h6>
					</div>
					<div class="card-body">
						<div class="d-flex justify-content-between align-items-center mb-2">
							<small class="text-muted">عدد النقاط: <span id="pointsCount">0</span></small>
							<button type="button" class="btn btn-sm btn-outline-primary" id="copyPoints">
								<i class="fas fa-copy"></i> نسخ النقاط
							</button>
						</div>
						<div style="max-height: 200px; overflow-y: auto;">
							<table class="table table-sm table-bordered table-striped mb-0">
								<thead class="table-light">
									<tr>
										<th style="width: 50px;">#</th>
										<th>Latitude</th>
										<th>Longitude</th>
									</tr>
								</thead>
								<tbody id="pointsList">
									<!-- سيتم ملؤها بواسطة JavaScript -->
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
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
				<form method="POST" action="{{ route('admin.settings.bounds.store') }}" id="addBoundFormElement">
					@csrf
					<input type="hidden" id="polygon_points" name="polygon_points" value="">
					<div class="mb-3">
						<label for="name" class="form-label">اسم الحد</label>
						<input type="text" class="form-control @error('name') is-invalid @enderror" 
							id="name" name="name" value="{{ old('name') }}" required>
						@error('name')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror
					</div>
					@error('polygon_points')
						<div class="alert alert-danger">
							{{ $message }}
						</div>
					@enderror
					<div class="alert alert-warning">
						<small><i class="fas fa-exclamation-triangle"></i> يجب رسم منطقة على الخريطة أولاً قبل الحفظ</small>
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
								onclick="toggleEditForm({{ $bound->id }}, '{{ $bound->name }}', null, null, null, null, {{ $bound->polygon_points ? json_encode($bound->polygon_points) : 'null' }})">
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
				<input type="hidden" id="edit_polygon_points" name="polygon_points" value="">
				<div class="mb-3">
					<label for="edit_name" class="form-label">اسم الحد</label>
					<input type="text" class="form-control" id="edit_name" name="name" required>
				</div>
				<div class="alert alert-warning">
					<small><i class="fas fa-exclamation-triangle"></i> يجب رسم منطقة على الخريطة أولاً قبل الحفظ</small>
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
		
		function toggleEditForm(id, name, minLat, maxLat, minLng, maxLng, polygonPointsData) {
			var form = document.getElementById('editBoundForm');
			if (!form) return;
			
			if (form.style.display === 'none' || !id) {
				// ملء البيانات إذا تم تمريرها
				if (id && name !== undefined) {
					document.getElementById('edit_name').value = name || '';
					
					// حفظ polygon_points إذا كانت موجودة
					var editPolygonPointsInput = document.getElementById('edit_polygon_points');
					if (editPolygonPointsInput && polygonPointsData && polygonPointsData !== 'null') {
						try {
							var points = typeof polygonPointsData === 'string' ? JSON.parse(polygonPointsData) : polygonPointsData;
							
							// التأكد من أن النقاط صحيحة
							if (Array.isArray(points) && points.length >= 3) {
								editPolygonPointsInput.value = JSON.stringify(points);
								
								// رسم المضلع على الخريطة إذا كان موجوداً
								if (map) {
									// حذف أي مضلع سابق
									if (selectedShape) {
										selectedShape.setMap(null);
										selectedShape = null;
									}
									
									// رسم المضلع الفعلي من النقاط المحفوظة
									const polygonPath = points.map(function(point) {
										return { lat: parseFloat(point.lat), lng: parseFloat(point.lng) };
									});
									
									selectedShape = new google.maps.Polygon({
										paths: polygonPath,
										fillColor: '#4285F4',
										fillOpacity: 0.3,
										strokeWeight: 2,
										strokeColor: '#4285F4',
										editable: true,
										map: map
									});
									
									// تحديث النقاط (استخدام getPolygonPoints للحصول على النقاط المنظفة)
									polygonPoints = getPolygonPoints(selectedShape);
									
									// استخراج الحدود
									const bounds = getPolygonBounds(selectedShape);
									polygonBounds = bounds;
									
									// عرض النقاط
									displayPolygonPoints(polygonPoints);
									
									// إضافة listener لتحديث النقاط عند تعديل المضلع
									selectedShape.getPath().addListener('set_at', function() {
										polygonPoints = getPolygonPoints(selectedShape);
										polygonBounds = getPolygonBounds(selectedShape);
										displayPolygonPoints(polygonPoints);
									});
									
									selectedShape.getPath().addListener('insert_at', function() {
										polygonPoints = getPolygonPoints(selectedShape);
										polygonBounds = getPolygonBounds(selectedShape);
										displayPolygonPoints(polygonPoints);
									});
									
									selectedShape.getPath().addListener('remove_at', function() {
										polygonPoints = getPolygonPoints(selectedShape);
										polygonBounds = getPolygonBounds(selectedShape);
										displayPolygonPoints(polygonPoints);
									});
									
									// إظهار أزرار التحكم
									document.getElementById('startDrawing').style.display = 'none';
									document.getElementById('clearDrawing').style.display = 'inline-block';
									document.getElementById('applyBounds').style.display = 'inline-block';
									
									// التمرير إلى الخريطة
									setTimeout(function() {
										const mapElement = document.getElementById('map');
										if (mapElement) {
											mapElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
										}
									}, 100);
								}
							} else {
								editPolygonPointsInput.value = '';
							}
						} catch(e) {
							console.error('Error loading polygon points:', e);
							if (editPolygonPointsInput) {
								editPolygonPointsInput.value = '';
							}
						}
					} else if (editPolygonPointsInput) {
						editPolygonPointsInput.value = '';
					}
					
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
		
		// Google Maps Integration
		let map;
		let drawingManager;
		let selectedShape;
		let polygonBounds = null;
		let polygonPoints = [];
		let existingBoundsShapes = [];

		function initMap() {
			// مركز الخريطة على دبي
			const dubaiCenter = { lat: 25.2048, lng: 55.2708 };
			
			map = new google.maps.Map(document.getElementById('map'), {
				center: dubaiCenter,
				zoom: 11,
				mapTypeId: 'roadmap'
			});

			// إعداد Drawing Manager
			drawingManager = new google.maps.drawing.DrawingManager({
				drawingMode: null,
				drawingControl: false,
				polygonOptions: {
					fillColor: '#4285F4',
					fillOpacity: 0.3,
					strokeWeight: 2,
					strokeColor: '#4285F4',
					clickable: false,
					editable: true,
					zIndex: 1
				}
			});

			drawingManager.setMap(map);

			// عند الانتهاء من الرسم
			google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
				if (event.type === google.maps.drawing.OverlayType.POLYGON) {
					// حذف أي مضلع سابق
					if (selectedShape) {
						selectedShape.setMap(null);
					}
					
					selectedShape = event.overlay;
					
					// استخراج النقاط من المضلع
					polygonPoints = getPolygonPoints(selectedShape);
					
					// التحقق من صحة المضلع
					const validation = validatePolygon(polygonPoints);
					if (!validation.valid) {
						alert(validation.message);
						selectedShape.setMap(null);
						selectedShape = null;
						polygonPoints = [];
						document.getElementById('startDrawing').style.display = 'inline-block';
						return;
					}
					
					// استخراج الحدود من المضلع
					const bounds = getPolygonBounds(selectedShape);
					polygonBounds = bounds;
					
					// عرض النقاط
					displayPolygonPoints(polygonPoints);
					
					// إضافة listener لتحديث النقاط عند تعديل المضلع
					selectedShape.getPath().addListener('set_at', function() {
						polygonPoints = getPolygonPoints(selectedShape);
						polygonBounds = getPolygonBounds(selectedShape);
						displayPolygonPoints(polygonPoints);
					});
					
					selectedShape.getPath().addListener('insert_at', function() {
						polygonPoints = getPolygonPoints(selectedShape);
						polygonBounds = getPolygonBounds(selectedShape);
						displayPolygonPoints(polygonPoints);
					});
					
					selectedShape.getPath().addListener('remove_at', function() {
						polygonPoints = getPolygonPoints(selectedShape);
						polygonBounds = getPolygonBounds(selectedShape);
						displayPolygonPoints(polygonPoints);
					});
					
					// إظهار أزرار التحكم
					document.getElementById('startDrawing').style.display = 'none';
					document.getElementById('clearDrawing').style.display = 'inline-block';
					document.getElementById('applyBounds').style.display = 'inline-block';
					
					// إيقاف وضع الرسم
					drawingManager.setDrawingMode(null);
				}
			});
			
			// عرض الحدود الموجودة على الخريطة
			displayExistingBounds();
		}

		// استخراج النقاط من المضلع مع تحسينات
		function getPolygonPoints(polygon) {
			const paths = polygon.getPath();
			const points = [];
			const tolerance = 0.000001; // للتحقق من النقاط المكررة
			
			// استخراج جميع النقاط
			paths.forEach(function(latLng) {
				points.push({
					lat: parseFloat(latLng.lat().toFixed(6)),
					lng: parseFloat(latLng.lng().toFixed(6))
				});
			});
			
			// إزالة النقاط المكررة المتتالية
			const cleanedPoints = [];
			for (let i = 0; i < points.length; i++) {
				const current = points[i];
				const prev = cleanedPoints[cleanedPoints.length - 1];
				
				if (!prev || 
					Math.abs(current.lat - prev.lat) > tolerance || 
					Math.abs(current.lng - prev.lng) > tolerance) {
					cleanedPoints.push(current);
				}
			}
			
			// إزالة النقطة الأخيرة إذا كانت مكررة (Google Maps يغلق المضلع تلقائياً)
			if (cleanedPoints.length > 0) {
				const first = cleanedPoints[0];
				const last = cleanedPoints[cleanedPoints.length - 1];
				
				// إذا كانت النقطة الأخيرة = الأولى، أزل النقطة الأخيرة
				if (Math.abs(first.lat - last.lat) < tolerance && 
					Math.abs(first.lng - last.lng) < tolerance &&
					cleanedPoints.length > 3) {
					cleanedPoints.pop(); // إزالة النقطة الأخيرة المكررة
				}
			}
			
			return cleanedPoints;
		}
		
		// التحقق من صحة المضلع
		function validatePolygon(points) {
			// التحقق من عدد النقاط
			if (!points || points.length < 3) {
				return {
					valid: false,
					message: 'يجب أن يحتوي المضلع على 3 نقاط على الأقل'
				};
			}
			
			// التحقق من أن النقاط ليست على خط مستقيم (للمثلث)
			if (points.length === 3) {
				const p1 = points[0];
				const p2 = points[1];
				const p3 = points[2];
				
				// حساب المسافة بين النقاط
				const dist12 = Math.sqrt(Math.pow(p2.lat - p1.lat, 2) + Math.pow(p2.lng - p1.lng, 2));
				const dist23 = Math.sqrt(Math.pow(p3.lat - p2.lat, 2) + Math.pow(p3.lng - p2.lng, 2));
				const dist13 = Math.sqrt(Math.pow(p3.lat - p1.lat, 2) + Math.pow(p3.lng - p1.lng, 2));
				
				// إذا كانت النقاط على خط مستقيم، مجموع المسافتين = المسافة الثالثة
				const tolerance = 0.0001;
				if (Math.abs(dist12 + dist23 - dist13) < tolerance ||
					Math.abs(dist12 + dist13 - dist23) < tolerance ||
					Math.abs(dist23 + dist13 - dist12) < tolerance) {
					return {
						valid: false,
						message: 'النقاط الثلاث على خط مستقيم. يرجى اختيار نقاط تشكل مثلثاً'
					};
				}
			}
			
			// لا نحتاج للتحقق من إغلاق المضلع - Google Maps يغلقه تلقائياً
			return { valid: true };
		}
		
		// استخراج الحدود من المضلع
		function getPolygonBounds(polygon) {
			const paths = polygon.getPath();
			let minLat = Infinity, maxLat = -Infinity;
			let minLng = Infinity, maxLng = -Infinity;
			
			paths.forEach(function(latLng) {
				const lat = latLng.lat();
				const lng = latLng.lng();
				
				minLat = Math.min(minLat, lat);
				maxLat = Math.max(maxLat, lat);
				minLng = Math.min(minLng, lng);
				maxLng = Math.max(maxLng, lng);
			});
			
			return {
				min_latitude: minLat,
				max_latitude: maxLat,
				min_longitude: minLng,
				max_longitude: maxLng
			};
		}
		
		// تحديد نوع الشكل
		function getPolygonType(points) {
			const pointCount = points.length;
			if (pointCount === 3) {
				return 'مثلث';
			} else if (pointCount === 4) {
				return 'مربع/مستطيل';
			} else if (pointCount >= 5) {
				return 'مضلع (' + pointCount + ' أضلاع)';
			}
			return 'مضلع';
		}
		
		// عرض نقاط المضلع في الجدول
		function displayPolygonPoints(points) {
			const pointsList = document.getElementById('pointsList');
			const pointsCount = document.getElementById('pointsCount');
			const pointsSection = document.getElementById('polygonPointsSection');
			
			if (!pointsList || !pointsCount || !pointsSection) return;
			
			// تحديث العدد مع نوع الشكل
			const polygonType = getPolygonType(points);
			pointsCount.textContent = points.length + ' (' + polygonType + ')';
			
			// مسح الجدول
			pointsList.innerHTML = '';
			
			// إضافة جميع النقاط (بدون النقطة المكررة - getPolygonPoints تزيلها)
			points.forEach(function(point, index) {
				const row = document.createElement('tr');
				row.innerHTML = `
					<td>${index + 1}</td>
					<td>${point.lat.toFixed(6)}</td>
					<td>${point.lng.toFixed(6)}</td>
				`;
				pointsList.appendChild(row);
			});
			
			// إظهار القسم
			pointsSection.style.display = 'block';
		}

		// تطبيق الحدود على الحقول
		function applyBoundsToForm() {
			if (!polygonPoints || polygonPoints.length < 3) {
				alert('يجب رسم مضلع يحتوي على 3 نقاط على الأقل');
				return;
			}
			
			// التحقق من صحة المضلع قبل الحفظ
			const validation = validatePolygon(polygonPoints);
			if (!validation.valid) {
				alert(validation.message);
				return;
			}
			
			// حفظ نقاط المضلع كما هي (بدون النقطة المكررة - getPolygonPoints تزيلها)
			const polygonPointsInput = document.getElementById('polygon_points');
			const editPolygonPointsInput = document.getElementById('edit_polygon_points');
			
			const pointsJson = JSON.stringify(polygonPoints);
			if (polygonPointsInput) polygonPointsInput.value = pointsJson;
			if (editPolygonPointsInput) editPolygonPointsInput.value = pointsJson;
		}
		
		// عرض الحدود الموجودة على الخريطة
		function displayExistingBounds() {
			@if($geographicalBounds && $geographicalBounds->count() > 0)
				const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F7DC6F', '#BB8FCE'];
				let colorIndex = 0;
				
				@foreach($geographicalBounds as $bound)
					@if($bound->polygon_points && is_array($bound->polygon_points) && count($bound->polygon_points) >= 3)
						// استخدام Polygon للشكل الفعلي المرسوم
						const polygonPoints{{ $bound->id }} = @json($bound->polygon_points);
						const paths{{ $bound->id }} = polygonPoints{{ $bound->id }}.map(function(point) {
							return { lat: point.lat, lng: point.lng };
						});
						
						const bounds{{ $bound->id }} = new google.maps.Polygon({
							paths: paths{{ $bound->id }},
							fillColor: colors[colorIndex % colors.length],
							fillOpacity: 0.2,
							strokeWeight: 2,
							strokeColor: colors[colorIndex % colors.length],
							editable: false,
							draggable: false,
							map: map
						});
						
						// حساب المركز للمعلومات
						const boundsObj{{ $bound->id }} = new google.maps.LatLngBounds();
						paths{{ $bound->id }}.forEach(function(point) {
							boundsObj{{ $bound->id }}.extend(point);
						});
						
						// إضافة معلومات عند النقر
						const infoWindow{{ $bound->id }} = new google.maps.InfoWindow({
							content: '<div style="padding: 5px;"><strong>{{ $bound->name }}</strong><br/>عدد النقاط: ' + polygonPoints{{ $bound->id }}.length + '</div>'
						});
						
						bounds{{ $bound->id }}.addListener('click', function(event) {
							infoWindow{{ $bound->id }}.setPosition(event.latLng);
							infoWindow{{ $bound->id }}.open(map);
						});
					@else
						// استخدام Rectangle للبيانات القديمة (للتوافق)
						const bounds{{ $bound->id }} = new google.maps.Rectangle({
							bounds: {
								north: {{ $bound->max_latitude }},
								south: {{ $bound->min_latitude }},
								east: {{ $bound->max_longitude }},
								west: {{ $bound->min_longitude }}
							},
							fillColor: colors[colorIndex % colors.length],
							fillOpacity: 0.2,
							strokeWeight: 2,
							strokeColor: colors[colorIndex % colors.length],
							editable: false,
							draggable: false,
							map: map
						});
						
						// إضافة معلومات عند النقر
						const infoWindow{{ $bound->id }} = new google.maps.InfoWindow({
							content: '<div style="padding: 5px;"><strong>{{ $bound->name }}</strong><br/>العرض: {{ $bound->min_latitude }} - {{ $bound->max_latitude }}<br/>الطول: {{ $bound->min_longitude }} - {{ $bound->max_longitude }}</div>'
						});
						
						bounds{{ $bound->id }}.addListener('click', function() {
							infoWindow{{ $bound->id }}.setPosition(bounds{{ $bound->id }}.getBounds().getCenter());
							infoWindow{{ $bound->id }}.open(map);
						});
					@endif
					
					existingBoundsShapes.push(bounds{{ $bound->id }});
					colorIndex++;
				@endforeach
			@endif
		}

		// تهيئة الخريطة عند تحميل الصفحة
		document.addEventListener('DOMContentLoaded', function() {
			const apiKey = '{{ env("GOOGLE_MAPS_API_KEY") }}';
			if (apiKey && apiKey !== '') {
				// تحميل Google Maps API
				const script = document.createElement('script');
				script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=drawing&callback=initMap`;
				script.async = true;
				script.defer = true;
				document.head.appendChild(script);
			} else {
				document.getElementById('map').innerHTML = '<div class="alert alert-warning m-3">يرجى إضافة Google Maps API Key في ملف .env</div>';
			}
			
			// زر بدء الرسم
			const startDrawingBtn = document.getElementById('startDrawing');
			if (startDrawingBtn) {
				startDrawingBtn.addEventListener('click', function() {
					if (drawingManager) {
						// حذف أي رسم سابق
						if (selectedShape) {
							selectedShape.setMap(null);
							selectedShape = null;
							polygonBounds = null;
							polygonPoints = [];
						}
						// إخفاء قسم النقاط
						const pointsSection = document.getElementById('polygonPointsSection');
						if (pointsSection) pointsSection.style.display = 'none';
						
						drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
						this.style.display = 'none';
						document.getElementById('clearDrawing').style.display = 'none';
						document.getElementById('applyBounds').style.display = 'none';
					}
				});
			}
			
			// زر مسح الرسم
			const clearDrawingBtn = document.getElementById('clearDrawing');
			if (clearDrawingBtn) {
				clearDrawingBtn.addEventListener('click', function() {
					if (selectedShape) {
						selectedShape.setMap(null);
						selectedShape = null;
						polygonBounds = null;
						polygonPoints = [];
					}
					// إخفاء قسم النقاط
					const pointsSection = document.getElementById('polygonPointsSection');
					if (pointsSection) pointsSection.style.display = 'none';
					
					this.style.display = 'none';
					const applyBtn = document.getElementById('applyBounds');
					if (applyBtn) applyBtn.style.display = 'none';
					if (startDrawingBtn) startDrawingBtn.style.display = 'inline-block';
				});
			}
			
			// زر نسخ النقاط
			const copyPointsBtn = document.getElementById('copyPoints');
			if (copyPointsBtn) {
				copyPointsBtn.addEventListener('click', function() {
					if (polygonPoints.length === 0) {
						alert('لا توجد نقاط للنسخ');
						return;
					}
					
					// إنشاء نص النقاط بصيغ مختلفة
					let text = 'نقاط المضلع:\n\n';
					
					// صيغة JSON
					text += 'JSON Format:\n';
					text += JSON.stringify(polygonPoints, null, 2);
					text += '\n\n';
					
					// صيغة CSV
					text += 'CSV Format:\n';
					text += 'Index,Latitude,Longitude\n';
					polygonPoints.forEach(function(point, index) {
						text += `${index + 1},${point.lat.toFixed(6)},${point.lng.toFixed(6)}\n`;
					});
					text += '\n';
					
					// صيغة Array
					text += 'Array Format:\n';
					text += '[\n';
					polygonPoints.forEach(function(point, index) {
						text += `  { lat: ${point.lat.toFixed(6)}, lng: ${point.lng.toFixed(6)} }`;
						if (index < polygonPoints.length - 1) text += ',';
						text += '\n';
					});
					text += ']';
					
					// نسخ إلى الحافظة
					navigator.clipboard.writeText(text).then(function() {
						alert('تم نسخ النقاط إلى الحافظة بنجاح!');
					}).catch(function(err) {
						// Fallback للأنظمة القديمة
						const textarea = document.createElement('textarea');
						textarea.value = text;
						textarea.style.position = 'fixed';
						textarea.style.opacity = '0';
						document.body.appendChild(textarea);
						textarea.select();
						try {
							document.execCommand('copy');
							alert('تم نسخ النقاط إلى الحافظة بنجاح!');
						} catch (err) {
							alert('فشل النسخ. يرجى نسخ النص يدوياً:\n\n' + text);
						}
						document.body.removeChild(textarea);
					});
				});
			}
			
			// زر تطبيق الحدود
			const applyBoundsBtn = document.getElementById('applyBounds');
			if (applyBoundsBtn) {
				applyBoundsBtn.addEventListener('click', function() {
					if (!polygonPoints || polygonPoints.length < 3) {
						alert('يرجى رسم مضلع على الخريطة أولاً');
						return;
					}
					
					applyBoundsToForm();
					
					// إظهار form الإضافة إذا كان مخفياً
					const addForm = document.getElementById('addBoundForm');
					if (addForm && addForm.style.display === 'none') {
						toggleAddForm();
					}
					
					// التمرير إلى form الإضافة
					setTimeout(function() {
						addForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
					}, 100);
					
					// إبراز حقل الاسم
					const nameInput = document.getElementById('name');
					if (nameInput) {
						nameInput.focus();
						nameInput.style.border = '2px solid #28a745';
						setTimeout(function() {
							nameInput.style.border = '';
						}, 2000);
					}
				});
			}
			
			// التحقق قبل إرسال النموذج
			const addBoundFormElement = document.getElementById('addBoundFormElement');
			if (addBoundFormElement) {
				addBoundFormElement.addEventListener('submit', function(e) {
					const polygonPointsInput = document.getElementById('polygon_points');
					if (!polygonPointsInput || !polygonPointsInput.value) {
						e.preventDefault();
						alert('يجب رسم منطقة على الخريطة أولاً ثم الضغط على "تطبيق الحدود على الحقول"');
						return false;
					}
					
					try {
						const points = JSON.parse(polygonPointsInput.value);
						if (!Array.isArray(points) || points.length < 3) {
							e.preventDefault();
							alert('يجب أن يحتوي المضلع على 3 نقاط على الأقل');
							return false;
						}
					} catch(err) {
						e.preventDefault();
						alert('نقاط المضلع غير صحيحة. يرجى رسم منطقة جديدة');
						return false;
					}
				});
			}
			
			const editBoundFormElement = document.getElementById('editBoundFormElement');
			if (editBoundFormElement) {
				editBoundFormElement.addEventListener('submit', function(e) {
					const editPolygonPointsInput = document.getElementById('edit_polygon_points');
					if (!editPolygonPointsInput || !editPolygonPointsInput.value) {
						e.preventDefault();
						alert('يجب رسم منطقة على الخريطة أولاً ثم الضغط على "تطبيق الحدود على الحقول"');
						return false;
					}
					
					try {
						const points = JSON.parse(editPolygonPointsInput.value);
						if (!Array.isArray(points) || points.length < 3) {
							e.preventDefault();
							alert('يجب أن يحتوي المضلع على 3 نقاط على الأقل');
							return false;
						}
					} catch(err) {
						e.preventDefault();
						alert('نقاط المضلع غير صحيحة. يرجى رسم منطقة جديدة');
						return false;
					}
				});
			}
		});

		// جعل initMap متاحاً بشكل global
		window.initMap = initMap;
	</script>
</div>
@endsection
