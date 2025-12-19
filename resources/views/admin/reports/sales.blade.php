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
                <div class="col-md-3 custom-dates {{ $period != 'custom' ? 'd-none' : '' }}">
                    <label class="form-label fw-bold">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-3 custom-dates {{ $period != 'custom' ? 'd-none' : '' }}">
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
                    @if($dailyRevenue->isEmpty())
                    <div class="text-center text-muted py-5">
                        No revenue data for this period.
                    </div>
                    @else
                    <canvas id="revenueChart" height="300"></canvas>
                    <script type="application/json" id="dailyRevenueLabels">
                        {
                            !!$dailyRevenue - > pluck('date') - > toJson() !!
                        }
                    </script>
                    <script type="application/json" id="dailyRevenueValues">
                        {
                            !!$dailyRevenue - > pluck('total') - > toJson() !!
                        }
                    </script>
                    @endif
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
            el.classList.toggle('d-none', select.value !== 'custom');
        });
    }

    // Revenue Chart
    window.addEventListener('load', function() {
        const chartCanvas = document.getElementById('revenueChart');
        if (!chartCanvas) return;

        const labelsEl = document.getElementById('dailyRevenueLabels');
        const valuesEl = document.getElementById('dailyRevenueValues');
        const labels = labelsEl ? JSON.parse(labelsEl.textContent || '[]') : [];
        const valuesRaw = valuesEl ? JSON.parse(valuesEl.textContent || '[]') : [];
        const values = Array.isArray(valuesRaw) ? valuesRaw.map((v) => Number(v) || 0) : [];

        const ctx = chartCanvas.getContext('2d');
        const parent = chartCanvas.parentElement;
        const width = parent ? Math.max(parent.clientWidth, 320) : 640;
        chartCanvas.width = width;
        chartCanvas.height = 300;

        const formatPeso = (n) => '₱' + Number(n || 0).toLocaleString();

        function renderFallback() {
            if (!ctx) return;
            ctx.clearRect(0, 0, chartCanvas.width, chartCanvas.height);

            if (!values.length) {
                ctx.fillStyle = '#64748B';
                ctx.font = '14px sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('No revenue data for this period.', chartCanvas.width / 2, chartCanvas.height / 2);
                return;
            }

            const w = chartCanvas.width;
            const h = chartCanvas.height;
            const padL = 56;
            const padR = 20;
            const padT = 16;
            const padB = 44;
            const plotW = w - padL - padR;
            const plotH = h - padT - padB;

            const maxVal = Math.max(...values);
            const minVal = Math.min(...values);
            const range = maxVal === minVal ? 1 : (maxVal - minVal);
            const stepX = values.length > 1 ? plotW / (values.length - 1) : plotW;

            const points = values.map((v, i) => {
                const x = padL + stepX * i;
                const y = padT + (maxVal - v) * (plotH / range);
                return {
                    x,
                    y
                };
            });

            ctx.strokeStyle = '#E2E8F0';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(padL, padT + plotH);
            ctx.lineTo(padL + plotW, padT + plotH);
            ctx.stroke();

            const ticks = 4;
            ctx.fillStyle = '#64748B';
            ctx.font = '12px sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';

            for (let i = 0; i <= ticks; i++) {
                const t = i / ticks;
                const val = minVal + (range * (1 - t));
                const y = padT + plotH * t;
                ctx.strokeStyle = 'rgba(226, 232, 240, 0.7)';
                ctx.beginPath();
                ctx.moveTo(padL, y);
                ctx.lineTo(padL + plotW, y);
                ctx.stroke();
                ctx.fillText(formatPeso(val), padL - 8, y);
            }

            ctx.strokeStyle = '#198754';
            ctx.lineWidth = 2;
            ctx.beginPath();
            points.forEach((p, idx) => {
                if (idx === 0) ctx.moveTo(p.x, p.y);
                else ctx.lineTo(p.x, p.y);
            });
            ctx.stroke();

            ctx.fillStyle = 'rgba(25, 135, 84, 0.12)';
            ctx.beginPath();
            points.forEach((p, idx) => {
                if (idx === 0) ctx.moveTo(p.x, p.y);
                else ctx.lineTo(p.x, p.y);
            });
            ctx.lineTo(padL + plotW, padT + plotH);
            ctx.lineTo(padL, padT + plotH);
            ctx.closePath();
            ctx.fill();

            ctx.fillStyle = '#0F172A';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'top';
            const first = labels[0] || '';
            const last = labels[labels.length - 1] || '';
            ctx.fillText(first, padL, padT + plotH + 10);
            ctx.fillText(last, padL + plotW, padT + plotH + 10);
        }

        if (window.Chart) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue (₱)',
                        data: values,
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: false,
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
        } else {
            renderFallback();
        }
    });
</script>
@endpush
@endsection