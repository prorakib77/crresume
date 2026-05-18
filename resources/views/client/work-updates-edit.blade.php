<x-app-layout>
    <x-slot name="title">Edit Work Update</x-slot>
    <x-slot name="pageTitle">Edit Work Update</x-slot>
    <x-slot name="pageSubtitle">Update the application result for this work update.</x-slot>

    @php
        $selectedStatus = old(
            'application_status',
            array_key_exists($workUpdate->application_status, $editableStatuses) ? $workUpdate->application_status : ''
        );
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">
                            <i class="fas fa-pen-to-square me-2"></i>{{ $workUpdate->job_title }}
                        </h5>
                        <p class="text-muted mb-0">{{ $workUpdate->company }}</p>
                    </div>
                    <span class="badge bg-light text-dark border">{{ $workUpdate->getApplicationStatusLabel() }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted text-uppercase fw-semibold mb-1">Applied Via</div>
                                <div class="fw-semibold">{{ $workUpdate->getAppliedMethodLabel() }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted text-uppercase fw-semibold mb-1">Applied Date</div>
                                <div class="fw-semibold">{{ ($workUpdate->applied_date ?? $workUpdate->created_at)?->format('M j, Y') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted text-uppercase fw-semibold mb-1">Submitted</div>
                                <div class="fw-semibold">{{ $workUpdate->created_at->format('M j, Y g:i A') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted text-uppercase fw-semibold mb-1">Agent</div>
                                <div class="fw-semibold">{{ $workUpdate->agent?->name ?? 'Not available' }}</div>
                            </div>
                        </div>
                    </div>

                    @if($workUpdate->note)
                        <div class="alert alert-light border mb-4">
                            <div class="small text-muted text-uppercase fw-semibold mb-1">Update Note</div>
                            <div>{{ $workUpdate->note }}</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('client.work-updates.update', $workUpdate->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="application_status" class="form-label fw-semibold">
                                Application Status <span class="text-danger">*</span>
                            </label>
                            <select
                                id="application_status"
                                name="application_status"
                                class="form-control @error('application_status') is-invalid @enderror"
                                required
                            >
                                <option value="">Select updated status...</option>
                                @foreach($editableStatuses as $value => $label)
                                    <option value="{{ $value }}" {{ $selectedStatus === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('application_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Current status: {{ $workUpdate->getApplicationStatusLabel() }}. Choose Interview, Hired, or Rejected when the application outcome changes.
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-between">
                            <a href="{{ route('client.work-updates.index') }}" class="btn btn-white">
                                <i class="fas fa-arrow-left me-2"></i>Back to Work Updates
                            </a>
                            <button type="submit" class="btn btn-black">
                                <i class="fas fa-floppy-disk me-2"></i>Save Status
                            </button>
                        </div>
                    </form>

                    @if($workUpdate->job_link || $workUpdate->job_success_link)
                        <div class="d-flex flex-column flex-md-row gap-2 mt-4">
                            @if($workUpdate->job_link)
                                <a href="{{ $workUpdate->job_link }}" target="_blank" class="btn btn-outline-dark">
                                    <i class="fas fa-external-link-alt me-2"></i>Open Job Link
                                </a>
                            @endif
                            @if($workUpdate->job_success_link)
                                <a href="{{ $workUpdate->job_success_link }}" target="_blank" class="btn btn-outline-success">
                                    <i class="fas fa-check-circle me-2"></i>Open Success Link
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
