@props([
    'activeVar' => 'currentBasemap',
    'setFn' => 'setBasemap',
    'compact' => false
])

<!-- mapcn MapBasemapSwitcher Component -->
<div {{ $attributes->merge(['class' => 'inline-flex items-center p-1 bg-slate-900/95 backdrop-blur-md rounded-xl border border-slate-800 shadow-2xl gap-1 text-xs select-none pointer-events-auto z-[1000]']) }}>
    
    <!-- 1. Citra Satelit (ESRI World Imagery) -->
    <button 
        type="button" 
        @click="{{ $setFn }}('imagery')" 
        :class="{{ $activeVar }} === 'imagery' 
            ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40 shadow-[0_0_12px_rgba(6,182,212,0.25)]' 
            : 'text-slate-400 hover:text-white border-transparent hover:bg-slate-800/60'"
        class="flex items-center gap-1.5 px-2 py-1 rounded-lg font-medium border transition-all duration-150 active:scale-95 group relative"
        title="Citra Satelit Resolusi Tinggi (ESRI World Imagery)">
        <!-- Satellite SVG Icon -->
        <svg class="w-3.5 h-3.5" :class="{{ $activeVar }} === 'imagery' ? 'text-cyan-400' : 'text-slate-400 group-hover:text-slate-200'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span @class(['hidden' => $compact, 'hidden md:inline' => !$compact])>Citra Satelit</span>
        <span class="sr-only">Citra Satelit</span>
    </button>

    <!-- 2. Radar Gelap (ESRI Dark Gray Canvas) -->
    <button 
        type="button" 
        @click="{{ $setFn }}('dark')" 
        :class="{{ $activeVar }} === 'dark' 
            ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40 shadow-[0_0_12px_rgba(6,182,212,0.25)]' 
            : 'text-slate-400 hover:text-white border-transparent hover:bg-slate-800/60'"
        class="flex items-center gap-1.5 px-2 py-1 rounded-lg font-medium border transition-all duration-150 active:scale-95 group relative"
        title="Kanvas Gelap Kontras Tinggi (Optimal untuk Analisis SDM & Hotspot)">
        <!-- Moon / Dark Mode Icon -->
        <svg class="w-3.5 h-3.5" :class="{{ $activeVar }} === 'dark' ? 'text-cyan-400' : 'text-slate-400 group-hover:text-slate-200'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
        <span @class(['hidden' => $compact, 'hidden md:inline' => !$compact])>Radar Gelap</span>
        <span class="sr-only">Radar Gelap</span>
    </button>

    <!-- 3. Batimetri Samudera (ESRI World Ocean Base) -->
    <button 
        type="button" 
        @click="{{ $setFn }}('ocean')" 
        :class="{{ $activeVar }} === 'ocean' 
            ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40 shadow-[0_0_12px_rgba(6,182,212,0.25)]' 
            : 'text-slate-400 hover:text-white border-transparent hover:bg-slate-800/60'"
        class="flex items-center gap-1.5 px-2 py-1 rounded-lg font-medium border transition-all duration-150 active:scale-95 group relative"
        title="Batimetri Samudera & Topografi Dasar Laut (ESRI Ocean Base)">
        <!-- Waves / Marine Icon -->
        <svg class="w-3.5 h-3.5" :class="{{ $activeVar }} === 'ocean' ? 'text-cyan-400' : 'text-slate-400 group-hover:text-slate-200'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <span @class(['hidden' => $compact, 'hidden md:inline' => !$compact])>Samudera</span>
        <span class="sr-only">Samudera</span>
    </button>

    @if(!$compact)
    <!-- 4. Topografi (ESRI World Topo Map) -->
    <button 
        type="button" 
        @click="{{ $setFn }}('topo')" 
        :class="{{ $activeVar }} === 'topo' 
            ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40 shadow-[0_0_12px_rgba(6,182,212,0.25)]' 
            : 'text-slate-400 hover:text-white border-transparent hover:bg-slate-800/60'"
        class="flex items-center gap-1.5 px-2 py-1 rounded-lg font-medium border transition-all duration-150 active:scale-95 group relative"
        title="Topografi & Elevasi Garis Pantai (ESRI Topo Map)">
        <!-- Mountain / Terrain Icon -->
        <svg class="w-3.5 h-3.5" :class="{{ $activeVar }} === 'topo' ? 'text-cyan-400' : 'text-slate-400 group-hover:text-slate-200'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span class="hidden md:inline">Topografi</span>
        <span class="sr-only">Topografi</span>
    </button>

    <!-- 5. Navigasi OSM (OpenStreetMap) -->
    <button 
        type="button" 
        @click="{{ $setFn }}('osm')" 
        :class="{{ $activeVar }} === 'osm' 
            ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40 shadow-[0_0_12px_rgba(6,182,212,0.25)]' 
            : 'text-slate-400 hover:text-white border-transparent hover:bg-slate-800/60'"
        class="flex items-center gap-1.5 px-2 py-1 rounded-lg font-medium border transition-all duration-150 active:scale-95 group relative"
        title="Peta Navigasi Terbuka (OpenStreetMap Standard)">
        <!-- Map Grid Icon -->
        <svg class="w-3.5 h-3.5" :class="{{ $activeVar }} === 'osm' ? 'text-cyan-400' : 'text-slate-400 group-hover:text-slate-200'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
        </svg>
        <span class="hidden md:inline">Navigasi OSM</span>
        <span class="sr-only">Navigasi OSM</span>
    </button>
    @endif

</div>

