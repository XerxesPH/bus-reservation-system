@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-lg mx-auto" style="max-width: 700px;">
        <div class="card-header bg-primary text-white">
            <h4>Generate Bus Schedules</h4>
        </div>
        <div class="card-body">

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

                <label class="form-label fw-bold">Select Departure Times</label>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="times[]" value="08:00:00" checked>
                        <label class="form-check-label">08:00 AM</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="times[]" value="10:00:00">
                        <label class="form-check-label">10:00 AM</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="times[]" value="12:00:00">
                        <label class="form-check-label">12:00 PM</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="times[]" value="14:00:00">
                        <label class="form-check-label">02:00 PM</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="times[]" value="16:00:00">
                        <label class="form-check-label">04:00 PM</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="times[]" value="19:00:00">
                        <label class="form-check-label">07:00 PM</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 py-2">Generate Schedules</button>
            </form>
        </div>
    </div>
</div>
@endsection