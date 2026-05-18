<x-app-layout>
    <x-slot name="title">My Profile</x-slot>
    <x-slot name="pageTitle">My Profile</x-slot>
    <x-slot name="pageSubtitle">Review your account details, security settings, and access preferences.</x-slot>

    @php
        $authUser = auth()->user();
        $initials = collect(preg_split('/\s+/', trim($authUser->name)))
            ->filter()
            ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
            ->take(2)
            ->implode('');

        if ($initials === '') {
            $initials = 'U';
        }

        $roleLabel = $authUser->role?->name ? \Illuminate\Support\Str::headline($authUser->role->name) : 'User';
        $joinedDate = optional($authUser->created_at)->format('F j, Y');
        $joinedYear = optional($authUser->created_at)->format('Y');
        $accountStatus = \Illuminate\Support\Str::headline((string) ($authUser->status ?? 'Active'));
        $isEmailVerified = filled($authUser->email_verified_at);
    @endphp

    <div class="space-y-5 sm:space-y-6">
        <section class="overflow-hidden rounded-[1.75rem] border border-[#d6c39d] bg-gradient-to-br from-black via-[#111111] to-[#1d1914] text-white shadow-[0_24px_70px_rgba(0,0,0,0.16)]">
            <div class="grid gap-5 p-4 sm:p-6 lg:grid-cols-[minmax(0,1fr)_minmax(300px,0.9fr)] lg:items-center lg:p-7">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <div class="inline-flex h-16 w-16 flex-none items-center justify-center rounded-[1.5rem] border border-white/15 bg-white/10 text-xl font-semibold uppercase tracking-[0.12em] text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.12)] sm:h-20 sm:w-20 sm:text-2xl">
                        {{ $initials }}
                    </div>

                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-[#ead8ae]">Account Center</p>
                            <h2 class="theme-display mt-2 text-2xl text-white sm:text-4xl">{{ $authUser->name }}</h2>
                            <p class="mt-1.5 text-sm text-white/70 sm:text-base">{{ $authUser->email }}</p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full border border-[#c8a45d]/35 bg-[#c8a45d]/16 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#f6e8c8] sm:px-4 sm:py-2">
                                {{ $roleLabel }}
                            </span>
                            <span class="inline-flex items-center rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.24em] sm:px-4 sm:py-2 {{ $isEmailVerified ? 'border-emerald-400/25 bg-emerald-400/12 text-emerald-200' : 'border-amber-300/25 bg-amber-300/12 text-amber-100' }}">
                                {{ $isEmailVerified ? 'Email Verified' : 'Verification Needed' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-3">
                    <div class="rounded-[1.3rem] border border-white/10 bg-white/6 p-3.5 sm:p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.26em] text-white/45">Joined</p>
                        <p class="mt-2 theme-display text-2xl text-white sm:text-3xl">{{ $joinedYear }}</p>
                        <p class="mt-1 text-xs text-white/65 sm:text-sm">{{ $joinedDate }}</p>
                    </div>

                    <div class="rounded-[1.3rem] border border-white/10 bg-white/6 p-3.5 sm:p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.26em] text-white/45">Status</p>
                        <p class="mt-2 text-base font-semibold text-white sm:text-lg">{{ $accountStatus }}</p>
                        <p class="mt-1 text-xs text-white/65 sm:text-sm">Secure and available.</p>
                    </div>

                    <div class="col-span-2 rounded-[1.3rem] border border-white/10 bg-white/6 p-3.5 sm:col-span-1 sm:p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.26em] text-white/45">Role</p>
                        <p class="mt-2 text-base font-semibold text-white sm:text-lg">{{ $roleLabel }}</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-5 lg:grid-cols-[minmax(0,1.15fr)_320px] xl:grid-cols-[minmax(0,1.2fr)_340px]">
            <div class="space-y-5">
                <section class="rounded-[1.6rem] border border-[#e9dfcd] bg-white p-4 shadow-[0_18px_40px_rgba(17,17,17,0.05)] sm:p-5 lg:p-6">
                    @include('profile.partials.update-profile-information-form')
                </section>

                <section class="rounded-[1.6rem] border border-[#e9dfcd] bg-white p-4 shadow-[0_18px_40px_rgba(17,17,17,0.05)] sm:p-5 lg:p-6">
                    @include('profile.partials.update-password-form')
                </section>
            </div>

            <aside class="grid gap-5 sm:grid-cols-2 lg:grid-cols-1">
                <section class="rounded-[1.6rem] border border-[#e9dfcd] bg-white p-5 shadow-[0_18px_40px_rgba(17,17,17,0.05)] sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#9b7431]">Overview</p>
                            <h3 class="theme-display mt-2 text-2xl font-bold text-stone-950 sm:text-3xl">Account details</h3>
                        </div>
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#fbf5e8] text-[#9b7431]">
                            <i class="fas fa-user-shield"></i>
                        </span>
                    </div>

                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex flex-col gap-1 rounded-[1.2rem] border border-[#efe7d7] bg-[#fffdfa] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <dt class="text-stone-500">Email status</dt>
                            <dd class="font-semibold {{ $isEmailVerified ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ $isEmailVerified ? 'Verified' : 'Pending' }}
                            </dd>
                        </div>
                        <div class="flex flex-col gap-1 rounded-[1.2rem] border border-[#efe7d7] bg-[#fffdfa] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <dt class="text-stone-500">Joined</dt>
                            <dd class="font-semibold text-stone-900">{{ $joinedDate }}</dd>
                        </div>
                        <div class="flex flex-col gap-1 rounded-[1.2rem] border border-[#efe7d7] bg-[#fffdfa] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <dt class="text-stone-500">Role</dt>
                            <dd class="font-semibold text-stone-900">{{ $roleLabel }}</dd>
                        </div>
                        <div class="flex flex-col gap-1 rounded-[1.2rem] border border-[#efe7d7] bg-[#fffdfa] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <dt class="text-stone-500">Account</dt>
                            <dd class="font-semibold text-stone-900">{{ $accountStatus }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-[1.6rem] border border-[#e9dfcd] bg-white p-5 shadow-[0_18px_40px_rgba(17,17,17,0.05)] sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#9b7431]">Shortcuts</p>
                    <h3 class="theme-display mt-2 text-2xl font-bold text-stone-950 sm:text-3xl">Quick actions</h3>

                    <div class="mt-5 space-y-2.5">
                        <a href="{{ route('dashboard') }}" class="flex items-center justify-between rounded-[1.25rem] border border-[#eadfca] bg-[#fffdfa] px-4 py-3.5 text-stone-900 transition-colors hover:border-[#c8a45d] hover:bg-[#fbf5e8]">
                            <span>
                                <span class="block text-sm font-semibold">Back to dashboard</span>
                                <span class="mt-1 block text-xs text-stone-500">Return to your main workspace.</span>
                            </span>
                            <i class="fas fa-arrow-right text-[#9b7431]"></i>
                        </a>

                        @if($authUser->isClient())
                            <a href="{{ route('client.work-updates.index') }}" class="flex items-center justify-between rounded-[1.25rem] border border-[#eadfca] bg-[#fffdfa] px-4 py-3.5 text-stone-900 transition-colors hover:border-[#c8a45d] hover:bg-[#fbf5e8]">
                                <span>
                                    <span class="block text-sm font-semibold">Work updates</span>
                                    <span class="mt-1 block text-xs text-stone-500">Review latest work updates.</span>
                                </span>
                                <i class="fas fa-briefcase text-[#9b7431]"></i>
                            </a>
                        @endif

                        @if($authUser->isAgent())
                            <a href="{{ route('agent.work-updates.create') }}" class="flex items-center justify-between rounded-[1.25rem] border border-[#eadfca] bg-[#fffdfa] px-4 py-3.5 text-stone-900 transition-colors hover:border-[#c8a45d] hover:bg-[#fbf5e8]">
                                <span>
                                    <span class="block text-sm font-semibold">Create work update</span>
                                    <span class="mt-1 block text-xs text-stone-500">Log progress for assigned client work.</span>
                                </span>
                                <i class="fas fa-clipboard-list text-[#9b7431]"></i>
                            </a>
                        @endif
                    </div>
                </section>

                @if($authUser->isAdmin() || $authUser->isSuperAdmin())
                    <section class="rounded-[1.6rem] border border-rose-200 bg-white p-5 shadow-[0_18px_40px_rgba(17,17,17,0.05)] sm:col-span-2 sm:p-6 lg:col-span-1">
                        @include('profile.partials.delete-user-form')
                    </section>
                @endif
            </aside>
        </div>
    </div>
</x-app-layout>
