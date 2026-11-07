@props([
    'position' => 'top-right',
    'showZoom' => true,
    'showCompass' => true,
    'showRecenter' => true,
    'showFullscreen' => true,
    'homeportLabel' => 'PPN Prigi'
])

@php
    $posClasses = match($position) {
        'top-left' => 'top-4 left-4',
        'bottom-right' => 'bottom-16 right-4',
        'bottom-left' => 'bottom-16 left-4',
        default => 'top-4 right-4',
    };
@endphp

<!-- mapcn MapControls Component -->
<div {{ $attributes->merge(['class' => "absolute {$posClasses} z-[1000] flex flex-col items-center bg-slate-900/95 backdrop-blur-md border border-slate-800/90 rounded-xl shadow-2xl p-1 gap-1 select-none pointer-events-auto"]) }}>
    
    @if ($showZoom)
        <!-- Zoom In -->
        <button 
            type="button" 
            @click="zoomIn()" 
            class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 active:scale-95 transition-all duration-150 relative group"
            title="Perbesar Peta (+)"
            aria-label="Zoom In">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="absolute right-full mr-2.5 px-2 py-1 bg-slate-950 text-slate-200 text-[11px] font-medium rounded border border-slate-800 shadow-md opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap">
                Perbesar
            </span>
        </button>

        <!-- Zoom Out -->
        <button 
            type="button" 
            @click="zoomOut()" 
            class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 active:scale-95 transition-all duration-150 relative group"
            title="Perkecil Peta (-)"
            aria-label="Zoom Out">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
            </svg>
            <span class="absolute right-full mr-2.5 px-2 py-1 bg-slate-950 text-slate-200 text-[11px] font-medium rounded border border-slate-800 shadow-md opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap">
                Perkecil
            </span>
        </button>

        <div class="w-5 h-px bg-slate-800 my-0.5"></div>
    @endif

    @if ($showCompass)
        <!-- Reset Compass / North -->
        <button 
            type="button" 
            @click="resetNorth()" 
            class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:text-cyan-400 hover:bg-slate-800 active:scale-95 transition-all duration-150 relative group"
            title="Arahkan ke Utara (Reset Bearing)"
            aria-label="Reset Compass">
            <svg class="w-4 h-4 transition-transform duration-200" :style="`transform: rotate(${mapBearing || 0}deg)`" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l4 9-4-2-4 2 4-9z" fill="currentColor" class="text-rose-500" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22l-4-9 4 2 4-2-4 9z" fill="currentColor" class="text-slate-400" />
            </svg>
            <span class="absolute right-full mr-2.5 px-2 py-1 bg-slate-950 text-slate-200 text-[11px] font-medium rounded border border-slate-800 shadow-md opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap">
                Reset Utara
            </span>
        </button>
    @endif

    @if ($showRecenter)
        <!-- Recenter to Homeport PPN Prigi -->
        <button 
            type="button" 
            @click="recenterHomeport()" 
            class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:text-cyan-400 hover:bg-slate-800 active:scale-95 transition-all duration-150 relative group"
            title="Pusatkan ke Homeport {{ $homeportLabel }}"
            aria-label="Recenter Homeport">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="absolute right-full mr-2.5 px-2 py-1 bg-slate-950 text-slate-200 text-[11px] font-medium rounded border border-slate-800 shadow-md opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap">
                Pusat {{ $homeportLabel }}
            </span>
        </button>
    @endif

    @if ($showFullscreen)
        <div class="w-5 h-px bg-slate-800 my-0.5"></div>

        <!-- Fullscreen Toggle -->
        <button 
            type="button" 
            @click="toggleFullscreen()" 
            class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 active:scale-95 transition-all duration-150 relative group"
            title="Layar Penuh"
            aria-label="Toggle Fullscreen">
            <template x-if="!isFullscreen">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
            </template>
            <template x-if="isFullscreen">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </template>
            <span class="absolute right-full mr-2.5 px-2 py-1 bg-slate-950 text-slate-200 text-[11px] font-medium rounded border border-slate-800 shadow-md opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap" x-text="isFullscreen ? 'Keluar Layar Penuh' : 'Layar Penuh'">
                Layar Penuh
            </span>
        </button>
    @endif

</div>

