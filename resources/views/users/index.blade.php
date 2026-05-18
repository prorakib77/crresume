@extends('layouts.bootstrap_master')

@section('title', 'Users Management')
@section('page-title', 'Users Management')
@section('page-subtitle', 'Manage all system users')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>All Users
                    </h5>
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add User
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search Box -->
                    <div class="table-search mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" id="userSearch" class="form-control search-input"
                                       placeholder="Search users by name, email, role...">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Permissions</th>
                                    <th>Assigned Clients</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                    <i class="fas fa-user text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $user->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->role)
                                                @if($user->role->name === 'admin')
                                                    <span class="badge bg-danger">{{ ucfirst($user->role->name) }}</span>
                                                @elseif($user->role->name === 'agent')
                                                    <span class="badge bg-success">{{ ucfirst($user->role->name) }}</span>
                                                @elseif($user->role->name === 'client')
                                                    <span class="badge bg-info">{{ ucfirst($user->role->name) }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($user->role->name) }}</span>
                                                @endif
                                            @else
                                                <span class="badge bg-warning text-dark">No Role</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->role && $user->role->permissions->count() > 0)
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($user->role->permissions->take(3) as $permission)
                                                        <span class="badge bg-light text-dark small">{{ $permission->name }}</span>
                                                    @endforeach
                                                    @if($user->role->permissions->count() > 3)
                                                        <span class="badge bg-light text-muted small">+{{ $user->role->permissions->count() - 3 }} more</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">No Permissions</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $user->clients_count ?? 0 }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-edit" title="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete User">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-users fa-2x mb-3"></i>
                                            <p class="mb-0">No users found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Search functionality
    $('#userSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#usersTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
@endpush
