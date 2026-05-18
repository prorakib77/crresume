<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-[#9b7431]">{{ __('Protected Area') }}</p>
            <h1 class="theme-display mt-3 text-4xl text-stone-950 sm:text-5xl">{{ __('Confirm your password') }}</h1>
            <p class="mt-3 text-sm leading-6 text-stone-600">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="password" :value="__('Password')" />

                <x-text-input
                    id="password"
                    class="block mt-1 w-full"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="pt-2">
                <x-primary-button class="w-full justify-center">
                    {{ __('Confirm') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
