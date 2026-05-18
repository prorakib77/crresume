<x-app-layout>
    <x-slot name="title">Email Templates</x-slot>
    <x-slot name="pageTitle">Email Templates</x-slot>
    <x-slot name="pageSubtitle">Customize every system email from one place.</x-slot>

    <div class="space-y-6">
        <section class="rounded-[1.9rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#9b7431]">Mailer</p>
                    <h2 class="theme-display mt-2 text-3xl text-stone-950">System Email Templates</h2>
                    <p class="mt-3 max-w-3xl text-sm text-stone-600">Edit subject and body placeholders for all outgoing emails including OTP, support, payment, auth, contact, and meeting notifications.</p>
                </div>
            </div>
        </section>

        <section class="rounded-[1.9rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">Tester</p>
                    <h3 class="theme-display mt-2 text-2xl text-stone-950">One Click Template Tester</h3>
                    <p class="mt-2 text-sm text-stone-600">Send all email templates instantly to one selected user or a custom email address.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.email-templates.send-all-tests') }}" class="mt-5 grid gap-4 lg:grid-cols-4">
                @csrf

                <div class="lg:col-span-2">
                    <label for="recipient_user_id" class="form-label">Select User Email</label>
                    <select id="recipient_user_id" name="recipient_user_id" class="form-select @error('recipient_user_id') is-invalid @enderror">
                        <option value="">Choose from users</option>
                        @foreach($recipientSuggestions as $user)
                            <option value="{{ $user->id }}" @selected(old('recipient_user_id') == $user->id)>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('recipient_user_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="test_email" class="form-label">Or Enter Email</label>
                    <input
                        id="test_email"
                        name="test_email"
                        type="email"
                        class="form-control @error('test_email') is-invalid @enderror"
                        placeholder="example@mail.com"
                        value="{{ old('test_email') }}"
                    >
                    @error('test_email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="test_name" class="form-label">Recipient Name (Optional)</label>
                    <input
                        id="test_name"
                        name="test_name"
                        type="text"
                        class="form-control @error('test_name') is-invalid @enderror"
                        placeholder="Test Recipient"
                        value="{{ old('test_name') }}"
                    >
                    @error('test_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="lg:col-span-4">
                    <button type="submit" class="btn btn-black">
                        <i class="fas fa-paper-plane me-2"></i>Send All Test Emails
                    </button>
                </div>
            </form>
        </section>

        @if($templates->isEmpty())
            <section class="rounded-[1.9rem] border border-dashed border-[#d8c6a1] bg-[#fffaf1] p-10 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full border border-[#e6d2ad] bg-white text-[#9b7431]">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <h3 class="theme-display text-2xl text-stone-900">No templates found</h3>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-stone-600">Templates will appear here after default sync.</p>
            </section>
        @else
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($templates as $template)
                    <article class="rounded-[1.5rem] border border-[#e7dcc5] bg-white/95 p-5 shadow-[0_18px_40px_rgba(17,17,17,0.05)]">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#9b7431]">{{ str_replace('_', ' ', $template->template_key) }}</p>
                                <h3 class="theme-display mt-2 text-xl text-stone-950">{{ $template->template_name }}</h3>
                            </div>
                            <span class="rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] {{ $template->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-100 text-stone-500' }}">
                                {{ $template->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        @if($template->description)
                            <p class="mt-3 text-sm leading-6 text-stone-600">{{ $template->description }}</p>
                        @endif

                        <div class="mt-4 rounded-xl border border-[#efe5d2] bg-[#faf7f2] p-3">
                            <p class="mb-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-stone-500">Subject</p>
                            <p class="text-sm font-medium text-stone-800">{{ $template->subject_template }}</p>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('admin.email-templates.edit', $template) }}" class="btn btn-black btn-sm w-100">
                                <i class="fas fa-pen me-2"></i>Edit Template
                            </a>
                        </div>
                    </article>
                @endforeach
            </section>

            <section class="rounded-[1.6rem] border border-[#e7dcc5] bg-white/95 px-4 py-3 shadow-[0_16px_38px_rgba(17,17,17,0.05)]">
                {{ $templates->links() }}
            </section>
        @endif
    </div>
</x-app-layout>
