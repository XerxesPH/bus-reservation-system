@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('admin.messages') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i> Back to Inbox
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h4 class="mb-1 fw-bold">{{ $message->subject }}</h4>
            <div class="text-muted small">
                From: <strong>{{ $message->name }}</strong> ({{ $message->email }})
                @if($message->phone)
                | Phone: {{ $message->phone }}
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <small class="text-muted">
                    <i class="fa-solid fa-clock me-1"></i>
                    {{ $message->created_at->format('F d, Y h:i A') }}
                    ({{ $message->created_at->diffForHumans() }})
                </small>
            </div>
            <hr>
            <div class="message-content" style="white-space: pre-wrap; font-size: 1.1rem; line-height: 1.8;">{{ $message->message }}</div>
        </div>
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between">
                <a href="mailto:{{ $message->email }}?subject=Re: {{ $message->subject }}" class="btn btn-primary">
                    <i class="fa-solid fa-reply me-2"></i> Reply via Email
                </a>
                <form action="{{ route('admin.messages.delete', $message->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Delete this message?')">
                        <i class="fa-solid fa-trash me-2"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection