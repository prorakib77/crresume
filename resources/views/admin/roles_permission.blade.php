@extends('layouts.dashboard_master')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
    <h1 class="h2">Role & Permission Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route("roles.create") }}" class="btn btn-sm btn-primary me-2">
            <i class="bi bi-plus-circle"></i> Add Role
        </a>
        <button class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-question-circle"></i> Help
        </button>
    </div>
</div>

<!-- Stats Overview -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stats-box admin">
            <h5>Admin Roles</h5>
            <div class="number">{{ $roles->where('name', 'admin')->count() }}</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stats-box editor">
            <h5>Editors</h5>
            <div class="number">{{ $roles->where('name', 'editor')->count() }}</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stats-box user">
            <h5>Users</h5>
            <div class="number">{{ $roles->where('name', 'user')->count() }}</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stats-box permission">
            <h5>Permissions</h5>
            <div class="number">{{ $permissions->count() }}</div>
        </div>
    </div>
</div>

<!-- Roles Table -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Roles</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Permissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                    <tr>
                        <td>{{ $role->id }}</td>
                        <td>
                            <span class="badge badge-primary text-dark badge-{{ $role->name }}">{{ ucfirst($role->name) }}</span>
                        </td>
                        <td>
                            @foreach ($role->permissions as $permission)
                                <span class="badge badge-primary text-dark">{{ $permission->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-info">Edit</a>
                            @if (!in_array($role->name, ['admin', 'editor', 'user']))
                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Permissions Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">All Permissions</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Permission Name</th>
                        <th>.</th>
                        <th>Roles</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissions as $permission)
                    <tr>
                        <td>{{ $permission->id }}</td>
                        <td><span class="badge bg-info text-dark">{{ $permission->name }}</span></td>
                        <td>{{ $permission->description ?? '' }}</td>
                        <td>
                            @foreach ($permission->roles as $role)
                                <span class="badge badge-secondary text-dark">{{ ucfirst($role->name) }}</span>
                            @endforeach
                        </td>
                        <td>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Role Modal (unchanged, keep as is) -->
<!-- ... your modal code ... -->

@endsection
