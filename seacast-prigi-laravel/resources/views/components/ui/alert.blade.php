@props([
    'variant' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
$variantConfig = [
    'info' => [
        'container' => 'bg-sky-50 dark:bg-sky-950/40 border-sky-200 dark:border-sky-800/60 text-sky-900 dark:text-sky-200',
        'iconColor' => 'text-sky-600 dark:text-sky-400',
        'iconSvg' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><line x1="12" y1="16" x2="12" y2="12" stroke-width="2"/><line x1="12" y1="8" x2="12.01" y2="8" stroke-width="2"/></svg>',
    ],
    'warning' => [
        'container' => 'bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800/60 text-amber-900 dark:text-amber-200',
        'iconColor' => 'text-amber-600 dark:text-amber-400',
        'iconSvg' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
    ],
    'danger' => [
        'container' => 'bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800/60 text-rose-900 dark:text-rose-200',
        'iconColor' => 'text-rose-600 dark:text-rose-400',
        'iconSvg' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><line x1="15" y1="9" x2="9" y2="15" stroke-width="2"/><line x1="9" y1="9" x2="15" y2="15" stroke-width="2"/></svg>',
    ],
    'success' => [
        'container' => 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800/60 text-emerald-900 dark:text-emerald-200',
        'iconColor' => 'text-emerald-600 dark:text-emerald-400',
        'iconSvg' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    ],
];

$config = $variantConfig[$variant] ?? $variantConfig['info'];
@endphp

<div 
    x-data="{ show: true }" 
    x-show="show" 
    role="alert" 
    {{ $attributes->merge(['class' => 'flex items-start gap-3.5 p-4 rounded-xl border transition-all ' . $config['container']]) }}
>
    <div class="shrink-0 mt-0.5 {{ $config['iconColor'] }}">
        {!! $icon ?? $config['iconSvg'] !!}
    </div>
    <div class="flex-1 space-y-1">
        @if($title)
            <h5 class="font-bold text-sm tracking-tight">{{ $title }}</h5>
        @endif
        <div class="text-xs leading-relaxed opacity-90">
            {{ $slot }}
        </div>
    </div>
    @if($dismissible)
        <button @click="show = false" type="button" class="shrink-0 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    @endif
</div>

