@extends('layouts.dashboard_master')

@section('content')
<div class="container">
    <h2 class="mb-4">Add New Client</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
        @csrf

        <div class="row">
            <!-- Name -->
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Client Name *</label>
                <input type="text" name="name" id="name" class="form-control" required value="{{ old('name') }}">
            </div>

            <!-- Email -->
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Client Email *</label>
                <input type="email" name="email" id="email" class="form-control" required value="{{ old('email') }}">
            </div>
        </div>

        <div class="row">
            <!-- Password -->
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password *</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <!-- Phone -->
            <div class="col-md-6 mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
            </div>
        </div>

        <div class="row">
            <!-- Address -->
            <div class="col-md-12 mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea name="address" id="address" rows="2" class="form-control">{{ old('address') }}</textarea>
            </div>
        </div>

        <div class="row">
            <!-- Apply To -->
            <div class="col-md-12 mb-3">
                <label for="apply_to" class="form-label">Apply To</label>
                <textarea name="apply_to" id="apply_to" rows="3" class="form-control">{{ old('apply_to') }}</textarea>
            </div>
        </div>

        <div class="row">
            <!-- Status -->
            <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="0">Select Assignment</option>
                    <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Assigned</option>
                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Not Assigned</option>
                </select>
            </div>
        </div>

        <div class="row">
            <!-- Resume -->
            <div class="col-md-6 mb-3">
                <label for="resume" class="form-label">Resume (PDF/DOC)</label>
                <input type="file" name="resume" id="resume" class="form-control" accept=".pdf,.doc,.docx">
            </div>

            <!-- Onboarding File -->
            <div class="col-md-6 mb-3">
                <label for="onboarding_file" class="form-label">Onboarding File (PDF/Excel/Doc)</label>
                <input type="file" name="onboarding_file" id="onboarding_file" class="form-control" accept=".pdf,.xls,.xlsx,.doc,.docx">
            </div>
        </div>

        <div class="row">
            <!-- Service Start Date -->
            <div class="col-md-6 mb-3">
                <label for="service_start_date" class="form-label">Service Start Date</label>
                <input type="date" name="service_start_date" id="service_start_date" class="form-control" value="{{ old('service_start_date') }}">
            </div>

            <!-- Service End Date -->
            <div class="col-md-6 mb-3">
                <label for="service_end_date" class="form-label">Service End Date</label>
                <input type="date" name="service_end_date" id="service_end_date" class="form-control" value="{{ old('service_end_date') }}">
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Add Client</button>
        </div>
    </form>
</div>
@endsection
