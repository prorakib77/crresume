<section class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#9b7431]">Security</p>
            <h2 class="theme-display mt-2 text-2xl font-bold text-stone-950 sm:text-3xl">Password & access</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-600 sm:leading-7">
                Refresh your password with something long and unique. The cleaner your credentials, the harder your account is to compromise.
            </p>
        </div>

        <span class="inline-flex items-center rounded-full border border-[#ead8ae] bg-[#fbf5e8] px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431] sm:px-4 sm:py-2">
            Secure
        </span>
    </div>

    <div class="rounded-[1.35rem] border border-[#ead8ae] bg-[#fbf5e8] p-4 sm:p-5">
        <p class="text-sm font-semibold text-stone-900">{{ __('Security recommendation') }}</p>
        <p class="mt-2 text-sm leading-6 text-stone-600 sm:leading-7">
            {{ __('Use a password with at least 8 characters and avoid reusing passwords from other websites or devices.') }}
        </p>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="form-label">{{ __('Current Password') }}</label>
            <div class="relative">
                <input
                    id="update_password_current_password"
                    name="current_password"
                    type="password"
                    class="form-control pr-24 @error('current_password', 'updatePassword') is-invalid @enderror"
                    autocomplete="current-password"
                    placeholder="Enter your current password"
                    required
                >
                <button
                    type="button"
                    data-password-toggle="update_password_current_password"
                    class="absolute inset-y-2 right-2 inline-flex transform-gpu items-center rounded-full border border-[#d8c6a1] bg-[#fbf5e8] px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-[#7f5e21] transition hover:scale-[1.02] active:scale-[0.99]"
                >
                    <span data-toggle-label>Show</span>
                </button>
            </div>
            @error('current_password', 'updatePassword')
                <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="update_password_password" class="form-label">{{ __('New Password') }}</label>
                <div class="relative">
                    <input
                        id="update_password_password"
                        name="password"
                        type="password"
                        class="form-control pr-24 @error('password', 'updatePassword') is-invalid @enderror"
                        autocomplete="new-password"
                        placeholder="Enter your new password"
                        required
                    >
                    <button
                        type="button"
                        data-password-toggle="update_password_password"
                        class="absolute inset-y-2 right-2 inline-flex transform-gpu items-center rounded-full border border-[#d8c6a1] bg-[#fbf5e8] px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-[#7f5e21] transition hover:scale-[1.02] active:scale-[0.99]"
                    >
                        <span data-toggle-label>Show</span>
                    </button>
                </div>
                @error('password', 'updatePassword')
                    <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs leading-6 text-stone-500">{{ __('Minimum 8 characters. Longer is better.') }}</p>
            </div>

            <div>
                <label for="update_password_password_confirmation" class="form-label">{{ __('Confirm New Password') }}</label>
                <div class="relative">
                    <input
                        id="update_password_password_confirmation"
                        name="password_confirmation"
                        type="password"
                        class="form-control pr-24 @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                        autocomplete="new-password"
                        placeholder="Confirm your new password"
                        required
                    >
                    <button
                        type="button"
                        data-password-toggle="update_password_password_confirmation"
                        class="absolute inset-y-2 right-2 inline-flex transform-gpu items-center rounded-full border border-[#d8c6a1] bg-[#fbf5e8] px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-[#7f5e21] transition hover:scale-[1.02] active:scale-[0.99]"
                    >
                        <span data-toggle-label>Show</span>
                    </button>
                </div>
                @error('password_confirmation', 'updatePassword')
                    <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-[#efe7d7] pt-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-h-[1.5rem] text-sm">
                @if (session('status') === 'password-updated')
                    <span
                        class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-emerald-700"
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 3000)"
                    >
                        {{ __('Password updated successfully.') }}
                    </span>
                @else
                    <span class="text-stone-500">{{ __('Updating your password does not affect your assigned role or existing data.') }}</span>
                @endif
            </div>

            <button
                type="submit"
                class="inline-flex transform-gpu items-center justify-center rounded-full border border-black bg-black px-5 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-white transition hover:scale-[1.02] active:scale-[0.99]"
            >
                {{ __('Update Password') }}
            </button>
        </div>
    </form>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            if (button.dataset.bound === 'true') {
                return;
            }

            button.dataset.bound = 'true';
            button.addEventListener('click', function () {
                const input = document.getElementById(button.dataset.passwordToggle);
                const label = button.querySelector('[data-toggle-label]');

                if (!input || !label) {
                    return;
                }

                const shouldShow = input.type === 'password';
                input.type = shouldShow ? 'text' : 'password';
                label.textContent = shouldShow ? 'Hide' : 'Show';
            });
        });
    });
</script>
@endpush
