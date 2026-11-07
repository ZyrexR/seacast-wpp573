@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'iconRight' => null,
])

@php
$baseClasses = "inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed select-none";

$variantClasses = [
    'primary' => 'bg-[#0D8195] hover:bg-[#0b6d7e] dark:bg-[#13B8C8] dark:hover:bg-[#10A1B0] text-white dark:text-slate-950 shadow-sm focus:ring-[#0D8195]',
    'secondary' => 'bg-slate-100 hover:bg-slate-200 dark:bg-[#12364A] dark:hover:bg-[#1a4a63] text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-[#244456] focus:ring-slate-400',
    'outline' => 'bg-transparent border border-slate-300 dark:border-[#244456] hover:bg-slate-50 dark:hover:bg-[#0D2B3E] text-slate-700 dark:text-slate-200 focus:ring-cyan-500',
    'ghost' => 'bg-transparent hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 focus:ring-slate-400',
    'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-sm focus:ring-rose-500',
];

$sizeClasses = [
    'sm' => 'text-xs px-3 py-1.5 gap-1.5',
    'md' => 'text-xs px-4 py-2 gap-2',
    'lg' => 'text-sm px-5 py-2.5 gap-2.5',
];

$classes = "{$baseClasses} " . ($variantClasses[$variant] ?? $variantClasses['primary']) . " " . ($sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon) <span class="shrink-0">{!! $icon !!}</span> @endif
        <span>{{ $slot }}</span>
        @if($iconRight) <span class="shrink-0">{!! $iconRight !!}</span> @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon) <span class="shrink-0">{!! $icon !!}</span> @endif
        <span>{{ $slot }}</span>
        @if($iconRight) <span class="shrink-0">{!! $iconRight !!}</span> @endif
    </button>
@endif

