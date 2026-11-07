<x-layouts.public title="SEACAST — Scientific Ocean Intelligence & Fisheries Decision Support">
    <!-- Hero Section with Centered Cinematic Marine Atmosphere -->
    <section class="relative overflow-hidden bg-gradient-to-b from-[#071B2B] via-[#091F30] to-[#0D2B3E] text-white pt-20 pb-24 border-b border-[#244456]">
        <!-- Background Ambient Glow & Grid -->
        <div class="absolute inset-0 opacity-15 pointer-events-none" style="background-image: radial-gradient(#13B8C8 1px, transparent 1px); background-size: 32px 32px;"></div>
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[350px] bg-cyan-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <!-- Verified PostGIS Pill -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#12364A]/90 border border-[#244456] text-xs font-mono text-cyan-300 shadow-lg mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>PostGIS 17 Kanonikal &bull; 8.129 Sel &bull; WPPNRI 573</span>
            </div>

            <!-- Main Cinematic Headline -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight text-white mb-6">
                Kecerdasan Oseanografi & Prediksi Presisi <br class="hidden sm:inline">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-teal-300 to-[#13B8C8]">Pelagis Samudera Hindia (WPP 573)</span>
            </h1>

            <!-- Descriptive Subtitle with Multi-Port Coverage -->
            <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-3xl mx-auto mb-8">
                Platform intelijen maritim terpadu untuk efisiensi bahan bakar nelayan, keselamatan pelayaran, dan kepatuhan zonasi Permen KP No. 59/2020 di <strong class="text-cyan-300">PPN Prigi</strong>, <strong class="text-cyan-300">PPS Cilacap</strong>, dan <strong class="text-cyan-300">PPN Palabuhanratu</strong>.
            </p>

            <!-- Dual CTA Action Buttons -->
            <div class="flex flex-wrap items-center justify-center gap-4 mb-12">
                <x-ui.button href="{{ route('dashboard') }}" variant="primary" size="lg" class="shadow-xl shadow-cyan-500/25 px-8">
                    Masuk Ruang Kendali
                </x-ui.button>
                <x-ui.button href="{{ route('dashboard.trip-planner') }}" variant="outline" size="lg" class="border-[#244456] text-slate-200 hover:bg-[#12364A] hover:text-white px-8">
                    Rencanakan Melaut
                </x-ui.button>
            </div>

            <!-- Integrated Multi-Port Metrics Strip -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-4xl mx-auto pt-8 border-t border-[#244456]/70">
                <div class="p-4 rounded-2xl bg-[#091F30]/80 border border-[#244456] backdrop-blur-sm">
                    <span class="block text-2xl font-black font-mono text-cyan-400">{{ number_format($totalCells) }}</span>
                    <span class="text-xs font-semibold text-slate-300">Sel Kanonik 0,10°</span>
                    <p class="text-[11px] text-slate-400 mt-1">Grid PostGIS WPPNRI 573</p>
                </div>
                <div class="p-4 rounded-2xl bg-[#091F30]/80 border border-[#244456] backdrop-blur-sm">
                    <span class="block text-2xl font-black font-mono text-emerald-400">{{ $hotspotsCount }} Sel</span>
                    <span class="text-xs font-semibold text-slate-300">Hotspot Tangkap Hari Ini</span>
                    <p class="text-[11px] text-slate-400 mt-1">Kesesuaian HSI &ge; 0,70</p>
                </div>
                <div class="p-4 rounded-2xl bg-[#091F30]/80 border border-[#244456] backdrop-blur-sm">
                    <span class="block text-2xl font-black font-mono text-cyan-300">2.450+ Kapal</span>
                    <span class="text-xs font-semibold text-slate-300">Armada WPPNRI 573 Terintegrasi</span>
                    <div class="flex items-center justify-center gap-1.5 mt-1.5 text-[10px] font-mono text-slate-400">
                        <span class="px-1.5 py-0.5 rounded bg-[#12364A] text-cyan-300">Prigi</span>
                        <span>&bull;</span>
                        <span class="px-1.5 py-0.5 rounded bg-[#12364A] text-cyan-300">Cilacap</span>
                        <span>&bull;</span>
                        <span class="px-1.5 py-0.5 rounded bg-[#12364A] text-cyan-300">Palabuhanratu</span>
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
                    subtitle="Prakiraan Rata-Rata WPP 573"
                    delta="Waspada"
                    deltaType="neutral"
                    accent="amber"
                />

                <x-ui.stat-card
                    title="Kesesuaian Tongkol"
                    value="{{ $tongkolHsi }}"
                    subtitle="Pendaratan Terbesar WPP 573"
                    delta="Dominan"
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
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-cyan-600 dark:text-cyan-400">Target Komoditas Pelagis Utama</span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                    Evaluasi SDM Ensemble & Habitat Pelagis WPP 573
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-2">
                    Pemodelan multi-algoritma (MaxEnt, Random Forest, GLM) terkalibrasi berdasarkan data empiris pendaratan di PPN Prigi, PPS Cilacap, dan PPN Palabuhanratu.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Tongkol Lisong / Krai -->
                <x-ui.card hoverable="true">
                    <div class="flex items-center justify-between mb-3">
                        <x-ui.badge variant="emerald" dot="true">Auxis thazard / Euthynnus</x-ui.badge>
                        <span class="text-xs font-mono font-bold text-emerald-500">Pendaratan Utama</span>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mb-1">Tongkol (Lisong & Krai)</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mb-4 leading-relaxed">
                        Spesies pelagis neritik dan epipelagis dengan volume pendaratan terbesar di selatan Jawa. Respon sangat kuat terhadap pengkayaan klorofil-a akibat upwelling musiman.
                    </p>
                    <div class="space-y-2 pt-3 border-t border-slate-100 dark:border-[#244456] text-xs">
                        <div class="flex justify-between text-slate-500">
                            <span>Status Armada WPP 573</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">Purse Seine & Pancing</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Rata-rata HSI Hari Ini</span>
                            <span class="font-mono font-bold text-emerald-500">{{ $tongkolHsi }}</span>
                        </div>
                    </div>
                </x-ui.card>

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
                        Tuna epipelagis dan mesopelagis bernilai tinggi. Asosiasi kuat dengan thermal fronts (34,8%) dan koridor lereng benua paparan selatan Jawa.
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
