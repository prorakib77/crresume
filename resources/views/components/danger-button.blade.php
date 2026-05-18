<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex w-full transform-gpu items-center justify-center rounded-full border border-[#9d3e32] bg-[#9d3e32] px-5 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-white transition duration-150 ease-in-out hover:scale-[1.02] active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-[#9d3e32]/50 focus:ring-offset-2 sm:w-auto']) }}>
    {{ $slot }}
</button>
