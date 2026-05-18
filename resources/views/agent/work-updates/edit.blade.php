<x-app-layout>
    <x-slot name="title">Edit Work Update Draft</x-slot>
    <x-slot name="pageTitle">Edit Work Update Draft</x-slot>
    <x-slot name="pageSubtitle">Edit your saved draft work updates and submit after reaching the assignment minimum.</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Draft #{{ $draft->id }}
                        <span class="badge bg-info ms-2">{{ $draft->getDraftSavedTime() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('agent.work-updates.update-draft', ['draft' => $draft->id]) }}" id="editDraftForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="minimumRequired" value="{{ (int) ($minimumRequired ?? \App\Models\AgentClientAssignment::DEFAULT_MINIMUM_WORK_UPDATES) }}">

                        <!-- Client Selection -->
                        <div class="mb-4">
                            <label class="form-label">Select Client *</label>
                            <select name="client_id" id="client_id" class="form-control" required>
                                <option value="">Choose a client...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ $draft->client_id == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }} ({{ $client->email }})
                                    </option>
                                @endforeach
                                {{-- Show current client even if not in agent's client list --}}
                                @if($draft->client && !$clients->contains('id', $draft->client_id))
                                    <option value="{{ $draft->client_id }}" selected>
                                        {{ $draft->client->name }} ({{ $draft->client->email }}) - Current
                                    </option>
                                @endif
                            </select>
                            @error('client_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="workUpdatesContainer">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <i class="fas fa-briefcase me-2"></i>Work Updates
                                    <span class="badge bg-primary ms-2" id="updateCount">{{ isset($drafts) ? $drafts->count() : 0 }}</span>
                                </h6>
                            </div>

                            @if(isset($drafts) && $drafts->count() > 0)
                                @foreach($drafts as $index => $update)
                                    <div class="work-update-item border rounded p-3 mb-3" data-index="{{ $index }}">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Update #{{ $index + 1 }}</h6>
                                            <button
                                                type="submit"
                                                form="deleteDraftItemForm-{{ $update->id }}"
                                                class="btn btn-outline-danger btn-sm ms-3"
                                                onclick="return confirm('Delete this draft item?');"
                                            >
                                                <i class="fas fa-trash-alt me-1"></i>Delete Item
                                            </button>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Job Title *</label>
                                                <input type="text" name="updates[{{ $update->id }}][job_title]" class="form-control" value="{{ $update->job_title }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Company *</label>
                                                <input type="text" name="updates[{{ $update->id }}][company]" class="form-control" value="{{ $update->company }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Applied Date *</label>
                                                <input type="date" name="updates[{{ $update->id }}][applied_date]" class="form-control" value="{{ $update->applied_date?->format('Y-m-d') ?? date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Applied Method *</label>
                                                <select name="updates[{{ $update->id }}][applied_method]" class="form-control" required>
                                                    <option value="">Select method...</option>
                                                    <option value="web" {{ $update->applied_method === 'web' ? 'selected' : '' }}>Company Website</option>
                                                    <option value="linkedin" {{ $update->applied_method === 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                                                    <option value="referral" {{ $update->applied_method === 'referral' ? 'selected' : '' }}>Referral</option>
                                                    <option value="direct" {{ $update->applied_method === 'direct' ? 'selected' : '' }}>Direct Application</option>
                                                    <option value="email" {{ $update->applied_method === 'email' ? 'selected' : '' }}>Email</option>
                                                    <option value="other" {{ $update->applied_method === 'other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Application Status *</label>
                                                <select name="updates[{{ $update->id }}][application_status]" class="form-control" required>
                                                    <option value="">Select status...</option>
                                                    @foreach(\App\Models\WorkUpdate::getAgentApplicationStatuses() as $value => $label)
                                                        <option value="{{ $value }}" {{ $update->application_status === $value ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Job Application Link *</label>
                                                <input type="url" name="updates[{{ $update->id }}][job_link]" class="form-control" value="{{ $update->job_link }}" placeholder="https://example.com/job" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Job Success Link *</label>
                                                <input type="url" name="updates[{{ $update->id }}][job_success_link]" class="form-control" value="{{ $update->job_success_link }}" placeholder="https://example.com/success" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Notes</label>
                                                <textarea name="updates[{{ $update->id }}][note]" class="form-control" rows="3" placeholder="Additional notes about this application...">{{ $update->note }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="alert alert-info">
                                    No drafts found for this client.
                                </div>
                            @endif
                        </div>

                        <!-- Validation Alert -->
                        <div class="alert alert-warning" id="validationAlert" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Low Application:</strong> You must submit at least
                            <span id="minimumRequiredText">{{ (int) ($minimumRequired ?? \App\Models\AgentClientAssignment::DEFAULT_MINIMUM_WORK_UPDATES) }}</span>
                            work updates for this client.
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('agent.work-updates.drafts') }}" class="btn btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Drafts
                                </a>
                                <button type="button" class="btn btn-outline-success me-2" onclick="saveDraft()">
                                    <i class="fas fa-save me-1"></i>Save Draft
                                </button>
                            </div>
                            <button type="button" class="btn btn-primary" id="submitBtn" onclick="submitWorkUpdates()">
                                <i class="fas fa-paper-plane me-1"></i>Submit Work Updates
                            </button>
                        </div>
                    </form>

                    @if(isset($drafts) && $drafts->count() > 0)
                        @foreach($drafts as $update)
                            <form
                                id="deleteDraftItemForm-{{ $update->id }}"
                                method="POST"
                                action="{{ route('agent.work-updates.delete-draft', ['draft' => $update->id]) }}"
                                class="d-none"
                            >
                                @csrf
                                @method('DELETE')
                            </form>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .work-update-item {
            transition: border-color 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            border: 2px solid #e9ecef !important;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
        }

        .btn {
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .col-md-6 {
                margin-bottom: 1rem;
            }
        }
    </style>

    @push('scripts')
    <script>
        const minimumRequired = Number(document.getElementById('minimumRequired')?.value || '{{ \App\Models\AgentClientAssignment::DEFAULT_MINIMUM_WORK_UPDATES }}');

        function updateCount() {
            const count = document.querySelectorAll('.work-update-item').length;
            document.getElementById('updateCount').textContent = count;

            // Show/hide validation alert
            const alert = document.getElementById('validationAlert');
            const submitBtn = document.getElementById('submitBtn');
            if (count < minimumRequired) {
                alert.style.display = 'block';
                submitBtn.disabled = true;
            } else {
                alert.style.display = 'none';
                submitBtn.disabled = false;
            }
        }

        function submitWorkUpdates() {
            // Add _submit parameter to indicate this is a submission, not just a draft save
            const form = document.getElementById('editDraftForm');
            const submitInput = document.createElement('input');
            submitInput.type = 'hidden';
            submitInput.name = '_submit';
            submitInput.value = '1';
            form.appendChild(submitInput);

            // Submit the form
            form.submit();
        }

        function saveDraft() {
            // Save draft without submitting - use the form's action URL
            const form = document.getElementById('editDraftForm');

            // Create a hidden input to indicate this is just a draft save
            const draftInput = document.createElement('input');
            draftInput.type = 'hidden';
            draftInput.name = '_draft_only';
            draftInput.value = '1';
            form.appendChild(draftInput);

            // Submit the form normally
            form.submit();
        }

        function showNotification(message, type = 'info') {
            // Create a simple toast notification
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(toast);

            // Auto-remove after 3 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 3000);
        }

        // Initial setup
        document.addEventListener('DOMContentLoaded', function() {
            // Initial count update
            updateCount();
        });
    </script>
    @endpush
</x-app-layout>
