<x-layouts.dashboard title="Dynamic Ocean Management (DOM) 5-Zona — SEACAST">
    <div 
        x-data="{ 
            selectedZone: 'Z1',
            zones: {{ Js::from($zones) }},
            get current() {
                return this.zones[this.selectedZone] || this.zones['Z1'];
            }
        }"
        class="space-y-8 max-w-7xl mx-auto"
    >
        <!-- Header -->
        <div class="pb-6 border-b border-[#16445A]">
            <div class="flex items-center gap-2 text-xs font-bold text-cyan-400 mb-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Tata Kelola Spasial Berkelanjutan</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Dynamic Ocean Management (DOM)
                    </h1>
                    <p class="text-xs text-[#A4BAC8] mt-1">
                        Klasifikasi spasial dinamis 8.129 sel kanonik WPPNRI 573 ke dalam 5 zona fungsional konservasi & penangkapan.
                    </p>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#0D2B3E] border border-[#16445A] text-xs font-mono text-cyan-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Snapshot: {{ $latestDate }}</span>
                </div>
            </div>
        </div>

        <!-- 6 Zones Selector Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach($zones as $code => $z)
                <button
                    type="button"
                    @click="selectedZone = '{{ $code }}'"
                    :class="selectedZone === '{{ $code }}' 
                        ? 'bg-[#0D2B3E] border-cyan-400 shadow-md ring-1 ring-cyan-400/30' 
                        : 'bg-[#071B2B] border-[#16445A] hover:bg-[#0D2B3E]/60 text-[#A4BAC8]'"
                    class="p-4 rounded-2xl border text-left transition-all flex flex-col justify-between gap-3"
                >
                    <div class="flex items-center justify-between">
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $z['color'] }}"></span>
                        <span class="text-xs font-mono font-bold text-cyan-300">{{ $z['code'] }}</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold truncate" :class="selectedZone === '{{ $code }}' ? 'text-white' : 'text-[#A4BAC8]'">
                            {{ $z['name'] }}
                        </p>
                        <p class="text-xs font-mono text-white font-bold mt-1">{{ number_format($z['cells'], 0, ',', '.') }} Sel</p>
                        <p class="text-[11px] text-[#A4BAC8]">{{ $z['pct'] }}% Wilayah</p>
                    </div>
                </button>
            @endforeach
        </div>

        <!-- Selected Zone Deep Dive Panel -->
        <div class="bg-[#0D2B3E] rounded-3xl border border-[#16445A] p-6 lg:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#16445A]">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded-full shrink-0" :style="{ backgroundColor: current.color }"></span>
                        <h2 class="text-xl font-bold text-white" x-text="current.name"></h2>
                        <span class="px-2.5 py-0.5 rounded text-xs font-bold font-mono border" :class="current.badge" x-text="current.code"></span>
                    </div>
                    <p class="text-xs text-[#A4BAC8] mt-2 leading-relaxed max-w-3xl" x-text="current.desc"></p>
                </div>

                <div class="bg-[#071B2B] p-3 rounded-xl border border-[#16445A] font-mono text-xs text-right shrink-0">
                    <span class="text-[11px] text-[#A4BAC8] block">Alokasi Wilayah</span>
                    <span class="text-base font-bold text-cyan-300" x-text="current.cells.toLocaleString('id-ID') + ' Sel (' + current.pct + '%)'"></span>
                </div>
            </div>

            <!-- Rules & Fleet Permissions Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div class="p-5 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-cyan-400 block font-mono">
                        Kriteria Ambang Batas Spasial
                    </span>
                    <p class="text-white font-mono leading-relaxed" x-text="current.criteria"></p>
                </div>

                <div class="p-5 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-cyan-400 block font-mono">
                        Izin & Rekomendasi Armada Kapal
                    </span>
                    <p class="text-white font-semibold leading-relaxed" x-text="current.fleet"></p>
                </div>

                <div class="p-5 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-cyan-400 block font-mono">
                        Ketentuan Alat Tangkap
                    </span>
                    <p class="text-[#A4BAC8] leading-relaxed" x-text="current.gear"></p>
                </div>

                <div class="p-5 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400 block font-mono">
                        Status Konservasi Kelautan
                    </span>
                    <p class="text-emerald-300 font-semibold leading-relaxed" x-text="current.status"></p>
                </div>
            </div>

            <!-- Scientific Priority Hierarchy Note -->
            <div class="p-4 rounded-xl bg-[#071B2B]/70 border border-[#16445A] flex items-start gap-3 text-xs text-slate-300">
                <svg class="w-5 h-5 text-cyan-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="leading-relaxed">
                    <strong class="text-white font-mono">Hierarki Resolusi Konflik Waterfall DOM:</strong>
                    Z4 (Nursery Conservation) &gt; Z5 (Bycatch Advisory) &gt; Z3 (Feeding Core) &gt; Z1 (PFG Inshore) &gt; Z2 (PFG Offshore) &gt; GEN (General Oceanic). Setiap sel dievaluasi berurutan dari perlindungan konservasi tertinggi sebelum dialokasikan sebagai daerah penangkapan.
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>

