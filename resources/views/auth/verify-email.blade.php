<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-[#9b7431]">{{ __('Email Verification') }}</p>
            <h1 class="theme-display mt-3 text-4xl text-stone-950 sm:text-5xl">{{ __('Check your inbox') }}</h1>
            <p class="mt-3 text-sm leading-6 text-stone-600">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
                @csrf

                <div class="w-full">
                    <x-primary-button class="w-full justify-center">
                        {{ __('Resend Verification Email') }}
                    </x-primary-button>
                </div>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                @csrf

                <button
                    type="submit"
                    class="inline-flex w-full transform-gpu items-center justify-center rounded-full border border-[#d8c6a1] bg-[#fffaf1] px-5 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-stone-800 transition hover:scale-[1.02] active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-[#c8a45d]/50 focus:ring-offset-2"
                >
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
