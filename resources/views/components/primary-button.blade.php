<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex w-full transform-gpu items-center justify-center rounded-full border border-black bg-black px-5 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-white transition duration-150 ease-in-out hover:scale-[1.02] active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-[#c8a45d]/60 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto']) }}>
    {{ $slot }}
</button>
