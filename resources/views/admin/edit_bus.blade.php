@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Bus Capacity</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.buses.update', $bus->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label>Bus Code</label>
                            <input type="text" name="code" class="form-control" value="{{ $bus->code }}">
                        </div>

                        <div class="mb-3">
                            <label>Type</label>
                            <select name="type" class="form-select">
                                <option value="deluxe" {{ $bus->type == 'deluxe' ? 'selected' : '' }}>Deluxe</option>
                                <option value="regular" {{ $bus->type == 'regular' ? 'selected' : '' }}>Regular</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Capacity</label>
                            <input type="number" name="capacity" class="form-control" value="{{ $bus->capacity }}">
                        </div>

                        <button type="submit" class="btn btn-success w-100">Update Bus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection