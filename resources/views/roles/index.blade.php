@extends('layouts.bootstrap_master')

@section('title', 'Roles Management')
@section('page-title', 'Roles Management')
@section('page-subtitle', 'Manage user roles and permissions')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i>Roles & Permissions
                    </h5>
                    <a href="{{ route('roles.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create New Role
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search Box -->
                    <div class="table-search mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" id="roleSearch" class="form-control search-input"
                                       placeholder="Search roles by name, permissions...">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="rolesTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Role Name</th>
                                    <th>Display Name</th>
                                    <th>Permissions</th>
                                    <th>Users Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ ucfirst($role->name) }}</span>
                                        </td>
                                        <td>{{ $role->display_name ?? ucfirst($role->name) }}</td>
                                        <td>
                                            @if($role->permissions->count() > 0)
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($role->permissions->take(4) as $permission)
                                                        <span class="badge bg-light text-dark small">{{ $permission->name }}</span>
                                                    @endforeach
                                                    @if($role->permissions->count() > 4)
                                                        <span class="badge bg-light text-muted small">+{{ $role->permissions->count() - 4 }} more</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">No permissions</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $role->users_count ?? 0 }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-edit" title="Edit Role">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if(!in_array($role->name, ['admin', 'agent', 'client']))
                                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Role">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-shield-alt fa-2x mb-3"></i>
                                            <p class="mb-0">No roles found.</p>
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
    $('#roleSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#rolesTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
@endpush
