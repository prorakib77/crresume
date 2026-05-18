<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex w-full transform-gpu items-center justify-center rounded-full border border-[#d8c6a1] bg-[#fffaf1] px-5 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-stone-800 shadow-sm transition duration-150 ease-in-out hover:scale-[1.02] active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-[#c8a45d]/50 focus:ring-offset-2 disabled:opacity-25 sm:w-auto']) }}>
    {{ $slot }}
</button>
