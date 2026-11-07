<x-layouts.dashboard title="DPI Radar Geospasial WPPNRI 573 — SEACAST" :fullBleed="true">
    
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
    </style>
    @endpush

    <div x-data="dpiRadarApp()" x-init="initRadar()" class="flex flex-col h-[calc(100vh-4rem)] overflow-hidden">
        
        <!-- Header Controls & Provenance Banner -->
        <header class="flex-shrink-0 bg-slate-900/90 border-b border-slate-800/80 px-4 py-2.5 backdrop-blur-md">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-sm font-semibold tracking-wide text-white uppercase">DPI Radar Spasial WPPNRI 573</h1>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-mono bg-cyan-500/10 border border-cyan-500/30 text-cyan-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                                Snapshot: {{ $latestDate }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-mono bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">
                                Leaflet GIS
                            </span>
                        </div>
                        <p class="text-xs text-slate-400">8.129 Poligon PostGIS • Batimetri GEBCO • Ensemble SDM Cakalang, Madidihang & Albakor</p>
                    </div>
                </div>

                <!-- mapcn Quick Layer Switcher (Desktop) -->
                <div class="hidden lg:flex items-center">
                    <x-ui.map-layer-switcher activeVar="activeLayer" setFn="setLayerMode" />
                </div>

                <!-- Provenance Disclaimer & Reset Controls -->
                <div class="flex items-center gap-3">
                    <div class="hidden xl:flex items-center gap-2 px-3 py-1 bg-slate-800/60 border border-slate-700/60 rounded-md text-xs text-slate-300">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Gelombang/Angin: <strong class="text-slate-200">Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)</strong></span>
                    </div>

                    <!-- Reset Filters Button -->
                    <button @click="resetFilters()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-md text-xs font-medium border border-slate-700 transition active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset Filter
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Body: Filter Sidebar + Interactive Map -->
        <div class="flex flex-1 overflow-hidden relative">
            
            <!-- Mobile Backdrop -->
            <div x-show="mobileFiltersOpen" @click="mobileFiltersOpen = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 lg:hidden" x-transition.opacity style="display: none;"></div>

            <!-- Filter Sidebar (Slide-over on Mobile, Static Sidebar on Desktop) -->
            <aside 
                :class="mobileFiltersOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                class="fixed inset-y-0 left-0 z-50 w-80 md:w-88 xl:w-96 flex-shrink-0 bg-slate-900/95 border-r border-slate-800 flex flex-col overflow-y-auto transition-transform duration-300 ease-out lg:relative lg:z-20">
                
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
                            <div><span class="text-slate-500 block">Kedalaman:</span><span class="text-slate-200" x-text="selectedCell?.depth_m + ' m'"></span></div>
                            <div><span class="text-slate-500 block">Jarak Prigi:</span><span class="text-slate-200" x-text="selectedCell?.dist_port_nm + ' NM'"></span></div>
                            <div><span class="text-slate-500 block">Suhu (SST):</span><span class="text-slate-200" x-text="selectedCell?.sst_c + ' °C'"></span></div>
                            <div><span class="text-slate-500 block">Klorofil-a:</span><span class="text-slate-200" x-text="selectedCell?.chla_mg_m3 + ' mg/m³'"></span></div>
                            <div><span class="text-slate-500 block">Gelombang:</span><span class="text-slate-200" x-text="selectedCell?.wave_height_m + ' m'"></span></div>
                            <div><span class="text-slate-500 block">Bycatch:</span>
                                <span :class="{
                                    'text-emerald-400': selectedCell?.bycatch_traffic_light === 'GREEN',
                                    'text-amber-400': selectedCell?.bycatch_traffic_light === 'YELLOW',
                                    'text-rose-400': selectedCell?.bycatch_traffic_light === 'RED'
                                }" x-text="selectedCell?.bycatch_traffic_light"></span>
                            </div>
                        </div>

                        <!-- Species HSI breakdown -->
                        <div class="pt-1.5 border-t border-slate-800/80 space-y-1">
                            <span class="text-[10px] text-slate-400 font-semibold uppercase block">Skor Kesesuaian Habitat (HSI)</span>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-300">Cakalang:</span>
                                <span class="font-mono font-bold text-cyan-400" x-text="selectedCell?.hsi_cakalang"></span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-300">Madidihang:</span>
                                <span class="font-mono font-bold text-amber-400" x-text="selectedCell?.hsi_madidihang"></span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-300">Albakor:</span>
                                <span class="font-mono font-bold text-emerald-400" x-text="selectedCell?.hsi_albakor"></span>
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

                <!-- Mobile Floating Filter Pill Button (Bottom-Left) -->
                <div class="lg:hidden absolute bottom-5 left-4 z-[1000]">
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

                <!-- Floating Scientific Legend (Bottom-Right) -->
                <div class="absolute bottom-4 right-4 z-[1000] bg-slate-900/90 border border-slate-800 rounded-lg p-3 shadow-xl backdrop-blur-md max-w-xs text-xs space-y-2 pointer-events-auto">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-1">
                        <span class="font-semibold text-white uppercase tracking-wider text-[11px]" x-text="legendTitle"></span>
                        <span class="text-[10px] font-mono text-cyan-400">EPSG:4326</span>
                    </div>

                    <!-- Dynamic Legend Items -->
                    <template x-if="activeLayer === 'hsi'">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-300">Hotspot Sangat Tinggi (&ge; 0.70)</span>
                                <span class="w-3.5 h-3.5 rounded bg-rose-500 border border-rose-400/80 shadow-[0_0_8px_rgba(244,63,94,0.6)]"></span>
                            </div>
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-300">Potensial Tinggi (0.55 - 0.70)</span>
                                <span class="w-3.5 h-3.5 rounded bg-amber-500 border border-amber-400/80"></span>
                            </div>
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-300">Sedang (0.40 - 0.55)</span>
                                <span class="w-3.5 h-3.5 rounded bg-cyan-500 border border-cyan-400/80"></span>
                            </div>
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-300">Rendah / Transisi (0.25 - 0.40)</span>
                                <span class="w-3.5 h-3.5 rounded bg-teal-800 border border-teal-700/80"></span>
                            </div>
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-400">Non-Habitat (&lt; 0.25)</span>
                                <span class="w-3.5 h-3.5 rounded bg-slate-900 border border-slate-800"></span>
                            </div>
                        </div>
                    </template>

                    <template x-if="activeLayer === 'dom'">
                        <div class="space-y-1 text-[11px]">
                            <div class="flex items-center justify-between"><span class="text-slate-300">Z1: PFG Inshore (&le; 12 NM)</span><span class="w-3.5 h-3.5 rounded bg-cyan-400"></span></div>
                            <div class="flex items-center justify-between"><span class="text-slate-300">Z2: PFG Offshore (12-60 NM)</span><span class="w-3.5 h-3.5 rounded bg-blue-500"></span></div>
                            <div class="flex items-center justify-between"><span class="text-slate-300">Z3: Feeding Core Area</span><span class="w-3.5 h-3.5 rounded bg-emerald-500"></span></div>
                            <div class="flex items-center justify-between"><span class="text-slate-300">Z4: Nursery Ground</span><span class="w-3.5 h-3.5 rounded bg-teal-500"></span></div>
                            <div class="flex items-center justify-between"><span class="text-slate-300">Z5: Bycatch Screening</span><span class="w-3.5 h-3.5 rounded bg-amber-500"></span></div>
                        </div>
                    </template>

                    <template x-if="activeLayer === 'bycatch'">
                        <div class="space-y-1 text-[11px]">
                            <div class="flex items-center justify-between"><span class="text-slate-300">Hijau (Aman / Non-Lereng)</span><span class="w-3.5 h-3.5 rounded bg-emerald-400"></span></div>
                            <div class="flex items-center justify-between"><span class="text-slate-300">Kuning (Waspada Lereng)</span><span class="w-3.5 h-3.5 rounded bg-amber-400"></span></div>
                            <div class="flex items-center justify-between"><span class="text-slate-300">Merah (Risiko Tinggi HSI+Front)</span><span class="w-3.5 h-3.5 rounded bg-rose-500"></span></div>
                        </div>
                    </template>

                    <template x-if="activeLayer === 'bathymetry'">
                        <div class="space-y-1 text-[11px]">
                            <div class="flex items-center justify-between"><span class="text-slate-300">Paparan Dangkal (&le; 150m)</span><span class="w-3.5 h-3.5 rounded bg-cyan-300"></span></div>
                            <div class="flex items-center justify-between"><span class="text-slate-300">Lereng Benua (150-1200m)</span><span class="w-3.5 h-3.5 rounded bg-sky-600"></span></div>
                            <div class="flex items-center justify-between"><span class="text-slate-300">Palung Dalam (&gt; 1200m)</span><span class="w-3.5 h-3.5 rounded bg-blue-950"></span></div>
                        </div>
                    </template>
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

                activeLayer: 'hsi',
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

                selectedCell: null,
                mobileFiltersOpen: false,
                isFullscreen: false,
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

                    // 5. Track fullscreen changes
                    document.addEventListener('fullscreenchange', () => {
                        this.isFullscreen = !!document.fullscreenElement;
                        if (this.map) setTimeout(() => this.map.invalidateSize(), 150);
                    });

                    // 6. Load Predictions
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
                    if (this.map) {
                        this.map.setView([-8.2833, 111.7275], 9.5);
                    }
                },

                toggleFullscreen() {
                    const el = this.$refs.mapWrapper;
                    if (!document.fullscreenElement) {
                        if (el.requestFullscreen) {
                            el.requestFullscreen().then(() => {
                                this.isFullscreen = true;
                                setTimeout(() => this.map.invalidateSize(), 150);
                            }).catch(err => console.error(err));
                        }
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen().then(() => {
                                this.isFullscreen = false;
                                setTimeout(() => this.map.invalidateSize(), 150);
                            }).catch(err => console.error(err));
                        }
                    }
                },

                addHomeportMarker() {
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

                    this.homeportMarker = L.marker([-8.2833, 111.7275], { icon: homeportIcon })
                        .addTo(this.map)
                        .bindPopup(`
                            <div class="p-3 min-w-[220px] bg-slate-950 text-slate-200 rounded-xl border border-slate-800 shadow-2xl font-sans">
                                <div class="flex items-center justify-between gap-2 border-b border-slate-800 pb-1.5 mb-2">
                                    <div class="font-bold text-xs text-white uppercase tracking-wider">PPN PRIGI HOMEPORT</div>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-cyan-500/10 text-cyan-300 border border-cyan-500/30">WPP 573</span>
                                </div>
                                <div class="text-xs text-slate-300 font-mono space-y-0.5">
                                    <div>Lat: <span class="text-slate-100">-8.2833°S</span> | Lon: <span class="text-slate-100">111.7275°E</span></div>
                                    <div class="text-[11px] text-slate-400 mt-1">Pusat Pendaratan Armada Tuna & Operasi Radar WPPNRI 573</div>
                                </div>
                            </div>
                        `);
                },

                addRangeRings() {
                    this.rangeRingsGroup = L.layerGroup();

                    // 12 NM = 22,224m (Batas Inshore <10 GT)
                    L.circle([-8.2833, 111.7275], {
                        radius: 22224,
                        color: '#22d3ee',
                        weight: 1.5,
                        dashArray: '5, 8',
                        fill: false,
                        opacity: 0.7
                    }).bindTooltip("Batas Zona Inshore 12 NM (< 10 GT)", { permanent: false, direction: 'top' })
                      .addTo(this.rangeRingsGroup);

                    // 60 NM = 111,120m (Batas Offshore Kapal 10-30 GT)
                    L.circle([-8.2833, 111.7275], {
                        radius: 111120,
                        color: '#0284c7',
                        weight: 1.2,
                        dashArray: '6, 10',
                        fill: false,
                        opacity: 0.5
                    }).bindTooltip("Batas Zona Offshore 60 NM (10-30 GT)", { permanent: false, direction: 'top' })
                      .addTo(this.rangeRingsGroup);

                    // 100 NM = 185,200m
                    L.circle([-8.2833, 111.7275], {
                        radius: 185200,
                        color: '#3b82f6',
                        weight: 1.0,
                        dashArray: '8, 12',
                        fill: false,
                        opacity: 0.35
                    }).bindTooltip("Zona Samudera Lepas 100 NM", { permanent: false, direction: 'top' })
                      .addTo(this.rangeRingsGroup);

                    this.rangeRingsGroup.addTo(this.map);
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

                loadPredictionsSource() {
                    this.loading = true;
                    const url = this.buildApiUrl();

                    fetch(url)
                        .then(res => res.json())
                        .then(geojson => {
                            this.loading = false;
                            const features = geojson.features || [];
                            this.filteredCount = features.length;
                            this.calculateHudStats(features);

                            // Remove existing geojson layer
                            if (this.geojsonLayer && this.map) {
                                this.map.removeLayer(this.geojsonLayer);
                            }

                            // Add new Leaflet GeoJSON layer
                            this.geojsonLayer = L.geoJSON(geojson, {
                                style: (feature) => this.styleFeature(feature),
                                onEachFeature: (feature, layer) => this.onEachFeature(feature, layer)
                            }).addTo(this.map);

                            // Re-anchor homeport marker to stay on top
                            if (this.homeportMarker && typeof this.homeportMarker.setZIndexOffset === 'function') {
                                this.homeportMarker.setZIndexOffset(1000);
                            }
                        })
                        .catch(err => {
                            this.loading = false;
                            console.error('Failed to load predictions:', err);
                        });
                },

                getFeatureColor(p) {
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
                        // Default: Species HSI Gradient
                        const hsiProp = this.selectedSpecies === 'cakalang' ? p.hsi_cakalang :
                                       this.selectedSpecies === 'madidihang' ? p.hsi_madidihang :
                                       this.selectedSpecies === 'albakor' ? p.hsi_albakor :
                                       ((p.hsi_cakalang + p.hsi_madidihang + p.hsi_albakor) / 3);
                        const v = parseFloat(hsiProp) || 0;
                        if (v >= 0.70) return '#ef4444'; // Hotspot Red
                        if (v >= 0.55) return '#eab308'; // Amber
                        if (v >= 0.40) return '#06b6d4'; // Cyan
                        if (v >= 0.25) return '#134e4a'; // Teal
                        return '#0f172a'; // Deep Navy
                    }
                },

                styleFeature(feature) {
                    const isHotspot = feature.properties.is_hotspot;
                    const color = this.getFeatureColor(feature.properties);
                    const op = typeof this.layerOpacity === 'number' ? this.layerOpacity : 0.75;
                    return {
                        fillColor: color,
                        weight: isHotspot ? 1.5 : 0.6,
                        opacity: Math.min(1.0, op + 0.15),
                        color: isHotspot ? '#fde047' : '#1e293b',
                        fillOpacity: op
                    };
                },

                onEachFeature(feature, layer) {
                    const p = feature.properties;

                    // Hover effects
                    layer.on('mouseover', (e) => {
                        const l = e.target;
                        l.setStyle({
                            weight: 2.5,
                            color: '#22d3ee',
                            fillOpacity: 0.95
                        });
                        l.bringToFront();
                        if (this.homeportMarker && typeof this.homeportMarker.setZIndexOffset === 'function') {
                            this.homeportMarker.setZIndexOffset(1000);
                        }
                    });

                    layer.on('mouseout', (e) => {
                        if (this.geojsonLayer) {
                            this.geojsonLayer.resetStyle(e.target);
                        }
                    });

                    // Click: populate inspector card and open popup
                    layer.on('click', (e) => {
                        this.selectedCell = p;

                        const popupHtml = `
                            <div class="p-3 min-w-[220px] bg-slate-950 text-slate-200 rounded-xl border border-cyan-500/40 shadow-2xl font-sans">
                                <div class="flex items-center justify-between border-b border-slate-800 pb-1.5 mb-2">
                                    <span class="font-bold text-xs font-mono text-cyan-400">${p.cell_id}</span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-slate-800 text-slate-300">${p.dom_zone}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-1 text-[11px] font-mono text-slate-300">
                                    <div>Kedalaman: <span class="text-white">${p.depth_m} m</span></div>
                                    <div>Jarak: <span class="text-white">${p.dist_port_nm} NM</span></div>
                                    <div>Suhu: <span class="text-white">${p.sst_c} °C</span></div>
                                    <div>Klorofil: <span class="text-white">${p.chla_mg_m3}</span></div>
                                </div>
                                <div class="mt-2 pt-1.5 border-t border-slate-800 flex items-center justify-between text-xs font-semibold">
                                    <span class="text-slate-400">HSI ${this.selectedSpecies.toUpperCase()}:</span>
                                    <span class="text-cyan-400 font-mono font-bold">${(this.selectedSpecies === 'cakalang' ? p.hsi_cakalang : (this.selectedSpecies === 'madidihang' ? p.hsi_madidihang : p.hsi_albakor))}</span>
                                </div>
                            </div>
                        `;
                        layer.bindPopup(popupHtml, { className: 'mapcn-dark-popup custom-leaflet-popup' }).openPopup();
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

                        const hsiVal = this.selectedSpecies === 'cakalang' ? p.hsi_cakalang :
                                       this.selectedSpecies === 'madidihang' ? p.hsi_madidihang :
                                       this.selectedSpecies === 'albakor' ? p.hsi_albakor :
                                       ((p.hsi_cakalang + p.hsi_madidihang + p.hsi_albakor) / 3);
                        sumHsi += hsiVal;
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
