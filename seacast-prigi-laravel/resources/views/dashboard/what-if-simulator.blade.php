<x-layouts.dashboard title="What-If Simulator: Rute Jelajah & Iklim — SEACAST">
    <div 
        x-data="{
            // Vessel economics state
            vesselClass: '< 10 GT',
            targetDistanceNm: 15,
            targetSpecies: 'cakalang',
            solarPrice: {{ $defaultSolarPrice }},
            specs: {{ Js::from($specs) }},
            get currentSpec() {
                return this.specs[this.vesselClass] || this.specs['< 10 GT'];
            },
            get roundTripNm() {
                return this.targetDistanceNm * 2;
            },
            get travelTimeHours() {
                return (this.roundTripNm / this.currentSpec.speed_knots).toFixed(1);
            },
            get fuelLiters() {
                return Math.round(this.roundTripNm * this.currentSpec.fuel_liters_per_nm);
            },
            get fuelCostRp() {
                return this.fuelLiters * this.solarPrice;
            },
            get tunaPricePerKg() {
                const prices = { cakalang: 22000, tuna_sirip_kuning: 45000, tongkol_krai: 28000 };
                return prices[this.targetSpecies] || 22000;
            },
            get grossRevenueRp() {
                return this.currentSpec.base_catch_kg * this.tunaPricePerKg;
            },
            get netProfitRp() {
                return this.grossRevenueRp - this.fuelCostRp;
            },
            get isOutOfRange() {
                return this.targetDistanceNm > this.currentSpec.max_range_nm;
            },

            // Climate perturbation state
            sstDelta: 0.0,
            chlaMult: 1.0,
            simLoading: false,
            simResult: null,

            async runClimateSim() {
                this.simLoading = true;
                try {
                    const res = await fetch('/api/simulate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                        },
                        body: JSON.stringify({
                            sstDelta: parseFloat(this.sstDelta),
                            chlaMultiplier: parseFloat(this.chlaMult),
                            limit: 500
                        })
                    });
                    if (res.ok) {
                        const json = await res.json();
                        this.simResult = json.data;
                    }
                } catch (e) {
                    console.error('Simulation error', e);
                } finally {
                    this.simLoading = false;
                }
            }
        }"
        x-init="runClimateSim()"
        class="space-y-8 max-w-7xl mx-auto"
    >
        <!-- Header -->
        <div class="pb-6 border-b border-[#16445A]">
            <div class="flex items-center gap-2 text-xs font-bold text-cyan-400 mb-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span>Kalkulator Operasional & Skenario Iklim</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                What-if Simulator: Rute Jelajah, Biaya Solar & Iklim
            </h1>
            <p class="text-xs text-[#A4BAC8] mt-1">
                Hitung estimasi kelayakan ekonomi penangkapan tuna berdasarkan kapasitas kapal dan simulasikan dampak pemanasan suhu laut.
            </p>
        </div>

        <!-- Section 1: Vessel Operational Economics Calculator -->
        <div class="bg-[#0D2B3E] rounded-3xl border border-[#16445A] p-6 lg:p-8 space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-[#16445A]">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    <span>1. Kalkulator Biaya Operasional & Keuntungan Armada</span>
                </h2>
                <span class="text-xs font-mono text-cyan-300">Basis PPN Prigi &amp; PPS Cilacap</span>
            </div>

            <!-- Inputs Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <!-- Vessel Class -->
                <div class="space-y-2">
                    <label class="font-bold text-white uppercase tracking-wider text-[11px]">Kelas Armada Kapal</label>
                    <select 
                        x-model="vesselClass" 
                        class="w-full bg-[#071B2B] border border-[#16445A] rounded-xl px-3 py-2 text-white font-mono focus:border-cyan-400 focus:outline-none"
                    >
                        <option value="< 10 GT">&lt; 10 GT (Perahu Tradisional / Sekoci)</option>
                        <option value="10 - 20 GT">10 - 20 GT (Kapal Motor Menengah)</option>
                        <option value="20 - 30 GT">20 - 30 GT (Purse Seine / Longline)</option>
                    </select>
                </div>

                <!-- Target Species -->
                <div class="space-y-2">
                    <label class="font-bold text-white uppercase tracking-wider text-[11px]">Target Tangkapan Tuna</label>
                    <select 
                        x-model="targetSpecies" 
                        class="w-full bg-[#071B2B] border border-[#16445A] rounded-xl px-3 py-2 text-white font-mono focus:border-cyan-400 focus:outline-none"
                    >
                        <option value="cakalang">Cakalang (Rp 22.000 / kg)</option>
                        <option value="tuna_sirip_kuning">Tuna Sirip Kuning (Rp 45.000 / kg)</option>
                        <option value="tongkol_krai">Tongkol Krai (Rp 28.000 / kg)</option>
                    </select>
                </div>

                <!-- Target Distance -->
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <label class="font-bold text-white uppercase tracking-wider text-[11px]">Jarak Fishing Ground</label>
                        <span class="font-mono text-cyan-300 font-bold" x-text="targetDistanceNm + ' NM'"></span>
                    </div>
                    <input 
                        type="range" 
                        min="2" 
                        max="60" 
                        x-model.number="targetDistanceNm" 
                        class="w-full h-2 bg-[#071B2B] rounded-lg appearance-none cursor-pointer accent-cyan-400"
                    >
                    <div class="flex justify-between text-[10px] text-[#A4BAC8] font-mono">
                        <span>2 NM</span>
                        <span x-text="'Batas Aman: ' + currentSpec.max_range_nm + ' NM'"></span>
                        <span>60 NM</span>
                    </div>
                </div>

                <!-- Subsidized Solar Price -->
                <div class="space-y-2">
                    <label class="font-bold text-white uppercase tracking-wider text-[11px]">Harga Solar Subsidi (Rp/L)</label>
                    <input 
                        type="number" 
                        x-model.number="solarPrice" 
                        class="w-full bg-[#071B2B] border border-[#16445A] rounded-xl px-3 py-2 text-white font-mono focus:border-cyan-400 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Danger Out-of-Range Warning Alert -->
            <template x-if="isOutOfRange">
                <div class="p-4 rounded-xl bg-rose-500/15 border border-rose-500/40 text-rose-300 text-xs flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <strong class="font-bold">PERINGATAN KESELAMATAN BERLAYAR:</strong> Jarak target (<span x-text="targetDistanceNm"></span> NM) melebihi radius operasional aman kapal kelas <span x-text="vesselClass"></span> (maksimal <span x-text="currentSpec.max_range_nm"></span> NM). Risiko kehabisan bahan bakar di tengah laut!
                    </div>
                </div>
            </template>

            <!-- Results Summary Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                <div class="p-4 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-1">
                    <span class="text-[#A4BAC8] block">Jarak PP</span>
                    <span class="text-xl font-bold font-mono text-white block" x-text="roundTripNm + ' NM'"></span>
                    <span class="text-[10px] text-slate-400" x-text="travelTimeHours + ' Jam Perjalanan'"></span>
                </div>

                <div class="p-4 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-1">
                    <span class="text-[#A4BAC8] block">Konsumsi Solar</span>
                    <span class="text-xl font-bold font-mono text-amber-300 block" x-text="fuelLiters + ' Liter'"></span>
                    <span class="text-[10px] text-slate-400">Total Pulang-Pergi</span>
                </div>

                <div class="p-4 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-1">
                    <span class="text-[#A4BAC8] block">Biaya Solar</span>
                    <span class="text-xl font-bold font-mono text-rose-300 block" x-text="'Rp ' + fuelCostRp.toLocaleString('id-ID')"></span>
                    <span class="text-[10px] text-slate-400">Pengeluaran BBM</span>
                </div>

                <div class="p-4 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-1">
                    <span class="text-[#A4BAC8] block">Estimasi Tangkapan</span>
                    <span class="text-xl font-bold font-mono text-cyan-300 block" x-text="currentSpec.base_catch_kg + ' Kg'"></span>
                    <span class="text-[10px] text-slate-400">Rerata CPUE Kapal</span>
                </div>

                <div class="p-4 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-1">
                    <span class="text-[#A4BAC8] block">Pendapatan Kotor</span>
                    <span class="text-xl font-bold font-mono text-sky-300 block" x-text="'Rp ' + grossRevenueRp.toLocaleString('id-ID')"></span>
                    <span class="text-[10px] text-slate-400">Nilai Jual Tangkapan</span>
                </div>

                <div class="p-4 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-1" :class="netProfitRp > 0 ? 'border-emerald-500/40 bg-emerald-950/20' : 'border-rose-500/40 bg-rose-950/20'">
                    <span class="text-[#A4BAC8] block">Laba Bersih Trip</span>
                    <span class="text-xl font-bold font-mono block" :class="netProfitRp > 0 ? 'text-emerald-400' : 'text-rose-400'" x-text="'Rp ' + netProfitRp.toLocaleString('id-ID')"></span>
                    <span class="text-[10px]" :class="netProfitRp > 0 ? 'text-emerald-300' : 'text-rose-300'" x-text="netProfitRp > 0 ? 'Margin Surplus' : 'Defisit BBM'"></span>
                </div>
            </div>
        </div>

        <!-- Section 2: Climate Perturbation Simulator -->
        <div class="bg-[#0D2B3E] rounded-3xl border border-[#16445A] p-6 lg:p-8 space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-[#16445A]">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>2. Simulator Perturbasi Biofisik & Pemanasan Laut</span>
                </h2>
                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        @click="sstDelta = 0.0; chlaMult = 1.0; runClimateSim()"
                        class="text-xs text-slate-400 hover:text-white px-2 py-1 rounded bg-[#071B2B] border border-[#16445A]"
                    >
                        Reset Normal
                    </button>
                    <button 
                        type="button" 
                        @click="runClimateSim()"
                        class="px-4 py-1.5 rounded-xl bg-cyan-500 hover:bg-cyan-600 text-slate-950 font-black text-xs transition-all flex items-center gap-1.5"
                    >
                        <template x-if="simLoading">
                            <span class="animate-spin w-3 h-3 border-2 border-slate-950 border-t-transparent rounded-full"></span>
                        </template>
                        <span>Simulasikan Pergeseran</span>
                    </button>
                </div>
            </div>

            <!-- Sliders -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div class="p-5 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-white uppercase tracking-wider text-[11px]">Anomali Suhu Muka Laut (&Delta;SST)</span>
                        <span class="font-mono text-amber-300 font-bold text-sm" x-text="(sstDelta >= 0 ? '+' : '') + sstDelta + ' °C'"></span>
                    </div>
                    <input 
                        type="range" 
                        min="-2.0" 
                        max="3.0" 
                        step="0.5" 
                        x-model.number="sstDelta" 
                        @change="runClimateSim()"
                        class="w-full h-2 bg-[#0D2B3E] rounded-lg appearance-none cursor-pointer accent-amber-400"
                    >
                    <div class="flex justify-between text-[10px] text-[#A4BAC8] font-mono">
                        <span>-2.0 °C (Upwelling Ekstrem)</span>
                        <span>0.0 °C (Baseline)</span>
                        <span>+3.0 °C (El Niño Kuat)</span>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-white uppercase tracking-wider text-[11px]">Faktor Pengali Klorofil-a</span>
                        <span class="font-mono text-emerald-300 font-bold text-sm" x-text="chlaMult + 'x'"></span>
                    </div>
                    <input 
                        type="range" 
                        min="0.5" 
                        max="2.0" 
                        step="0.1" 
                        x-model.number="chlaMult" 
                        @change="runClimateSim()"
                        class="w-full h-2 bg-[#0D2B3E] rounded-lg appearance-none cursor-pointer accent-emerald-400"
                    >
                    <div class="flex justify-between text-[10px] text-[#A4BAC8] font-mono">
                        <span>0.5x (Nutrien Rendah)</span>
                        <span>1.0x (Baseline)</span>
                        <span>2.0x (Ledakan Plankton)</span>
                    </div>
                </div>
            </div>

            <!-- Simulation Output -->
            <template x-if="simResult">
                <div class="space-y-4 pt-4 border-t border-[#16445A]">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-mono">
                        <div class="p-4 rounded-xl bg-[#071B2B] border border-[#16445A]">
                            <span class="text-[#A4BAC8] block text-[11px]">Hotspot Baseline</span>
                            <span class="text-xl font-bold text-white block mt-1" x-text="simResult.summary.baseline_hotspots + ' Sel'"></span>
                        </div>
                        <div class="p-4 rounded-xl bg-[#071B2B] border border-[#16445A]">
                            <span class="text-[#A4BAC8] block text-[11px]">Hotspot Hasil Simulasi</span>
                            <span class="text-xl font-bold text-cyan-300 block mt-1" x-text="simResult.summary.simulated_hotspots + ' Sel'"></span>
                        </div>
                        <div class="p-4 rounded-xl bg-[#071B2B] border border-[#16445A]">
                            <span class="text-[#A4BAC8] block text-[11px]">Perubahan Persentase</span>
                            <span class="text-xl font-bold block mt-1" :class="simResult.summary.hotspot_delta_pct >= 0 ? 'text-emerald-400' : 'text-rose-400'" x-text="(simResult.summary.hotspot_delta_pct >= 0 ? '+' : '') + simResult.summary.hotspot_delta_pct + '%'"></span>
                        </div>
                    </div>

                    <!-- DOM Redistribution -->
                    <div class="p-4 rounded-xl bg-[#071B2B] border border-[#16445A] space-y-2">
                        <span class="text-xs font-bold text-white uppercase tracking-wider font-mono block">
                            Redistribusi Zona DOM Hasil Skenario:
                        </span>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-xs font-mono">
                            <div class="p-2 rounded bg-[#091F30] border border-[#16445A] text-center">
                                <span class="text-sky-400 font-bold block">Z1 (PFG Inshore)</span>
                                <span class="text-white" x-text="simResult.summary.zones.Z1.count + ' Sel (' + simResult.summary.zones.Z1.pct + '%)'"></span>
                            </div>
                            <div class="p-2 rounded bg-[#091F30] border border-[#16445A] text-center">
                                <span class="text-blue-400 font-bold block">Z2 (PFG Offshore)</span>
                                <span class="text-white" x-text="simResult.summary.zones.Z2.count + ' Sel (' + simResult.summary.zones.Z2.pct + '%)'"></span>
                            </div>
                            <div class="p-2 rounded bg-[#091F30] border border-[#16445A] text-center">
                                <span class="text-cyan-400 font-bold block">Z3 (Feeding Core)</span>
                                <span class="text-white" x-text="simResult.summary.zones.Z3.count + ' Sel (' + simResult.summary.zones.Z3.pct + '%)'"></span>
                            </div>
                            <div class="p-2 rounded bg-[#091F30] border border-[#16445A] text-center">
                                <span class="text-purple-400 font-bold block">Z4 (Nursery)</span>
                                <span class="text-white" x-text="simResult.summary.zones.Z4.count + ' Sel (' + simResult.summary.zones.Z4.pct + '%)'"></span>
                            </div>
                            <div class="p-2 rounded bg-[#091F30] border border-[#16445A] text-center">
                                <span class="text-rose-400 font-bold block">Z5 (Bycatch)</span>
                                <span class="text-white" x-text="simResult.summary.zones.Z5.count + ' Sel (' + simResult.summary.zones.Z5.pct + '%)'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</x-layouts.dashboard>

