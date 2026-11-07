@props([
    'title',
    'value',
    'subtitle' => null,
    'delta' => null,
    'deltaType' => 'neutral', // positive, negative, neutral
    'icon' => null,
    'accent' => 'cyan', // cyan, emerald, amber, rose
])

@php
$accentBorder = [
    'cyan' => 'border-cyan-500/20 dark:border-cyan-500/30',
    'emerald' => 'border-emerald-500/20 dark:border-emerald-500/30',
    'amber' => 'border-amber-500/20 dark:border-amber-500/30',
    'rose' => 'border-rose-500/20 dark:border-rose-500/30',
][$accent] ?? 'border-slate-200 dark:border-[#244456]';

$accentIcon = [
    'cyan' => 'bg-cyan-50 dark:bg-cyan-950/50 text-cyan-600 dark:text-cyan-400',
    'emerald' => 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400',
    'amber' => 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400',
    'rose' => 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400',
][$accent] ?? 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300';
@endphp

<div {{ $attributes->merge(['class' => 'p-5 rounded-2xl bg-white dark:bg-[#0D2B3E] border ' . $accentBorder . ' shadow-sm relative overflow-hidden transition-all duration-200 hover:shadow-md']) }}>
    <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ $title }}</span>
        @if($icon)
            <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $accentIcon }}">
                {!! $icon !!}
            </div>
        @endif
    </div>

    <div class="flex items-baseline gap-2 mb-1">
        <span class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight font-mono">{{ $value }}</span>
        @if($delta)
            <span class="text-xs font-semibold {{ $deltaType === 'positive' ? 'text-emerald-500' : ($deltaType === 'negative' ? 'text-rose-500' : 'text-slate-400') }}">
                {{ $delta }}
            </span>
        @endif
    </div>

    @if($subtitle)
        <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-1">{{ $subtitle }}</p>
    @endif
</div>

