@props([
    'activeVar' => 'activeLayer',
    'setFn' => 'setLayerMode'
])

<!-- mapcn MapLayerSwitcher Component -->
<div {{ $attributes->merge(['class' => 'inline-flex items-center p-1 bg-slate-950/80 backdrop-blur-md rounded-xl border border-slate-800/80 shadow-lg gap-1']) }}>
    
    <!-- HSI Layer -->
    <button 
        type="button" 
        @click="{{ $setFn }}('hsi')" 
        :class="{{ $activeVar }} === 'hsi' 
            ? 'bg-cyan-500/15 text-cyan-300 border-cyan-500/40 shadow-[0_0_12px_rgba(6,182,212,0.2)]' 
            : 'text-slate-400 hover:text-slate-200 border-transparent hover:bg-slate-900/60'" 
        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-all duration-150 active:scale-95">
        <span class="w-2 h-2 rounded-full bg-cyan-400" :class="{{ $activeVar }} === 'hsi' ? 'animate-pulse' : 'opacity-60'"></span>
        <span>HSI Tuna</span>
    </button>

    <!-- DOM Layer -->
    <button 
        type="button" 
        @click="{{ $setFn }}('dom')" 
        :class="{{ $activeVar }} === 'dom' 
            ? 'bg-cyan-500/15 text-cyan-300 border-cyan-500/40 shadow-[0_0_12px_rgba(6,182,212,0.2)]' 
            : 'text-slate-400 hover:text-slate-200 border-transparent hover:bg-slate-900/60'" 
        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-all duration-150 active:scale-95">
        <span class="w-2 h-2 rounded-full bg-teal-400" :class="{{ $activeVar }} === 'dom' ? 'animate-pulse' : 'opacity-60'"></span>
        <span>Zonasi DOM</span>
    </button>

    <!-- Bycatch Layer -->
    <button 
        type="button" 
        @click="{{ $setFn }}('bycatch')" 
        :class="{{ $activeVar }} === 'bycatch' 
            ? 'bg-cyan-500/15 text-cyan-300 border-cyan-500/40 shadow-[0_0_12px_rgba(6,182,212,0.2)]' 
            : 'text-slate-400 hover:text-slate-200 border-transparent hover:bg-slate-900/60'" 
        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-all duration-150 active:scale-95">
        <span class="w-2 h-2 rounded-full bg-amber-400" :class="{{ $activeVar }} === 'bycatch' ? 'animate-pulse' : 'opacity-60'"></span>
        <span>Bycatch IOTC</span>
    </button>

    <!-- Bathymetry Layer -->
    <button 
        type="button" 
        @click="{{ $setFn }}('bathymetry')" 
        :class="{{ $activeVar }} === 'bathymetry' 
            ? 'bg-cyan-500/15 text-cyan-300 border-cyan-500/40 shadow-[0_0_12px_rgba(6,182,212,0.2)]' 
            : 'text-slate-400 hover:text-slate-200 border-transparent hover:bg-slate-900/60'" 
        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-all duration-150 active:scale-95">
        <span class="w-2 h-2 rounded-full bg-blue-500" :class="{{ $activeVar }} === 'bathymetry' ? 'animate-pulse' : 'opacity-60'"></span>
        <span>Batimetri</span>
    </button>

</div>

