@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Route Plans (Automation)</h2>
            <p class="text-muted mb-0">These "Templates" allow the system to automatically generate schedules every day.</p>
        </div>
        <div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary me-2">Back</a>
            <a href="{{ route('admin.templates.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i> Create New Plan</a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3">Route</th>
                        <th class="py-3">Bus</th>
                        <th class="py-3">Schedule</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center fw-bold">
                                {{ $template->origin->city }}
                                <i class="fa-solid fa-arrow-right mx-2 text-muted small"></i>
                                {{ $template->destination->city }}
                            </div>
                            <small class="text-muted">₱{{ number_format($template->price, 2) }}</small>
                        </td>
                        <td>
                            <div>{{ $template->bus->code }}</div>
                            <span class="badge bg-light text-dark border">{{ ucfirst($template->bus->type) }}</span>
                        </td>
                        <td>
                            <div class="mb-1">
                                <i class="fa-regular fa-calendar me-1 text-muted"></i>
                                @foreach($template->active_days as $day)
                                <span class="badge bg-info text-dark">{{ $day }}</span>
                                @endforeach
                            </div>
                            <div>
                                <i class="fa-regular fa-clock me-1 text-muted"></i>
                                @foreach($template->departure_times as $time)
                                <span class="badge bg-secondary">{{ \Carbon\Carbon::parse($time)->format('h:i A') }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            @if($template->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <form action="{{ route('admin.templates.toggle', $template->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $template->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} me-1" title="{{ $template->is_active ? 'Pause Automation' : 'Resume Automation' }}">
                                    <i class="fa-solid {{ $template->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                </button>
                            </form>

                            <form action="{{ route('admin.templates.delete', $template->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this plan? The system will stop generating trips for this route.');">
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
                            <i class="fa-solid fa-robot fa-3x text-muted mb-3 opacity-50"></i>
                            <p class="text-muted fw-bold">No route plans found.</p>
                            <small>Create a template to start automating schedules.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-3">
            {{ $templates->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection