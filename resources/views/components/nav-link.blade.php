@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-[#c8a45d] px-1 pt-1 text-sm font-medium leading-5 text-stone-950 focus:border-[#a67f34] focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-stone-500 hover:border-[#dccfb8] hover:text-stone-700 focus:border-[#dccfb8] focus:outline-none focus:text-stone-700 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
