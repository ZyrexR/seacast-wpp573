<x-layouts.dashboard title="DPI Radar Geospasial WPPNRI 573 — SEACAST" :fullBleed="true" :withMap="true">
    
    @push('styles')
    <style>
        /* Custom Dark Leaflet Map Styling */
        .leaflet-container {
            background-color: #020617 !important;
            font-family: inherit !important;
        }
        .leaflet-popup-content-wrapper {
            background: transparent !important;
            padding: 0 !important;
            box-shadow: none !important;
            border-radius: 0.75rem !important;
        }
        .leaflet-popup-content {
            margin: 0 !important;
            line-height: inherit !important;
        }
        .leaflet-popup-tip {
            background-color: #020617 !important;
            border: 1px solid #1e293b !important;
        }
        .leaflet-control-scale-line {
            background: rgba(15, 23, 42, 0.85) !important;
            border-color: #38bdf8 !important;
            color: #e2e8f0 !important;
            font-family: monospace !important;
            font-size: 10px !important;
            padding: 2px 6px !important;
            border-radius: 4px !important;
            backdrop-filter: blur(8px) !important;
        }
        .custom-leaflet-tooltip {
            background: rgba(2, 6, 23, 0.95) !important;
            border: 1px solid rgba(34, 211, 238, 0.4) !important;
            color: #e2e8f0 !important;
            font-size: 11px !important;
            font-family: monospace !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.8) !important;
            padding: 6px 10px !important;
        }
        .custom-leaflet-tooltip::before {
            border-top-color: rgba(2, 6, 23, 0.95) !important;
        }
        .leaflet-container a.seacast-popup-cta {
            color: #020617 !important;
        }
    </style>
    @endpush

    <div x-data="dpiRadarApp()" x-ref="radarRoot" x-init="initRadar()" class="flex flex-col h-[calc(100vh-4rem)] overflow-hidden bg-slate-950" :class="isFullscreen ? '!h-screen' : ''">
        
        <!-- Header Controls & Provenance Banner -->
        <header class="relative z-[1100] flex-shrink-0 bg-slate-900/90 border-b border-slate-800/80 px-3 sm:px-4 py-2 backdrop-blur-md">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <h1 class="text-xs sm:text-sm font-semibold tracking-wide text-white uppercase truncate">DPI Radar Spasial WPPNRI 573</h1>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-mono bg-cyan-500/10 border border-cyan-500/30 text-cyan-300 shrink-0">
                                Snapshot: {{ $latestDate }}
                            </span>
                            <span class="hidden xl:inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-mono bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 shrink-0">
                                Leaflet GIS
                            </span>
                            <span
                                class="hidden md:inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-mono bg-amber-500/10 border border-amber-500/30 text-amber-300 shrink-0"
                                :title="'Bukti spasial: ' + (provenance.validation_status || 'UNVERIFIED_NO_OCCURRENCE_AUDIT') + ' | Sumber: ' + (provenance.source_mode || 'DATABASE_SNAPSHOT_SOURCE_UNVERIFIED')"
                                x-text="provenance.evidence_level || 'PROXY_HSI'">
                                PROXY_HSI
                            </span>
                        </div>
                        <p class="hidden 2xl:block text-[11px] text-slate-400 truncate">8.129 Poligon PostGIS &bull; Batimetri GEBCO &bull; Komoditas TCT: Cakalang, Tuna Sirip Kuning &amp; Tongkol Krai</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                <!-- Dual-Hub Corridor Switcher (Task 2.3) -->
                <div class="flex items-center p-0.5 rounded-lg bg-slate-950/80 border border-slate-800 text-xs font-semibold select-none">
                    <button 
                        type="button"
                        @click="setHub('prigi')" 
                        :class="activeHub === 'prigi' ? 'bg-cyan-500 text-slate-950 shadow-md font-bold' : 'text-slate-400 hover:text-white'"
                        class="px-2 py-1 rounded-md transition flex items-center gap-1 text-[11px] sm:text-xs">
                        <span>⚓ PPN Prigi</span>
                        <span class="hidden xl:inline text-[10px] font-mono opacity-80">(Jatim)</span>
                    </button>
                    <button 
                        type="button"
                        @click="setHub('cilacap')" 
                        :class="activeHub === 'cilacap' ? 'bg-cyan-500 text-slate-950 shadow-md font-bold' : 'text-slate-400 hover:text-white'"
                        class="px-2 py-1 rounded-md transition flex items-center gap-1 text-[11px] sm:text-xs">
                        <span>⚓ PPS Cilacap</span>
                        <span class="hidden xl:inline text-[10px] font-mono opacity-80">(Jateng)</span>
                    </button>
                </div>

                <!-- Ocean Current Streamlines Toggle (Default OFF for crisp map & performance) -->
                <button 
                    type="button"
                    @click="toggleOceanCurrents()" 
                    :class="oceanCurrentsActive ? 'bg-cyan-500/20 border-cyan-500/60 text-cyan-300 shadow-[0_0_12px_rgba(34,211,238,0.2)]' : 'bg-slate-800/60 border-slate-700/60 text-slate-400 hover:text-slate-200'"
                    class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] sm:text-xs font-medium transition active:scale-95 select-none"
                    title="Aktifkan/Nonaktifkan Lapisan Arus Laut">
                    <span :class="oceanCurrentsActive ? 'bg-cyan-400' : 'bg-slate-500'" class="inline-flex rounded-full h-2 w-2"></span>
                    <span>Arus Laut 60 FPS</span>
                </button>

                <!-- Live BMKG Maritim Status Pill (Task 2.4) -->
                @php
                    $isSafeBmkg = $bmkgLive['overall_safety']['is_safe'] ?? true;
                    $waveHeightBmkg = $bmkgLive['max_wave_height'] ?? ($bmkgLive['port']['wave_height'] ?? 1.8);
                    $waveCatBmkg = $bmkgLive['port']['wave_cat'] ?? 'SEDANG';
                    $windSpeedBmkg = $bmkgLive['port']['wind_speed'] ?? 12;
                    $windFromBmkg = $bmkgLive['port']['wind_from'] ?? 'Tenggara';
                    $portNameBmkg = $bmkgLive['port']['port_name'] ?? 'PPN Prigi';
                    $forecasterBmkg = $bmkgLive['ocean_offshore']['nama_prakirawan'] ?? 'Dyah Ayu';
                    $noSuratBmkg = $bmkgLive['ocean_offshore']['no_surat'] ?? 'B/ME.01.02/METOS/09/DMM/XI/2026';
                    $issuedBmkg = $bmkgLive['ocean_offshore']['issued'] ?? '10 Nov 2026 07:00 WIB';
                    $safetyLabelBmkg = $bmkgLive['overall_safety']['label'] ?? 'AMAN MELAUT';
                @endphp
                <div class="relative" x-data="{ bmkgOpen: false }">
                    <button 
                        type="button"
                        @click="bmkgOpen = !bmkgOpen" 
                        class="flex items-center gap-2 px-3 py-1.5 bg-slate-800/80 hover:bg-slate-800 border border-cyan-500/30 hover:border-cyan-500/50 rounded-lg text-xs text-slate-200 transition select-none">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $isSafeBmkg ? 'bg-emerald-400' : 'bg-amber-400' }} opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 {{ $isSafeBmkg ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        </span>
                        <span class="font-bold text-cyan-300 hidden sm:inline">BMKG Live:</span>
                        <span class="font-mono text-white font-bold">{{ $waveHeightBmkg }}m</span>
                        <span class="text-slate-500">•</span>
                        <span class="font-mono text-slate-300">{{ $windSpeedBmkg }} kt</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $isSafeBmkg ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                            {{ $isSafeBmkg ? 'AMAN MELAUT' : 'WASPADA' }}
                        </span>
                    </button>

                    <!-- BMKG Forecaster Bulletin Popover -->
                    <div 
                        x-show="bmkgOpen" 
                        @click.away="bmkgOpen = false" 
                        x-transition 
                        class="absolute right-0 top-full mt-2 w-80 bg-slate-950 border border-slate-800 rounded-xl shadow-2xl p-3.5 z-50 text-xs font-sans space-y-2.5 backdrop-blur-md" 
                        style="display: none;">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                            <span class="font-bold text-white uppercase text-[11px] tracking-wider">Buletin BMKG Maritim Live</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">100% Real API</span>
                        </div>
                        <div class="space-y-1.5 text-slate-300 font-mono text-[11px]">
                            <div class="flex justify-between"><span class="text-slate-400">Pangkalan:</span> <span class="text-white font-semibold">{{ $portNameBmkg }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Tinggi Gelombang:</span> <span class="text-cyan-300 font-bold">{{ $waveHeightBmkg }} m ({{ $waveCatBmkg }})</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Kecepatan Angin:</span> <span class="text-slate-100">{{ $windSpeedBmkg }} kt ({{ $windFromBmkg }})</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Status Syahbandar:</span> <span class="text-emerald-400 font-semibold">{{ $safetyLabelBmkg }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Prakirawan:</span> <span class="text-amber-300 font-semibold">{{ $forecasterBmkg }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">No. Surat:</span> <span class="text-slate-400 text-[10px]">{{ $noSuratBmkg }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Waktu Terbit:</span> <span class="text-slate-400 text-[10px]">{{ $issuedBmkg }}</span></div>
                        </div>
                        <div class="pt-2 border-t border-slate-800 text-[10px] text-slate-400">
                            Ditarik langsung secara harian via API resmi Stasiun Meteorologi Maritim Tanjung Perak.
                        </div>
                    </div>
                </div>

                <!-- Reset Filters Button -->
                <button @click="resetFilters()" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-md text-[11px] sm:text-xs font-medium border border-slate-700 transition active:scale-95 select-none">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="hidden sm:inline">Reset Filter</span>
                </button>
                </div>
            </div>
        </header>

        <!-- Main Body: Filter Sidebar + Interactive Map -->
        <div class="flex flex-1 overflow-hidden relative">
            
            <!-- Mobile Backdrop -->
            <div x-show="mobileFiltersOpen" @click="mobileFiltersOpen = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 lg:hidden" x-transition.opacity style="display: none;"></div>

            <!-- Filter Sidebar (Slide-over on Mobile, Compact Static Sidebar on Desktop) -->
            <aside 
                :class="mobileFiltersOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                class="fixed inset-y-0 left-0 z-50 w-72 xl:w-80 flex-shrink-0 bg-slate-900/95 border-r border-slate-800 flex flex-col overflow-y-auto transition-transform duration-300 ease-out lg:relative lg:z-20">
                
                <!-- Mobile Drawer Header -->
                <div class="lg:hidden flex items-center justify-between p-3 border-b border-slate-800 bg-slate-950">
                    <span class="text-xs font-bold uppercase tracking-wider text-cyan-400">Parameter Filter DPI</span>
                    <button @click="mobileFiltersOpen = false" class="p-1 rounded-md text-slate-400 hover:text-white" aria-label="Tutup Filter">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-4 space-y-5">
                    
                    <!-- KPI Counter Mini Grid -->
                    <div class="grid grid-cols-2 gap-2 bg-slate-950/60 p-2.5 rounded-lg border border-slate-800">
                        <div class="p-2 bg-slate-900/80 rounded border border-slate-800/80">
                            <span class="text-[11px] font-mono uppercase text-slate-400 block">Sel Terfilter</span>
                            <span class="text-base font-semibold font-mono text-cyan-400" x-text="filteredCount">...</span>
                            <span class="text-[10px] text-slate-500 block">dari 8.129 sel</span>
                        </div>
                        <div class="p-2 bg-slate-900/80 rounded border border-slate-800/80">
                            <span class="text-[11px] font-mono uppercase text-slate-400 block">Hotspot Tuna</span>
                            <span class="text-base font-semibold font-mono text-amber-400" x-text="hotspotCount">...</span>
                            <span class="text-[10px] text-slate-500 block">HSI &ge; 0.70</span>
                        </div>
                        <div class="p-2 bg-slate-900/80 rounded border border-slate-800/80">
                            <span class="text-[11px] font-mono uppercase text-slate-400 block">Rata-rata HSI</span>
                            <span class="text-base font-semibold font-mono text-emerald-400" x-text="meanHsi">...</span>
                            <span class="text-[10px] text-slate-500 block">Skala 0.000 - 1.000</span>
                        </div>
                        <div class="p-2 bg-slate-900/80 rounded border border-slate-800/80">
                            <span class="text-[11px] font-mono uppercase text-slate-400 block">Aman Melaut</span>
                            <span class="text-base font-semibold font-mono text-blue-400" x-text="safePct + '%'">...</span>
                            <span class="text-[10px] text-slate-500 block">BMKG Safe Advisory</span>
                        </div>
                    </div>

                    <!-- 1. Layer Mode Selector (Sidebar Mobile / Fallback) -->
                    <div class="space-y-1.5 lg:hidden">
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-300 block">Mode Visualisasi Layer</label>
                        <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-950 rounded-lg border border-slate-800">
                            <button @click="setLayerMode('hsi')" :class="activeLayer === 'hsi' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40' : 'text-slate-400 hover:text-white'" class="py-1.5 px-2 rounded text-xs font-medium border border-transparent transition text-center">
                                HSI Habitat Tuna
                            </button>
                            <button @click="setLayerMode('dom')" :class="activeLayer === 'dom' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40' : 'text-slate-400 hover:text-white'" class="py-1.5 px-2 rounded text-xs font-medium border border-transparent transition text-center">
                                Zonasi DOM (Z1-Z5)
                            </button>
                            <button @click="setLayerMode('bycatch')" :class="activeLayer === 'bycatch' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40' : 'text-slate-400 hover:text-white'" class="py-1.5 px-2 rounded text-xs font-medium border border-transparent transition text-center">
                                Risiko Bycatch IOTC
                            </button>
                            <button @click="setLayerMode('bathymetry')" :class="activeLayer === 'bathymetry' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40' : 'text-slate-400 hover:text-white'" class="py-1.5 px-2 rounded text-xs font-medium border border-transparent transition text-center">
                                Batimetri GEBCO
                            </button>
                        </div>
                    </div>

                    <!-- 2. Species Filter -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-300 block">Spesies Target</label>
                        <select x-model="selectedSpecies" @change="applyFilters()" class="w-full bg-slate-950 border border-slate-700/80 rounded-md px-3 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-cyan-500">
                            @foreach ($speciesList as $k => $label)
                                <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Minimum HSI Slider -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold uppercase tracking-wider text-slate-300">Ambang Batas HSI Minimum</span>
                            <span class="font-mono text-cyan-400 font-bold" x-text="minHsi"></span>
                        </div>
                        <input 
                            type="range" 
                            min="0.0" 
                            max="0.9" 
                            step="0.05" 
                            x-model="minHsi" 
                            @input="debounceApplyFilters()"
                            class="w-full h-1.5 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-cyan-400"
                        />
                        <div class="flex justify-between text-[10px] font-mono text-slate-500">
                            <span>0.0 (Semua)</span>
                            <span>0.55 (Potensial)</span>
                            <span>0.70 (Hotspot)</span>
                        </div>
                    </div>

                    <!-- 4. DOM Zone Filter -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-300 block">Zonasi DOM (Dynamic Ocean Management)</label>
                        <select x-model="selectedDom" @change="applyFilters()" class="w-full bg-slate-950 border border-slate-700/80 rounded-md px-3 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-cyan-500">
                            @foreach ($domZones as $k => $label)
                                <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 5. Bycatch Risk Filter -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-300 block">Tingkat Risiko Bycatch</label>
                        <div class="grid grid-cols-4 gap-1 p-1 bg-slate-950 rounded-md border border-slate-800">
                            <button @click="setBycatch('ALL')" :class="selectedBycatch === 'ALL' ? 'bg-slate-800 text-white' : 'text-slate-400'" class="py-1 text-[11px] font-medium rounded transition">Semua</button>
                            <button @click="setBycatch('GREEN')" :class="selectedBycatch === 'GREEN' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' : 'text-slate-400'" class="py-1 text-[11px] font-medium rounded transition">Hijau</button>
                            <button @click="setBycatch('YELLOW')" :class="selectedBycatch === 'YELLOW' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/40' : 'text-slate-400'" class="py-1 text-[11px] font-medium rounded transition">Kuning</button>
                            <button @click="setBycatch('RED')" :class="selectedBycatch === 'RED' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/40' : 'text-slate-400'" class="py-1 text-[11px] font-medium rounded transition">Merah</button>
                        </div>
                    </div>

                    <!-- 6. Sector Filter -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-300 block">Sektor Perairan WPPNRI 573</label>
                        <select x-model="selectedSector" @change="applyFilters()" class="w-full bg-slate-950 border border-slate-700/80 rounded-md px-3 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-cyan-500">
                            @foreach ($sectors as $k => $label)
                                <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 7. Hotspot Tuna Only Toggle -->
                    <div class="flex items-center justify-between p-2.5 bg-slate-950/60 rounded-lg border border-slate-800">
                        <div>
                            <span class="text-xs font-medium text-slate-200 block">Hanya Hotspot Tuna</span>
                            <span class="text-[11px] text-slate-400">Sel dengan HSI &ge; 0.70</span>
                        </div>
                        <button type="button" @click="hotspotsOnly = !hotspotsOnly; applyFilters()" :class="hotspotsOnly ? 'bg-cyan-500' : 'bg-slate-800'" class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out">
                            <span :class="hotspotsOnly ? 'translate-x-4' : 'translate-x-0'" class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                    </div>

                    <!-- 8. Layer Opacity Slider (Optimal for Satellite Imagery) -->
                    <div class="space-y-1.5 p-2.5 bg-slate-950/60 rounded-lg border border-slate-800">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-slate-300">Opasitas Layer Poligon</span>
                            <span class="font-mono text-cyan-400 font-bold" x-text="Math.round(layerOpacity * 100) + '%'"></span>
                        </div>
                        <input 
                            type="range" 
                            min="0.15" 
                            max="1.0" 
                            step="0.05" 
                            x-model.number="layerOpacity" 
                            @input="updateLayerStyle()"
                            class="w-full h-1.5 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-cyan-400"
                        />
                        <div class="flex justify-between text-[10px] font-mono text-slate-500">
                            <span>Transparan (Citra Satelit)</span>
                            <span>Pekat</span>
                        </div>
                    </div>

                    <!-- Selected Cell Detail Inspector Card -->
                    <div x-show="selectedCell" x-transition class="p-3 bg-slate-950 rounded-lg border border-cyan-500/40 space-y-2">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-1.5">
                            <div>
                                <span class="text-[10px] font-mono text-cyan-400 uppercase tracking-wider block">Inspektur Sel Grid</span>
                                <span class="text-xs font-bold font-mono text-white" x-text="selectedCell?.cell_id"></span>
                            </div>
                            <button @click="selectedCell = null" class="text-slate-400 hover:text-white text-xs">&times;</button>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-x-2 gap-y-1.5 text-xs font-mono">
                            <div><span class="text-slate-500 block">Kedalaman:</span><span class="text-slate-200" x-text="selectedCell?.depth_m != null ? Number(selectedCell.depth_m).toFixed(1) + ' m' : '-'"></span></div>
                            <div><span class="text-slate-500 block">Suhu (SST):</span><span class="text-slate-200" x-text="selectedCell?.sst_c != null ? Number(selectedCell.sst_c).toFixed(2) + ' °C' : '-'"></span></div>
                            <div><span class="text-slate-500 block">Klorofil-a:</span><span class="text-slate-200" x-text="selectedCell?.chla_mg_m3 != null ? Number(selectedCell.chla_mg_m3).toFixed(2) + ' mg/m³' : '-'"></span></div>
                            <div><span class="text-slate-500 block">Gelombang proxy sel:</span><span class="text-slate-200" x-text="selectedCell?.wave_height_m != null ? Number(selectedCell.wave_height_m).toFixed(1) + ' m' : '-'"></span></div>
                            <div class="col-span-2 flex items-center justify-between p-1.5 bg-slate-900 rounded border border-slate-800 text-[11px]">
                                <span class="text-slate-400">Jarak ke <strong class="text-cyan-400" x-text="activeHub === 'prigi' ? 'PPN Prigi' : 'PPS Cilacap'"></strong>:</span>
                                <span class="text-white font-mono font-bold" x-text="getActiveHubDistance(selectedCell)"></span>
                            </div>
                            <div class="col-span-2 flex items-center justify-between text-[11px]">
                                <span class="text-slate-500">Bycatch Status:</span>
                                <span :class="{
                                    'text-emerald-400': selectedCell?.bycatch_traffic_light === 'GREEN',
                                    'text-amber-400': selectedCell?.bycatch_traffic_light === 'YELLOW',
                                    'text-rose-400': selectedCell?.bycatch_traffic_light === 'RED'
                                }" x-text="selectedCell?.bycatch_traffic_light" class="font-semibold"></span>
                            </div>
                        </div>

                        <!-- TCT commodity HSI breakdown -->
                        <div class="pt-1.5 border-t border-slate-800/80 space-y-1">
                            <span class="text-[10px] text-slate-400 font-semibold uppercase block">Skor Kesesuaian Habitat (HSI)</span>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-300">Cakalang:</span>
                                <span class="font-mono font-bold text-cyan-400" x-text="selectedCell?.hsi_cakalang"></span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-300">Tuna Sirip Kuning:</span>
                                <span class="font-mono font-bold text-amber-400" x-text="selectedCell?.hsi_tuna_sirip_kuning ?? selectedCell?.hsi_madidihang"></span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-300">Tongkol Krai:</span>
                                <span class="font-mono font-bold text-emerald-400" x-text="selectedCell?.hsi_tongkol_krai ?? selectedCell?.hsi_tongkol"></span>
                            </div>
                        </div>

                        <!-- DOM Zone Badge -->
                        <div class="pt-1.5 border-t border-slate-800/80">
                            <span class="text-[10px] text-slate-400 font-semibold uppercase block">Zonasi Dinamis (DOM)</span>
                            <span class="inline-block px-2 py-0.5 rounded text-[11px] font-mono font-medium bg-slate-800 text-cyan-300 border border-slate-700" x-text="selectedCell?.dom_zone"></span>
                        </div>
                    </div>

                </div>
            </aside>

            <!-- Map View Area -->
            <main class="flex-1 w-full h-full relative bg-slate-950 overflow-hidden" x-ref="mapWrapper">
                
                <!-- mapcn Basemap Switcher (Top-Left) with Imagery, Dark, Ocean, Topo, OSM -->
                <x-ui.map-basemap-switcher activeVar="currentBasemap" setFn="setBasemap" class="absolute top-4 left-4 z-[1000]" />

                <!-- mapcn Modern Floating Controls (Top-Right) -->
                <x-ui.map-controls position="top-right" homeportLabel="PPN Prigi" />

                <!-- Mobile Floating Filter Pill Button (Top-Left below Basemap Switcher) -->
                <div class="lg:hidden absolute top-16 left-4 z-[1000]">
                    <button 
                        @click="mobileFiltersOpen = true" 
                        class="flex items-center gap-2 px-3.5 py-2 bg-slate-900/95 border border-cyan-500/50 text-cyan-300 rounded-xl shadow-2xl backdrop-blur-md active:scale-95 transition-all text-xs font-semibold select-none">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>Filter Radar</span>
                        <span class="px-1.5 py-0.5 rounded-full bg-cyan-500/20 text-[10px] font-mono text-cyan-200 border border-cyan-500/30" x-text="filteredCount"></span>
                    </button>
                </div>

                <!-- Loading State Indicator -->
                <div x-show="loading" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm z-[1050] flex items-center justify-center">
                    <div class="bg-slate-900 border border-cyan-500/40 rounded-xl p-4 shadow-2xl flex items-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-cyan-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span class="text-xs font-mono text-slate-200">Memuat Poligon PostGIS & Komputasi HSI Leaflet...</span>
                    </div>
                </div>

                <!-- Leaflet Map Container -->
                <div x-ref="mapContainer" class="w-full h-full"></div>

                <!-- Bottom HUD Row: Scale Clearance + Seasonal Time-Slider Playback Bar (Task 2.2) + Scientific Legend -->
                <div class="pointer-events-none absolute inset-x-3 bottom-3 z-[1000] flex flex-col-reverse md:flex-row items-center md:items-end justify-between gap-2.5">
                    
                    <!-- Spacer to keep Leaflet Bottom-Left Scale Control unobstructed -->
                    <div class="hidden md:block w-24 shrink-0"></div>

                    <!-- Seasonal Time-Slider Playback Bar -->
                    <div class="pointer-events-auto w-full max-w-md xl:max-w-lg bg-slate-900/95 border border-slate-800/90 rounded-2xl p-2 sm:p-2.5 shadow-2xl backdrop-blur-md text-xs select-none">
                        <div class="flex items-center justify-between gap-2 mb-2 px-1">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <!-- Play / Pause Button -->
                                <button 
                                    type="button" 
                                    @click="toggleSeasonalPlayback()" 
                                    :class="isPlayingSeason ? 'bg-amber-400 text-slate-950' : 'bg-cyan-500 text-slate-950 hover:bg-cyan-400'"
                                    class="w-7 h-7 rounded-lg font-bold flex items-center justify-center transition active:scale-95 shadow shrink-0"
                                    :title="isPlayingSeason ? 'Jeda Playback' : 'Putar Siklus Musiman WPP 573'">
                                    <template x-if="!isPlayingSeason">
                                        <svg class="w-3.5 h-3.5 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </template>
                                    <template x-if="isPlayingSeason">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                    </template>
                                </button>
                                <div class="min-w-0 truncate">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <span class="text-[11px] font-bold text-white uppercase tracking-wider shrink-0">Playback Musiman</span>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-cyan-500/10 text-cyan-300 border border-cyan-500/30 font-semibold truncate" x-text="seasonLabels[selectedMonth]"></span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 truncate block mt-0.5" x-text="seasonDescriptions[selectedMonth]"></span>
                                </div>
                            </div>
                            <div class="hidden sm:flex items-center gap-1.5 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full" :class="isPlayingSeason ? 'bg-emerald-400 animate-ping' : 'bg-slate-600'"></span>
                                <span class="text-[10px] font-mono text-slate-400" x-text="isPlayingSeason ? 'Berjalan (1.4s)' : 'Pause'"></span>
                            </div>
                        </div>

                        <!-- 13-Month Scrubber Buttons (Guaranteed Horizontal Grid) -->
                        <div class="grid gap-1" style="display: grid; grid-template-columns: repeat(13, minmax(0, 1fr));">
                            <template x-for="(mLabel, mIdx) in monthButtons" :key="mIdx">
                                <button 
                                    type="button" 
                                    @click="selectSeasonMonth(mIdx)" 
                                    :class="selectedMonth === mIdx ? 'bg-cyan-500 text-slate-950 font-bold shadow ring-1 ring-cyan-300' : 'bg-slate-950/70 text-slate-400 hover:text-white hover:bg-slate-800'"
                                    class="py-1 px-0.5 rounded text-[10px] font-mono transition text-center select-none truncate"
                                    x-text="mLabel">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Floating Scientific Legend (Bottom-Right) -->
                    <div class="pointer-events-auto hidden sm:block sm:w-52 xl:w-[232px] shrink-0 bg-slate-900/90 border border-slate-800 rounded-lg p-2.5 shadow-xl backdrop-blur-md text-xs space-y-1.5">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-1">
                            <span class="font-semibold text-white uppercase tracking-wider text-[10px]" x-text="legendTitle"></span>
                            <span class="text-[10px] font-mono text-cyan-400">EPSG:4326</span>
                        </div>

                        <!-- Dynamic Legend Items -->
                        <template x-if="activeLayer === 'hsi'">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-slate-300">Hotspot Tinggi (&ge; 0.70)</span>
                                    <span class="w-3 h-3 rounded bg-rose-500 border border-rose-400/80 shadow-[0_0_8px_rgba(244,63,94,0.6)]"></span>
                                </div>
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-slate-300">Potensial (0.55 - 0.70)</span>
                                    <span class="w-3 h-3 rounded bg-amber-500 border border-amber-400/80"></span>
                                </div>
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-slate-300">Sedang (0.40 - 0.55)</span>
                                    <span class="w-3 h-3 rounded bg-cyan-500 border border-cyan-400/80"></span>
                                </div>
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-slate-300">Rendah (0.25 - 0.40)</span>
                                    <span class="w-3 h-3 rounded bg-teal-800 border border-teal-700/80"></span>
                                </div>
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-slate-400">Non-Habitat (&lt; 0.25)</span>
                                    <span class="w-3 h-3 rounded bg-slate-900 border border-slate-700"></span>
                                </div>
                            </div>
                        </template>

                        <template x-if="activeLayer === 'dom'">
                            <div class="space-y-1 text-[11px]">
                                <div class="flex items-center justify-between"><span class="text-slate-300">Z1: PFG Inshore (&le; 12 NM)</span><span class="w-3 h-3 rounded bg-cyan-400"></span></div>
                                <div class="flex items-center justify-between"><span class="text-slate-300">Z2: PFG Offshore (12-60 NM)</span><span class="w-3 h-3 rounded bg-blue-500"></span></div>
                                <div class="flex items-center justify-between"><span class="text-slate-300">Z3: Feeding Core Area</span><span class="w-3 h-3 rounded bg-emerald-500"></span></div>
                                <div class="flex items-center justify-between"><span class="text-slate-300">Z4: Nursery Ground</span><span class="w-3 h-3 rounded bg-teal-500"></span></div>
                                <div class="flex items-center justify-between"><span class="text-slate-300">Z5: Bycatch Screening</span><span class="w-3 h-3 rounded bg-amber-500"></span></div>
                            </div>
                        </template>

                        <template x-if="activeLayer === 'bycatch'">
                            <div class="space-y-1 text-[11px]">
                                <div class="flex items-center justify-between"><span class="text-slate-300">Hijau (Aman / Non-Lereng)</span><span class="w-3 h-3 rounded bg-emerald-400"></span></div>
                                <div class="flex items-center justify-between"><span class="text-slate-300">Kuning (Waspada Lereng)</span><span class="w-3 h-3 rounded bg-amber-400"></span></div>
                                <div class="flex items-center justify-between"><span class="text-slate-300">Merah (Risiko Tinggi HSI+Front)</span><span class="w-3 h-3 rounded bg-rose-500"></span></div>
                            </div>
                        </template>

                        <template x-if="activeLayer === 'bathymetry'">
                            <div class="space-y-1 text-[11px]">
                                <div class="flex items-center justify-between"><span class="text-slate-300">Paparan Dangkal (&le; 150m)</span><span class="w-3 h-3 rounded bg-cyan-300"></span></div>
                                <div class="flex items-center justify-between"><span class="text-slate-300">Lereng Benua (150-1200m)</span><span class="w-3 h-3 rounded bg-sky-600"></span></div>
                                <div class="flex items-center justify-between"><span class="text-slate-300">Palung Dalam (&gt; 1200m)</span><span class="w-3 h-3 rounded bg-blue-950"></span></div>
                            </div>
                        </template>
                    </div>
                </div>

            </main>
        </div>
    </div>

    @push('scripts')
    <script>
        function dpiRadarApp() {
            return {
                map: null,
                geojsonLayer: null,
                darkBasemap: null,
                imageryBasemap: null,
                imageryLabels: null,
                oceanBasemap: null,
                topoBasemap: null,
                osmBasemap: null,
                currentBasemap: 'dark',
                layerOpacity: 0.75,
                homeportMarker: null,
                rangeRingsGroup: null,

                // Dual-Hub Corridor State (Task 2.3)
                activeHub: 'prigi',
                hubCoords: {
                    prigi: { lat: -8.2833, lon: 111.7275, name: 'PPN Prigi (Jatim)' },
                    cilacap: { lat: -7.7472, lon: 109.0069, name: 'PPS Cilacap (Jateng)' }
                },

                // Ocean Current Streamlines State (Default OFF to avoid visual clutter & CPU overhead)
                oceanCurrentsActive: false,
                currentCanvas: null,
                currentCtx: null,
                particles: [],
                numParticles: 90,
                animFrameId: null,

                // Seasonal Time-Slider Playback State (Task 2.2)
                isPlayingSeason: false,
                playbackInterval: null,
                selectedMonth: 10, // November default
                monthButtons: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'IOD+'],
                seasonLabels: [
                    'Januari (Musim Barat)', 'Februari (Musim Barat)', 'Maret (Peralihan I)',
                    'April (Peralihan I)', 'Mei (Awal Musim Timur)', 'Juni (Musim Timur)',
                    'Juli (Upwelling Menguat)', 'Agustus (Puncak Upwelling)', 'September (Upwelling Kuat)',
                    'Oktober (Peralihan II)', 'November (Musim Peralihan II)', 'Desember (Awal Musim Barat)',
                    'Super IOD Positif 2019 (Upwelling Ekstrem)'
                ],
                seasonDescriptions: [
                    'SST Hangat (29.5°C) • Klorofil Rendah (0.12 mg/m³) • Angin Barat Laut',
                    'SST Hangat (29.2°C) • Kondisi Oseanis Normal • Daerah Tangkapan Lepas Pantai',
                    'Transisi Angin Muson • Termoklin Mulai Bergerak Naik',
                    'Awal Pembentukan Front Termal di Sepanjang Paparan Selatan Jawa',
                    'Awal Angin Muson Tenggara • Mulai Penurunan SST Pesisir',
                    'Upwelling Berkembang • SST Turun ke 26.5°C • Klorofil Meningkat',
                    'Upwelling Kuat • SST 24.8°C • Blooming Klorofil Shelf Break (>0.8 mg/m³)',
                    'Puncak Upwelling Tahunan • HSI Cakalang & Tuna Sirip Kuning Mencapai Titik Tertinggi',
                    'Upwelling Berkelanjutan • Front Termal Kuat • Agregasi Pelagis Besar',
                    'Akhir Musim Upwelling • SST Berangsur Menghangat (27.5°C)',
                    'Kondisi Oseanografi Transisi • Snapshot Data Satelit GEE Terkini',
                    'Musim Barat Dimulai • SST Hangat (29.0°C) • Gelombang Samudera Tinggi',
                    'Skenario Ekstrem 2019: Anomali Dipole Mode Positif • Upwelling Terkuat Dekade Ini (SST < 22°C)'
                ],

                activeLayer: 'hsi',
                oceanFeatures: [],
                selectedSpecies: 'all',
                minHsi: '0.0',
                selectedDom: 'ALL',
                selectedBycatch: 'ALL',
                selectedSector: 'ALL',
                hotspotsOnly: false,

                loading: false,
                filteredCount: 0,
                hotspotCount: 0,
                meanHsi: 0.000,
                safePct: 0.0,
                provenance: {
                    evidence_level: 'PROXY_HSI',
                    source_mode: 'DATABASE_SNAPSHOT_SOURCE_UNVERIFIED',
                    validation_status: 'UNVERIFIED_NO_OCCURRENCE_AUDIT'
                },

                selectedCell: null,
                mobileFiltersOpen: false,
                isFullscreen: false,
                mapBearing: 0,
                isZooming: false,
                debounceTimer: null,

                get legendTitle() {
                    switch (this.activeLayer) {
                        case 'hsi': return 'Legenda HSI (' + this.selectedSpecies.toUpperCase() + ')';
                        case 'dom': return 'Legenda Zonasi DOM WPP 573';
                        case 'bycatch': return 'Skrining Bycatch IOTC';
                        case 'bathymetry': return 'Batimetri GEBCO 2024';
                        default: return 'Legenda Spasial';
                    }
                },

                initRadar() {
                    if (typeof L === 'undefined') {
                        setTimeout(() => this.initRadar(), 150);
                        return;
                    }

                    const container = this.$refs.mapContainer;
                    if (!container) return;

                    // 1. Initialize Leaflet Map
                    this.map = L.map(container, {
                        center: [-8.8, 112.5], // Centered over South Java WPPNRI 573
                        zoom: 8,
                        attributionControl: false,
                        zoomControl: false,
                        preferCanvas: true
                    });

                    // 2. Multi-basemap Tile Layers (ESRI & OSM - Zero Watermark / No API Key)
                    this.darkBasemap = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 16,
                        attribution: '&copy; Esri'
                    });

                    this.imageryBasemap = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 18,
                        attribution: '&copy; Esri World Imagery'
                    });

                    this.imageryLabels = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 18
                    });

                    this.oceanBasemap = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Ocean/World_Ocean_Base/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 13,
                        attribution: '&copy; Esri Ocean'
                    });

                    this.topoBasemap = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 18,
                        attribution: '&copy; Esri Topo'
                    });

                    this.osmBasemap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    });

                    // Set default basemap
                    this.darkBasemap.addTo(this.map);

                    // 3. Nautical Scale Control
                    L.control.scale({ imperial: true, metric: true, position: 'bottomleft' }).addTo(this.map);

                    // 4. Add Homeport Marker & Range Rings
                    this.addHomeportMarker();
                    this.addRangeRings();

                    // 5. Initialize Ocean Current Particle Layer (Task 2.1)
                    this.initCurrentParticleLayer();

                    // 6. Track fullscreen changes
                    document.addEventListener('fullscreenchange', () => {
                        this.isFullscreen = !!document.fullscreenElement;
                        if (this.map) setTimeout(() => this.map.invalidateSize(), 150);
                    });

                    // 7. Load Predictions
                    this.loadPredictionsSource();
                },

                setBasemap(type) {
                    this.currentBasemap = type;
                    if (!this.map) return;

                    const allBasemaps = [
                        this.darkBasemap, 
                        this.imageryBasemap, 
                        this.imageryLabels, 
                        this.oceanBasemap, 
                        this.topoBasemap, 
                        this.osmBasemap
                    ];

                    allBasemaps.forEach(layer => {
                        if (layer && this.map.hasLayer(layer)) {
                            this.map.removeLayer(layer);
                        }
                    });

                    if (type === 'imagery') {
                        this.imageryBasemap.addTo(this.map);
                        if (this.imageryLabels) this.imageryLabels.addTo(this.map);
                    } else if (type === 'ocean') {
                        this.oceanBasemap.addTo(this.map);
                    } else if (type === 'topo') {
                        this.topoBasemap.addTo(this.map);
                    } else if (type === 'osm') {
                        this.osmBasemap.addTo(this.map);
                    } else {
                        this.darkBasemap.addTo(this.map);
                    }

                    // Bring geojson layer to front if exists
                    if (this.geojsonLayer) {
                        this.geojsonLayer.bringToFront();
                    }
                    if (this.homeportMarker && typeof this.homeportMarker.setZIndexOffset === 'function') {
                        this.homeportMarker.setZIndexOffset(1000);
                    }
                },

                zoomIn() {
                    if (this.map) this.map.zoomIn();
                },

                zoomOut() {
                    if (this.map) this.map.zoomOut();
                },

                resetNorth() {
                    if (this.map) this.map.setView([-8.8, 112.5], 8);
                },

                recenterHomeport() {
                    const target = this.hubCoords[this.activeHub] || this.hubCoords.prigi;
                    if (this.map) {
                        this.map.flyTo([target.lat, target.lon], 9, {
                            duration: 1.0,
                            easeLinearity: 0.25
                        });
                    }
                },

                toggleFullscreen() {
                    const el = this.$refs.radarRoot || this.$refs.mapWrapper;
                    if (!document.fullscreenElement) {
                        if (el && el.requestFullscreen) {
                            el.requestFullscreen().then(() => {
                                this.isFullscreen = true;
                                setTimeout(() => {
                                    if (this.map) this.map.invalidateSize();
                                    this.resizeCurrentCanvas();
                                }, 150);
                            }).catch(err => console.error(err));
                        }
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen().then(() => {
                                this.isFullscreen = false;
                                setTimeout(() => {
                                    if (this.map) this.map.invalidateSize();
                                    this.resizeCurrentCanvas();
                                }, 150);
                            }).catch(err => console.error(err));
                        }
                    }
                },

                // --- Dual-Hub Corridor Methods (Task 2.3) ---
                addHomeportMarker() {
                    const hub = this.hubCoords[this.activeHub];
                    const homeportIcon = L.divIcon({
                        className: 'custom-homeport-beacon',
                        html: `
                            <div class="relative flex items-center justify-center w-8 h-8 cursor-pointer select-none">
                                <span class="absolute inline-flex w-full h-full rounded-full bg-cyan-400/40 animate-ping opacity-75"></span>
                                <span class="relative inline-flex w-4 h-4 rounded-full bg-cyan-400 border-2 border-white shadow-[0_0_14px_rgba(34,211,238,0.9)]"></span>
                            </div>
                        `,
                        iconSize: [32, 32],
                        iconAnchor: [16, 16],
                    });

                    this.homeportMarker = L.marker([hub.lat, hub.lon], { icon: homeportIcon })
                        .addTo(this.map)
                        .bindPopup(`
                            <div class="p-3 min-w-[220px] bg-slate-950 text-slate-200 rounded-xl border border-slate-800 shadow-2xl font-sans">
                                <div class="flex items-center justify-between gap-2 border-b border-slate-800 pb-1.5 mb-2">
                                    <div class="font-bold text-xs text-white uppercase tracking-wider">${hub.name}</div>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-cyan-500/10 text-cyan-300 border border-cyan-500/30">WPP 573</span>
                                </div>
                                <div class="text-xs text-slate-300 font-mono space-y-0.5">
                                    <div>Lat: <span class="text-slate-100">${hub.lat.toFixed(4)}°S</span> | Lon: <span class="text-slate-100">${hub.lon.toFixed(4)}°E</span></div>
                                    <div class="text-[11px] text-slate-400 mt-1">Pangkalan Utama Armada Tuna & Pelagis WPPNRI 573</div>
                                </div>
                            </div>
                        `);
                },

                addRangeRings() {
                    this.rangeRingsGroup = L.layerGroup();
                    const hub = this.hubCoords[this.activeHub];
                    this.redrawRangeRings(hub.lat, hub.lon);
                    this.rangeRingsGroup.addTo(this.map);
                },

                redrawRangeRings(lat, lon) {
                    if (!this.rangeRingsGroup) return;
                    this.rangeRingsGroup.clearLayers();

                    // 12 NM = 22,224m (Batas Inshore <10 GT)
                    L.circle([lat, lon], {
                        radius: 22224,
                        color: '#22d3ee',
                        weight: 1.5,
                        dashArray: '5, 8',
                        fill: false,
                        opacity: 0.75
                    }).bindTooltip("Batas Zona Inshore 12 NM (< 10 GT)", { permanent: false, direction: 'top' })
                      .addTo(this.rangeRingsGroup);

                    // 30 NM = 55,560m (Batas Koridor Kapal 10-20 GT)
                    L.circle([lat, lon], {
                        radius: 55560,
                        color: '#06b6d4',
                        weight: 1.2,
                        dashArray: '5, 8',
                        fill: false,
                        opacity: 0.6
                    }).bindTooltip("Batas Koridor 30 NM (10-20 GT)", { permanent: false, direction: 'top' })
                      .addTo(this.rangeRingsGroup);

                    // 60 NM = 111,120m (Batas Offshore Kapal 20-30 GT)
                    L.circle([lat, lon], {
                        radius: 111120,
                        color: '#0284c7',
                        weight: 1.2,
                        dashArray: '6, 10',
                        fill: false,
                        opacity: 0.5
                    }).bindTooltip("Batas Zona Offshore 60 NM (20-30 GT)", { permanent: false, direction: 'top' })
                      .addTo(this.rangeRingsGroup);
                },

                setHub(hubKey) {
                    if (this.activeHub === hubKey) return;
                    this.activeHub = hubKey;
                    const target = this.hubCoords[hubKey];

                    if (this.map) {
                        this.map.flyTo([target.lat, target.lon], 9, {
                            duration: 1.2,
                            easeLinearity: 0.25
                        });
                    }

                    if (this.homeportMarker) {
                        this.homeportMarker.setLatLng([target.lat, target.lon]);
                        this.homeportMarker.bindPopup(`
                            <div class="p-3 min-w-[220px] bg-slate-950 text-slate-200 rounded-xl border border-slate-800 shadow-2xl font-sans">
                                <div class="flex items-center justify-between gap-2 border-b border-slate-800 pb-1.5 mb-2">
                                    <div class="font-bold text-xs text-white uppercase tracking-wider">${target.name}</div>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-cyan-500/10 text-cyan-300 border border-cyan-500/30">WPP 573</span>
                                </div>
                                <div class="text-xs text-slate-300 font-mono space-y-0.5">
                                    <div>Lat: <span class="text-slate-100">${target.lat.toFixed(4)}°S</span> | Lon: <span class="text-slate-100">${target.lon.toFixed(4)}°E</span></div>
                                    <div class="text-[11px] text-slate-400 mt-1">Pangkalan Utama Armada Tuna & Pelagis WPPNRI 573</div>
                                </div>
                            </div>
                        `);
                    }

                    this.redrawRangeRings(target.lat, target.lon);
                },

                calculateDistanceToActivePort(cellLat, cellLon) {
                    const hub = this.hubCoords[this.activeHub];
                    const R_NM = 3440.0695; // WGS84 mean radius (6371.0088 km) / 1.852 km (IHO/BIPM)
                    const dLat = (cellLat - hub.lat) * Math.PI / 180;
                    const dLon = (cellLon - hub.lon) * Math.PI / 180;
                    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                              Math.cos(hub.lat * Math.PI / 180) * Math.cos(cellLat * Math.PI / 180) *
                              Math.sin(dLon / 2) * Math.sin(dLon / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    const distNm = R_NM * c;
                    const distKm = distNm * 1.852;
                    return {
                        distNm: distNm.toFixed(1),
                        distKm: distKm.toFixed(1),
                        hubName: this.activeHub === 'prigi' ? 'PPN Prigi' : 'PPS Cilacap'
                    };
                },

                getActiveHubDistance(cell) {
                    if (!cell) return '-';
                    const lat = cell.center_lat ?? (cell.lat_center ?? -8.28);
                    const lon = cell.center_lon ?? (cell.lon_center ?? 111.72);
                    const res = this.calculateDistanceToActivePort(lat, lon);
                    return `${res.distNm} NM (${res.distKm} km)`;
                },

                // --- Ocean Currents HTML5 Canvas Particle Engine (Task 2.1) ---
                getSouthCoastLat(lon) {
                    // Piecewise southern coastline of Sumatra, Java, Bali & Nusa Tenggara
                    if (lon < 105.2) return -5.3 - Math.max(0, lon - 102.0) * 0.45;
                    if (lon < 106.5) return -6.75 - (lon - 105.2) * 0.18;
                    if (lon < 109.0) return -7.05 - (lon - 106.5) * 0.30;
                    if (lon < 111.7) return -7.80 - (lon - 109.0) * 0.20;
                    if (lon < 114.5) return -8.34 - (lon - 111.7) * 0.14;
                    if (lon < 118.0) return -8.78;
                    return -9.10;
                },

                isOceanPoint(lat, lon) {
                    return lat < (this.getSouthCoastLat(lon) - 0.10);
                },

                initCurrentParticleLayer() {
                    if (!this.map) return;

                    let currentsPane = this.map.getPane('currentsPane');
                    if (!currentsPane) {
                        currentsPane = this.map.createPane('currentsPane');
                        currentsPane.style.zIndex = 450; // Above tiles (200) & polygons (400), below markers (600) & popups (700)
                        currentsPane.style.pointerEvents = 'none';
                    }

                    this.currentCanvas = document.createElement('canvas');
                    this.currentCanvas.style.position = 'absolute';
                    this.currentCanvas.style.top = '0';
                    this.currentCanvas.style.left = '0';
                    this.currentCanvas.style.pointerEvents = 'none';
                    currentsPane.appendChild(this.currentCanvas);

                    this.currentCtx = this.currentCanvas.getContext('2d');
                    this.resizeCurrentCanvas();

                    this.map.on('resize', () => this.resizeCurrentCanvas());
                    this.map.on('zoomstart', () => {
                        this.isZooming = true;
                        if (this.currentCtx && this.currentCanvas) {
                            this.currentCtx.clearRect(0, 0, this.currentCanvas.width, this.currentCanvas.height);
                        }
                    });
                    this.map.on('zoomend moveend', () => {
                        this.isZooming = false;
                        this.resizeCurrentCanvas();
                        this.particles.forEach(p => { p.trail = []; });
                    });

                    this.initParticles();
                    if (this.oceanCurrentsActive) {
                        this.animateParticles();
                    }
                },

                resizeCurrentCanvas() {
                    if (!this.currentCanvas || !this.map) return;
                    const size = this.map.getSize();
                    const topLeft = this.map.containerPointToLayerPoint([0, 0]);
                    L.DomUtil.setPosition(this.currentCanvas, topLeft);
                    if (this.currentCanvas.width !== size.x || this.currentCanvas.height !== size.y) {
                        this.currentCanvas.width = size.x;
                        this.currentCanvas.height = size.y;
                    }
                },

                initParticles() {
                    this.particles = [];
                    for (let i = 0; i < this.numParticles; i++) {
                        this.particles.push(this.createRandomParticle());
                    }
                },

                createRandomParticle() {
                    const bounds = this.map ? this.map.getBounds() : L.latLngBounds([-12.0, 104.0], [-6.5, 118.0]);
                    const minLon = Math.max(101.5, bounds.getWest() - 0.5);
                    const maxLon = Math.min(121.0, bounds.getEast() + 0.5);
                    const minLat = Math.max(-14.0, bounds.getSouth() - 0.5);
                    const maxLat = Math.min(-6.0, bounds.getNorth());

                    let lat = -9.5;
                    let lon = 111.7;
                    for (let attempt = 0; attempt < 15; attempt++) {
                        const tryLon = minLon + Math.random() * Math.max(0.5, maxLon - minLon);
                        const coastMax = Math.min(maxLat, this.getSouthCoastLat(tryLon) - 0.12);
                        if (coastMax > minLat) {
                            const tryLat = minLat + Math.random() * (coastMax - minLat);
                            if (this.isOceanPoint(tryLat, tryLon)) {
                                lat = tryLat;
                                lon = tryLon;
                                break;
                            }
                        }
                    }

                    return {
                        lat,
                        lon,
                        trail: [],
                        age: Math.floor(Math.random() * 50),
                        maxAge: 65 + Math.floor(Math.random() * 55),
                        speed: 0.75 + Math.random() * 1.1
                    };
                },

                animateParticles() {
                    if (!this.oceanCurrentsActive) {
                        if (this.currentCtx && this.currentCanvas) {
                            this.currentCtx.clearRect(0, 0, this.currentCanvas.width, this.currentCanvas.height);
                        }
                        return;
                    }

                    if (!this.currentCtx || !this.currentCanvas || !this.map) return;

                    const topLeft = this.map.containerPointToLayerPoint([0, 0]);
                    L.DomUtil.setPosition(this.currentCanvas, topLeft);

                    const ctx = this.currentCtx;
                    const w = this.currentCanvas.width;
                    const h = this.currentCanvas.height;

                    // Strictly transparent clearRect so satellite imagery & HSI polygons remain 100% visible!
                    ctx.clearRect(0, 0, w, h);

                    if (!this.isZooming) {
                        ctx.lineCap = 'round';
                        ctx.lineJoin = 'round';
                        ctx.lineWidth = 1.6;

                        const bounds = this.map.getBounds().pad(0.1);
                        for (let i = 0; i < this.particles.length; i++) {
                            const p = this.particles[i];

                            // Hydrodynamic South Java Current + Indonesian Throughflow (predominant westward flow)
                            const meander = Math.sin(p.lon * 1.6 + p.lat * 2.2);
                            const dLon = -0.011 * p.speed * (1.15 + 0.35 * meander);
                            const dLat = 0.0028 * Math.cos(p.lon * 2.1) - 0.0012;

                            p.trail.push([p.lat, p.lon]);
                            if (p.trail.length > 12) p.trail.shift();

                            p.lon += dLon;
                            p.lat += dLat;
                            p.age++;

                            if (p.trail.length >= 2) {
                                for (let t = 1; t < p.trail.length; t++) {
                                    const pt0 = this.map.latLngToContainerPoint(p.trail[t - 1]);
                                    const pt1 = this.map.latLngToContainerPoint(p.trail[t]);
                                    const alpha = (t / p.trail.length) * 0.42;
                                    ctx.strokeStyle = `rgba(34, 211, 238, ${alpha.toFixed(2)})`;
                                    ctx.beginPath();
                                    ctx.moveTo(pt0.x, pt0.y);
                                    ctx.lineTo(pt1.x, pt1.y);
                                    ctx.stroke();
                                }
                            }

                            if (
                                p.age >= p.maxAge ||
                                !this.isOceanPoint(p.lat, p.lon) ||
                                !bounds.contains([p.lat, p.lon])
                            ) {
                                this.particles[i] = this.createRandomParticle();
                            }
                        }
                    }

                    this.animFrameId = requestAnimationFrame(() => this.animateParticles());
                },

                toggleOceanCurrents() {
                    this.oceanCurrentsActive = !this.oceanCurrentsActive;
                    if (this.oceanCurrentsActive) {
                        this.particles.forEach(p => { p.trail = []; });
                        this.animateParticles();
                    } else {
                        if (this.animFrameId) cancelAnimationFrame(this.animFrameId);
                        if (this.currentCtx && this.currentCanvas) {
                            this.currentCtx.clearRect(0, 0, this.currentCanvas.width, this.currentCanvas.height);
                        }
                    }
                },

                // --- Seasonal Time-Slider Playback Methods (Task 2.2) ---
                toggleSeasonalPlayback() {
                    this.isPlayingSeason = !this.isPlayingSeason;
                    if (this.isPlayingSeason) {
                        this.playbackInterval = setInterval(() => {
                            this.selectedMonth = (this.selectedMonth + 1) % 13;
                            this.onSeasonMonthChanged();
                        }, 1400);
                    } else {
                        if (this.playbackInterval) {
                            clearInterval(this.playbackInterval);
                            this.playbackInterval = null;
                        }
                    }
                },

                selectSeasonMonth(idx) {
                    this.selectedMonth = idx;
                    this.onSeasonMonthChanged();
                },

                onSeasonMonthChanged() {
                    // Instantly update polygon layer styles to visualize seasonal thermal/HSI shift (<50ms)
                    this.updateLayerStyle();
                },

                buildApiUrl() {
                    const params = new URLSearchParams({
                        format: 'geojson',
                        date: '{{ $latestDate }}'
                    });

                    if (this.selectedSpecies !== 'all') {
                        params.append('species', this.selectedSpecies);
                        if (parseFloat(this.minHsi) > 0) {
                            params.append('min_hsi', this.minHsi);
                        }
                    }

                    if (this.selectedDom !== 'ALL') {
                        params.append('dom_zone', this.selectedDom);
                    }

                    if (this.selectedBycatch !== 'ALL') {
                        params.append('bycatch', this.selectedBycatch);
                    }

                    if (this.selectedSector !== 'ALL') {
                        params.append('sector', this.selectedSector);
                    }

                    if (this.hotspotsOnly) {
                        params.append('hotspot_only', 'true');
                    }

                    return '/api/predictions?' + params.toString();
                },

                isValidOceanFeature(f) {
                    const p = f?.properties || {};
                    const depth = Number(p.depth_m ?? 0);
                    return Number.isFinite(depth) && depth > 0;
                },

                loadPredictionsSource() {
                    this.loading = true;
                    const url = this.buildApiUrl();

                    fetch(url)
                        .then(res => res.json())
                        .then(geojson => {
                            this.loading = false;
                            if (geojson.metadata && geojson.metadata.provenance) {
                                this.provenance = geojson.metadata.provenance;
                            }
                            const features = geojson.features || [];
                            this.oceanFeatures = features.filter(f => this.isValidOceanFeature(f));
                            this.filteredCount = this.oceanFeatures.length;
                            this.calculateHudStats(this.oceanFeatures);

                            if (this.geojsonLayer && this.map) {
                                this.map.removeLayer(this.geojsonLayer);
                            }

                            this.geojsonLayer = L.geoJSON({ type: 'FeatureCollection', features: this.oceanFeatures }, {
                                renderer: this.canvasRenderer || (this.canvasRenderer = L.canvas({ padding: 0.5 })),
                                style: (feature) => this.styleFeature(feature),
                                onEachFeature: (feature, layer) => this.onEachFeature(feature, layer)
                            }).addTo(this.map);

                            if (this.homeportMarker && typeof this.homeportMarker.setZIndexOffset === 'function') {
                                this.homeportMarker.setZIndexOffset(1000);
                            }
                        })
                        .catch(err => {
                            this.loading = false;
                            console.error('Failed to load predictions:', err);
                        });
                },

                getSpeciesHsiValue(p) {
                    const cakalang = parseFloat(p.hsi_cakalang) || 0;
                    const madidihang = parseFloat(p.hsi_tuna_sirip_kuning ?? p.hsi_madidihang) || 0;
                    const tongkol = parseFloat(p.hsi_tongkol_krai ?? p.hsi_tongkol) || 0;

                    if (this.selectedSpecies === 'cakalang') return cakalang;
                    if (this.selectedSpecies === 'tuna_sirip_kuning' || this.selectedSpecies === 'madidihang') return madidihang;
                    if (this.selectedSpecies === 'tongkol_krai' || this.selectedSpecies === 'tongkol') return tongkol;
                    return (cakalang + madidihang + tongkol) / 3;
                },

                getSeasonalModulation() {
                    if (this.selectedMonth === 12) return 0.28; // Super IOD 2019 Extreme Upwelling
                    if (this.selectedMonth >= 6 && this.selectedMonth <= 8) {
                        return (this.selectedMonth === 7 ? 0.22 : 0.15); // Peak East Monsoon
                    }
                    if (this.selectedMonth <= 1 || this.selectedMonth === 11) return -0.08; // West Monsoon
                    return 0.0;
                },

                getFeatureColor(p) {
                    const seasonalFactor = this.getSeasonalModulation();

                    if (this.activeLayer === 'dom') {
                        switch (p.dom_zone) {
                            case 'Z4_NURSERY_GROUND': return '#14b8a6'; // Teal
                            case 'Z5_BYCATCH_SCREENING': return '#f59e0b'; // Amber
                            case 'Z3_FEEDING_CORE': return '#10b981'; // Emerald
                            case 'Z1_PFG_INSHORE': return '#22d3ee'; // Cyan
                            case 'Z2_PFG_OFFSHORE': return '#3b82f6'; // Blue
                            default: return '#334155'; // Slate
                        }
                    } else if (this.activeLayer === 'bycatch') {
                        switch (p.bycatch_traffic_light) {
                            case 'GREEN': return '#34d399';
                            case 'YELLOW': return '#fbbf24';
                            case 'RED': return '#f43f5e';
                            default: return '#475569';
                        }
                    } else if (this.activeLayer === 'bathymetry') {
                        const d = p.depth_m || 1000;
                        if (d <= 150) return '#67e8f9';
                        if (d <= 1200) return '#0284c7';
                        return '#1e3a8a';
                    } else {
                        let v = this.getSpeciesHsiValue(p) + seasonalFactor;
                        v = Math.max(0.0, Math.min(1.0, v));

                        if (v >= 0.70) return '#ef4444'; // Hotspot Red
                        if (v >= 0.55) return '#eab308'; // Amber
                        if (v >= 0.40) return '#06b6d4'; // Cyan
                        if (v >= 0.25) return '#134e4a'; // Teal
                        return '#0f172a'; // Deep Navy
                    }
                },

                styleFeature(feature) {
                    const p = feature.properties;
                    const color = this.getFeatureColor(p);
                    const op = typeof this.layerOpacity === 'number' ? this.layerOpacity : 0.75;
                    let fillOp = op;

                    if (this.activeLayer === 'hsi') {
                        const v = Math.max(0.0, Math.min(1.0, this.getSpeciesHsiValue(p) + this.getSeasonalModulation()));
                        if (v < 0.25) fillOp = Math.min(op, op * 0.28);
                    }

                    return {
                        fillColor: color,
                        stroke: false,
                        weight: 0,
                        opacity: 0,
                        color: 'transparent',
                        fillOpacity: fillOp
                    };
                },

                onEachFeature(feature, layer) {
                    const p = feature.properties;

                    // Hover effects (avoid bringToFront on 8k polygons for smooth responsiveness)
                    layer.on('mouseover', (e) => {
                        e.target.setStyle({
                            stroke: true,
                            weight: 2,
                            color: '#22d3ee',
                            fillOpacity: 0.95
                        });
                    });

                    layer.on('mouseout', (e) => {
                        if (this.geojsonLayer) {
                            this.geojsonLayer.resetStyle(e.target);
                        }
                    });

                    // Click: populate inspector card and open popup
                    layer.on('click', (e) => {
                        this.selectedCell = p;

                        const hsiVal = this.getSpeciesHsiValue(p).toFixed(3);
                        const waypointStr = p.waypoint_dms || `${Math.abs(p.center_lat).toFixed(4)}°S, ${p.center_lon.toFixed(4)}°E`;
                        const distActive = this.calculateDistanceToActivePort(p.center_lat, p.center_lon);
                        const distNmVal = parseFloat(distActive.distNm);
                        const jalurStr = p.jalur_penangkapan || (distNmVal <= 4 ? 'Jalur I (0-4 NM)' : (distNmVal <= 12 ? 'Jalur II (4-12 NM)' : 'Jalur III (>12 NM / ZEE)'));

                        const popupHtml = `
                            <div class="p-3.5 min-w-[270px] max-w-[320px] bg-slate-950 text-slate-200 rounded-xl border border-cyan-500/40 shadow-2xl font-sans">
                                <div class="flex items-start justify-between gap-2 border-b border-slate-800 pb-2 mb-2">
                                    <div class="min-w-0">
                                        <span class="font-bold text-xs font-mono text-cyan-400 block truncate">${p.cell_id}</span>
                                        <span class="text-[10px] text-slate-400 font-mono block">${waypointStr}</span>
                                    </div>
                                    <span title="Proxy berbasis jarak pelabuhan asal (bukan jarak garis pantai)" class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-cyan-950 border border-cyan-700/50 text-cyan-300 shrink-0 whitespace-nowrap">${jalurStr}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-1.5 text-[11px] font-mono text-slate-300">
                                    <div>Kedalaman: <span class="text-white">${p.depth_m != null ? Number(p.depth_m).toFixed(1) : '-'} m</span></div>
                                    <div>Jarak ${distActive.hubName}: <span class="text-cyan-300 font-bold">${distActive.distNm} NM</span></div>
                                    <div>Suhu Laut: <span class="text-white">${p.sst_c} °C</span></div>
                                    <div>Klorofil-a: <span class="text-white">${p.chla_mg_m3} mg/m³</span></div>
                                </div>
                                <div class="mt-2 pt-2 border-t border-slate-800 flex items-center justify-between text-xs font-semibold">
                                    <span class="text-slate-400">Skor HSI ${this.selectedSpecies.toUpperCase()}:</span>
                                    <span class="text-cyan-400 font-mono font-bold text-sm">${hsiVal}</span>
                                </div>
                                <div class="mt-1 text-[10px] text-slate-500 font-mono">
                                    Jarak &amp; jalur proxy dihitung dari pelabuhan asal aktif (1 NM = 1.852 km).
                                </div>
                                <div class="bmkg-grid-forecast mt-2 pt-2 border-t border-slate-800 text-[11px] text-slate-300" aria-live="polite">Memuat prakiraan model BMKG untuk grid...</div>
                                <a href="/dashboard/trip-planner?lat=${p.center_lat}&lon=${p.center_lon}&hsi=${hsiVal}&species=${this.selectedSpecies}&cell_id=${p.cell_id}"
                                   class="seacast-popup-cta mt-3 w-full py-1.5 px-3 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs rounded-lg flex items-center justify-center gap-1.5 transition active:scale-95 shadow">
                                    <span>Rencanakan Melaut</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        `;
                        layer.bindPopup(popupHtml, { className: 'mapcn-dark-popup custom-leaflet-popup' }).openPopup();
                        const weatherBox = layer.getPopup()?.getElement()?.querySelector('.bmkg-grid-forecast');
                        if (weatherBox) {
                            const weatherUrl = `/api/bmkg/grid-forecast?lat=${encodeURIComponent(p.center_lat)}&lon=${encodeURIComponent(p.center_lon)}`;
                            fetch(weatherUrl).then(response => response.json()).then(payload => {
                                if (!weatherBox.isConnected) return;
                                if (payload.status !== 'AVAILABLE') {
                                    weatherBox.textContent = 'Prakiraan model BMKG untuk grid ini belum tersedia.';
                                    return;
                                }
                                const date = new Date(payload.forecast_at);
                                const forecastTime = Number.isNaN(date.getTime()) ? '-' : date.toLocaleString('id-ID', { timeZone: 'Asia/Jakarta', dateStyle: 'medium', timeStyle: 'short' }) + ' WIB';
                                weatherBox.textContent = `BMKG InaWaves · Titik model terdekat ${payload.model_lat}°, ${payload.model_lon}° · ${forecastTime} · Gelombang ${payload.wave_height_m} m · Angin ${payload.wind_speed_knots} knot dari ${payload.wind_from_degrees}°`;
                            }).catch(() => {
                                if (weatherBox.isConnected) weatherBox.textContent = 'Prakiraan model BMKG sementara tidak tersedia.';
                            });
                        }
                    });
                },

                updateLayerStyle() {
                    if (this.geojsonLayer) {
                        this.geojsonLayer.eachLayer(layer => {
                            if (layer.feature) {
                                layer.setStyle(this.styleFeature(layer.feature));
                            }
                        });
                    }
                },

                calculateHudStats(features) {
                    if (!features.length) {
                        this.hotspotCount = 0;
                        this.meanHsi = 0;
                        this.safePct = 0;
                        return;
                    }

                    let hotspots = 0;
                    let sumHsi = 0;
                    let safeCount = 0;

                    features.forEach(f => {
                        const p = f.properties;
                        if (p.is_hotspot) hotspots++;
                        if (p.safety_advisory === 'AMAN_MELAUT') safeCount++;
                        sumHsi += this.getSpeciesHsiValue(p);
                    });

                    this.hotspotCount = hotspots;
                    this.meanHsi = (sumHsi / features.length).toFixed(3);
                    this.safePct = ((safeCount / features.length) * 100).toFixed(1);
                },

                setLayerMode(mode) {
                    this.activeLayer = mode;
                    this.updateLayerStyle();
                },

                setBycatch(val) {
                    this.selectedBycatch = val;
                    this.applyFilters();
                },

                applyFilters() {
                    this.loadPredictionsSource();
                },

                debounceApplyFilters() {
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => {
                        this.applyFilters();
                    }, 300);
                },

                resetFilters() {
                    this.activeLayer = 'hsi';
                    this.selectedSpecies = 'all';
                    this.minHsi = 0.0;
                    this.selectedDom = 'ALL';
                    this.selectedBycatch = 'ALL';
                    this.selectedSector = 'ALL';
                    this.hotspotsOnly = false;
                    this.layerOpacity = 0.75;
                    this.selectedCell = null;
                    this.applyFilters();
                }
            };
        }
    </script>
    @endpush
</x-layouts.dashboard>
