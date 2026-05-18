<x-guest-layout>
    <div class="space-y-8">
        <div class="text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-[#9b7431]">Set New Credentials</p>
            <h1 class="theme-display mt-3 text-4xl text-stone-950 sm:text-5xl">Choose a new password</h1>
            <p class="mt-3 text-sm leading-6 text-stone-600">
                Create a strong new password to secure your account and continue working from your dashboard.
            </p>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.store') }}" id="resetForm" class="space-y-5">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="form-label">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    class="form-control"
                    placeholder="Enter your email"
                    required
                    autofocus
                    autocomplete="username"
                >
            </div>

            <div>
                <label for="password" class="form-label">New password</label>
                <div class="relative">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-control pr-20"
                        placeholder="Enter new password"
                        required
                        autocomplete="new-password"
                    >
                    <button
                        type="button"
                        class="absolute inset-y-2 right-2 inline-flex transform-gpu items-center rounded-full border border-[#d8c6a1] bg-[#fbf5e8] px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-[#7f5e21] transition hover:scale-[1.02] active:scale-[0.99]"
                        onclick="togglePasswordField('password', 'toggle-password-text')"
                    >
                        <span id="toggle-password-text">Show</span>
                    </button>
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="form-label">Confirm password</label>
                <div class="relative">
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="form-control pr-20"
                        placeholder="Confirm new password"
                        required
                        autocomplete="new-password"
                    >
                    <button
                        type="button"
                        class="absolute inset-y-2 right-2 inline-flex transform-gpu items-center rounded-full border border-[#d8c6a1] bg-[#fbf5e8] px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-[#7f5e21] transition hover:scale-[1.02] active:scale-[0.99]"
                        onclick="togglePasswordField('password_confirmation', 'toggle-password-confirmation-text')"
                    >
                        <span id="toggle-password-confirmation-text">Show</span>
                    </button>
                </div>
            </div>

            <button
                type="submit"
                id="resetBtn"
                class="inline-flex w-full transform-gpu items-center justify-center rounded-full border border-black bg-black px-5 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-white transition duration-150 ease-in-out hover:scale-[1.02] active:scale-[0.99]"
            >
                <span id="resetBtnLabel">Reset password</span>
            </button>
        </form>

        <div class="relative py-1 text-center">
            <div class="absolute inset-x-0 top-1/2 h-px -translate-y-1/2 bg-gradient-to-r from-transparent via-[#d8ccb6] to-transparent"></div>
            <span class="relative bg-white px-4 text-[11px] font-semibold uppercase tracking-[0.28em] text-stone-400">Back instead?</span>
        </div>

        <p class="text-center text-sm text-stone-600">
            Return to sign in
            <a href="{{ route('login') }}" class="ml-1 font-semibold text-[#9b7431] transition hover:text-[#7f5e21]">Login</a>
        </p>
    </div>

    <script>
        function togglePasswordField(fieldId, labelId) {
            const passwordInput = document.getElementById(fieldId);
            const label = document.getElementById(labelId);
            const isHidden = passwordInput.type === 'password';

            passwordInput.type = isHidden ? 'text' : 'password';
            label.textContent = isHidden ? 'Hide' : 'Show';
        }

        document.getElementById('resetForm').addEventListener('submit', function () {
            const button = document.getElementById('resetBtn');
            document.getElementById('resetBtnLabel').textContent = 'Saving';
            button.disabled = true;
            button.classList.add('cursor-not-allowed', 'opacity-70');
        });
    </script>
</x-guest-layout>
