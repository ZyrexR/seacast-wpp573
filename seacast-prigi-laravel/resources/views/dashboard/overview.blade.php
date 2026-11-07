<x-layouts.dashboard title="Executive Overview — SEACAST">
    <div class="space-y-6">
        <!-- Header & Live Timestamp -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#244456]">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2.5">
                    Ruang Kendali Eksekutif Oseanografi
                    <x-ui.badge variant="cyan" :dot="true" :pulse="true">Live PostGIS</x-ui.badge>
                </h1>
                <p class="text-xs text-slate-400 mt-1">
                    Telemetri harian terkalibrasi per tanggal <span class="font-mono font-bold text-cyan-400">{{ $latestDate }}</span> &bull; Pelabuhan Perikanan Nusantara (PPN) Prigi
                </p>
            </div>

            <div class="flex items-center gap-2">
                <x-ui.button href="{{ route('dashboard.system-status') }}" variant="secondary" size="sm">
                    <svg class="w-4 h-4 mr-1 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Status Mesin DB
                </x-ui.button>
            </div>
        </div>

        <!-- 4 Dynamic KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-ui.stat-card
                title="Hotspot Tangkap Hari Ini"
                value="{{ $hotspotsCount }} Sel"
                subtitle="Kriteria: HSI &ge; 0,70 (Cakalang / Madidihang)"
                delta="Terkini"
                deltaType="positive"
                accent="rose"
            />

            <x-ui.stat-card
                title="Tinggi Gelombang Signifikan"
                value="{{ $avgWave }} m"
                subtitle="Simulasi Oseanografi Musiman BMKG Terkalibrasi"
                delta="Waspada &le;10 GT"
                deltaType="neutral"
                accent="amber"
            />

            <x-ui.stat-card
                title="Kesesuaian Habitat Albakor"
                value="{{ $albakorHsi }}"
                subtitle="Skor Tertinggi di antara 3 Spesies Tuna"
                delta="Tertinggi"
                deltaType="positive"
                accent="cyan"
            />

            <x-ui.stat-card
                title="Proteksi Bycatch Megafauna"
                value="{{ $safeBycatchPct }}%"
                subtitle="Screening Lereng Batimetri Standar IOTC"
                delta="Zona Aman Hijau"
                deltaType="positive"
                accent="emerald"
            />
        </div>

        <!-- Geospatial Radar Map Preview & DOM Allocation Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left 8 Cols: Leaflet GIS Ocean Map Container -->
            <div class="lg:col-span-8 bg-[#091F30] border border-[#244456] rounded-2xl p-4 shadow-xl flex flex-col space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-ping"></span>
                        <h3 class="text-xs font-bold text-slate-100 uppercase tracking-wider">Canvas Geospasial Leaflet GIS (PPN Prigi)</h3>
                    </div>
                    <span class="text-floor font-mono text-slate-400">EPSG:4326 &bull; Multi-Basemap GIS</span>
                </div>

                <!-- Reusable Map Component with Alpine.js -->
                <div 
                    x-data="seacastMap({ center: [111.7275, -8.2833], zoom: 7.5 })" 
                    x-init="initMap()" 
                    class="w-full h-96 rounded-xl overflow-hidden relative bg-[#061524] border border-[#244456]/60"
                >
                    <div x-ref="mapContainer" class="w-full h-full"></div>

                    <!-- Basemap Switcher Controls -->
                    <div class="absolute top-3 left-3 z-[1000]">
                        <x-ui.map-basemap-switcher activeVar="currentBasemap" setFn="setBasemap" :compact="true" />
                    </div>

                    <!-- Loading State -->
                    <div x-show="!loaded && !error" class="absolute inset-0 bg-[#071B2B]/85 flex items-center justify-center text-xs font-mono text-cyan-400 z-[1050]">
                        Memuat Tile Peta Samudera...
                    </div>

                    <!-- Reset Controls -->
                    <div class="absolute top-3 right-3 z-[1000]">
                        <button @click="resetView()" title="Fokus PPN Prigi" class="p-2 rounded-xl bg-[#091F30]/90 border border-[#244456] text-cyan-400 hover:text-white shadow-lg text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-floor text-slate-400 pt-1">
                    <span>Radius Jelajah: &le; 12 NM (&lt;10 GT) &bull; &le; 30 NM (10-20 GT) &bull; &le; 60 NM (20-30 GT)</span>
                    <a href="{{ route('dashboard.dpi-radar') }}" class="text-cyan-400 hover:underline font-semibold">Buka Radar Penuh &rarr;</a>
                </div>
            </div>

            <!-- Right 4 Cols: DOM 5-Zone Breakdown -->
            <div class="lg:col-span-4 space-y-4">
                <x-ui.card title="Alokasi Zonasi DOM Dinamis" subtitle="Hierarki Konservasi vs Penangkapan">
                    <div class="space-y-3">
                        @foreach($domBreakdown as $b)
                            @php
                                $badgeVariant = match($b->dom_zone) {
                                    'Z1_PFG_INSHORE' => 'z1',
                                    'Z2_PFG_OFFSHORE' => 'z2',
                                    'Z3_FEEDING_CORE' => 'z3',
                                    'Z4_NURSERY_GROUND' => 'z4',
                                    'Z5_BYCATCH_RISK' => 'z5',
                                    default => 'gen',
                                };
                            @endphp
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#071B2B] border border-[#244456]/70 text-xs">
                                <div class="flex items-center gap-2">
                                    <x-ui.badge :variant="$badgeVariant">{{ str_replace('_', ' ', $b->dom_zone) }}</x-ui.badge>
                                </div>
                                <span class="font-mono font-bold text-slate-200">{{ number_format($b->cell_count) }} Sel</span>
                            </div>
                        @endforeach
                    </div>

                    <x-slot:footer>
                        Total Sel Terklasifikasi: {{ number_format($totalCells) }} Poligon WPP 573
                    </x-slot:footer>
                </x-ui.card>

                <!-- Disclaimer Box -->
                <x-ui.alert variant="info" title="Pelabelan Ilmiah Mandatori">
                    Tinggi gelombang dilabeli secara transparan sebagai <em>Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)</em>.
                </x-ui.alert>
            </div>
        </div>
    </div>
</x-layouts.dashboard>

