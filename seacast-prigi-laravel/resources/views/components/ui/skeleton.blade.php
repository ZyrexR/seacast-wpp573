@props([
    'variant' => 'text', // text, circle, rect, card
    'count' => 1,
])

@php
$variantClasses = [
    'text' => 'h-4 w-full rounded-md',
    'circle' => 'h-10 w-10 rounded-full',
    'rect' => 'h-24 w-full rounded-xl',
    'card' => 'h-40 w-full rounded-2xl',
];
$style = $variantClasses[$variant] ?? 'h-4 w-full rounded-md';
@endphp

<div class="space-y-2.5 animate-pulse">
    @for($i = 0; $i < $count; $i++)
        <div {{ $attributes->merge(['class' => 'bg-slate-200 dark:bg-[#12364A] ' . $style]) }}></div>
    @endfor
</div>

