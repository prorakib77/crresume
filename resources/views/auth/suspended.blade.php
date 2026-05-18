<x-guest-layout>
    <div class="space-y-6 text-center">
        <div class="mx-auto inline-flex h-16 w-16 items-center justify-center rounded-full border border-rose-200 bg-rose-50 text-rose-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="9"></circle>
                <path d="M8.5 8.5 15.5 15.5"></path>
                <path d="M15.5 8.5 8.5 15.5"></path>
            </svg>
        </div>

        <div class="space-y-3">
            <p class="text-[11px] font-semibold uppercase tracking-[0.35em] text-rose-500">Access Restricted</p>
            <h1 class="text-3xl font-semibold tracking-tight text-stone-900">User Suspended</h1>
            <p class="text-sm leading-7 text-stone-600">
                {{ $user->name }} cannot access this account right now. Please contact an administrator for support.
            </p>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="pt-2">
            @csrf
            <button
                type="submit"
                class="inline-flex w-full items-center justify-center rounded-full border border-black bg-black px-5 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-white transition duration-150 ease-in-out hover:scale-[1.02] active:scale-[0.99]"
            >
                Sign out
            </button>
        </form>
    </div>
</x-guest-layout>
