@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">Add New Bus</h2>
        <a href="{{ route('admin.buses') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i> Back to Fleet
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.buses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <h5 class="mb-4 text-muted fw-bold">Bus Details</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Bus Number / Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. BUS-101" value="{{ old('code') }}" required>
                        @error('code')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Bus Type</label>
                        <select name="type" class="form-select" id="busTypeInput" required>
                            <option value="regular" {{ old('type') == 'regular' ? 'selected' : '' }}>Regular (Aircon)</option>
                            <option value="deluxe" {{ old('type') == 'deluxe' ? 'selected' : '' }}>Deluxe (Restroom)</option>
                            <option value="luxury" {{ old('type') == 'luxury' ? 'selected' : '' }}>Luxury</option>
                        </select>
                        @error('type')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Seating Capacity</label>
                        <input type="number" name="capacity" id="busCapacityInput" class="form-control" placeholder="e.g. 45" value="{{ old('capacity') }}" min="10" max="60" required>
                        @error('capacity')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <h5 class="mb-4 text-muted fw-bold border-top pt-4">Driver Information</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Driver Name</label>
                        <input type="text" name="driver_name" class="form-control" placeholder="Full Name" value="{{ old('driver_name') }}" required>
                        @error('driver_name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Driver Photo</label>
                        <input type="file" name="driver_image" class="form-control" accept="image/*">
                        <div class="form-text">Recommended size: Square (e.g., 500x500px). Max 2MB.</div>
                        @error('driver_image')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-5 fw-bold">
                        <i class="fa-solid fa-save me-2"></i> Save Bus
                    </button>
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