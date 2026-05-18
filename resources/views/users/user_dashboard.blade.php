@extends('layouts.bootstrap_master')

@section('title', 'User Dashboard')
@section('page-title', 'User Dashboard')
@section('page-subtitle', 'Welcome back, ' . auth()->user()->name)

@section('content')
<div class="row">
    <!-- Profile Card -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-circle me-2"></i>Profile Information
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>{{ auth()->user()->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{{ auth()->user()->email }}</td>
                    </tr>
                    <tr>
                        <td><strong>Role:</strong></td>
                        <td>
                            <span class="badge bg-primary">{{ auth()->user()->role->display_name ?? 'User' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <span class="badge bg-success">Active</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary w-100">
                            <i class="fas fa-edit d-block mb-1"></i>
                            <small>Edit Profile</small>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('workUpdates.my') }}" class="btn btn-success w-100">
                            <i class="fas fa-clipboard-list d-block mb-1"></i>
                            <small>My Updates</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Welcome Message -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Welcome to W Automation
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb me-2"></i>Getting Started</h6>
                    <p class="mb-2">Welcome to the W Automation system! Here's what you can do:</p>
                    <ul class="mb-0">
                        <li><strong>View Updates:</strong> Check work updates relevant to you</li>
                        <li><strong>Manage Profile:</strong> Keep your profile information up to date</li>
                        <li><strong>Contact Support:</strong> Contact your system administrator for help</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
