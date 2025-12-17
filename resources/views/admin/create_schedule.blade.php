@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">Generate Bus Schedules</h2>
        <a href="{{ route('admin.schedules') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i> Back to Schedules
        </a>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mx-auto" style="max-width: 800px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Schedule Generator</h5>
        </div>
        <div class="card-body p-4">

            <form action="{{ route('admin.store_schedule') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Origin</label>
                        <select name="origin_id" class="form-select" required>
                            @foreach($terminals as $t)
                            <option value="{{ $t->id }}">{{ $t->city }} ({{ $t->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Destination</label>
                        <select name="destination_id" class="form-select" required>
                            @foreach($terminals as $t)
                            <option value="{{ $t->id }}">{{ $t->city }} ({{ $t->name }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-8">
                        <label>Assign Bus</label>
                        <select name="bus_id" class="form-select" required>
                            @foreach($buses as $bus)
                            <option value="{{ $bus->id }}">{{ $bus->code }} - {{ ucfirst($bus->type) }} ({{ $bus->capacity }} seats)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Ticket Price (₱)</label>
                        <input type="number" name="price" class="form-control" value="500" required>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" required min="{{ date('Y-m-d') }}">
                    </div>
                    <small class="text-muted">Trips will be created for every day in this range.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Active Days</label>
                    <div class="d-flex flex-wrap gap-3 mb-2">
                        @php $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']; @endphp
                        @foreach($days as $day)
                        <div class="form-check">
                            <input class="form-check-input day-checkbox" type="checkbox" name="active_days[]" value="{{ $day }}" checked>
                            <label class="form-check-label">{{ $day }}</label>
                        </div>
                        @endforeach
                    </div>
                    <div class="btn-group btn-group-sm mb-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="selectDays(['Mon','Tue','Wed','Thu','Fri'])">Weekdays</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="selectDays(['Sat','Sun'])">Weekends</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="selectDays(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'])">All</button>
                    </div>
                    <small class="text-muted d-block">Uncheck days to exclude them.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Exclude Specific Dates (Holidays/Maintenance)</label>
                    <textarea name="excluded_dates" class="form-control" rows="2" placeholder="YYYY-MM-DD, YYYY-MM-DD (e.g., 2025-12-25)"></textarea>
                    <small class="text-muted">Enter dates separated by commas to skip schedule generation for these specific dates.</small>
                </div>

                <label class="form-label fw-bold">Select Departure Times (Hourly)</label>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    @for($h = 4; $h <= 21; $h++)
                        @php
                        $timeStr=sprintf('%02d:00:00', $h);
                        $display=date('h:i A', strtotime($timeStr));
                        @endphp
                        <div class="form-check" style="width: 100px;">
                        <input class="form-check-input time-checkbox" type="checkbox" name="times[]" value="{{ $timeStr }}">
                        <label class="form-check-label small">{{ $display }}</label>
                </div>
                @endfor
        </div>
        <div class="btn-group btn-group-sm mb-4">
            <button type="button" class="btn btn-outline-secondary" onclick="toggleTimes(true)">Select All</button>
            <button type="button" class="btn btn-outline-secondary" onclick="toggleTimes(false)">Clear All</button>
        </div>

        <button type="submit" class="btn btn-success w-100 py-2">Generate Schedules</button>
        </form>
    </div>
</div>
</div>

<script>
    function selectDays(daysToSelect) {
        const checkboxes = document.querySelectorAll('.day-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = daysToSelect.includes(cb.value);
        });
    }

    function toggleTimes(check) {
        const checkboxes = document.querySelectorAll('.time-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = check;
        });
    }
</script>
@endsection