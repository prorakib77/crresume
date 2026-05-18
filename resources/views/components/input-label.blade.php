@props(['value'])

<label {{ $attributes->merge(['class' => 'mb-2 block text-sm font-medium uppercase tracking-[0.16em] text-stone-600']) }}>
    {{ $value ?? $slot }}
</label>
