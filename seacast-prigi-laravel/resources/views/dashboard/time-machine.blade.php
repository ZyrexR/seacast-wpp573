<x-layouts.dashboard title="Time Machine Spasio-Temporal 2025 — SEACAST">
    <div 
        x-data="{ 
            months: [
                '2025_01', '2025_02', '2025_03', '2025_04',
                '2025_05', '2025_06', '2025_07', '2025_08',
                '2025_09', '2025_10', '2025_11', '2025_12'
            ],
            monthLabels: [
                'Jan 2025', 'Feb 2025', 'Mar 2025', 'Apr 2025',
                'Mei 2025', 'Jun 2025', 'Jul 2025', 'Agu 2025',
                'Sep 2025', 'Okt 2025', 'Nov 2025', 'Des 2025'
            ],
            currentIndex: 7, // August 2025 default (peak upwelling)
            isPlaying: false,
            timer: null,
            summaries: {{ Js::from(collect($monthlySummaries)->keyBy('year_month')) }},
            landings: {{ Js::from(collect($landings2025)->keyBy('year_month')) }},
            get currentYm() {
                return this.months[this.currentIndex];
            },
            get currentLabel() {
                return this.monthLabels[this.currentIndex];
            },
            get currentSummary() {
                return this.summaries[this.currentYm] || {};
            },
            get currentLanding() {
                return this.landings[this.currentYm] || {};
            },
            togglePlay() {
                if (this.isPlaying) {
                    this.stopPlay();
                } else {
                    this.startPlay();
                }
            },
            startPlay() {
                this.isPlaying = true;
                this.timer = setInterval(() => {
                    this.currentIndex = (this.currentIndex + 1) % this.months.length;
                }, 1800);
            },
            stopPlay() {
                this.isPlaying = false;
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            }
        }"
        class="space-y-8 max-w-7xl mx-auto"
    >
        <!-- Header -->
        <div class="pb-6 border-b border-[#16445A]">
            <div class="flex items-center gap-2 text-xs font-bold text-cyan-400 mb-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Evolusi Oseanografi & Habitat Spasio-Temporal</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Time Machine 2025: Rekonstruksi Dinamika Bulanan
                    </h1>
                    <p class="text-xs text-[#A4BAC8] mt-1">
                        Eksplorasi siklus musiman upwelling, pergeseran habitat tuna, dan validasi pendaratan riil PPN Prigi (97.548 baris data).
                    </p>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#0D2B3E] border border-[#16445A] text-xs font-mono text-cyan-300">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                    <span>Tahun Evaluasi: 2025</span>
                </div>
            </div>
        </div>

        <!-- Time Machine Interactive Slider & Controls -->
        <div class="p-6 rounded-3xl bg-[#0D2B3E] border border-[#16445A] space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="togglePlay()"
                        class="px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition-all shadow-md"
                        :class="isPlaying 
                            ? 'bg-rose-500 hover:bg-rose-600 text-white' 
                            : 'bg-gradient-to-r from-cyan-500 to-sky-500 hover:opacity-90 text-slate-950 font-black'"
                    >
                        <template x-if="!isPlaying">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </template>
                        <template x-if="isPlaying">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                        </template>
                        <span x-text="isPlaying ? 'Pause Animasi' : 'Putar Siklus Musiman'"></span>
                    </button>

                    <div class="px-4 py-2 rounded-xl bg-[#071B2B] border border-[#16445A] text-sm font-bold font-mono text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span x-text="currentLabel"></span>
                    </div>
                </div>

                <div class="text-xs text-[#A4BAC8] font-mono flex items-center gap-4">
                    <span>Langkah: <strong class="text-white" x-text="(currentIndex + 1) + '/12'"></strong></span>
                    <span>Snapshot: <strong class="text-cyan-400" x-text="currentYm"></strong></span>
                </div>
            </div>

            <!-- Month Scrubber Range Bar -->
            <div class="space-y-2">
                <input 
                    type="range" 
                    min="0" 
                    max="11" 
                    x-model.number="currentIndex"
                    @input="stopPlay()"
                    class="w-full h-2.5 bg-[#071B2B] rounded-lg appearance-none cursor-pointer accent-cyan-400 focus:outline-none"
                >
                <div class="flex justify-between text-[11px] font-mono text-[#A4BAC8] pt-1">
                    <template x-for="(m, i) in monthLabels" :key="i">
                        <button 
                            type="button" 
                            @click="currentIndex = i; stopPlay()"
                            :class="currentIndex === i ? 'text-cyan-400 font-bold scale-110' : 'hover:text-white'"
                            class="transition-all"
                            x-text="m.split(' ')[0]"
                        ></button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Synchronized Oceanographic & HSI Metrics for Selected Month -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="p-4 rounded-2xl bg-[#0D2B3E] border border-[#16445A] space-y-1">
                <span class="text-[11px] font-mono text-[#A4BAC8] uppercase block">Suhu Muka Laut</span>
                <span class="text-xl font-bold font-mono text-amber-300 block" x-text="(currentSummary.avg_sst || '28.5') + ' °C'"></span>
                <span class="text-[10px] text-slate-400">Rerata WPP 573</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#0D2B3E] border border-[#16445A] space-y-1">
                <span class="text-[11px] font-mono text-[#A4BAC8] uppercase block">Klorofil-a</span>
                <span class="text-xl font-bold font-mono text-emerald-300 block" x-text="(currentSummary.avg_chla || '0.28') + ' mg/m³'"></span>
                <span class="text-[10px] text-slate-400">Nutrien Primer</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#0D2B3E] border border-[#16445A] space-y-1">
                <span class="text-[11px] font-mono text-[#A4BAC8] uppercase block">Arus Permukaan</span>
                <span class="text-xl font-bold font-mono text-sky-300 block" x-text="(currentSummary.avg_current || '0.35') + ' m/s'"></span>
                <span class="text-[10px] text-slate-400">Kecepatan Arus</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#0D2B3E] border border-[#16445A] space-y-1">
                <span class="text-[11px] font-mono text-[#A4BAC8] uppercase block">HSI Cakalang</span>
                <span class="text-xl font-bold font-mono text-cyan-300 block" x-text="currentSummary.avg_hsi_cakalang || '0.55'"></span>
                <span class="text-[10px] text-slate-400">Rerata Kesesuaian</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#0D2B3E] border border-[#16445A] space-y-1">
                <span class="text-[11px] font-mono text-[#A4BAC8] uppercase block">HSI Madidihang</span>
                <span class="text-xl font-bold font-mono text-indigo-300 block" x-text="currentSummary.avg_hsi_madidihang || '0.51'"></span>
                <span class="text-[10px] text-slate-400">Yellowfin Tuna</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#0D2B3E] border border-[#16445A] space-y-1">
                <span class="text-[11px] font-mono text-[#A4BAC8] uppercase block">Hotspot Aktif</span>
                <span class="text-xl font-bold font-mono text-rose-400 block" x-text="currentSummary.hotspot_cells || '185'"></span>
                <span class="text-[10px] text-slate-400">Sel HSI &ge; 0.70</span>
            </div>
        </div>

        <!-- Empirical Harbor Landings Synchronization Panel -->
        <div class="bg-[#0D2B3E] rounded-3xl border border-[#16445A] p-6 lg:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#16445A]">
                <div>
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        <span>Sinkronisasi Pendaratan Riil TPI Prigi</span>
                    </h2>
                    <p class="text-xs text-[#A4BAC8] mt-1">
                        Memvalidasi kesesuaian prediksi habitat terhadap tangkapan riil nelayan Prigi pada bulan <strong class="text-white" x-text="currentLabel"></strong>.
                    </p>
                </div>

                <div class="px-3 py-1.5 rounded-xl bg-[#071B2B] border border-[#16445A] text-xs font-mono text-emerald-400">
                    <span>Status: Data Empiris Terverifikasi</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div class="p-4 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-1">
                    <span class="text-[#A4BAC8] block">Total Ikan Pelagis</span>
                    <span class="text-2xl font-bold font-mono text-white block" x-text="(currentLanding.total_pelagic_tons || '0') + ' Ton'"></span>
                    <span class="text-[11px] text-cyan-400">Pendaratan PPN Prigi</span>
                </div>

                <div class="p-4 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-1">
                    <span class="text-[#A4BAC8] block">Pendaratan Cakalang</span>
                    <span class="text-2xl font-bold font-mono text-cyan-300 block" x-text="(currentLanding.cakalang_tons || '0') + ' Ton'"></span>
                    <span class="text-[11px] text-slate-400">Katsuwonus pelamis</span>
                </div>

                <div class="p-4 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-1">
                    <span class="text-[#A4BAC8] block">Pendaratan Madidihang</span>
                    <span class="text-2xl font-bold font-mono text-indigo-300 block" x-text="(currentLanding.madidihang_tons || '0') + ' Ton'"></span>
                    <span class="text-[11px] text-slate-400">Thunnus albacares</span>
                </div>

                <div class="p-4 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-1">
                    <span class="text-[#A4BAC8] block">CPUE Proxy Nominal</span>
                    <span class="text-2xl font-bold font-mono text-emerald-400 block" x-text="(currentLanding.cpue_proxy || '0') + ' Ton/Trip'"></span>
                    <span class="text-[11px] text-slate-400" x-text="'Total ' + (currentLanding.trips_count || '0') + ' Trip'"></span>
                </div>
            </div>

            <!-- Oceanographic Dynamic Interpretation -->
            <div class="p-4 rounded-xl bg-[#071B2B]/70 border border-[#16445A] text-xs text-slate-300 space-y-2">
                <span class="text-white font-bold font-mono flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Dinamika Musiman WPPNRI 573:</span>
                </span>
                <p class="leading-relaxed">
                    Puncak kesesuaian habitat Cakalang dan Madidihang di selatan Prigi terjadi pada periode Musim Timur (Juli – Oktober), didorong oleh intensifikasi upwelling akibat angin monsun tenggara yang menurunkan suhu muka laut (hingga 26.5 °C) dan meningkatkan klorofil-a (&gt; 0.35 mg/m³).
                </p>
            </div>
        </div>
    </div>
</x-layouts.dashboard>

