<section class="space-y-6">
    @php($isEmailVerified = filled($user->email_verified_at))

    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#9b7431]">Profile Details</p>
            <h2 class="theme-display mt-2 text-2xl font-bold text-stone-950 sm:text-3xl">Personal information</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-600 sm:leading-7">
                Keep your account identity up to date so notifications, approvals, and system activity stay tied to the right profile.
            </p>
        </div>

        <span class="inline-flex items-center rounded-full border border-[#ead8ae] bg-[#fbf5e8] px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431] sm:px-4 sm:py-2">
            Identity
        </span>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="name" class="form-label">{{ __('Full Name') }}</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $user->name) }}"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Enter your full name"
                >
                @error('name')
                    <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="form-label">{{ __('Email Address') }}</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $user->email) }}"
                    required
                    autocomplete="username"
                    placeholder="Enter your email address"
                >
                @error('email')
                    <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if (! $isEmailVerified)
            <div class="rounded-[1.35rem] border border-amber-200 bg-[#fbf6ec] p-4 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-amber-900">{{ __('Email verification required') }}</p>
                        <p class="mt-2 text-sm leading-6 text-amber-800/80 sm:leading-7">
                            {{ __('Your email address is still unverified. Confirm it to unlock the most reliable notifications and account recovery flow.') }}
                        </p>
                    </div>

                    <button
                        form="send-verification"
                        type="submit"
                        class="inline-flex transform-gpu items-center justify-center rounded-full border border-[#c8a45d] bg-[#c8a45d] px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#1a1307] transition hover:scale-[1.02] active:scale-[0.99]"
                    >
                        {{ __('Send Verification Link') }}
                    </button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <div class="mt-4 rounded-[1rem] border border-emerald-200 bg-white px-4 py-3 text-sm text-emerald-700">
                        {{ __('A fresh verification link has been sent to your email address.') }}
                    </div>
                @endif
            </div>
        @else
            <div class="rounded-[1.3rem] border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-700">
                {{ __('Your email address is verified and active.') }}
            </div>
        @endif

        <div class="flex flex-col gap-3 border-t border-[#efe7d7] pt-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-h-[1.5rem] text-sm">
                @if (session('status') === 'profile-updated')
                    <span
                        class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-emerald-700"
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 3000)"
                    >
                        {{ __('Profile updated successfully.') }}
                    </span>
                @else
                    <span class="text-stone-500">{{ __('Changes are saved without affecting your backend permissions or account history.') }}</span>
                @endif
            </div>

            <button
                type="submit"
                class="inline-flex transform-gpu items-center justify-center rounded-full border border-black bg-black px-5 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-white transition hover:scale-[1.02] active:scale-[0.99]"
            >
                {{ __('Save Profile') }}
            </button>
        </div>
    </form>
</section>
