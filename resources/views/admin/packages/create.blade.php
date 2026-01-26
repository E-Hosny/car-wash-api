@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-plus"></i> {{ __('packages.add_package') }}
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

                    <form action="{{ route('admin.packages.store') }}" method="POST" enctype="multipart/form-data" id="createPackageForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-tag"></i> {{ __('packages.package_name') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="name" name="name" 
                                           value="{{ old('name') }}" required
                                           placeholder="{{ __('packages.enter_package_name') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="price" class="form-label">
                                        <i class="fas fa-money-bill"></i> {{ __('packages.package_price') }} ({{ __('packages.currency') }}) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-lg" id="price" name="price" 
                                               value="{{ old('price') }}" step="0.01" min="0" required
                                               placeholder="0.00">
                                        <span class="input-group-text">{{ __('packages.currency') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="image" class="form-label">
                                        <i class="fas fa-image"></i> {{ __('packages.package_image') }}
                                    </label>
                                    <input type="file" class="form-control form-control-lg" id="image" name="image" 
                                           accept="image/*" onchange="previewImage(this)">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle"></i> 
                                        {{ __('packages.select_package_image') }} ({{ __('packages.optional') }}) - {{ __('packages.max_size_2mb') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label">
                                <i class="fas fa-align-left"></i> {{ __('packages.package_description') }}
                            </label>
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Description Items</span>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="addDescriptionItem()">
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
                            <input type="hidden" id="description" name="description" value="">
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
                                                               value="{{ old("services.{$loop->index}.quantity", 0) }}" 
                                                               min="0" required
                                                               onchange="updateServiceStatus(this)">
                                                        <span class="input-group-text">{{ __('packages.times') }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary service-status">
                                                        <i class="fas fa-times"></i> {{ __('packages.not_available') }}
                                                    </span>
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
                                    <i class="fas fa-save"></i> {{ __('packages.save') }}
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
                            <div class="bg-light border rounded p-5">
                                <i class="fas fa-image fa-3x text-muted"></i>
                                <p class="text-muted mt-2">{{ __('packages.no_image') }}</p>
                            </div>
                        </div>
                        <h4 id="previewName">{{ __('packages.package_name') }}</h4>
                        <p id="previewDescription" class="text-muted">{{ __('packages.package_description') }}</p>
                        <div class="row">
                            <div class="col-6">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 id="previewPrice">0 {{ __('packages.currency') }}</h5>
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
document.getElementById('createPackageForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __('packages.saving') }}...';
    submitBtn.disabled = true;
});

// Description items management
let descriptionItemIndex = 0;

function addDescriptionItem(header = '', description = '') {
    const container = document.getElementById('descriptionItemsContainer');
    const index = descriptionItemIndex++;
    
    const itemDiv = document.createElement('div');
    itemDiv.className = 'card mb-3 description-item';
    itemDiv.setAttribute('data-index', index);
    itemDiv.innerHTML = `
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="card-title mb-0">Item ${index + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeDescriptionItem(${index})">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
            <div class="mb-3">
                <label class="form-label">Header <span class="text-danger">*</span></label>
                <input type="text" class="form-control description-header" 
                       name="description_items[${index}][header]" 
                       value="${header}" 
                       placeholder="Enter header title" required>
            </div>
            <div class="mb-0">
                <label class="form-label">Description <span class="text-danger">*</span></label>
                <textarea class="form-control description-text" 
                          name="description_items[${index}][description]" 
                          rows="3" 
                          placeholder="Enter description" required>${description}</textarea>
            </div>
        </div>
    `;
    
    container.appendChild(itemDiv);
    updateDescriptionHiddenField();
}

function removeDescriptionItem(index) {
    const item = document.querySelector(`.description-item[data-index="${index}"]`);
    if (item) {
        item.remove();
        updateDescriptionHiddenField();
        // Re-index remaining items
        reindexDescriptionItems();
    }
}

function reindexDescriptionItems() {
    const items = document.querySelectorAll('.description-item');
    items.forEach((item, newIndex) => {
        item.setAttribute('data-index', newIndex);
        const headerInput = item.querySelector('.description-header');
        const descTextarea = item.querySelector('.description-text');
        const title = item.querySelector('.card-title');
        
        if (headerInput) {
            headerInput.name = `description_items[${newIndex}][header]`;
        }
        if (descTextarea) {
            descTextarea.name = `description_items[${newIndex}][description]`;
        }
        if (title) {
            title.textContent = `Item ${newIndex + 1}`;
        }
    });
}

function updateDescriptionHiddenField() {
    // This is for backward compatibility - we'll use description_items in controller
    const items = document.querySelectorAll('.description-item');
    const descriptionField = document.getElementById('description');
    if (items.length === 0) {
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
    
    // Initialize service statuses
    document.querySelectorAll('input[name*="[quantity]"]').forEach(function(input) {
        updateServiceStatus(input);
    });
    
    // Add one empty description item by default
    addDescriptionItem();
});
</script>
@endsection 