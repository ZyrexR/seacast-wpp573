@props([
    'mapId' => 'map',
])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 bg-white/90 dark:bg-[#0D2B3E]/90 backdrop-blur-md p-1.5 rounded-2xl border border-slate-200 dark:border-[#244456] shadow-lg select-none']) }}>
    <button 
        type="button" 
        @click="$dispatch('map-zoom-in')"
        title="Zoom In"
        class="p-2 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#12364A] transition-colors"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    </button>
    <button 
        type="button" 
        @click="$dispatch('map-zoom-out')"
        title="Zoom Out"
        class="p-2 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#12364A] transition-colors"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
    </button>
    <div class="h-4 w-px bg-slate-200 dark:bg-[#244456] mx-0.5"></div>
    <button 
        type="button" 
        @click="$dispatch('map-reset-view')"
        title="Reset ke PPN Prigi Homeport"
        class="p-2 rounded-xl text-cyan-600 dark:text-cyan-400 hover:bg-cyan-50 dark:hover:bg-cyan-950/50 transition-colors"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
    </button>
</div>

