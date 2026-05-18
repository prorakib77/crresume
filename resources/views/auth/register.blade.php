<x-guest-layout>
    <div class="space-y-8">
        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="registerForm" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="form-label">Full name</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="form-control"
                    placeholder="Enter your full name"
                    required
                    autofocus
                    autocomplete="name"
                >
            </div>

            <div>
                <label for="email" class="form-label">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-control"
                    placeholder="Enter your email"
                    required
                    autocomplete="username"
                >
            </div>

            <div>
                <label for="password" class="form-label">Password</label>
                <div class="relative">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-control pr-20"
                        placeholder="Create a password"
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
                        placeholder="Confirm your password"
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

            <p class="text-center text-sm text-stone-600">
                By creating an account, you agree to our
                <a href="{{ route('terms-of-service.page') }}" class="font-bold text-stone-900 underline underline-offset-2 transition hover:text-[#7f5e21]">
                    Terms of Service.
                </a>
            </p>

            <button
                type="submit"
                id="registerBtn"
                class="inline-flex w-full transform-gpu items-center justify-center rounded-full border border-black bg-black px-5 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-white transition duration-150 ease-in-out hover:scale-[1.02] active:scale-[0.99]"
            >
                <span id="registerBtnLabel">Create account</span>
            </button>
        </form>

        <p class="text-center text-sm text-stone-600">
            Already registered?
            <a href="{{ route('login') }}" class="ml-1 font-semibold text-[#9b7431] transition hover:text-[#7f5e21]">Sign in</a>
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

        document.getElementById('registerForm').addEventListener('submit', function () {
            const button = document.getElementById('registerBtn');
            document.getElementById('registerBtnLabel').textContent = 'Creating';
            button.disabled = true;
            button.classList.add('cursor-not-allowed', 'opacity-70');
        });
    </script>
</x-guest-layout>
