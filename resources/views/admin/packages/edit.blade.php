@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i> {{ __('packages.edit_package') }}: {{ $package->name }}
                    </h3>
                    <div>
                        <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('packages.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>{{ __('packages.correct_errors') }}:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.packages.update', $package->id) }}" method="POST" enctype="multipart/form-data" id="editPackageForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-tag"></i> {{ __('packages.package_name') }} (English) <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="name" name="name" 
                                           value="{{ old('name', $package->name) }}" required
                                           placeholder="{{ __('packages.enter_package_name') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name_ar" class="form-label">
                                        <i class="fas fa-tag"></i> {{ __('packages.package_name') }} (العربية)
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="name_ar" name="name_ar" 
                                           value="{{ old('name_ar', $package->name_ar) }}" dir="rtl"
                                           placeholder="اسم الباقة بالعربية">
                                    <small class="form-text text-muted">اسم الباقة بالعربية</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="price" class="form-label">
                                        <i class="fas fa-money-bill"></i> {{ __('packages.package_price') }} ({{ __('packages.currency') }}) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-lg" id="price" name="price" 
                                               value="{{ old('price', $package->price) }}" step="0.01" min="0" required
                                               placeholder="0.00">
                                        <span class="input-group-text">{{ __('packages.currency') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="validity_months" class="form-label">
                                        <i class="fas fa-calendar-alt"></i> {{ __('packages.validity_months') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control form-control-lg" id="validity_months" name="validity_months" 
                                           value="{{ old('validity_months', $package->validity_months ?? 1) }}" min="1" max="120" required
                                           placeholder="1">
                                    <small class="form-text text-muted">{{ __('packages.validity_months_help') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="image" class="form-label">
                                        <i class="fas fa-image"></i> {{ __('packages.package_image') }}
                                    </label>
                                    @if($package->image)
                                        <div class="mb-3">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <img src="{{ asset('storage/' . $package->image) }}" 
                                                         alt="{{ $package->name }}" 
                                                         class="img-thumbnail"
                                                         style="max-width: 200px; max-height: 200px;">
                                                    <p class="text-muted mt-2">{{ __('packages.current_image') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control form-control-lg" id="image" name="image" 
                                           accept="image/*" onchange="previewImage(this)">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle"></i> 
                                        {{ __('packages.select_new_image_or_keep_current') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label">
                                <i class="fas fa-align-left"></i> {{ __('packages.package_description') }} (English)
                            </label>
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Description Items (English)</span>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="addDescriptionItem('descriptionItemsContainer', 'description_items')">
                                        <i class="fas fa-plus"></i> Add Header
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="descriptionItemsContainer">
                                        <!-- Description items will be added here dynamically -->
                                    </div>
                                    <div class="text-muted small mt-2">
                                        <i class="fas fa-info-circle"></i> Add headers with descriptions. Each header will be displayed in the app card.
                                    </div>
                                </div>
                            </div>
                            <!-- Hidden field for backward compatibility -->
                            <input type="hidden" id="description" name="description" value="{{ old('description', $package->description) }}">
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label">
                                <i class="fas fa-align-left"></i> {{ __('packages.package_description') }} (العربية)
                            </label>
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Description Items (العربية)</span>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="addDescriptionItem('descriptionItemsContainerAr', 'description_items_ar')">
                                        <i class="fas fa-plus"></i> إضافة عنوان
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="descriptionItemsContainerAr">
                                        <!-- Arabic description items will be added here dynamically -->
                                    </div>
                                    <div class="text-muted small mt-2">
                                        <i class="fas fa-info-circle"></i> أضف عناوين مع أوصاف. سيتم عرض كل عنوان في بطاقة التطبيق.
                                    </div>
                                </div>
                            </div>
                            <!-- Hidden field for backward compatibility -->
                            <input type="hidden" id="description_ar" name="description_ar" value="{{ old('description_ar', $package->description_ar) }}">
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-cogs"></i> {{ __('packages.package_services') }}
                                </h5>
                                <small class="text-muted">{{ __('packages.set_quantity_per_service') }}</small>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>{{ __('messages.services') }}</th>
                                                <th>{{ __('packages.quantity') }}</th>
                                                <th>{{ __('packages.status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($services as $service)
                                            @php
                                                $currentQuantity = isset($packageServices[$service->id]) ? $packageServices[$service->id]->quantity : 0;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                        <strong>{{ $service->name }}</strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="hidden" name="services[{{ $loop->index }}][service_id]" 
                                                           value="{{ $service->id }}">
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" 
                                                               name="services[{{ $loop->index }}][quantity]" 
                                                               value="{{ old("services.{$loop->index}.quantity", $currentQuantity) }}" 
                                                               min="0" required
                                                               onchange="updateServiceStatus(this)">
                                                        <span class="input-group-text">{{ __('packages.times') }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($currentQuantity > 0)
                                                        <span class="badge bg-success service-status">
                                                            <i class="fas fa-check"></i> {{ __('packages.available') }} ({{ $currentQuantity }})
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary service-status">
                                                            <i class="fas fa-times"></i> {{ __('packages.not_available') }}
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="fas fa-save"></i> {{ __('packages.update_package') }}
                                </button>
                                <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-arrow-left"></i> {{ __('packages.back') }}
                                </a>
                                <button type="button" class="btn btn-outline-info btn-lg" onclick="previewPackage()">
                                    <i class="fas fa-eye"></i> {{ __('packages.preview') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> {{ __('packages.preview_package') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body text-center">
                        <div id="previewImage" class="mb-3">
                            @if($package->image)
                                <img src="{{ asset('storage/' . $package->image) }}" 
                                     alt="{{ $package->name }}" 
                                     class="img-fluid rounded"
                                     style="max-height: 300px;">
                            @else
                                <div class="bg-light border rounded p-5">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                    <p class="text-muted mt-2">{{ __('packages.no_image') }}</p>
                                </div>
                            @endif
                        </div>
                        <h4 id="previewName">{{ $package->name }}</h4>
                        <p id="previewDescription" class="text-muted">{{ $package->description }}</p>
                        <div class="row">
                            <div class="col-6">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 id="previewPrice">{{ $package->price }} {{ __('packages.currency') }}</h5>
                                        <small>{{ __('packages.price') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5 id="previewPoints">0 {{ __('packages.services') }}</h5>
                                        <small>{{ __('packages.services_count') }}</small>
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

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewDiv = document.getElementById('previewImage');
            previewDiv.innerHTML = `
                <img src="${e.target.result}" 
                     alt="معاينة الصورة" 
                     class="img-fluid rounded"
                     style="max-height: 300px;">
            `;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function updateServiceStatus(input) {
    const row = input.closest('tr');
    const statusBadge = row.querySelector('.service-status');
    const quantity = parseInt(input.value) || 0;
    
    if (quantity > 0) {
        statusBadge.className = 'badge bg-success service-status';
        statusBadge.innerHTML = '<i class="fas fa-check"></i> {{ __('packages.available') }} (' + quantity + ')';
    } else {
        statusBadge.className = 'badge bg-secondary service-status';
        statusBadge.innerHTML = '<i class="fas fa-times"></i> {{ __('packages.not_available') }}';
    }
}

function previewPackage() {
    const name = document.getElementById('name').value;
    const description = document.getElementById('description').value;
    const price = document.getElementById('price').value;
    
    document.getElementById('previewName').textContent = name || '{{ __('packages.package_name') }}';
    document.getElementById('previewDescription').textContent = description || '{{ __('packages.package_description') }}';
    document.getElementById('previewPrice').textContent = (price || '0') + ' {{ __('packages.currency') }}';
    
    // Count services with quantity > 0
    let servicesCount = 0;
    document.querySelectorAll('input[name*="[quantity]"]').forEach(function(input) {
        if (parseInt(input.value) > 0) {
            servicesCount++;
        }
    });
    document.getElementById('previewPoints').textContent = servicesCount + ' {{ __('packages.services') }}';
    
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
}

// Form validation and loading state
document.getElementById('editPackageForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __('packages.updating_package') }}...';
    submitBtn.disabled = true;
});

// Description items management
let descriptionItemIndex = 0;
let descriptionItemIndexAr = 0;

function addDescriptionItem(containerId = 'descriptionItemsContainer', namePrefix = 'description_items', header = '', description = '') {
    const container = document.getElementById(containerId);
    const isArabic = containerId === 'descriptionItemsContainerAr';
    const index = isArabic ? descriptionItemIndexAr++ : descriptionItemIndex++;
    
    const itemDiv = document.createElement('div');
    itemDiv.className = 'card mb-3 description-item';
    itemDiv.setAttribute('data-index', index);
    itemDiv.setAttribute('data-container', containerId);
    itemDiv.innerHTML = `
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="card-title mb-0">${isArabic ? 'عنصر' : 'Item'} ${index + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeDescriptionItem('${containerId}', ${index})">
                    <i class="fas fa-trash"></i> ${isArabic ? 'حذف' : 'Remove'}
                </button>
            </div>
            <div class="mb-3">
                <label class="form-label">${isArabic ? 'العنوان' : 'Header'} <span class="text-danger">*</span></label>
                <input type="text" class="form-control description-header" 
                       name="${namePrefix}[${index}][header]" 
                       value="${header}" 
                       dir="${isArabic ? 'rtl' : 'ltr'}"
                       placeholder="${isArabic ? 'أدخل عنوان' : 'Enter header title'}" required>
            </div>
            <div class="mb-0">
                <label class="form-label">${isArabic ? 'الوصف' : 'Description'} <span class="text-danger">*</span></label>
                <textarea class="form-control description-text" 
                          name="${namePrefix}[${index}][description]" 
                          rows="3" 
                          dir="${isArabic ? 'rtl' : 'ltr'}"
                          placeholder="${isArabic ? 'أدخل الوصف' : 'Enter description'}" required>${description}</textarea>
            </div>
        </div>
    `;
    
    container.appendChild(itemDiv);
    updateDescriptionHiddenField(containerId, namePrefix);
}

function removeDescriptionItem(containerId, index) {
    const item = document.querySelector(`.description-item[data-container="${containerId}"][data-index="${index}"]`);
    if (item) {
        item.remove();
        const namePrefix = containerId === 'descriptionItemsContainerAr' ? 'description_items_ar' : 'description_items';
        updateDescriptionHiddenField(containerId, namePrefix);
        // Re-index remaining items
        reindexDescriptionItems(containerId, namePrefix);
    }
}

function reindexDescriptionItems(containerId, namePrefix) {
    const items = document.querySelectorAll(`.description-item[data-container="${containerId}"]`);
    const isArabic = containerId === 'descriptionItemsContainerAr';
    items.forEach((item, newIndex) => {
        item.setAttribute('data-index', newIndex);
        const headerInput = item.querySelector('.description-header');
        const descTextarea = item.querySelector('.description-text');
        const title = item.querySelector('.card-title');
        
        if (headerInput) {
            headerInput.name = `${namePrefix}[${newIndex}][header]`;
        }
        if (descTextarea) {
            descTextarea.name = `${namePrefix}[${newIndex}][description]`;
        }
        if (title) {
            title.textContent = `${isArabic ? 'عنصر' : 'Item'} ${newIndex + 1}`;
        }
    });
}

function updateDescriptionHiddenField(containerId, namePrefix) {
    // This is for backward compatibility - we'll use description_items in controller
    const items = document.querySelectorAll(`.description-item[data-container="${containerId}"]`);
    const fieldId = containerId === 'descriptionItemsContainerAr' ? 'description_ar' : 'description';
    const descriptionField = document.getElementById(fieldId);
    if (items.length === 0 && descriptionField) {
        descriptionField.value = '';
    }
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Load existing description items (English)
    const descriptionField = document.getElementById('description');
    const descriptionValue = descriptionField ? descriptionField.value : '';
    
    if (descriptionValue) {
        try {
            // Try to parse as JSON
            const parsed = JSON.parse(descriptionValue);
            if (Array.isArray(parsed) && parsed.length > 0) {
                // It's JSON format - load items
                parsed.forEach(function(item) {
                    addDescriptionItem('descriptionItemsContainer', 'description_items', item.header || '', item.description || '');
                });
            } else {
                // It's a plain string - add as single item
                addDescriptionItem('descriptionItemsContainer', 'description_items', 'Description', descriptionValue);
            }
        } catch (e) {
            // Not JSON - treat as plain string
            addDescriptionItem('descriptionItemsContainer', 'description_items', 'Description', descriptionValue);
        }
    } else {
        // No description - add one empty item
        addDescriptionItem('descriptionItemsContainer', 'description_items');
    }

    // Load existing Arabic description items
    const descriptionArField = document.getElementById('description_ar');
    const descriptionArValue = descriptionArField ? descriptionArField.value : '';
    
    if (descriptionArValue) {
        try {
            // Try to parse as JSON
            const parsed = JSON.parse(descriptionArValue);
            if (Array.isArray(parsed) && parsed.length > 0) {
                // It's JSON format - load items
                parsed.forEach(function(item) {
                    addDescriptionItem('descriptionItemsContainerAr', 'description_items_ar', item.header || '', item.description || '');
                });
            } else {
                // It's a plain string - add as single item
                addDescriptionItem('descriptionItemsContainerAr', 'description_items_ar', 'الوصف', descriptionArValue);
            }
        } catch (e) {
            // Not JSON - treat as plain string
            addDescriptionItem('descriptionItemsContainerAr', 'description_items_ar', 'الوصف', descriptionArValue);
        }
    }
});
</script>
@endsection 