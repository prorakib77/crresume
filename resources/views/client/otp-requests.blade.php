<x-app-layout>
    <x-slot name="title">Verification code Requests</x-slot>
    <x-slot name="pageTitle">Verification code Requests</x-slot>
    <x-slot name="pageSubtitle">View and submit your pending verification code requests.</x-slot>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="card-title mb-0">Verification Code Submission Links</h5>
                <small class="text-muted">Use the links below to submit your company & verification code details.</small>
            </div>
        </div>
        <div class="card-body">
            @if($otps->count())
                <div class="table-responsive d-none d-md-block">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Requested</th>
                                <th>Agent</th>
                                <th>Status</th>
                                <th>Expires</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($otps as $otp)
                                @php
                                    $isExpired = $otp->expires_at?->isPast();
                                    $submission = $otpStatuses[$otp->id] ?? null;
                                    $statusLabel = $submission?->status
                                        ? ucfirst($submission->status)
                                        : ($otp->is_verified ? 'Pending' : ($isExpired ? 'Expired' : 'Pending'));
                                    $statusClass = match($statusLabel) {
                                        'Approved' => 'bg-success',
        'Rejected' => 'bg-danger',
        'Pending' => 'bg-warning text-dark',
        default => 'bg-secondary'
    };
                                    $publicLink = route('otp.submit.public', $otp);
                                @endphp
                                <tr>
                                    <td>{{ $otp->created_at?->format('M j, Y g:i A') ?? 'N/A' }}</td>
                                    <td>{{ $otp->agent?->name ?? 'Agent' }}</td>
                                    <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                    <td>{{ $otp->expires_at?->diffForHumans() ?? 'N/A' }}</td>
                                    <td class="text-end">
                                        <a href="{{ $publicLink }}" target="_blank" class="btn btn-sm btn-black">
                                            <i class="fas fa-link me-1"></i>Open Link
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-md-none">
                    @foreach($otps as $otp)
                        @php
                            $isExpired = $otp->expires_at?->isPast();
                            $submission = $otpStatuses[$otp->id] ?? null;
                            $statusLabel = $submission?->status
                                ? ucfirst($submission->status)
                                : ($otp->is_verified ? 'Pending' : ($isExpired ? 'Expired' : 'Pending'));
                            $statusClass = match($statusLabel) {
                                'Approved' => 'bg-success',
                                'Rejected' => 'bg-danger',
                                'Pending' => 'bg-warning text-dark',
                                default => 'bg-secondary'
                            };
                            $publicLink = route('otp.submit.public', $otp);
                        @endphp
                        <div class="ticket-card mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="ticket-title">Requested {{ $otp->created_at?->diffForHumans() ?? 'recently' }}</div>
                                    <div class="text-muted small">Agent: {{ $otp->agent?->name ?? 'Agent' }}</div>
                                </div>
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                            <div class="ticket-meta mt-2">
                                <div><i class="fas fa-clock me-2 text-muted"></i>Expires: {{ $otp->expires_at?->diffForHumans() ?? 'N/A' }}</div>
                                @if($otp->message)
                                    <div class="small text-muted">{{ $otp->message }}</div>
                                @endif
                            </div>
                            <div class="ticket-footer mt-3">
                                <span class="small text-muted">Public Link</span>
                                <a href="{{ $publicLink }}" target="_blank" class="btn btn-sm btn-black">Open</a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <x-beautiful-pagination :paginator="$otps" />
            @else
                <div class="text-center py-5">
                    <i class="fas fa-key fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No OTP requests yet</h5>
                    <p class="text-muted">We will send requests when needed. Check back later.</p>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
    <style>
        .ticket-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1rem 1.1rem;
            background: #fff;
            box-shadow: 0 8px 18px rgba(0,0,0,0.04);
            display: grid;
            gap: 0.35rem;
        }

        .ticket-title {
            font-weight: 700;
            color: #0f172a;
        }

        .ticket-meta {
            color: #475569;
            font-size: 0.9rem;
            display: grid;
            gap: 0.25rem;
        }

        .ticket-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
    </style>
    @endpush
</x-app-layout>
