@extends('layouts.dashboard_master')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-user-plus"></i>
                        @if($currentClient)
                            Manage Assignments for: {{ $currentClient->name }}
                        @else
                            Assign Client to Agent
                        @endif
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle"></i> {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Show current assignments if editing specific client --}}
                    @if($currentClient && $currentAssignments->count() > 0)
                        <div class="mb-4">
                            <h6 class="fw-bold text-success">
                                <i class="fas fa-users"></i> Current Assignments:
                            </h6>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach($currentAssignments as $assignment)
                                    <div class="d-flex align-items-center bg-light rounded px-3 py-2">
                                        <span class="badge bg-success rounded-pill me-2">
                                            <i class="fas fa-user-tie"></i>
                                        </span>
                                        <span class="fw-bold">{{ $assignment->name }}</span>
                                        <form action="{{ route('assign.remove') }}" method="POST" class="ms-2"
                                              onsubmit="return confirm('Remove this assignment?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="client_id" value="{{ $currentClient->id }}">
                                            <input type="hidden" name="agent_id" value="{{ $assignment->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                            <hr>
                        </div>
                    @endif

                    <form action="{{ route('assign.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="client_id" class="form-label fw-bold">
                                <i class="fas fa-user"></i> Select Client *
                            </label>
                            <select name="client_id" id="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                                <option value="">-- Choose Client --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}"
                                            {{ (old('client_id') == $client->id || ($currentClient && $currentClient->id == $client->id)) ? 'selected' : '' }}>
                                        {{ $client->name ?? 'Unnamed Client' }}
                                        @if($client->email)
                                            ({{ $client->email }})
                                        @endif
                                        @if($client->clientProfile)
                                            - Status:
                                            @switch($client->clientProfile->status)
                                                @case(0) Inactive @break
                                                @case(1) Assigned @break
                                                @case(2) Active @break
                                                @case(3) Completed @break
                                                @default Unknown
                                            @endswitch
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="agent_id" class="form-label fw-bold">
                                <i class="fas fa-user-tie"></i> Select Agent *
                            </label>
                            <select name="agent_id" id="agent_id" class="form-select @error('agent_id') is-invalid @enderror" required>
                                <option value="">-- Choose Agent --</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }}
                                        @if($agent->email)
                                            ({{ $agent->email }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('agent_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('agents.assign') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Overview
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Assign Agent
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Quick Stats --}}
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card bg-info text-white text-center">
                        <div class="card-body">
                            <h4>{{ $clients->count() }}</h4>
                            <p class="mb-0">Total Clients</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white text-center">
                        <div class="card-body">
                            <h4>{{ $agents->count() }}</h4>
                            <p class="mb-0">Available Agents</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white text-center">
                        <div class="card-body">
                            <h4>
                                @if($currentClient && $currentAssignments)
                                    {{ $currentAssignments->count() }}
                                @else
                                    0
                                @endif
                            </h4>
                            <p class="mb-0">Current Assignments</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-select client if URL parameter is provided
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const clientId = urlParams.get('client');

    if (clientId) {
        const clientSelect = document.getElementById('client_id');
        if (clientSelect) {
            clientSelect.value = clientId;
        }
    }
});
</script>
@endpush
