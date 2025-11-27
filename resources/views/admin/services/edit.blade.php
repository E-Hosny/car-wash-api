@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h3 class="mb-3">{{ __('messages.edit_service') }}</h3>

    <form method="POST" action="{{ route('admin.services.update', $service->id) }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label>{{ __('messages.name') }}:</label>
            <input type="text" name="name" class="form-control" value="{{ $service->name }}" required>
        </div>

        <div class="mb-3">
            <label>{{ __('messages.description') }}:</label>
            <textarea name="description" class="form-control">{{ $service->description }}</textarea>
        </div>

        <div class="mb-3">
            <label>{{ __('messages.price') }}:</label>
            <input type="number" name="price" step="0.01" class="form-control" value="{{ $service->price }}" required>
        </div>

        <div class="mb-3">
            <label>{{ __('messages.image') ?? 'Image' }}:</label>
            @if($service->image)
                <div class="mb-2">
                    <img src="{{ Storage::url($service->image) }}" alt="Current Image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #ddd;">
                </div>
            @endif
            <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/jpg" onchange="previewImage(this)">
            <small class="form-text text-muted">Recommended: 600x600px, Max: 2MB, Format: JPG/PNG. Leave empty to keep current image.</small>
            <div id="imagePreview" class="mt-2" style="display: none;">
                <img id="previewImg" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; margin-top: 10px;">
            </div>
        </div>

        <div class="mb-3">
            <label>{{ __('messages.sort_order') }}:</label>
            <input type="number" name="sort_order" class="form-control" min="1" value="{{ $service->sort_order }}" required>
            <small class="form-text text-muted">{{ __('messages.sort_order_help') }}</small>
        </div>

        <button class="btn btn-primary">{{ __('messages.update') }}</button>
    </form>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}
</script>
@endsection
