@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-2xl border border-[#dccfb8] bg-[#fffdfa] px-4 py-3 text-stone-900 shadow-sm transition focus:border-[#c8a45d] focus:ring focus:ring-[#c8a45d]/25 disabled:cursor-not-allowed disabled:opacity-60']) }}>
