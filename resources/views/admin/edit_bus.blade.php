@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">Edit Bus Details</h2>
        <a href="{{ route('admin.buses') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i> Back to Fleet
        </a>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mx-auto" style="max-width: 600px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Update Bus Information</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.buses.update', $bus->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-bold small">Bus Code</label>
                    <input type="text" name="code" class="form-control" value="{{ $bus->code }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Type</label>
                    <select name="type" class="form-select" id="busTypeInput" required>
                        <option value="deluxe" {{ $bus->type == 'deluxe' ? 'selected' : '' }}>Deluxe</option>
                        <option value="regular" {{ $bus->type == 'regular' ? 'selected' : '' }}>Regular</option>
                        <option value="luxury" {{ $bus->type == 'luxury' ? 'selected' : '' }}>Luxury</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Capacity</label>
                    <input type="number" name="capacity" id="busCapacityInput" class="form-control" value="{{ $bus->capacity }}" required min="10" max="60">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Driver Name</label>
                    <input type="text" name="driver_name" class="form-control" value="{{ $bus->driver_name }}" placeholder="Full Name">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Driver Photo</label>
                    @if($bus->driver_image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $bus->driver_image) }}" alt="Driver" style="width: 64px; height: 64px; object-fit: cover; border-radius: 12px;">
                    </div>
                    @endif
                    <input type="file" name="driver_image" class="form-control" accept="image/*">
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary fw-bold py-2">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeEl = document.getElementById('busTypeInput');
        const capEl = document.getElementById('busCapacityInput');

        function applyCapacity() {
            const type = (typeEl?.value || '').toLowerCase();
            if (type === 'regular') {
                capEl.value = 40;
                capEl.readOnly = true;
            } else if (type === 'deluxe') {
                capEl.value = 20;
                capEl.readOnly = true;
            } else {
                capEl.readOnly = false;
            }
        }

        if (typeEl && capEl) {
            typeEl.addEventListener('change', applyCapacity);
            applyCapacity();
        }
    });
</script>
@endpush