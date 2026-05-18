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

        <form method="POST" action="{{ route('password.email') }}" id="forgotForm" class="space-y-5">
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

            <button
                type="submit"
                id="resetBtn"
                class="inline-flex w-full transform-gpu items-center justify-center rounded-full border border-black bg-black px-5 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-white transition duration-150 ease-in-out hover:scale-[1.02] active:scale-[0.99]"
            >
                <span id="resetBtnLabel">Email reset link</span>
            </button>
        </form>

        <p class="text-center text-sm text-stone-600">
            Remembered your password?
            <a href="{{ route('login') }}" class="ml-1 font-semibold text-[#9b7431] transition hover:text-[#7f5e21]">Sign in</a>
        </p>
    </div>

    <script>
        document.getElementById('forgotForm').addEventListener('submit', function () {
            const button = document.getElementById('resetBtn');
            document.getElementById('resetBtnLabel').textContent = 'Sending';
            button.disabled = true;
            button.classList.add('cursor-not-allowed', 'opacity-70');
        });
    </script>
</x-guest-layout>
