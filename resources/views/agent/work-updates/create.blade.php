<x-app-layout>
    <x-slot name="title">Work Updates</x-slot>
    <x-slot name="pageTitle">Add Work Update</x-slot>
    <x-slot name="pageSubtitle">Add one update per save; submit drafts when the assigned minimum is reached.</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-briefcase me-2"></i>Work Updates</h5>
                    <a href="{{ route('agent.work-updates.drafts') }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-save me-2"></i>View Drafts
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('agent.work-updates.store') }}" id="workUpdateForm">
                        @csrf
                        <input type="hidden" name="action" id="formAction" value="draft">

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Select Client</label>
                                <select name="client_id" class="form-control @error('client_id') is-invalid @enderror" required>
                                    <option value="">Choose a client...</option>
                                    @foreach($assignedClients as $client)
                                        <option
                                            value="{{ $client->id }}"
                                            data-minimum="{{ (int) ($minimumByClientId[$client->id] ?? \App\Models\AgentClientAssignment::DEFAULT_MINIMUM_WORK_UPDATES) }}"
                                            {{ $selectedClientId == $client->id ? 'selected' : '' }}
                                        >
                                            {{ $client->name }} ({{ $client->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('client_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="text-muted small mt-2" id="minimumHint">
                                    Minimum required for selected client:
                                    <strong id="minimumHintValue">{{ (int) ($selectedClientId ? ($minimumByClientId[$selectedClientId] ?? \App\Models\AgentClientAssignment::DEFAULT_MINIMUM_WORK_UPDATES) : 0) }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="work-update-item border rounded p-3 mb-3" data-index="0">
                            <h6 class="mb-3">Work Update</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Job Title *</label>
                                    <input type="text" name="work_updates[0][job_title]" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Company *</label>
                                    <input type="text" name="work_updates[0][company]" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Applied Date *</label>
                                    <input type="date" name="work_updates[0][applied_date]" class="form-control" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Applied Method *</label>
                                    <select name="work_updates[0][applied_method]" class="form-control" required>
                                        <option value="">Select method...</option>
                                        <option value="web">Company Website</option>
                                        <option value="linkedin">LinkedIn</option>
                                        <option value="referral">Referral</option>
                                        <option value="direct">Direct Application</option>
                                        <option value="email">Email</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <div class="text-muted small mt-2">
                                        We prefer to select Company Website.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Application Status *</label>
                                    <select name="work_updates[0][application_status]" class="form-control" required>
                                        <option value="">Select status...</option>
                                        @foreach(\App\Models\WorkUpdate::getAgentApplicationStatuses() as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Job Application Link *</label>
                                    <input type="url" name="work_updates[0][job_link]" class="form-control" placeholder="https://..." required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Success Link *</label>
                                    <input type="url" name="work_updates[0][job_success_link]" class="form-control" placeholder="https://..." required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Note</label>
                                    <textarea name="work_updates[0][note]" class="form-control" rows="2" placeholder="Update details..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            Save each update as a draft. Submit from the Drafts page after the client reaches its minimum required draft count.
                        </div>

                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                            <button type="submit" class="btn btn-black">
                                <i class="fas fa-save me-2"></i>Save Draft
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const clientSelect = document.querySelector('select[name="client_id"]');
            const minimumHintValue = document.getElementById('minimumHintValue');

            if (!clientSelect || !minimumHintValue) {
                return;
            }

            const updateHint = () => {
                const selected = clientSelect.options[clientSelect.selectedIndex];
                const minimum = selected?.value
                    ? (selected?.dataset?.minimum || '{{ \App\Models\AgentClientAssignment::DEFAULT_MINIMUM_WORK_UPDATES }}')
                    : '0';
                minimumHintValue.textContent = minimum;
            };

            clientSelect.addEventListener('change', updateHint);
            updateHint();
        });
    </script>
    @endpush
</x-app-layout>
