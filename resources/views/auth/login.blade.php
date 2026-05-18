<x-guest-layout>
    <div class="space-y-8">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm" class="space-y-5">
            @csrf

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
                    autofocus
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
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
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

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <label for="remember_me" class="inline-flex items-center gap-3 text-sm text-stone-600">
                    <input
                        id="remember_me"
                        type="checkbox"
                        name="remember"
                        class="h-4 w-4 rounded border-[#cfbf9c] text-[#b68c3a] focus:ring-[#c8a45d]/40"
                    >
                    <span>Remember me</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-semibold text-[#9b7431] transition hover:text-[#7f5e21]">
                        Forgot password?
                    </a>
                @endif
            </div>

            <button
                type="submit"
                id="loginBtn"
                class="inline-flex w-full transform-gpu items-center justify-center rounded-full border border-black bg-black px-5 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-white transition duration-150 ease-in-out hover:scale-[1.02] active:scale-[0.99]"
            >
                <span id="loginBtnLabel">Sign in</span>
            </button>
        </form>

        <p class="text-center text-sm text-stone-600">
            New here?
            <a href="{{ route('register') }}" class="ml-1 font-semibold text-[#9b7431] transition hover:text-[#7f5e21]">Register</a>
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

        document.getElementById('loginForm').addEventListener('submit', function () {
            const button = document.getElementById('loginBtn');
            document.getElementById('loginBtnLabel').textContent = 'Signing in';
            button.disabled = true;
            button.classList.add('cursor-not-allowed', 'opacity-70');
        });
    </script>
</x-guest-layout>
