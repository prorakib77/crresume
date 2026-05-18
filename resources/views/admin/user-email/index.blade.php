<x-app-layout>
    <x-slot name="title">Custom Email</x-slot>
    <x-slot name="pageTitle">Custom Email Sender</x-slot>
    <x-slot name="pageSubtitle">Choose whether this message goes to everyone or one selected user, then send it from a dedicated admin workspace.</x-slot>

    @php
        $currentScope = old('recipient_scope', $recipientScope ?? 'individual');
        $selectedUserId = old('email_user_id', $selectedUser?->id);
        $selectedRole = $selectedUser?->role?->name ? \Illuminate\Support\Str::headline((string) $selectedUser->role->name) : null;
    @endphp

    <div class="space-y-6" x-data="{ recipientScope: @js($currentScope) }">
        <section class="rounded-[1.9rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#9b7431]">Communication</p>
                    <h2 class="theme-display mt-2 text-3xl text-stone-950">Dedicated Custom Email Workspace</h2>
                    <p class="mt-3 max-w-3xl text-sm text-stone-600">Use the same searchable selector style as the assignment page, or switch to all-users delivery when the message should go to every account.</p>
                </div>

                <div class="rounded-[1.4rem] border border-[#eadcc2] bg-[#fff9ef] px-4 py-3 text-sm text-stone-700 shadow-sm">
                    <div class="font-semibold text-stone-900">{{ $recipientCount }}</div>
                    <div class="text-xs uppercase tracking-[0.16em] text-stone-500">Available recipients</div>
                </div>
            </div>
        </section>

        <section class="rounded-[1.9rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <form method="POST" action="{{ route('admin.user-email.send') }}" class="space-y-6">
                @csrf

                <div class="grid gap-6 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.15fr)]">
                    <section class="space-y-5">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">Step 1</p>
                                <h3 class="theme-display mt-2 text-2xl text-stone-950">Choose Recipients</h3>
                                <p class="mt-2 text-sm text-stone-600">Switch between sending to all users or choosing one person with the assignment-style searchable selector.</p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="custom-email-scope-card" :class="{ 'is-active': recipientScope === 'all' }">
                                <input
                                    type="radio"
                                    name="recipient_scope"
                                    value="all"
                                    class="custom-email-scope-input"
                                    x-model="recipientScope"
                                >
                                <span class="custom-email-scope-icon">
                                    <i class="fas fa-users"></i>
                                </span>
                                <span class="custom-email-scope-copy">
                                    <span class="custom-email-scope-title">Send To All Users</span>
                                    <span class="custom-email-scope-note">Email every account that currently has a valid email address.</span>
                                </span>
                            </label>

                            <label class="custom-email-scope-card" :class="{ 'is-active': recipientScope === 'individual' }">
                                <input
                                    type="radio"
                                    name="recipient_scope"
                                    value="individual"
                                    class="custom-email-scope-input"
                                    x-model="recipientScope"
                                >
                                <span class="custom-email-scope-icon">
                                    <i class="fas fa-user"></i>
                                </span>
                                <span class="custom-email-scope-copy">
                                    <span class="custom-email-scope-title">Send To Individual User</span>
                                    <span class="custom-email-scope-note">Choose one admin, agent, or client with a searchable select field.</span>
                                </span>
                            </label>
                        </div>

                        @error('recipient_scope')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        <div x-show="recipientScope === 'all'" x-cloak class="rounded-[1.55rem] border border-[#eadcc2] bg-[#fff9ef] p-5 shadow-[0_14px_28px_rgba(17,17,17,0.04)]">
                            <div class="flex flex-wrap items-start gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-stone-950 text-lg text-white">
                                    <i class="fas fa-earth-americas"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="mb-1 text-lg font-semibold text-stone-950">All users will receive this email</h4>
                                    <p class="mb-1 text-sm text-stone-700">{{ $recipientCount }} user accounts with valid email addresses are currently eligible.</p>
                                    <p class="mb-0 text-xs uppercase tracking-[0.16em] text-stone-500">Admin, agent, and client accounts are included</p>
                                </div>
                            </div>
                        </div>

                        <div x-show="recipientScope === 'individual'" x-cloak class="space-y-3">
                            <div class="assignment-field-shell">
                                <x-searchable-select
                                    name="email_user_id"
                                    label="Select User"
                                    placeholder="Choose a user..."
                                    :options="$recipientOptions"
                                    :value="$selectedUserId"
                                    search-url="{{ route('admin.user-email.search-users') }}"
                                />
                                <p class="assignment-field-note">Open the dropdown to view users, or type a name or email to search exactly like the assignment page selectors.</p>
                            </div>

                            @if($selectedUser)
                                <div class="rounded-[1.55rem] border border-[#eadcc2] bg-[#fff9ef] p-5 shadow-[0_14px_28px_rgba(17,17,17,0.04)]">
                                    <div class="flex flex-wrap items-start gap-4">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-stone-950 text-lg font-semibold text-white">
                                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($selectedUser->name, 0, 1)) }}
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h4 class="mb-0 text-lg font-semibold text-stone-950">{{ $selectedUser->name }}</h4>
                                                @if($selectedRole)
                                                    <span class="rounded-full bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-stone-600">
                                                        {{ $selectedRole }}
                                                    </span>
                                                @endif
                                            </div>

                                            <p class="mt-2 mb-1 text-sm text-stone-700 break-all">{{ $selectedUser->email }}</p>
                                            <p class="mb-0 text-xs uppercase tracking-[0.16em] text-stone-500">Selected recipient preview</p>
                                        </div>

                                        <a href="{{ route('admin.users.edit', $selectedUser) }}" class="btn btn-border-black btn-sm">
                                            <i class="fas fa-user-pen me-2"></i>Edit User
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">Step 2</p>
                                <h3 class="theme-display mt-2 text-2xl text-stone-950">Compose Email</h3>
                                <p class="mt-2 text-sm text-stone-600">Write a clear subject and a polite message. The email will be delivered using the shared system template.</p>
                            </div>
                        </div>

                        <div>
                            <label for="email_subject" class="form-label">Subject</label>
                            <input
                                id="email_subject"
                                name="email_subject"
                                type="text"
                                value="{{ old('email_subject') }}"
                                class="form-control @error('email_subject') is-invalid @enderror"
                                maxlength="190"
                                placeholder="Write a clear email subject"
                            >
                            @error('email_subject')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="email_body" class="form-label">Message</label>
                            <textarea
                                id="email_body"
                                name="email_body"
                                rows="12"
                                class="form-control @error('email_body') is-invalid @enderror"
                                placeholder="Write your message here"
                            >{{ old('email_body') }}</textarea>
                            @error('email_body')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="rounded-[1.35rem] border border-[#ece2d0] bg-[#faf7f2] px-4 py-3 text-sm text-stone-600">
                            <i class="fas fa-circle-info me-2 text-[#9b7431]"></i>
                            <span x-show="recipientScope === 'all'" x-cloak>This email will be sent individually to every user with a valid email address, and each recipient will also receive the matching in-app notice.</span>
                            <span x-show="recipientScope === 'individual'" x-cloak>This email will be sent as a polite plain-text message using the shared email layout and the current admin sender identity.</span>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button type="submit" class="btn btn-black">
                                <i class="fas fa-paper-plane me-2"></i>Send Email
                            </button>
                            <a href="{{ route('admin.user-email.index') }}" class="btn btn-border-black">
                                <i class="fas fa-rotate-left me-2"></i>Reset Form
                            </a>
                        </div>
                    </section>
                </div>
            </form>
        </section>
    </div>

    @push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }

        .custom-email-scope-card {
            position: relative;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 1rem;
            align-items: flex-start;
            border: 1px solid rgba(15, 15, 15, 0.08);
            border-radius: 1.5rem;
            background: linear-gradient(180deg, #ffffff 0%, #fbfaf7 100%);
            box-shadow: 0 16px 36px rgba(17, 17, 17, 0.05);
            cursor: pointer;
            padding: 1.15rem;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .custom-email-scope-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 20px 42px rgba(17, 17, 17, 0.07);
        }

        .custom-email-scope-card.is-active {
            border-color: rgba(200, 164, 93, 0.58);
            background: linear-gradient(180deg, #fffdf7 0%, #fff7e9 100%);
            box-shadow: 0 0 0 4px rgba(200, 164, 93, 0.12), 0 20px 42px rgba(17, 17, 17, 0.07);
        }

        .custom-email-scope-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .custom-email-scope-icon {
            display: inline-flex;
            height: 3.2rem;
            width: 3.2rem;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            background: linear-gradient(180deg, #1b1b1b 0%, #0f0f0f 100%);
            color: #f1ca78;
            box-shadow: 0 14px 28px rgba(15, 15, 15, 0.16);
        }

        .custom-email-scope-title {
            display: block;
            color: #111111;
            font-size: 1rem;
            font-weight: 700;
        }

        .custom-email-scope-note {
            display: block;
            margin-top: 0.35rem;
            color: #675f54;
            font-size: 0.9rem;
            line-height: 1.65;
        }
    </style>
    @endpush
</x-app-layout>
