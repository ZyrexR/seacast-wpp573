@props([
    'title' => null,
    'subtitle' => null,
    'action' => null,
    'footer' => null,
    'hoverable' => false,
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456] rounded-2xl shadow-sm overflow-hidden ' . ($hoverable ? 'hover:border-cyan-500/50 dark:hover:border-cyan-500/40 transition-all duration-200' : '')]) }}>
    @if($title || $subtitle || $action)
        <div class="px-5 py-4 border-b border-slate-100 dark:border-[#244456]/70 flex items-center justify-between gap-4">
            <div>
                @if($title)
                    <h4 class="font-bold text-sm text-slate-900 dark:text-slate-100 tracking-tight">{{ $title }}</h4>
                @endif
                @if($subtitle)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if($action)
                <div class="shrink-0">
                    {{ $action }}
                </div>
            @endif
        </div>
    @endif

    <div class="p-5">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="px-5 py-3 bg-slate-50 dark:bg-[#091F30] border-t border-slate-100 dark:border-[#244456]/70 text-xs text-slate-500 dark:text-slate-400">
            {{ $footer }}
        </div>
    @endif
</div>

