@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-[#c8a45d] bg-[#fbf5e8] ps-3 pe-4 py-2 text-start text-base font-medium text-[#9b7431] focus:border-[#a67f34] focus:bg-[#f8eed9] focus:text-[#7f5e21] focus:outline-none transition duration-150 ease-in-out'
            : 'block w-full border-l-4 border-transparent ps-3 pe-4 py-2 text-start text-base font-medium text-stone-600 hover:border-[#dccfb8] hover:bg-[#fffcf7] hover:text-stone-800 focus:border-[#dccfb8] focus:bg-[#fffcf7] focus:text-stone-800 focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
