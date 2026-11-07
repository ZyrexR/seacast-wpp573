@props([
    'color' => 'cyan',
    'pulse' => true,
    'label' => null,
    'sublabel' => null
])

@php
    $colorMap = [
        'cyan' => [
            'ping' => 'bg-cyan-400/40',
            'core' => 'bg-cyan-400',
            'shadow' => 'shadow-[0_0_14px_rgba(34,211,238,0.9)]',
            'text' => 'text-cyan-300',
            'border' => 'border-cyan-500/40'
        ],
        'emerald' => [
            'ping' => 'bg-emerald-400/40',
            'core' => 'bg-emerald-400',
            'shadow' => 'shadow-[0_0_14px_rgba(52,211,153,0.9)]',
            'text' => 'text-emerald-300',
            'border' => 'border-emerald-500/40'
        ],
        'amber' => [
            'ping' => 'bg-amber-400/40',
            'core' => 'bg-amber-400',
            'shadow' => 'shadow-[0_0_14px_rgba(251,191,36,0.9)]',
            'text' => 'text-amber-300',
            'border' => 'border-amber-500/40'
        ],
        'rose' => [
            'ping' => 'bg-rose-400/40',
            'core' => 'bg-rose-400',
            'shadow' => 'shadow-[0_0_14px_rgba(244,63,94,0.9)]',
            'text' => 'text-rose-300',
            'border' => 'border-rose-500/40'
        ],
    ];

    $c = $colorMap[$color] ?? $colorMap['cyan'];
@endphp

<!-- mapcn MapMarker Component -->
<div {{ $attributes->merge(['class' => 'group flex items-center gap-2 cursor-pointer select-none']) }}>
    <div class="relative flex items-center justify-center w-6 h-6">
        @if ($pulse)
            <span class="absolute inline-flex w-full h-full rounded-full {{ $c['ping'] }} animate-ping duration-1000 opacity-75"></span>
        @endif
        <span class="relative inline-flex w-3.5 h-3.5 rounded-full {{ $c['core'] }} border-2 border-white {{ $c['shadow'] }} transition-transform duration-150 group-hover:scale-125"></span>
    </div>

    @if ($label)
        <div class="flex flex-col px-2 py-0.5 rounded-md bg-slate-900/90 backdrop-blur-md border {{ $c['border'] }} shadow-lg">
            <span class="text-xs font-semibold {{ $c['text'] }} tracking-wide leading-tight">{{ $label }}</span>
            @if ($sublabel)
                <span class="text-[10px] text-slate-400 font-mono leading-none">{{ $sublabel }}</span>
            @endif
        </div>
    @endif
</div>

