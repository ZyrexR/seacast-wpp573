<x-layouts.dashboard title="Rencana Operasional Melaut — SEACAST">

    <div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="tripPlanner({
        initialTarget: {{ Js::from($initialTarget) }},
        initialPlan: {{ Js::from($initialPlan) }},
        calcUrl: '{{ route('dashboard.trip-planner.calculate') }}',
        csrf: '{{ csrf_token() }}'
    })">

        <!-- Top Header & Context -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#244456] pb-5">
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2.5">
                    <span class="inline-block w-2.5 h-6 bg-cyan-500 rounded-full"></span>
                    Rencana Operasional Melaut
                </h1>
                <p class="text-xs text-slate-400 mt-1">
                    Kalkulator Terpadu Efisiensi Bahan Bakar, Keselamatan Maritim & Kepatuhan Zonasi Permen KP 59/2020
                </p>
            </div>
            
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard.dpi-radar') }}" class="inline-flex items-center gap-2 px-3 py-2 bg-[#0D2B3E] hover:bg-[#12364A] text-slate-300 hover:text-white rounded-xl text-xs font-semibold border border-[#244456] transition">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    Buka DPI Radar Spasial
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-[#244456] pb-1">
            <button type="button" @click="activeTab = 'planner'"
                    :class="activeTab === 'planner' ? 'bg-[#091F30] text-cyan-400 border-[#244456] border-b-cyan-500 font-bold' : 'text-slate-400 hover:text-slate-200 border-transparent'"
                    class="px-4 py-2.5 rounded-t-xl text-xs sm:text-sm border-t border-x transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Kalkulator & Rencana Baru
            </button>
            <button type="button" @click="activeTab = 'my_trips'"
                    :class="activeTab === 'my_trips' ? 'bg-[#091F30] text-cyan-400 border-[#244456] border-b-cyan-500 font-bold' : 'text-slate-400 hover:text-slate-200 border-transparent'"
                    class="px-4 py-2.5 rounded-t-xl text-xs sm:text-sm border-t border-x transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Riwayat Melaut Saya
                <span class="px-1.5 py-0.5 rounded-full text-floor font-mono bg-cyan-500/20 text-cyan-300">{{ count($myTrips) }}</span>
            </button>
            @if ($adminLogbook !== null)
            <button type="button" @click="activeTab = 'admin_logbook'"
                    :class="activeTab === 'admin_logbook' ? 'bg-[#091F30] text-amber-400 border-[#244456] border-b-amber-500 font-bold' : 'text-slate-400 hover:text-slate-200 border-transparent'"
                    class="px-4 py-2.5 rounded-t-xl text-xs sm:text-sm border-t border-x transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Buku Log Pelabuhan (Syahbandar)
                <span class="px-1.5 py-0.5 rounded-full text-floor font-mono bg-amber-500/20 text-amber-300">{{ count($adminLogbook) }}</span>
            </button>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: KALKULATOR RENCANA BARU             -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'planner'" class="space-y-6">

            <!-- Regulatory & Advisory Banner -->
            <div class="p-4 rounded-xl border flex items-start gap-3 transition-colors"
                 :class="{
                     'bg-emerald-500/10 border-emerald-500/30 text-emerald-200': plan.permen_kp.is_compliant && plan.wave_safety.is_safe_to_sail,
                     'bg-amber-500/10 border-amber-500/30 text-amber-200': (!plan.permen_kp.is_compliant || !plan.wave_safety.is_safe_to_sail) && plan.wave_safety.status !== 'DANGER' && plan.wave_safety.status !== 'BAHAYA GELOMBANG EKSTREM' && plan.permen_kp.status !== 'VIOLATION_RESTRICTED_INSHORE',
                     'bg-rose-500/10 border-rose-500/30 text-rose-200': plan.wave_safety.status === 'DANGER' || plan.wave_safety.status === 'BAHAYA GELOMBANG EKSTREM' || plan.permen_kp.status === 'VIOLATION_RESTRICTED_INSHORE'
                 }">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-xs space-y-1">
                    <div class="font-bold flex items-center gap-2">
                        <span x-text="plan.permen_kp.status_label || (plan.permen_kp.is_compliant ? 'PATUH' : 'PERLU TINJAUAN')"></span>
                        <span>•</span>
                        <span x-text="plan.wave_safety.status_label || plan.wave_safety.status"></span>
                    </div>
                    <p x-text="plan.permen_kp.message"></p>
                    <p class="text-floor opacity-80" x-text="plan.permen_kp.citation"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Left Column: Parameter Form (5 Cols) -->
                <div class="lg:col-span-5 bg-[#091F30] border border-[#244456] rounded-2xl p-5 space-y-5">
                    <div class="flex items-center justify-between border-b border-[#244456] pb-3">
                        <h2 class="text-sm font-bold text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Parameter Pelayaran & Kapal
                        </h2>
                        <span class="text-floor font-mono text-cyan-400" x-text="plan.reference_no"></span>
                    </div>

                    <form action="{{ route('dashboard.trip-planner.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="cell_id" :value="form.cell_id || plan.cell_id || ''">
                        <input type="hidden" name="hsi_score" :value="form.hsi_score">
                        <input type="hidden" name="wave_height_m" :value="form.wave_height_m">

                        <!-- Port of Origin -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Pelabuhan Pangkalan / Keberangkatan</label>
                            <select name="port_code" x-model="form.port_code" @change="recalculate()"
                                    class="w-full bg-[#071B2B] border border-[#244456] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-400">
                                @foreach ($ports as $code => $port)
                                    <option value="{{ $code }}">{{ $port['name'] }} ({{ $port['province'] }})</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-slate-400 mt-1">Titik tolak koordinat acuan penghitungan jarak nautika.</p>
                        </div>

                        <!-- Departure Date -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tanggal Melaut</label>
                                <input type="date" name="departure_date" x-model="form.departure_date" @change="recalculate()"
                                       class="w-full bg-[#071B2B] border border-[#244456] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-400">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Kelas Tonase Kapal (GT)</label>
                                <select name="gt_class" x-model="form.gt_class" @change="recalculate()"
                                        class="w-full bg-[#071B2B] border border-[#244456] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-400">
                                    @foreach ($gtSpecs as $gt => $spec)
                                        <option value="{{ $gt }}">{{ $gt }} ({{ $spec['gear_code'] }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Vessel and Skipper Names -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Kapal</label>
                                <input type="text" name="vessel_name" x-model="form.vessel_name"
                                       class="w-full bg-[#071B2B] border border-[#244456] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-400"
                                       placeholder="Contoh: KM Sumber Rejeki">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Nakhoda</label>
                                <input type="text" name="skipper_name" x-model="form.skipper_name"
                                       class="w-full bg-[#071B2B] border border-[#244456] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-400"
                                       placeholder="Nama Nakhoda">
                            </div>
                        </div>

                        <!-- Target Commodity & Trip Days -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Komoditas Sasaran</label>
                                <select name="target_commodity" x-model="form.target_commodity" @change="recalculate()"
                                        class="w-full bg-[#071B2B] border border-[#244456] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-400">
                                    <option value="Cakalang">Cakalang (Katsuwonus pelamis) — Rp 22.000/kg</option>
                                    <option value="tuna_sirip_kuning">Tuna Sirip Kuning (Thunnus albacares) — Rp 45.000/kg</option>
                                    <option value="Tongkol">Tongkol Lisong / Krai (Auxis spp.) — Rp 28.000/kg</option>
                                    <option value="tongkol_krai">Tongkol Krai (Auxis thazard) — Rp 28.000/kg</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Lama Melaut (Hari)</label>
                                <input type="number" min="1" max="14" step="1" name="trip_days" x-model.number="form.trip_days" @change="recalculate()"
                                       class="w-full bg-[#071B2B] border border-[#244456] rounded-xl px-3 py-2 text-xs font-mono text-white focus:outline-none focus:border-cyan-400"
                                       title="Durasi rencana melaut (1 - 14 hari)">
                            </div>
                        </div>

                        <!-- Vessel Readiness -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Kesiapan Kapal &amp; Alat Tangkap</label>
                            <select name="vessel_readiness" x-model="form.vessel_readiness" @change="recalculate()"
                                    class="w-full bg-[#071B2B] border border-[#244456] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-400">
                                <option value="OPTIMAL">Optimal (100% — Mesin, Palka Es &amp; Alat Tangkap Prima)</option>
                                <option value="CUKUP">Cukup (85% — Layak Jalan, Perlu Penyesuaian Ringan)</option>
                                <option value="TERBATAS">Terbatas (65% — Kurang Prima, Hanya Trip Dekat 1 Hari)</option>
                            </select>
                        </div>

                        <!-- Target Coordinates -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-300">Koordinat Titik Tangkap Sasaran (WPPNRI 573)</label>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-[10px] font-mono text-slate-400">Lat:</span>
                                        <input type="number" step="0.0001" name="target_lat" x-model.number="form.target_lat" @input.debounce.400ms="recalculate()"
                                               class="w-full bg-[#071B2B] border border-[#244456] rounded-xl pl-10 pr-2 py-2 text-xs text-white font-mono focus:outline-none focus:border-cyan-400">
                                    </div>
                                </div>
                                <div>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-[10px] font-mono text-slate-400">Lon:</span>
                                        <input type="number" step="0.0001" name="target_lon" x-model.number="form.target_lon" @input.debounce.400ms="recalculate()"
                                               class="w-full bg-[#071B2B] border border-[#244456] rounded-xl pl-10 pr-2 py-2 text-xs text-white font-mono focus:outline-none focus:border-cyan-400">
                                    </div>
                                </div>
                            </div>
                            <div class="p-2 rounded-lg bg-[#071B2B] border border-[#244456]/60 flex items-center justify-between text-[11px] font-mono text-cyan-300">
                                <span>Format Presisi DMS:</span>
                                <span x-text="plan.target_coordinates.dms"></span>
                            </div>
                        </div>

                        <!-- Operational Notes -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Catatan Operasional / Logistik (Opsional)</label>
                            <textarea name="notes" rows="2"
                                      class="w-full bg-[#071B2B] border border-[#244456] rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-cyan-400"
                                      placeholder="Catatan tambahan perlengkapan pancing, es curah, atau kru kapal..."></textarea>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="pt-2 flex flex-col sm:flex-row gap-3">
                            <button type="submit"
                                    class="flex-1 py-2.5 px-4 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black rounded-xl text-xs transition active:scale-95 shadow-md flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                Simpan Rencana Pelayaran
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Decision Support Cockpit (7 Cols) -->
                <div class="lg:col-span-7 space-y-5">

                    <!-- Metric Row 1: Navigasi & Bahan Bakar (+15% Safety Reserve) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <!-- Navigation Card -->
                        <div class="bg-[#091F30] border border-[#244456] rounded-2xl p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                    Navigasi Nautika
                                </span>
                                <span class="text-floor font-mono text-cyan-400" x-text="plan.navigation.compass_direction"></span>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <div class="p-3 rounded-xl bg-[#071B2B] border border-[#244456]/60">
                                    <div class="text-[10px] text-slate-400">Jarak Tempuh (One-Way)</div>
                                    <div class="text-lg font-black text-white font-mono mt-0.5">
                                        <span x-text="plan.navigation.distance_nm"></span> <span class="text-xs text-cyan-400 font-sans font-normal">NM</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 font-mono">
                                        ~<span x-text="plan.navigation.distance_km"></span> km
                                    </div>
                                </div>
                                <div class="p-3 rounded-xl bg-[#071B2B] border border-[#244456]/60">
                                    <div class="text-[10px] text-slate-400">Sudut Haluan (Bearing)</div>
                                    <div class="text-lg font-black text-white font-mono mt-0.5">
                                        <span x-text="plan.navigation.bearing_degrees"></span>°
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 font-mono">
                                        Arah: <span x-text="plan.navigation.compass_direction"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-[11px] text-slate-400 flex items-center justify-between border-t border-[#244456]/60 pt-2">
                                <span>Alat Tangkap Legal (KBLI/API):</span>
                                <span class="font-mono text-cyan-300 font-semibold" x-text="plan.gear_code"></span>
                            </div>
                        </div>

                        <!-- Fuel Card (With +15% Safety Reserve Highlight) -->
                        <div class="bg-[#091F30] border border-[#244456] rounded-2xl p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    Logistik Solar & Anggaran
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                                    Cadangan Keselamatan +15%
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <div class="p-3 rounded-xl bg-[#071B2B] border border-[#244456]/60">
                                    <div class="text-[10px] text-slate-400">Total Kebutuhan Solar</div>
                                    <div class="text-lg font-black text-amber-400 font-mono mt-0.5">
                                        <span x-text="plan.fuel.total_fuel_liters"></span> <span class="text-xs text-slate-300 font-sans font-normal">Liter</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        Termasuk +<span x-text="plan.fuel.safety_reserve_liters"></span> L safety
                                    </div>
                                </div>
                                <div class="p-3 rounded-xl bg-[#071B2B] border border-[#244456]/60">
                                    <div class="text-[10px] text-slate-400">Estimasi Biaya Solar (Subsidi)</div>
                                    <div class="text-base font-black text-white font-mono mt-0.5">
                                        <span x-text="formatRupiah(plan.fuel.total_fuel_cost_rp)"></span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 font-mono">
                                        @ Rp 6.800 / Liter
                                    </div>
                                </div>
                            </div>

                            <div class="text-[11px] text-slate-400 flex flex-wrap items-center justify-between gap-2 border-t border-[#244456]/60 pt-2">
                                <span>Basis PP: <strong class="font-mono text-slate-300" x-text="plan.fuel.base_fuel_liters + ' L'"></strong> &bull; Ops/Hari: <strong class="font-mono text-cyan-300" x-text="(plan.daily_ops_fuel_liters ?? 0) + ' L'"></strong></span>
                                <span>Logistik (<span x-text="plan.trip_days ?? 1"></span> hr): <strong class="font-mono text-amber-300" x-text="formatRupiah(plan.provisions_cost_rp ?? 0)"></strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Metric Row 2: Zonasi Permen KP 59/2020 & Keselamatan Gelombang BMKG -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Permen KP 59/2020 Compliance Card -->
                        <div class="bg-[#091F30] border border-[#244456] rounded-2xl p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Permen KP 59/2020 Zonasi
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-floor font-bold uppercase tracking-wider"
                                      :class="{
                                          'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40': plan.permen_kp.is_compliant,
                                          'bg-rose-500/20 text-rose-300 border border-rose-500/40': !plan.permen_kp.is_compliant
                                      }"
                                      x-text="plan.permen_kp.status_label || (plan.permen_kp.is_compliant ? 'PATUH' : 'PERLU TINJAUAN')">
                                </span>
                            </div>

                            <div class="p-3 rounded-xl bg-[#071B2B] border border-[#244456]/60 space-y-1">
                                <div class="text-[10px] text-slate-400">Alokasi Jalur Penangkapan Ikan:</div>
                                <div class="text-sm font-bold text-white flex items-center justify-between">
                                    <span x-text="plan.permen_kp.jalur_label || plan.permen_kp.jalur_target"></span>
                                    <span class="text-xs font-mono text-cyan-400" x-text="plan.permen_kp.jalur_desc || (plan.permen_kp.is_compliant ? 'Sesuai kelas tonase' : 'Melebihi batas jalur')"></span>
                                </div>
                            </div>

                            <p class="text-[11px] text-slate-300 leading-relaxed" x-text="plan.permen_kp.message"></p>
                            <p class="text-[10px] font-mono text-slate-400 border-t border-[#244456]/60 pt-2" x-text="plan.permen_kp.citation"></p>
                        </div>

                        <!-- Wave & Lunar Safety Advisory Card -->
                        <div class="bg-[#091F30] border border-[#244456] rounded-2xl p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                    </svg>
                                    Oseanografi & Fase Bulan
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-floor font-bold uppercase tracking-wider"
                                      :class="{
                                          'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40': plan.wave_safety.status === 'SAFE' || plan.wave_safety.status === 'AMAN',
                                          'bg-amber-500/20 text-amber-300 border border-amber-500/40': plan.wave_safety.status === 'CAUTION' || plan.wave_safety.status === 'WASPADA',
                                          'bg-rose-500/20 text-rose-300 border border-rose-500/40': plan.wave_safety.status === 'DANGER' || plan.wave_safety.status === 'BAHAYA GELOMBANG EKSTREM'
                                      }"
                                      x-text="plan.wave_safety.status_label || plan.wave_safety.status">
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div class="p-3 rounded-xl bg-[#071B2B] border border-[#244456]/60">
                                    <div class="text-[10px] text-slate-400">Tinggi Gelombang (SWH)</div>
                                    <div class="text-base font-black text-white font-mono mt-0.5">
                                        <span x-text="plan.wave_safety.wave_height_m"></span> <span class="text-xs text-slate-300 font-sans font-normal">meter</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        <span x-text="plan.wave_safety.advice || plan.wave_safety.message"></span>
                                    </div>
                                </div>
                                <div class="p-3 rounded-xl bg-[#071B2B] border border-[#244456]/60">
                                    <div class="text-[10px] text-slate-400">Fase Bulan (Lunar)</div>
                                    <div class="text-xs font-black text-white mt-0.5" x-text="plan.moon_phase.phase_name"></div>
                                    <div class="text-[10px] text-cyan-300 mt-0.5 font-mono">
                                        Iluminasi: <span x-text="plan.moon_phase.illumination_pct"></span>%
                                    </div>
                                </div>
                            </div>

                            <div class="text-[11px] text-slate-300 border-t border-[#244456]/60 pt-2">
                                <span class="text-slate-400">Tips Memancing Malam:</span>
                                <span x-text="plan.moon_phase.fishing_advisory"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Duration Feasibility, Vessel Size & Readiness Card -->
                    <div class="bg-[#091F30] border rounded-2xl p-4 sm:p-5 space-y-3"
                         :class="{
                             'border-[#244456]': (plan.readiness_status ?? 'SIAP_MELAUT') === 'SIAP_MELAUT',
                             'border-amber-500/50': (plan.readiness_status ?? 'SIAP_MELAUT') === 'SIAP_DENGAN_CATATAN',
                             'border-rose-500/60': (plan.readiness_status ?? 'SIAP_MELAUT') === 'TIDAK_DISARANKAN'
                         }">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Kelayakan Durasi (<span x-text="plan.trip_days ?? 1"></span> Hari), Kapasitas &amp; Kesiapan Kapal
                            </span>
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider"
                                      :class="(plan.duration_feasibility_status ?? 'LAYAK') === 'LAYAK' ? 'bg-slate-800 text-slate-300 border border-slate-700' : 'bg-amber-500/20 text-amber-300 border border-amber-500/40'"
                                      x-text="plan.duration_feasibility_status ?? 'LAYAK'">
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider"
                                      :class="{
                                          'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40': (plan.readiness_status ?? 'SIAP_MELAUT') === 'SIAP_MELAUT',
                                          'bg-amber-500/20 text-amber-300 border border-amber-500/40': (plan.readiness_status ?? 'SIAP_MELAUT') === 'SIAP_DENGAN_CATATAN',
                                          'bg-rose-500/20 text-rose-300 border border-rose-500/40': (plan.readiness_status ?? 'SIAP_MELAUT') === 'TIDAK_DISARANKAN'
                                      }"
                                      x-text="plan.readiness_status ?? 'SIAP_MELAUT'">
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                            <div class="p-2.5 rounded-xl bg-[#071B2B] border border-[#244456]/60">
                                <div class="text-[10px] text-slate-400">Jam Tangkap Efektif</div>
                                <div class="font-mono font-bold text-white mt-0.5"><span x-text="plan.effective_fishing_hours ?? 0"></span> jam</div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#071B2B] border border-[#244456]/60">
                                <div class="text-[10px] text-slate-400">Kapasitas Palka GT</div>
                                <div class="font-mono font-bold text-cyan-300 mt-0.5">
                                    <span x-text="(plan.max_hold_capacity_kg ?? 400).toLocaleString('id-ID')"></span> kg
                                    <span x-show="plan.is_hold_capped" class="ml-1 text-[9px] px-1 py-0.5 rounded bg-amber-500/20 text-amber-300">PENUH</span>
                                </div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#071B2B] border border-[#244456]/60">
                                <div class="text-[10px] text-slate-400">Daya Tahan / Kesiapan</div>
                                <div class="font-mono font-bold text-amber-300 mt-0.5">
                                    Maks <span x-text="plan.max_endurance_days ?? 2"></span> hr &bull; <span x-text="Math.round((plan.readiness_factor ?? 1) * 100)"></span>%
                                </div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#071B2B] border border-[#244456]/60">
                                <div class="text-[10px] text-slate-400">Total Biaya Operasional</div>
                                <div class="font-mono font-bold text-rose-300 mt-0.5" x-text="formatRupiah(plan.total_operational_cost_rp ?? plan.fuel.total_fuel_cost_rp)"></div>
                            </div>
                        </div>

                        <template x-if="(plan.readiness_notes ?? []).length > 0">
                            <ul class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-[11px] text-amber-200 space-y-1 list-disc list-inside">
                                <template x-for="(note, idx) in (plan.readiness_notes ?? [])" :key="idx">
                                    <li x-text="note"></li>
                                </template>
                            </ul>
                        </template>
                    </div>

                    <!-- Metric Row 3: HSI Catch & Economic Profit Projection -->
                    <div class="bg-gradient-to-br from-[#091F30] to-[#0D2B3E] border border-cyan-500/30 rounded-2xl p-5 space-y-4">
                        <div class="flex items-center justify-between border-b border-[#244456] pb-3">
                            <span class="text-xs font-bold text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Proyeksi Hasil Tangkapan & Margin Finansial
                                <span class="text-cyan-300 font-mono" x-text="plan.estimate_source === 'TEMPORAL_CPUE_CALIBRATED' ? '(CPUE Terkalibrasi)' : '(Skenario Formula HSI)'"></span>
                            </span>
                            <span class="text-floor font-mono text-cyan-300">
                                HSI Score: <strong x-text="plan.catch_projection.hsi_score"></strong> (<span x-text="plan.catch_projection.hsi_category || (plan.catch_projection.hsi_score >= 0.7 ? 'Tinggi' : (plan.catch_projection.hsi_score >= 0.55 ? 'Sedang' : 'Rendah'))"></span>)
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="p-3.5 rounded-xl bg-[#071B2B] border border-[#244456]">
                                <div class="text-[10px] text-slate-400">Estimasi Volume Tangkap</div>
                                <div class="text-xl font-black text-white font-mono mt-0.5">
                                    <span x-text="plan.catch_projection.projected_kg"></span> <span class="text-xs text-slate-300 font-sans font-normal">kg</span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-1 font-mono">
                                    Komoditas: <span class="text-cyan-400" x-text="plan.target_commodity"></span>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-xl bg-[#071B2B] border border-[#244456]">
                                <div class="text-[10px] text-slate-400">Estimasi Penerimaan Kotor</div>
                                <div class="text-base font-black text-emerald-400 font-mono mt-0.5">
                                    <span x-text="formatRupiah(plan.catch_projection.gross_revenue_rp)"></span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-1 font-mono">
                                    Harga acuan TPI: <span x-text="formatRupiah(plan.catch_projection.price_per_kg_rp ?? plan.catch_projection.benchmark_price_per_kg)"></span>/kg
                                </div>
                            </div>

                            <div class="p-3.5 rounded-xl bg-[#071B2B] border border-[#244456]">
                                <div class="text-[10px] text-slate-400">Estimasi Margin Laba Bersih</div>
                                <div class="text-base font-black font-mono mt-0.5"
                                     :class="plan.economics.net_profit_rp >= 0 ? 'text-cyan-400' : 'text-rose-400'">
                                    <span x-text="formatRupiah(plan.economics.net_profit_rp)"></span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-1 font-mono">
                                    ROI: <span class="font-bold text-white" x-text="plan.economics.roi_pct"></span>% &bull; Biaya Ops: <span class="text-slate-300" x-text="formatRupiah(plan.total_operational_cost_rp ?? plan.economics.total_operational_cost_rp ?? plan.fuel.total_fuel_cost_rp)"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Rincian Transparansi Hitungan -->
                        <div class="p-3.5 rounded-xl bg-[#071B2B]/80 border border-[#244456]/70 space-y-2.5 text-[11px]">
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#244456]/60 pb-2">
                                <span class="font-bold text-slate-200 uppercase tracking-wider text-[10px]">Rincian Transparansi Hitungan</span>
                                <span class="text-cyan-300 font-mono text-[10px]">
                                    <span x-text="plan.estimate_source"></span> &bull; <span x-text="plan.spatial_evidence_level"></span>
                                </span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 font-mono text-[11px]">
                                <div class="space-y-1 text-slate-300">
                                    <div class="text-[10px] font-sans font-semibold text-slate-400 uppercase">Komponen Biaya Operasional</div>
                                    <div class="flex justify-between">
                                        <span>BBM PP (+15% safety, <span x-text="plan.fuel.total_fuel_liters"></span> L):</span>
                                        <span class="text-white" x-text="formatRupiah(plan.fuel.total_fuel_cost_rp)"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>BBM Ops (<span x-text="plan.daily_ops_fuel_liters ?? 0"></span> L/hr &times; <span x-text="plan.trip_days ?? 1"></span> hr):</span>
                                        <span class="text-white" x-text="formatRupiah(plan.economics.operational_fuel_cost_rp ?? 0)"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Logistik (<span x-text="formatRupiah(plan.daily_provisions_cost_rp ?? plan.economics.daily_provisions_cost_rp ?? 150000)"></span>/hr &times; <span x-text="plan.trip_days ?? 1"></span> hr):</span>
                                        <span class="text-amber-300" x-text="formatRupiah(plan.provisions_cost_rp ?? 0)"></span>
                                    </div>
                                    <div class="flex justify-between border-t border-[#244456]/60 pt-1 font-bold text-rose-300">
                                        <span>Total Biaya Operasional:</span>
                                        <span x-text="formatRupiah(plan.total_operational_cost_rp ?? plan.economics.total_operational_cost_rp ?? plan.fuel.total_fuel_cost_rp)"></span>
                                    </div>
                                </div>
                                <div class="space-y-1 text-slate-300">
                                    <div class="text-[10px] font-sans font-semibold text-slate-400 uppercase">Rumus Skenario Tangkapan &amp; Margin</div>
                                    <div class="flex justify-between">
                                        <span>Basis GT &times; HSI &times; Musim:</span>
                                        <span class="text-white"><span x-text="plan.catch_projection.base_capacity_kg"></span> kg &times; <span x-text="Number(plan.catch_projection.hsi_score).toFixed(2)"></span> &times; <span x-text="plan.catch_projection.seasonal_factor"></span></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Pengali Durasi &times; Kesiapan:</span>
                                        <span class="text-cyan-300 font-bold"><span x-text="(plan.catch_projection.duration_factor ?? 1) + 'x'"></span> &times; <span x-text="(plan.readiness_factor ?? 1) + 'x'"></span></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Proyeksi (<span x-text="plan.catch_projection.projected_kg"></span> / maks <span x-text="plan.max_hold_capacity_kg ?? '-'"></span> kg):</span>
                                        <span class="text-emerald-400" x-text="formatRupiah(plan.catch_projection.gross_revenue_rp)"></span>
                                    </div>
                                    <div class="flex justify-between border-t border-[#244456]/60 pt-1 font-bold" :class="plan.economics.net_profit_rp >= 0 ? 'text-cyan-400' : 'text-rose-400'">
                                        <span>Margin Bersih (Kotor &minus; Ops):</span>
                                        <span x-text="formatRupiah(plan.economics.net_profit_rp)"></span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-[10px] text-slate-400 font-sans pt-1">
                                Catatan: Skenario formula bersifat simulasi perencanaan operasional, bukan jaminan hasil tangkapan aktual.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: RIWAYAT MELAUT SAYA                 -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'my_trips'" class="space-y-4" style="display: none;">
            <div class="bg-[#091F30] border border-[#244456] rounded-2xl p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-[#244456] pb-3">
                    <div>
                        <h2 class="text-base font-bold text-white">Riwayat Perencanaan Melaut Pribadi</h2>
                        <p class="text-xs text-slate-400">Daftar dokumen pelayaran yang telah Anda simpan dan diverifikasi sistem.</p>
                    </div>
                </div>

                @if ($myTrips->isEmpty())
                    <div class="text-center py-12 text-slate-400 text-xs">
                        Belum ada riwayat rencana melaut tersimpan. Gunakan kalkulator di atas untuk membuat rencana pertama Anda.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#071B2B] text-slate-400 uppercase font-mono text-floor border-b border-[#244456]">
                                <tr>
                                    <th class="px-4 py-3">No. Dokumen / Ref</th>
                                    <th class="px-4 py-3">Tgl Tolak</th>
                                    <th class="px-4 py-3">Kapal & GT</th>
                                    <th class="px-4 py-3">Pelabuhan Pangkalan</th>
                                    <th class="px-4 py-3">Jarak (NM)</th>
                                    <th class="px-4 py-3">BBM (L)</th>
                                    <th class="px-4 py-3">Proyeksi Tangkap</th>
                                    <th class="px-4 py-3">Margin Bersih</th>
                                    <th class="px-4 py-3">Status Permen KP</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#244456] text-slate-300">
                                @foreach ($myTrips as $trip)
                                    <tr class="hover:bg-[#0D2B3E] transition">
                                        <td class="px-4 py-3 font-mono font-bold text-cyan-400">{{ $trip->reference_no }}</td>
                                        <td class="px-4 py-3 font-mono">{{ $trip->departure_date->format('d M Y') }}</td>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-white">{{ $trip->vessel_name }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $trip->gt_class }}</div>
                                        </td>
                                        <td class="px-4 py-3">{{ $trip->port_name }}</td>
                                        <td class="px-4 py-3 font-mono">{{ $trip->distance_nm }} NM</td>
                                        <td class="px-4 py-3 font-mono text-amber-300">{{ $trip->total_fuel_liters }} L</td>
                                        <td class="px-4 py-3 font-mono">{{ $trip->projected_catch_kg }} kg ({{ $trip->target_commodity }})</td>
                                        <td class="px-4 py-3 font-mono font-semibold {{ $trip->net_profit_rp >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                            Rp {{ number_format($trip->net_profit_rp, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if (in_array($trip->permen_kp_status, ['COMPLIANT', 'COMPLIANT_WITHIN_LEGAL_BOUNDS'], true))
                                                <span class="px-2 py-0.5 rounded-full text-floor font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">PATUH</span>
                                            @elseif ($trip->permen_kp_status === 'WARNING_OUT_OF_LEGAL_RANGE')
                                                <span class="px-2 py-0.5 rounded-full text-floor font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">&gt;12 NM</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-floor font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">PELANGGARAN</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('dashboard.trip-planner.pdf', $trip->reference_no) }}" target="_blank"
                                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-cyan-500/15 hover:bg-cyan-500/30 text-cyan-300 text-floor font-semibold border border-cyan-500/30 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                PDF Slip
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: BUKU LOG PELABUHAN (SYAHBANDAR/ADM) -->
        <!-- ========================================== -->
        @if ($adminLogbook !== null)
        <div x-show="activeTab === 'admin_logbook'" class="space-y-4" style="display: none;">
            <div class="bg-[#091F30] border border-[#244456] rounded-2xl p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-[#244456] pb-3">
                    <div>
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            Buku Log Keberangkatan Pelabuhan Terpadu
                        </h2>
                        <p class="text-xs text-slate-400">Khusus Otoritas Kesyahbandaran & Pengawas Perikanan Pelabuhan.</p>
                    </div>
                    <span class="text-xs font-mono text-amber-400 px-3 py-1 rounded-lg bg-amber-500/10 border border-amber-500/30">
                        Otoritas Port-Scoped Active
                    </span>
                </div>

                @if ($adminLogbook->isEmpty())
                    <div class="text-center py-12 text-slate-400 text-xs">
                        Belum ada rekaman keberangkatan di pelabuhan pangkalan ini.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#071B2B] text-slate-400 uppercase font-mono text-floor border-b border-[#244456]">
                                <tr>
                                    <th class="px-4 py-3">No. Dokumen</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Pelabuhan</th>
                                    <th class="px-4 py-3">Kapal & Nakhoda</th>
                                    <th class="px-4 py-3">Tonase & Alat Tangkap</th>
                                    <th class="px-4 py-3">Titik Koordinat Sasaran</th>
                                    <th class="px-4 py-3">Jarak Nautika</th>
                                    <th class="px-4 py-3">Kepatuhan Permen KP</th>
                                    <th class="px-4 py-3">Status Gelombang</th>
                                    <th class="px-4 py-3 text-right">Verifikasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#244456] text-slate-300">
                                @foreach ($adminLogbook as $log)
                                    <tr class="hover:bg-[#0D2B3E] transition">
                                        <td class="px-4 py-3 font-mono font-bold text-cyan-400">{{ $log->reference_no }}</td>
                                        <td class="px-4 py-3 font-mono">{{ $log->departure_date->format('d M Y') }}</td>
                                        <td class="px-4 py-3 font-semibold text-white">{{ $log->port_name }}</td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-white">{{ $log->vessel_name }}</div>
                                            <div class="text-[10px] text-slate-400">Nakhoda: {{ $log->skipper_name }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-mono text-amber-300">{{ $log->gt_class }}</span>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $log->gear_code }}</div>
                                        </td>
                                        <td class="px-4 py-3 font-mono text-[11px] text-slate-300">{{ $log->target_dms }}</td>
                                        <td class="px-4 py-3 font-mono">{{ $log->distance_nm }} NM</td>
                                        <td class="px-4 py-3">
                                            @if (in_array($log->permen_kp_status, ['COMPLIANT', 'COMPLIANT_WITHIN_LEGAL_BOUNDS'], true))
                                                <span class="px-2 py-0.5 rounded-full text-floor font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">PATUH</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-floor font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">PERLU TINJAUAN</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                $waveBadgeClass = match($log->wave_status) {
                                                    'AMAN', 'SAFE' => 'bg-emerald-500/20 text-emerald-300',
                                                    'WASPADA', 'CAUTION' => 'bg-amber-500/20 text-amber-300',
                                                    default => 'bg-rose-500/20 text-rose-300',
                                                };
                                                $waveBadgeText = match($log->wave_status) {
                                                    'AMAN', 'SAFE' => 'AMAN',
                                                    'WASPADA', 'CAUTION' => 'WASPADA',
                                                    'DANGER' => 'BAHAYA',
                                                    default => $log->wave_status,
                                                };
                                            @endphp
                                            <span class="px-2 py-0.5 rounded-full text-floor font-bold {{ $waveBadgeClass }}">
                                                {{ $waveBadgeText }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('dashboard.trip-planner.pdf', $log->reference_no) }}" target="_blank"
                                               class="inline-flex items-center gap-1 px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-cyan-300 text-floor font-semibold border border-slate-700">
                                                Cetak Dokumen
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        @endif

    </div>

    <!-- Alpine.js Trip Planner Logic -->
    <script>
        function tripPlanner(config) {
            return {
                activeTab: 'planner',
                form: { trip_days: 1, vessel_readiness: 'OPTIMAL', ...config.initialTarget },
                plan: { ...config.initialPlan },
                calcUrl: config.calcUrl,
                csrf: config.csrf,
                isLoading: false,

                async recalculate() {
                    this.isLoading = true;
                    try {
                        const response = await fetch(this.calcUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.form)
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.plan = data;
                        }
                    } catch (err) {
                        console.error('Kalkulasi gagal:', err);
                    } finally {
                        this.isLoading = false;
                    }
                },

                formatRupiah(num) {
                    return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
                }
            };
        }
    </script>
</x-layouts.dashboard>
