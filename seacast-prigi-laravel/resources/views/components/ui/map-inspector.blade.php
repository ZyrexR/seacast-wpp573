@props([
    'cellVar' => 'selectedCell',
    'closeFn' => 'closeCellInspector'
])

<!-- mapcn MapInspector Component -->
<div 
    x-show="{{ $cellVar }} !== null" 
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-4 scale-95"
    {{ $attributes->merge(['class' => 'absolute bottom-6 right-4 z-30 w-84 max-w-[calc(100vw-2rem)] bg-slate-900/95 backdrop-blur-md rounded-2xl border border-slate-700/80 shadow-2xl p-4 text-slate-200 select-none pointer-events-auto']) }}
    style="display: none;"
>
    <!-- Header -->
    <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
            <div>
                <span class="text-[10px] font-mono text-cyan-400 uppercase tracking-widest block leading-none">Inspeksi Sel PostGIS</span>
                <h4 class="text-sm font-semibold text-white font-mono" x-text="{{ $cellVar }}?.cell_id || '-'"></h4>
            </div>
        </div>
        <button 
            type="button" 
            @click="{{ $closeFn }}()" 
            class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
            aria-label="Tutup Inspektor">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Spatial & Hydrographic Metadata -->
    <div class="grid grid-cols-2 gap-2 my-3 text-xs">
        <div class="p-2 rounded-lg bg-slate-950/60 border border-slate-800/60">
            <span class="text-[10px] text-slate-400 block">Koordinat WGS84</span>
            <span class="font-mono text-slate-200 font-medium" x-text="`${Number({{ $cellVar }}?.center_lat || 0).toFixed(4)}°, ${Number({{ $cellVar }}?.center_lon || 0).toFixed(4)}°`"></span>
        </div>
        <div class="p-2 rounded-lg bg-slate-950/60 border border-slate-800/60">
            <span class="text-[10px] text-slate-400 block">Kedalaman Laut</span>
            <span class="font-mono text-cyan-300 font-semibold" x-text="`${Math.abs(Math.round({{ $cellVar }}?.depth_m || 0))} m`"></span>
        </div>
        <div class="p-2 rounded-lg bg-slate-950/60 border border-slate-800/60">
            <span class="text-[10px] text-slate-400 block">Jarak ke PPN Prigi</span>
            <span class="font-mono text-slate-200" x-text="`${Number({{ $cellVar }}?.dist_port_nm || 0).toFixed(1)} NM (${Number({{ $cellVar }}?.dist_port_km || 0).toFixed(1)} km)`"></span>
        </div>
        <div class="p-2 rounded-lg bg-slate-950/60 border border-slate-800/60">
            <span class="text-[10px] text-slate-400 block">Sektor Spasial</span>
            <span class="font-mono text-amber-300 font-medium" x-text="{{ $cellVar }}?.sector || '-'"></span>
        </div>
    </div>

    <!-- Oceanographic Telemetry -->
    <div class="space-y-1.5 mb-3 text-xs">
        <span class="text-[10px] uppercase font-mono text-slate-400 tracking-wider">Kondisi Biofisik Aktual</span>
        <div class="grid grid-cols-3 gap-1.5 text-center">
            <div class="p-1.5 rounded-lg bg-slate-950/40 border border-slate-800/50">
                <span class="text-[10px] text-slate-400 block">SST</span>
                <span class="font-mono text-emerald-300 font-medium" x-text="`${Number({{ $cellVar }}?.sst_c || 0).toFixed(2)} °C`"></span>
            </div>
            <div class="p-1.5 rounded-lg bg-slate-950/40 border border-slate-800/50">
                <span class="text-[10px] text-slate-400 block">Klorofil-a</span>
                <span class="font-mono text-emerald-300 font-medium" x-text="`${Number({{ $cellVar }}?.chla_mg_m3 || 0).toFixed(3)} mg/m³`"></span>
            </div>
            <div class="p-1.5 rounded-lg bg-slate-950/40 border border-slate-800/50">
                <span class="text-[10px] text-slate-400 block">Gelombang</span>
                <span class="font-mono text-blue-300 font-medium" x-text="`${Number({{ $cellVar }}?.wave_height_m || 0).toFixed(1)} m`"></span>
            </div>
        </div>
    </div>

    <!-- Species HSI Suitability -->
    <div class="space-y-1.5 mb-3 text-xs">
        <span class="text-[10px] uppercase font-mono text-slate-400 tracking-wider">Indeks Kesesuaian Habitat (HSI)</span>
        
        <!-- Cakalang -->
        <div>
            <div class="flex justify-between text-[11px] mb-0.5">
                <span class="text-slate-300">Cakalang (SKJ)</span>
                <span class="font-mono font-medium text-cyan-300" x-text="Number({{ $cellVar }}?.hsi_cakalang || 0).toFixed(3)"></span>
            </div>
            <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-cyan-400 rounded-full transition-all duration-300" :style="`width: ${Math.min(100, ({{ $cellVar }}?.hsi_cakalang || 0) * 100)}%`"></div>
            </div>
        </div>

        <!-- Madidihang -->
        <div>
            <div class="flex justify-between text-[11px] mb-0.5">
                <span class="text-slate-300">Madidihang (YFT)</span>
                <span class="font-mono font-medium text-amber-300" x-text="Number({{ $cellVar }}?.hsi_madidihang || 0).toFixed(3)"></span>
            </div>
            <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-amber-400 rounded-full transition-all duration-300" :style="`width: ${Math.min(100, ({{ $cellVar }}?.hsi_madidihang || 0) * 100)}%`"></div>
            </div>
        </div>

        <!-- Albakor -->
        <div>
            <div class="flex justify-between text-[11px] mb-0.5">
                <span class="text-slate-300">Albakor (ALB)</span>
                <span class="font-mono font-medium text-indigo-300" x-text="Number({{ $cellVar }}?.hsi_albakor || 0).toFixed(3)"></span>
            </div>
            <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-400 rounded-full transition-all duration-300" :style="`width: ${Math.min(100, ({{ $cellVar }}?.hsi_albakor || 0) * 100)}%`"></div>
            </div>
        </div>
    </div>

    <!-- Management & Safety Status -->
    <div class="pt-2.5 border-t border-slate-800/80 flex items-center justify-between text-[11px]">
        <div>
            <span class="text-[10px] text-slate-500 block">Zonasi DOM</span>
            <span class="font-mono font-semibold" :class="{
                'text-emerald-400': {{ $cellVar }}?.dom_zone === 'Z1_PFG_INSHORE',
                'text-cyan-400': {{ $cellVar }}?.dom_zone === 'Z2_PFG_OFFSHORE',
                'text-indigo-400': {{ $cellVar }}?.dom_zone === 'Z3_FEEDING_CORE',
                'text-amber-400': {{ $cellVar }}?.dom_zone === 'Z4_NURSERY_GROUND',
                'text-red-400': {{ $cellVar }}?.dom_zone === 'Z5_CONSERVATION_HOTSPOT',
                'text-slate-400': !{{ $cellVar }}?.dom_zone || {{ $cellVar }}?.dom_zone === 'GENERAL_OCEANIC'
            }" x-text="{{ $cellVar }}?.dom_zone || 'GENERAL_OCEANIC'"></span>
        </div>
        <div class="text-right">
            <span class="text-[10px] text-slate-500 block">Bycatch Advisory</span>
            <span class="inline-flex items-center gap-1 font-mono font-semibold" :class="{
                'text-emerald-400': {{ $cellVar }}?.bycatch_traffic_light === 'GREEN',
                'text-amber-400': {{ $cellVar }}?.bycatch_traffic_light === 'YELLOW',
                'text-rose-400': {{ $cellVar }}?.bycatch_traffic_light === 'RED'
            }">
                <span class="w-1.5 h-1.5 rounded-full" :class="{
                    'bg-emerald-400': {{ $cellVar }}?.bycatch_traffic_light === 'GREEN',
                    'bg-amber-400': {{ $cellVar }}?.bycatch_traffic_light === 'YELLOW',
                    'bg-rose-400': {{ $cellVar }}?.bycatch_traffic_light === 'RED'
                }"></span>
                <span x-text="{{ $cellVar }}?.bycatch_traffic_light || 'GREEN'"></span>
            </span>
        </div>
    </div>
</div>

