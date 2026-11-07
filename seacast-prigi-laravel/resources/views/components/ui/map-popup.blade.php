@props([
    'title' => null,
    'badge' => null,
    'badgeColor' => 'cyan'
])

@php
    $badgeClasses = match($badgeColor) {
        'emerald' => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30',
        'amber' => 'bg-amber-500/10 text-amber-300 border-amber-500/30',
        'rose' => 'bg-rose-500/10 text-rose-300 border-rose-500/30',
        default => 'bg-cyan-500/10 text-cyan-300 border-cyan-500/30',
    };
@endphp

<!-- mapcn MapPopup Component -->
<div {{ $attributes->merge(['class' => 'min-w-[240px] max-w-xs bg-slate-950/95 backdrop-blur-md border border-slate-800/90 rounded-xl shadow-2xl overflow-hidden p-3 text-slate-200 select-none']) }}>
    
    @if ($title || $badge)
        <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2 mb-2">
            @if ($title)
                <h4 class="text-xs font-bold text-white tracking-wide uppercase">{{ $title }}</h4>
            @endif

            @if ($badge)
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono border {{ $badgeClasses }}">
                    {{ $badge }}
                </span>
            @endif
        </div>
    @endif

    <div class="space-y-1.5 text-xs">
        {{ $slot }}
    </div>

</div>

