@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Contact Messages</h2>
            <p class="text-muted mb-0">
                Customer inquiries from Contact Us form
                @if($unreadCount > 0)
                <span class="badge bg-danger ms-2">{{ $unreadCount }} unread</span>
                @endif
            </p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th width="40"></th>
                        <th>From</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                    <tr class="{{ !$message->is_read ? 'table-warning' : '' }}">
                        <td>
                            @if(!$message->is_read)
                            <span class="badge bg-primary rounded-circle p-1">&nbsp;</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $message->name }}</strong>
                            <div class="small text-muted">{{ $message->email }}</div>
                        </td>
                        <td>
                            <a href="{{ route('admin.messages.show', $message->id) }}" class="text-decoration-none text-dark">
                                {{ Str::limit($message->subject, 50) }}
                            </a>
                            <div class="small text-muted">{{ Str::limit($message->message, 80) }}</div>
                        </td>
                        <td>
                            <span class="small">{{ $message->created_at->diffForHumans() }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.messages.show', $message->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <form action="{{ route('admin.messages.delete', $message->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this message?')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No messages yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($messages->hasPages())
        <div class="card-footer bg-white">
            {{ $messages->links() }}
        </div>
        @endif
    </div>
</div>
@endsection