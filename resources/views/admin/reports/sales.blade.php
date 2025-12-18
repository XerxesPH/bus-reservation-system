@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Sales Report</h2>
            <p class="text-muted mb-0">Revenue and booking analytics</p>
        </div>
    </div>

    {{-- Period Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.reports.sales') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Period</label>
                    <select name="period" class="form-select" onchange="toggleCustomDates(this)">
                        <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ $period == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="week" {{ $period == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="year" {{ $period == 'year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3 custom-dates" style="{{ $period != 'custom' ? 'display:none' : '' }}">
                    <label class="form-label fw-bold">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-3 custom-dates" style="{{ $period != 'custom' ? 'display:none' : '' }}">
                    <label class="form-label fw-bold">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-filter me-2"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center py-4">
                    <h2 class="fw-bold mb-1">₱{{ number_format($revenue, 2) }}</h2>
                    <p class="mb-0 text-white-50">Total Revenue</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center py-4">
                    <h2 class="fw-bold mb-1">{{ $bookingsCount }}</h2>
                    <p class="mb-0 text-white-50">Confirmed Bookings</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body text-center py-4">
                    <h2 class="fw-bold mb-1">{{ $cancelledCount }}</h2>
                    <p class="mb-0 text-white-50">Cancelled Bookings</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Daily Revenue Chart --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">Daily Revenue</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="300"></canvas>
                </div>
            </div>
        </div>

        {{-- Top Routes --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">Top Routes by Revenue</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($topRoutes as $route)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $route->route }}</strong>
                                <div class="small text-muted">{{ $route->bookings }} bookings</div>
                            </div>
                            <span class="badge bg-success fs-6">₱{{ number_format($route->total) }}</span>
                        </li>
                        @empty
                        <li class="list-group-item text-center text-muted py-4">
                            No booking data for this period.
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function toggleCustomDates(select) {
        const customDates = document.querySelectorAll('.custom-dates');
        customDates.forEach(el => {
            el.style.display = select.value === 'custom' ? 'block' : 'none';
        });
    }

    // Revenue Chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {
                !!json_encode($dailyRevenue - > pluck('date')) !!
            },
            datasets: [{
                label: 'Revenue (₱)',
                data: {
                    !!json_encode($dailyRevenue - > pluck('total')) !!
                },
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection