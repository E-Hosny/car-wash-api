@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h3 class="mb-3">{{ __('messages.services') }}</h3>

    <a href="{{ route('admin.services.create') }}" class="btn btn-success mb-3">{{ __('messages.add_service') }}</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('messages.service_ordering') }}</h5>
            <small class="text-muted">{{ __('messages.service_ordering_description') }}</small>
        </div>
        <div class="card-body">
            <div id="services-container">
                @foreach ($services as $service)
                    <div class="service-item mb-2 p-3 border rounded" data-id="{{ $service->id }}" data-sort-order="{{ $service->sort_order }}">
                        <div class="row align-items-center">
                            <div class="col-md-1">
                                <i class="fas fa-grip-vertical handle" style="cursor: move; color: #6c757d;" title="{{ __('messages.drag_handle') }}"></i>
                            </div>
                            <div class="col-md-1">
                                @if($service->image)
                                    <img src="{{ Storage::url($service->image) }}" alt="{{ $service->name }}" 
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                                @else
                                    <div style="width: 60px; height: 60px; background-color: #e9ecef; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd;">
                                        <i class="fas fa-image" style="color: #adb5bd; font-size: 24px;"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-2">
                                <strong>{{ $service->name }}</strong>
                            </div>
                            <div class="col-md-3">
                                {{ $service->description ?: __('messages.no_description') }}
                            </div>
                            <div class="col-md-2">
                                {{ $service->price }} {{ __('messages.currency') }}
                            </div>
                            <div class="col-md-3">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary move-up" data-id="{{ $service->id }}" title="{{ __('messages.move_up') }}">
                                        <i class="fas fa-arrow-up"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary move-down" data-id="{{ $service->id }}" title="{{ __('messages.move_down') }}">
                                        <i class="fas fa-arrow-down"></i>
                                    </button>
                                    <a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-primary" title="{{ __('messages.edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.services.delete', $service->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-danger" onclick="return confirm('{{ __('messages.confirm_delete') }}')" title="{{ __('messages.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-3">
                <button type="button" class="btn btn-success" id="save-order">
                    <i class="fas fa-save"></i> {{ __('messages.save_order') }}
                </button>
                <span id="save-status" class="ml-2"></span>
            </div>
        </div>
    </div>
</div>

<!-- Include Sortable.js -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('services-container');
    const saveButton = document.getElementById('save-order');
    const saveStatus = document.getElementById('save-status');
    
    // Initialize Sortable
    const sortable = Sortable.create(container, {
        handle: '.handle',
        animation: 150,
        onEnd: function() {
            updateSortOrders();
        }
    });
    
    // Update sort orders after drag and drop
    function updateSortOrders() {
        const items = container.querySelectorAll('.service-item');
        items.forEach((item, index) => {
            item.dataset.sortOrder = index + 1;
        });
    }
    
    // Save order button click
    saveButton.addEventListener('click', function() {
        const services = [];
        const items = container.querySelectorAll('.service-item');
        
        items.forEach((item, index) => {
            services.push({
                id: parseInt(item.dataset.id),
                sort_order: index + 1
            });
        });
        
        // Show loading state
        saveButton.disabled = true;
        saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("messages.saving") }}';
        saveStatus.innerHTML = '';
        
        // Send AJAX request
        fetch('{{ route("admin.services.update-order") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ services: services })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                saveStatus.innerHTML = '<span class="text-success">✓ ' + data.message + '</span>';
                // Update all sort_order data attributes
                items.forEach((item, index) => {
                    item.dataset.sortOrder = index + 1;
                });
            } else {
                saveStatus.innerHTML = '<span class="text-danger">✗ ' + data.message + '</span>';
            }
        })
        .catch(error => {
            saveStatus.innerHTML = '<span class="text-danger">✗ {{ __("messages.order_save_error") }}</span>';
            console.error('Error:', error);
        })
        .finally(() => {
            saveButton.disabled = false;
            saveButton.innerHTML = '<i class="fas fa-save"></i> {{ __("messages.save_order") }}';
        });
    });
    
    // Move up buttons
    document.querySelectorAll('.move-up').forEach(button => {
        button.addEventListener('click', function() {
            const serviceId = this.dataset.id;
            fetch(`/admin/services/${serviceId}/move-up`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(() => {
                window.location.reload();
            });
        });
    });
    
    // Move down buttons
    document.querySelectorAll('.move-down').forEach(button => {
        button.addEventListener('click', function() {
            const serviceId = this.dataset.id;
            fetch(`/admin/services/${serviceId}/move-down`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(() => {
                window.location.reload();
            });
        });
    });
});
</script>

<style>
.service-item {
    background-color: #f8f9fa;
    transition: all 0.2s ease;
}

.service-item:hover {
    background-color: #e9ecef;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.handle {
    font-size: 1.2em;
}

.handle:hover {
    color: #495057 !important;
}

.btn-group .btn {
    margin-right: 2px;
}

#save-status {
    font-size: 0.9em;
}
</style>
@endsection
