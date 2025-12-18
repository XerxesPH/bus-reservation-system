@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">User Management</h2>
            <p class="text-muted mb-0">Manage registered users</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Bookings</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <strong>{{ $user->name }}</strong>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->contact_number ?? $user->phone_number ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $user->bookings_count }}</span>
                        </td>
                        <td>
                            <span class="small">{{ $user->created_at->format('M d, Y') }}</span>
                        </td>
                        <td>
                            @if($user->is_banned ?? false)
                            <span class="badge bg-danger">Banned</span>
                            @else
                            <span class="badge bg-success">Active</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.users.toggle_ban', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                @if($user->is_banned ?? false)
                                <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Unban this user?')">
                                    <i class="fa-solid fa-unlock"></i> Unban
                                </button>
                                @else
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Ban this user?')">
                                    <i class="fa-solid fa-ban"></i> Ban
                                </button>
                                @endif
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fa-solid fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No users found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="card-footer bg-white">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection