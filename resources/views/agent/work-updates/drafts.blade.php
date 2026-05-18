<x-app-layout>
    <x-slot name="title">Draft Work Updates</x-slot>
    <x-slot name="pageTitle">Draft Work Updates</x-slot>
    <x-slot name="pageSubtitle">Drafts are saved per client. Submit when each client reaches its assigned minimum.</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-save me-2"></i>Saved Drafts
                    </h5>
                    <a href="{{ route('agent.work-updates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Work Updates
                    </a>
                </div>
                <div class="card-body">
                    @if($drafts->count())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Draft Count</th>
                                        <th>Required Minimum</th>
                                        <th>Last Saved</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($drafts as $clientId => $draftInfo)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $draftInfo['client']?->name ?? 'Unknown' }}</div>
                                                <small class="text-muted">{{ $draftInfo['client']?->email }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $draftInfo['count'] }} update(s)</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-dark">{{ $draftInfo['minimum_required'] }} update(s)</span>
                                            </td>
                                            <td>{{ \Illuminate\Support\Carbon::parse($draftInfo['latest'])->diffForHumans() }}</td>
                                            <td>
                                                @if(!empty($draftInfo['draft']))
                                                    <a href="{{ route('agent.work-updates.edit-draft', ['draft' => $draftInfo['draft']->id]) }}" class="btn btn-outline-primary btn-sm me-2">
                                                        <i class="fas fa-edit me-1"></i>Edit
                                                    </a>
                                                    <form method="POST" action="{{ route('agent.work-updates.delete-draft-group', ['draft' => $draftInfo['draft']->id]) }}" class="d-inline" onsubmit="return confirm('Delete all draft items for this client?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm me-2">
                                                            <i class="fas fa-trash-alt me-1"></i>Delete
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('agent.work-updates.submit-drafts') }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="client_id" value="{{ $clientId }}">
                                                    <button type="submit" class="btn btn-success btn-sm" {{ $draftInfo['count'] < $draftInfo['minimum_required'] ? 'disabled' : '' }}>
                                                        <i class="fas fa-paper-plane me-1"></i>Submit
                                                    </button>
                                                </form>
                                                @if($draftInfo['count'] < $draftInfo['minimum_required'])
                                                    <div class="text-muted small mt-1">Need at least {{ $draftInfo['minimum_required'] }} to submit</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-save fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No drafts yet. Start by adding work updates.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
