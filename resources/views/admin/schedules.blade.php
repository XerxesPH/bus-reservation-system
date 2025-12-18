@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">Manage Schedules</h2>
        <div>
            <a href="{{ route('admin.create_schedule') }}" class="btn btn-success fw-bold shadow-sm">
                <i class="fa-solid fa-plus me-2"></i> Generate New Schedules
            </a>
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
                            <th class="ps-4 py-3">Date & Time</th>
                            <th class="py-3">Route</th>
                            <th class="py-3">Bus Details</th>
                            <th class="py-3">Price</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ \Carbon\Carbon::parse($schedule->departure_date)->format('M d, Y') }}</div>
                                <div class="text-muted small">{{ \Carbon\Carbon::parse($schedule->departure_time)->format('h:i A') }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="fw-bold">{{ $schedule->origin->city }}</span>
                                    <i class="fa-solid fa-arrow-right mx-2 text-muted small"></i>
                                    <span class="fw-bold">{{ $schedule->destination->city }}</span>
                                </div>
                            </td>
                            <td>
                                <div>{{ $schedule->bus->code }}</div>
                                <div class="small text-muted text-uppercase">{{ $schedule->bus->type }}</div>
                            </td>
                            <td>
                                ₱{{ number_format($schedule->price, 2) }}
                            </td>
                            <td>
                                @if($schedule->status === 'cancelled')
                                <span class="badge bg-danger">Cancelled</span>
                                @elseif($schedule->status === 'scheduled')
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">{{ ucfirst($schedule->status) }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.manifest', $schedule->id) }}" class="btn btn-sm btn-outline-primary me-1" title="View Passenger Manifest">
                                    <i class="fa-solid fa-clipboard-list"></i> Manifest
                                </a>
                                @if($schedule->status !== 'cancelled')
                                <form action="{{ route('admin.cancel_schedule', $schedule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this trip?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel Trip">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                </form>
                                @else
                                <span class="badge bg-secondary">Cancelled</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fa-solid fa-calendar-xmark fa-3x text-muted mb-3 opacity-50"></i>
                                <p class="text-muted fw-bold">No schedules found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination Links --}}
        <div class="card-footer bg-white py-3">
            {{ $schedules->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection