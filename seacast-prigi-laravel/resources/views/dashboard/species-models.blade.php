<x-layouts.dashboard title="Diagnostik Model SDM Ensemble — SEACAST">
    <div 
        x-data="{ 
            selectedSp: 'cakalang',
            species: {{ Js::from($species) }},
            get current() {
                return this.species[this.selectedSp] || this.species['cakalang'];
            }
        }"
        class="space-y-8 max-w-7xl mx-auto"
    >
        <!-- Header -->
        <div class="pb-6 border-b border-[#16445A]">
            <div class="flex items-center gap-2 text-xs font-bold text-cyan-400 mb-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Validasi Metodologi & Ekologi Kuantitatif</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Diagnostik Model Spesies Ensemble (SDM)
                    </h1>
                    <p class="text-xs text-[#A4BAC8] mt-1">
                        Evaluasi diskriminasi habitat (MaxEnt + Random Forest + GLM) dan bobot kontribusi biofisik oseanografi.
                    </p>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#0D2B3E] border border-[#16445A] text-xs font-mono text-cyan-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>10-Fold Spatial Cross Validation</span>
                </div>
            </div>
        </div>

        <!-- 3 Species Selector Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <template x-for="(sp, key) in species" :key="key">
                <button
                    type="button"
                    @click="selectedSp = key"
                    :class="selectedSp === key 
                        ? 'bg-[#0D2B3E] border-cyan-400 ring-1 ring-cyan-400/30' 
                        : 'bg-[#071B2B] border-[#16445A] hover:bg-[#0D2B3E]/60'"
                    class="p-5 rounded-2xl border text-left transition-all space-y-3"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-white" x-text="sp.name"></span>
                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-cyan-500/15 text-cyan-300 border border-cyan-500/30" x-text="'AUC ' + sp.auc"></span>
                    </div>
                    <p class="text-xs text-cyan-400 italic font-serif" x-text="sp.latin"></p>
                    <div class="pt-2 border-t border-[#16445A] flex items-center justify-between text-xs font-mono text-[#A4BAC8]">
                        <span>TSS: <strong class="text-white" x-text="sp.tss"></strong></span>
                        <span>Titik Kehadiran: <strong class="text-white" x-text="sp.presence_points"></strong></span>
                    </div>
                </button>
            </template>
        </div>

        <!-- Selected Species Model Metrics -->
        <div class="bg-[#0D2B3E] rounded-3xl border border-[#16445A] p-6 lg:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#16445A]">
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold text-white" x-text="current.name"></h2>
                        <span class="text-sm text-cyan-400 italic font-serif" x-text="'(' + current.latin + ')'"></span>
                    </div>
                    <p class="text-xs text-[#A4BAC8] mt-2 leading-relaxed max-w-3xl" x-text="current.description"></p>
                </div>

                <div class="bg-[#071B2B] p-3 rounded-xl border border-[#16445A] font-mono text-xs text-right shrink-0">
                    <span class="text-[11px] text-[#A4BAC8] block">Ambang Batas Hotspot Optimal</span>
                    <span class="text-base font-bold text-cyan-300" x-text="'HSI >= ' + current.opt_threshold"></span>
                </div>
            </div>

            <!-- Model Evaluation Scorecards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs font-mono">
                <div class="p-4 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-1">
                    <span class="text-[11px] text-[#A4BAC8] block uppercase">Ensemble ROC-AUC</span>
                    <span class="text-2xl font-bold text-emerald-400 block" x-text="current.auc"></span>
                    <span class="text-[10px] text-emerald-300">Diskriminasi Sangat Baik</span>
                </div>

                <div class="p-4 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-1">
                    <span class="text-[11px] text-[#A4BAC8] block uppercase">True Skill Statistic (TSS)</span>
                    <span class="text-2xl font-bold text-cyan-300 block" x-text="current.tss"></span>
                    <span class="text-[10px] text-cyan-200">Akurasi Validasi Silang</span>
                </div>

                <div class="p-4 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-1">
                    <span class="text-[11px] text-[#A4BAC8] block uppercase">Target PRD Baseline</span>
                    <span class="text-2xl font-bold text-sky-300 block" x-text="current.target_prd"></span>
                    <span class="text-[10px] text-slate-400">Spesifikasi Target</span>
                </div>

                <div class="p-4 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-1">
                    <span class="text-[11px] text-[#A4BAC8] block uppercase">Titik Observasi GPS</span>
                    <span class="text-2xl font-bold text-white block" x-text="current.presence_points"></span>
                    <span class="text-[10px] text-slate-400">Logbook PPN Prigi</span>
                </div>
            </div>

            <!-- Ensemble Algorithm Weights -->
            <div class="p-5 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-4">
                <span class="text-xs font-bold text-white uppercase tracking-wider font-mono block">
                    Bobot Kombinasi Algoritma Ensemble (Weighted Averaging):
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-mono">
                    <div class="p-3 rounded-xl bg-[#091F30] border border-[#16445A] flex justify-between items-center">
                        <div>
                            <span class="text-white font-bold block">MaxEnt</span>
                            <span class="text-[10px] text-[#A4BAC8]">Maximum Entropy</span>
                        </div>
                        <span class="text-sm font-bold text-cyan-400" x-text="(current.weights.maxent * 100).toFixed(1) + '%'"></span>
                    </div>

                    <div class="p-3 rounded-xl bg-[#091F30] border border-[#16445A] flex justify-between items-center">
                        <div>
                            <span class="text-white font-bold block">Random Forest</span>
                            <span class="text-[10px] text-[#A4BAC8]">Non-linear Trees</span>
                        </div>
                        <span class="text-sm font-bold text-emerald-400" x-text="(current.weights.rf * 100).toFixed(1) + '%'"></span>
                    </div>

                    <div class="p-3 rounded-xl bg-[#091F30] border border-[#16445A] flex justify-between items-center">
                        <div>
                            <span class="text-white font-bold block">GLM</span>
                            <span class="text-[10px] text-[#A4BAC8]">Logistic Regression</span>
                        </div>
                        <span class="text-sm font-bold text-indigo-400" x-text="(current.weights.glm * 100).toFixed(1) + '%'"></span>
                    </div>
                </div>
            </div>

            <!-- Environmental Variable Importance Bars -->
            <div class="p-5 rounded-2xl bg-[#071B2B] border border-[#16445A] space-y-4">
                <span class="text-xs font-bold text-white uppercase tracking-wider font-mono block">
                    Kontribusi Variabel Oseanografi & Preferensi Relung:
                </span>
                <div class="space-y-3">
                    <template x-for="(v, idx) in current.variables" :key="idx">
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between items-center font-mono">
                                <span class="text-white font-semibold" x-text="v.name"></span>
                                <div class="flex items-center gap-3">
                                    <span class="text-cyan-400 text-[11px]" x-text="'Rentang Ideal: ' + v.ideal"></span>
                                    <span class="text-white font-bold" x-text="v.pct + '%'"></span>
                                </div>
                            </div>
                            <div class="w-full h-2 bg-[#091F30] rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-cyan-500 to-sky-400 rounded-full" :style="{ width: v.pct + '%' }"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>

