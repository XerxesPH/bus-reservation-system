@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">User Management</h2>
            <p class="text-muted mb-0">Manage registered users</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users') }}" class="row g-2 align-items-center">
                <div class="col-12 col-lg">
                    <input type="text" name="q" class="form-control" placeholder="Search users (supports multiple terms, e.g. 'juan@gmail.com active')" value="{{ request('q') }}">
                </div>
                <div class="col-12 col-lg-auto d-grid d-lg-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="fa-solid fa-magnifying-glass me-2"></i> Search
                    </button>
                    @if(request()->filled('q'))
                    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary fw-bold">
                        Clear
                    </a>
                    @endif
                </div>
            </form>
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
                        <td>{{ $user->contact_number ?? 'N/A' }}</td>
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
                            @if(request()->filled('q'))
                            <div class="mt-2">
                                <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-secondary fw-bold">Clear search</a>
                            </div>
                            @endif
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