<x-guest-layout>
    <div class="space-y-6">
        @if($otpVerification)
            @php
                $validityMinutes = 10;
                if ($otpVerification->created_at && $otpVerification->expires_at) {
                    $validityMinutes = max(1, (int) ceil($otpVerification->created_at->diffInSeconds($otpVerification->expires_at, false) / 60));
                }
            @endphp
            <div class="rounded-2xl border border-[#eadfca] bg-[#fffdfa] p-4">
                <div class="grid gap-3 text-sm text-stone-700">
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-stone-500">Requested By</p>
                            <p class="mt-1 font-semibold text-stone-900">Support Team</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-stone-500">Requested On</p>
                            <p class="mt-1 font-semibold text-stone-900">{{ $otpVerification->created_at->format('M j, Y \a\t g:i A') }}</p>
                        </div>
                    </div>

                    @if($otpVerification->message)
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-stone-500">Message</p>
                            <p class="mt-1 text-stone-700">{{ $otpVerification->message }}</p>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        @if($otpVerification->isExpired())
                            <span class="inline-flex items-center rounded-full border border-rose-300 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">Expired</span>
                        @elseif($otpVerification->is_verified)
                            <span class="inline-flex items-center rounded-full border border-emerald-300 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Submitted</span>
                        @else
                            <span class="inline-flex items-center rounded-full border border-emerald-300 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Active</span>
                        @endif
                        <span class="text-xs text-stone-500">Expires in {{ $validityMinutes }} minute{{ $validityMinutes === 1 ? '' : 's' }}</span>
                    </div>
                </div>
            </div>

            @if($otpVerification->isExpired())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    This verification request has expired.
                </div>
            @elseif($otpVerification->is_verified)
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    Verification already submitted.
                </div>
            @else
                <form id="otpSubmitForm" class="space-y-5">
                    @csrf

                    <div id="otpAlertHost"></div>

                    <div>
                        <label for="company_name" class="form-label">Company Name</label>
                        <input
                            type="text"
                            class="form-control"
                            id="company_name"
                            name="company_name"
                            placeholder="Enter company name"
                            required
                        >
                    </div>

                    <div>
                        <label for="otp_code" class="form-label">Verification Code</label>
                        <input
                            type="text"
                            class="form-control"
                            id="otp_code"
                            name="otp_code"
                            placeholder="Enter verification code"
                            required
                        >
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full transform-gpu items-center justify-center rounded-full border border-black bg-black px-5 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-white transition duration-150 ease-in-out hover:scale-[1.02] active:scale-[0.99]"
                        id="submitBtn"
                    >
                        <span id="submitBtnLabel">Submit</span>
                    </button>
                </form>
            @endif
        @else
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                Invalid or expired verification request.
            </div>
        @endif

        <p class="text-center text-xs font-medium text-stone-500">
            @if($otpVerification)
                This verification code is valid for {{ $validityMinutes }} minute{{ $validityMinutes === 1 ? '' : 's' }}.
            @else
                This verification code is valid for 10 minutes.
            @endif
        </p>
    </div>

    @if($otpVerification && !$otpVerification->isExpired() && !$otpVerification->is_verified)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('otpSubmitForm');
                const submitBtn = document.getElementById('submitBtn');
                const submitBtnLabel = document.getElementById('submitBtnLabel');
                const alertHost = document.getElementById('otpAlertHost');

                if (!form) {
                    return;
                }

                const showAlert = (type, message) => {
                    const palette = type === 'success'
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                        : 'border-rose-200 bg-rose-50 text-rose-700';

                    alertHost.innerHTML = `<div class="rounded-2xl border px-4 py-3 text-sm ${palette}">${message}</div>`;

                    if (type !== 'success') {
                        setTimeout(() => {
                            alertHost.innerHTML = '';
                        }, 5000);
                    }
                };

                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    const companyName = document.getElementById('company_name').value.trim();
                    const otpCode = document.getElementById('otp_code').value.trim();

                    if (!companyName) {
                        showAlert('error', 'Please enter the company name.');
                        return;
                    }

                    if (!otpCode) {
                        showAlert('error', 'Please enter the verification code.');
                        return;
                    }

                    submitBtn.disabled = true;
                    submitBtn.classList.add('cursor-not-allowed', 'opacity-70');
                    submitBtnLabel.textContent = 'Submitting';

                    fetch('{{ route("otp.verify.public", $otpVerification) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            company_name: companyName,
                            otp_code: otpCode,
                        }),
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showAlert('success', data.message || 'Submitted successfully.');
                                setTimeout(() => {
                                    window.location.href = '{{ route("client.dashboard") }}';
                                }, 2000);
                                return;
                            }

                            showAlert('error', data.message || 'Submission failed. Please try again.');
                        })
                        .catch(() => {
                            showAlert('error', 'An error occurred while submitting. Please try again.');
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.classList.remove('cursor-not-allowed', 'opacity-70');
                            submitBtnLabel.textContent = 'Submit';
                        });
                });
            });
        </script>
    @endif
</x-guest-layout>
