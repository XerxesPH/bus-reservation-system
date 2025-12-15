@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Manage Fleet (Buses)</h2>
        <div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary me-2">Back to Dashboard</a>
            {{-- Optional: Add Create Bus Button if needed, currently not in requirements but good to have --}}
            {{-- <a href="#" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i> Add New Bus</a> --}}
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">Bus Code</th>
                            <th class="py-3">Type</th>
                            <th class="py-3">Capacity</th>
                            <th class="py-3 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($buses as $bus)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $bus->code }}</td>
                            <td>
                                @if($bus->type == 'deluxe')
                                <span class="badge bg-warning text-dark">Deluxe</span>
                                @else
                                <span class="badge bg-secondary">Regular</span>
                                @endif
                            </td>
                            <td>{{ $bus->capacity }} Seats</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.buses.edit', $bus->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
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
                            <td colspan="4" class="text-center py-5">
                                <p class="text-muted fw-bold">No buses found.</p>
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