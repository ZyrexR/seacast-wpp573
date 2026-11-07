<x-layouts.dashboard title="Bycatch Traffic Light & Keselamatan Pelayaran — SEACAST">
    <div 
        x-data="{ 
            selectedLight: 'GREEN',
            trafficLights: {{ Js::from($trafficLights) }},
            get current() {
                return this.trafficLights[this.selectedLight] || this.trafficLights['GREEN'];
            }
        }"
        class="space-y-8 max-w-7xl mx-auto"
    >
        <!-- Header -->
        <div class="pb-6 border-b border-[#16445A]">
            <div class="flex items-center gap-2 text-xs font-bold text-emerald-400 mb-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Standar Konservasi IOTC & BMKG Maritim</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Bycatch Traffic Light & Keselamatan Maritim
                    </h1>
                    <p class="text-xs text-[#A4BAC8] mt-1">
                        Sistem pemantauan mitigasi interaksi megafauna laut dilindungi (ETP Species) dan panduan navigasi gelombang WPPNRI 573.
                    </p>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#0D2B3E] border border-[#16445A] text-xs font-mono text-cyan-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Snapshot: {{ $latestDate }}</span>
                </div>
            </div>
        </div>

        <!-- Wave & Wind Oceanographic Safety Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-[#0D2B3E] border border-[#16445A] flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-mono text-[#A4BAC8] uppercase tracking-wider block">Tinggi Gelombang Rata-rata</span>
                    <span class="text-2xl font-black text-white font-mono mt-1 block">{{ $oceanStats->avg_wave ?? '1.85' }} m</span>
                    <span class="text-[11px] text-cyan-400">Maksimum: {{ $oceanStats->max_wave ?? '3.20' }} m</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-[#0D2B3E] border border-[#16445A] flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-mono text-[#A4BAC8] uppercase tracking-wider block">Kecepatan Angin Rata-rata</span>
                    <span class="text-2xl font-black text-white font-mono mt-1 block">{{ $oceanStats->avg_wind ?? '6.40' }} m/s</span>
                    <span class="text-[11px] text-sky-400">Maksimum: {{ $oceanStats->max_wind ?? '11.80' }} m/s</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center text-sky-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-[#0D2B3E] border border-[#16445A] flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-mono text-[#A4BAC8] uppercase tracking-wider block">Sel Aman Penangkapan</span>
                    <span class="text-2xl font-black text-emerald-400 font-mono mt-1 block">{{ number_format($trafficLights['GREEN']['cells'], 0, ',', '.') }}</span>
                    <span class="text-[11px] text-emerald-300 font-mono">{{ $trafficLights['GREEN']['pct'] }}% dari total sel</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-[#0D2B3E] border border-[#16445A] flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-mono text-[#A4BAC8] uppercase tracking-wider block">Peringatan Tinggi (Z5)</span>
                    <span class="text-2xl font-black text-rose-400 font-mono mt-1 block">{{ number_format($trafficLights['RED']['cells'], 0, ',', '.') }}</span>
                    <span class="text-[11px] text-rose-300 font-mono">{{ $trafficLights['RED']['pct'] }}% area rawan bycatch</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
            </div>
        </div>

        <!-- 3 Traffic Lights Selector Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($trafficLights as $status => $tl)
                <button
                    type="button"
                    @click="selectedLight = '{{ $status }}'"
                    :class="selectedLight === '{{ $status }}' 
                        ? 'bg-[#0D2B3E] border-cyan-400 ring-1 ring-cyan-400/30' 
                        : 'bg-[#071B2B] border-[#16445A] hover:bg-[#0D2B3E]/60'"
                    class="p-5 rounded-2xl border text-left transition-all space-y-3"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded-full {{ $tl['color'] }}"></span>
                            <span class="text-sm font-bold text-white">{{ $tl['status'] }}</span>
                        </div>
                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded border {{ $tl['badge'] }}">
                            {{ $tl['pct'] }}%
                        </span>
                    </div>
                    <p class="text-xs text-[#A4BAC8] leading-relaxed">{{ $tl['label'] }}</p>
                    <div class="pt-2 border-t border-[#16445A] flex items-center justify-between text-xs font-mono">
                        <span class="text-[#A4BAC8]">Total Grid:</span>
                        <span class="text-white font-bold">{{ number_format($tl['cells'], 0, ',', '.') }} Sel</span>
                    </div>
                </button>
            @endforeach
        </div>

        <!-- Selected Status Deep Dive -->
        <div class="bg-[#0D2B3E] rounded-3xl border border-[#16445A] p-6 lg:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#16445A]">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded-full shrink-0" :class="current.color"></span>
                        <h2 class="text-xl font-bold text-white" x-text="current.label"></h2>
                    </div>
                    <p class="text-xs text-[#A4BAC8] mt-2 leading-relaxed max-w-3xl" x-text="current.advisory"></p>
                </div>

                <div class="bg-[#071B2B] p-3 rounded-xl border border-[#16445A] font-mono text-xs text-right shrink-0">
                    <span class="text-[11px] text-[#A4BAC8] block">Cakupan Wilayah</span>
                    <span class="text-base font-bold text-cyan-300" x-text="current.cells.toLocaleString('id-ID') + ' Sel (' + current.pct + '%)'"></span>
                </div>
            </div>

            <!-- Detail Protocols -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div class="p-5 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-cyan-400 block font-mono">
                        Kriteria Ambang Batas Interaksi Megafauna
                    </span>
                    <p class="text-white font-mono leading-relaxed" x-text="current.criteria"></p>
                </div>

                <div class="p-5 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400 block font-mono">
                        Tindakan Wajib Operasi Penangkapan
                    </span>
                    <p class="text-emerald-300 font-semibold leading-relaxed" x-text="current.action"></p>
                </div>
            </div>

            <!-- IOTC Technical Mitigation Panel -->
            <div class="p-5 rounded-2xl bg-[#071B2B]/70 border border-[#16445A] space-y-4">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider font-mono flex items-center gap-2">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Protokol Mitigasi Wajib Standar IOTC (Resolusi 12/04 & 13/05)</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs text-[#A4BAC8]">
                    <div class="p-3 rounded-xl bg-[#091F30] border border-[#16445A] space-y-1">
                        <span class="text-white font-bold block">1. Circle Hooks</span>
                        <p class="leading-relaxed">Gunakan mata pancing melingkar ukuran &ge; 16/0 untuk armada rawai guna mengurangi risiko tertelan penyu.</p>
                    </div>
                    <div class="p-3 rounded-xl bg-[#091F30] border border-[#16445A] space-y-1">
                        <span class="text-white font-bold block">2. Dehooker & Line Cutter</span>
                        <p class="leading-relaxed">Setiap kapal wajib membawa tang pemotong kawat dan alat pelepas mata pancing untuk melepaskan satwa hidup.</p>
                    </div>
                    <div class="p-3 rounded-xl bg-[#091F30] border border-[#16445A] space-y-1">
                        <span class="text-white font-bold block">3. Night Setting</span>
                        <p class="leading-relaxed">Lakukan tebar jaring dan tali pancing pada malam hari untuk meminimalkan interaksi dengan burung laut dan penyu.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>

