<x-layouts.public title="SEACAST — Scientific Ocean Intelligence & Fisheries Decision Support">
    <!-- Hero Section with Live Telemetry & Editorial Composition -->
    <section class="relative overflow-hidden bg-gradient-to-b from-[#071B2B] via-[#091F30] to-[#0D2B3E] text-white pt-16 pb-20 border-b border-[#244456]">
        <!-- Background Ambient Glow & Grid -->
        <div class="absolute inset-0 opacity-15 pointer-events-none" style="background-image: radial-gradient(#13B8C8 1px, transparent 1px); background-size: 32px 32px;"></div>
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-cyan-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <!-- Left Editorial Column -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#12364A] border border-[#244456] text-xs font-mono text-cyan-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>PostGIS 17.11 Kanonikal Terverifikasi &bull; 8.129 Sel</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-tight text-white">
                        Kecerdasan Oseanografi & Prediksi Presisi <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-[#13B8C8]">Pelagis Samudera Hindia</span>
                    </h1>

                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-2xl">
                        SEACAST mentransformasi data satelit oseanografi dan algoritma Species Distribution Model (SDM) ensemble menjadi panduan penangkapan ikan pelagis presisi, zonasi Dynamic Ocean Management (DOM), dan mitigasi bycatch megafauna di WPPNRI 573.
                    </p>

                    <div class="flex flex-wrap items-center gap-4 pt-2">
                        <x-ui.button href="{{ route('dashboard') }}" variant="primary" size="lg" class="shadow-lg shadow-cyan-500/20">
                            Masuk Ruang Kendali Operasional
                        </x-ui.button>
                        <x-ui.button href="{{ route('science') }}" variant="outline" size="lg" class="border-slate-500 text-slate-200 hover:bg-slate-800">
                            Pelajari Landasan Ilmiah
                        </x-ui.button>
                    </div>

                    <!-- Operational Badge Grid -->
                    <div class="grid grid-cols-3 gap-4 pt-4 border-t border-[#244456]/60">
                        <div>
                            <span class="block text-xl font-black font-mono text-cyan-400">{{ number_format($totalCells) }}</span>
                            <span class="text-floor text-slate-400">Sel Kanonik 0,10°</span>
                        </div>
                        <div>
                            <span class="block text-xl font-black font-mono text-emerald-400">{{ $hotspotsCount }} Sel</span>
                            <span class="text-floor text-slate-400">Hotspot Tangkap Hari Ini</span>
                        </div>
                        <div>
                            <span class="block text-xl font-black font-mono text-cyan-300">{{ number_format($totalFleet ?? $totalVessels ?? 1269) }} Kapal</span>
                            <span class="text-floor text-slate-400">Armada Resmi PPN Prigi</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Interactive Map Preview Foundation -->
                <div class="lg:col-span-5">
                    <div class="rounded-3xl border border-[#244456] bg-[#091F30] p-3 shadow-2xl overflow-hidden relative">
                        <div class="flex items-center justify-between pb-3 px-2 border-b border-[#244456]/60 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 pulse-beacon"></span>
                                <span class="font-bold text-slate-200">Radar WPPNRI 573 (PPN Prigi)</span>
                            </div>
                            <span class="text-floor font-mono text-slate-400">Leaflet GIS Native</span>
                        </div>

                        <!-- Leaflet Alpine Container -->
                        <div 
                            x-data="seacastMap({ center: [111.7275, -8.2833], zoom: 7.2 })" 
                            x-init="initMap()" 
                            class="w-full h-80 rounded-2xl overflow-hidden relative mt-2 bg-[#061524]"
                        >
                            <div x-ref="mapContainer" class="w-full h-full"></div>

                            <!-- Basemap Switcher Controls -->
                            <div class="absolute top-3 left-3 z-[1000]">
                                <x-ui.map-basemap-switcher activeVar="currentBasemap" setFn="setBasemap" :compact="true" />
                            </div>

                            <!-- Map Loading Overlay -->
                            <div x-show="!loaded && !error" class="absolute inset-0 bg-[#071B2B]/80 backdrop-blur-sm flex items-center justify-center text-xs text-cyan-400 font-mono z-[1050]">
                                Memuat Tile Peta Samudera...
                            </div>

                            <!-- Map Error Overlay -->
                            <div x-show="error" style="display: none;" class="absolute inset-0 bg-[#071B2B]/90 p-4 flex flex-col items-center justify-center text-center text-xs text-rose-300 z-[1050]">
                                <span x-text="error"></span>
                            </div>

                            <!-- Reset Button Overlay -->
                            <div class="absolute top-3 right-3 z-[1000]">
                                <button @click="resetView()" title="Fokus ke PPN Prigi" class="p-2 rounded-xl bg-[#091F30]/90 border border-[#244456] text-cyan-400 hover:text-white hover:bg-[#12364A] shadow-md transition-colors text-xs">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="px-2 pt-3 flex items-center justify-between text-floor text-slate-400 font-mono">
                            <span>Bujur: 111.7275° E</span>
                            <span>Lintang: -8.2833° S</span>
                            <span class="text-cyan-400 font-bold">EPSG:4326</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Operational Telemetry Snapshot Strip -->
    <section class="bg-white dark:bg-[#0D2B3E] border-b border-slate-200 dark:border-[#244456] py-6 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-ui.stat-card
                    title="Hotspot Tangkap"
                    value="{{ $hotspotsCount }} Sel"
                    subtitle="Kriteria: HSI &ge; 0,70"
                    delta="Terkini"
                    deltaType="positive"
                    accent="rose"
                />

                <x-ui.stat-card
                    title="Kondisi Gelombang"
                    value="{{ $avgWave }} m"
                    subtitle="Simulasi Oseanografi Musiman BMKG"
                    delta="Waspada"
                    deltaType="neutral"
                    accent="amber"
                />

                <x-ui.stat-card
                    title="Kesesuaian Albakor"
                    value="{{ $albakorHsi }}"
                    subtitle="Nilai rata-rata HSI WPP 573"
                    delta="Tertinggi"
                    deltaType="positive"
                    accent="cyan"
                />

                <x-ui.stat-card
                    title="Perlindungan Bycatch"
                    value="{{ $bycatchSafePct }}%"
                    subtitle="Standar Mitigasi IOTC"
                    delta="Zona Aman"
                    deltaType="positive"
                    accent="emerald"
                />
            </div>
        </div>
    </section>

    <!-- Species Habitat Dossiers Section -->
    <section class="py-16 bg-[#F6F9FB] dark:bg-[#071B2B] transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-cyan-600 dark:text-cyan-400">Target Spesies Pelagis</span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                    Evaluasi SDM Ensemble & Habitat Tuna
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-2">
                    Pemodelan multi-algoritma (MaxEnt, Random Forest, GLM) yang telah diaudit secara saintifik berdasarkan data empiris pendaratan PPN Prigi.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Cakalang -->
                <x-ui.card hoverable="true">
                    <div class="flex items-center justify-between mb-3">
                        <x-ui.badge variant="cyan" dot="true">Katsuwonus pelamis</x-ui.badge>
                        <span class="text-xs font-mono font-bold text-slate-500">AUC 0.795</span>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mb-1">Cakalang (Skipjack)</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mb-4 leading-relaxed">
                        Spesies pelagis perenang cepat permukaan. Sensitivitas dominan terhadap SST (38,5%) dan kelimpahan Klorofil-a (31,2%).
                    </p>
                    <div class="space-y-2 pt-3 border-t border-slate-100 dark:border-[#244456] text-xs">
                        <div class="flex justify-between text-slate-500">
                            <span>Bobot MaxEnt / RF / GLM</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">0.320 / 0.355 / 0.325</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Rata-rata HSI Hari Ini</span>
                            <span class="font-mono font-bold text-cyan-500">{{ $cakalangHsi }}</span>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Madidihang -->
                <x-ui.card hoverable="true">
                    <div class="flex items-center justify-between mb-3">
                        <x-ui.badge variant="amber" dot="true">Thunnus albacares</x-ui.badge>
                        <span class="text-xs font-mono font-bold text-slate-500">AUC 0.784</span>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mb-1">Madidihang (Yellowfin)</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mb-4 leading-relaxed">
                        Tuna epipelagis dan mesopelagis. Asosiasi kuat dengan thermal fronts (34,8%) dan koridor lereng benua paparan selatan Jawa.
                    </p>
                    <div class="space-y-2 pt-3 border-t border-slate-100 dark:border-[#244456] text-xs">
                        <div class="flex justify-between text-slate-500">
                            <span>Bobot MaxEnt / RF / GLM</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">0.295 / 0.365 / 0.340</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Rata-rata HSI Hari Ini</span>
                            <span class="font-mono font-bold text-amber-500">{{ $madidihangHsi }}</span>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Albakor -->
                <x-ui.card hoverable="true">
                    <div class="flex items-center justify-between mb-3">
                        <x-ui.badge variant="emerald" dot="true">Thunnus alalunga</x-ui.badge>
                        <span class="text-xs font-mono font-bold text-slate-500">AUC 0.860</span>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mb-1">Albakor (Albacore)</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mb-4 leading-relaxed">
                        Tuna laut dalam sub-tropis/oseanik. Bobot MaxEnt di-nolkan (0,000) karena evaluasi sirkular, sepenuhnya dialokasikan ke RF dan GLM.
                    </p>
                    <div class="space-y-2 pt-3 border-t border-slate-100 dark:border-[#244456] text-xs">
                        <div class="flex justify-between text-slate-500">
                            <span>Bobot MaxEnt / RF / GLM</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">0.000 / 0.517 / 0.483</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Rata-rata HSI Hari Ini</span>
                            <span class="font-mono font-bold text-emerald-500">{{ $albakorHsi }}</span>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </section>

    <!-- Dynamic Ocean Management (DOM) Visualizer Overview -->
    <section class="py-16 bg-white dark:bg-[#091F30] border-t border-slate-200 dark:border-[#244456] transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-5 space-y-4">
                    <x-ui.badge variant="indigo">Dynamic Ocean Management</x-ui.badge>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        Tata Kelola Laut Dinamis & Mitigasi Bycatch IOTC
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        SEACAST menerapkan zonasi laut bergerak dengan hierarki proteksi mutlak: area konservasi benih ikan (Z4) dan screening bycatch penyu/hiu (Z5) selalu menganulir rekomendasi penangkapan inshore/offshore.
                    </p>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] text-xs font-mono space-y-1 text-slate-700 dark:text-slate-300">
                        <div class="font-bold text-cyan-600 dark:text-cyan-400">Aturan Penegakan Hierarki:</div>
                        <div>Z4 (Nursery) &rarr; Z5 (Bycatch) &rarr; Z3 (Feeding) &rarr; Z1 (Inshore) &rarr; Z2 (Offshore) &rarr; GEN</div>
                    </div>
                </div>

                <div class="lg:col-span-7">
                    <x-ui.scientific-legend type="dom" title="Matriks 5 Zona Dynamic Ocean Management" />
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
