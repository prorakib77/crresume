<section class="space-y-5">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-rose-600">Danger Zone</p>
        <h2 class="theme-display mt-2 text-2xl text-stone-950 sm:text-3xl">Delete account</h2>
        <p class="mt-2 text-sm leading-6 text-stone-600 sm:leading-7">
            This permanently removes your account and every connected resource. Use it only if you are certain you no longer need this profile.
        </p>
    </div>

    <div class="rounded-[1.35rem] border border-rose-200 bg-rose-50 p-4 text-sm leading-6 text-rose-700 sm:p-5 sm:leading-7">
        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, make sure you have retained anything you still need.') }}
    </div>

    <button
        type="button"
        class="inline-flex transform-gpu items-center justify-center rounded-full border border-rose-700 bg-rose-700 px-5 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-white transition hover:scale-[1.02] active:scale-[0.99]"
        data-bs-toggle="modal"
        data-bs-target="#deleteAccountModal"
    >
        {{ __('Delete Account') }}
    </button>

    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteAccountModalLabel">{{ __('Delete Account') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-body space-y-5">
                        <div class="flex items-center gap-4 rounded-[1.3rem] border border-rose-200 bg-rose-50 px-4 py-4">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-rose-700 text-white">
                                <i class="fas fa-exclamation-triangle"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-rose-900">{{ __('This action cannot be undone.') }}</p>
                                <p class="mt-1 text-sm leading-7 text-rose-700">
                                    {{ __('Enter your password to confirm permanent deletion of your account and all related data.') }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <label for="delete_account_password" class="form-label">{{ __('Confirm Password') }}</label>
                            <input
                                id="delete_account_password"
                                name="password"
                                type="password"
                                class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            >
                            @error('password', 'userDeletion')
                                <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="inline-flex transform-gpu items-center justify-center rounded-full border border-[#d8c6a1] bg-[#fffaf1] px-5 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-stone-800 transition hover:scale-[1.02] active:scale-[0.99]"
                            data-bs-dismiss="modal"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="inline-flex transform-gpu items-center justify-center rounded-full border border-rose-700 bg-rose-700 px-5 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-white transition hover:scale-[1.02] active:scale-[0.99]"
                        >
                            {{ __('Delete Account') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@if ($errors->userDeletion->any())
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalElement = document.getElementById('deleteAccountModal');

            if (modalElement && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
                window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
            }
        });
    </script>
    @endpush
@endif
