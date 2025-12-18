@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">Manage Fleet (Buses)</h2>
        <div>
            <a href="{{ route('admin.buses.create') }}" class="btn btn-primary shadow-sm fw-bold">
                <i class="fa-solid fa-plus me-2"></i> Add New Bus
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">Bus Details</th>
                            <th class="py-3">Driver</th>
                            <th class="py-3">Type</th>
                            <th class="py-3">Capacity</th>
                            <th class="py-3 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($buses as $bus)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $bus->code }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($bus->driver_image)
                                    <img src="{{ asset('storage/' . $bus->driver_image) }}" alt="Driver" class="rounded-circle me-2 driver-img">
                                    @else
                                    <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2 text-secondary driver-placeholder">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold small">{{ $bus->driver_name ?? 'Unassigned' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($bus->type == 'deluxe')
                                <span class="badge bg-warning text-dark border border-warning">Deluxe</span>
                                @else
                                <span class="badge bg-light text-dark border">Regular</span>
                                @endif
                            </td>
                            <td>{{ $bus->capacity }} Seats</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.buses.edit', $bus->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.buses.delete', $bus->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa-solid fa-bus fa-2x mb-3 opacity-25"></i>
                                    <p class="fw-bold mb-0">No buses found.</p>
                                    <small>Click "Add New Bus" to get started.</small>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white py-3">
            {{ $buses->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection