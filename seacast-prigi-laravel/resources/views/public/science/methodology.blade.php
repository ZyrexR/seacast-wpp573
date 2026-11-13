<x-layouts.public title="Metodologi Ensemble SDM — SEACAST">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        <!-- Header -->
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Kerangka Sains', 'url' => route('science')], ['label' => 'Metodologi Ensemble SDM', 'url' => null]]" />
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Metodologi Ensemble Species Distribution Modeling (SDM)
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-3xl leading-relaxed">
                Pendekatan multi-model ensemble memadukan keunggulan diskriminasi spasial Maximum Entropy (MaxEnt), robustitas non-linear Random Forest (RF), dan stabilitas Generalized Linear Model (GLM) untuk memprediksi Indeks Kesesuaian Habitat (*Habitat Suitability Index* / HSI).
            </p>
        </div>

        <!-- 3 Pillars of Ensemble -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.card title="1. Maximum Entropy (MaxEnt)">
                <div class="space-y-2 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    <span class="font-bold text-cyan-600 dark:text-cyan-400 font-mono block">Presence-Only Modeling</span>
                    <p>Sangat efektif mengestimasi distribusi probabilitas habitat dari titik koordinat GPS pendaratan tanpa membutuhkan data absensi semu yang bias.</p>
                </div>
            </x-ui.card>

            <x-ui.card title="2. Random Forest (RF)">
                <div class="space-y-2 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono block">Non-Linear Tree Ensemble</span>
                    <p>Mendeteksi interaksi kompleks antara variabel oseanografi (misalnya pertemuan suhu dingin upwelling dan klorofil tinggi) tanpa risiko overfitting.</p>
                </div>
            </x-ui.card>

            <x-ui.card title="3. Generalized Linear Model (GLM)">
                <div class="space-y-2 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    <span class="font-bold text-indigo-600 dark:text-indigo-400 font-mono block">Parametric Baseline</span>
                    <p>Menjaga stabilitas generalisasi model pada kondisi lingkungan ekstrem di luar sampel observasi historis dan membatasi ekstrapolasi liar.</p>
                </div>
            </x-ui.card>
        </div>

        <!-- 10-Fold Spatial Cross Validation -->
        <x-ui.card title="Validasi Silang Spasial (10-Fold Spatial Cross-Validation)">
            <div class="space-y-4 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                <p>
                    Untuk menghindari autokorelasi spasial semu (*spatial autocorrelation*), data titik tangkapan dibagi ke dalam 10 blok spasial independen. Model dievaluasi menggunakan metrik diskriminasi area di bawah kurva ROC (*Area Under the Curve* / ROC-AUC) dan *True Skill Statistic* (TSS).
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 font-mono">
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071B2B] border border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500 block text-[11px]">Cakalang:</span>
                        <span class="text-base font-bold text-cyan-600 dark:text-cyan-400">AUC 0.892 &bull; TSS 0.684</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071B2B] border border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500 block text-[11px]">Tuna Sirip Kuning:</span>
                        <span class="text-base font-bold text-emerald-600 dark:text-emerald-400">AUC 0.874 &bull; TSS 0.648</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071B2B] border border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500 block text-[11px]">Tongkol Krai:</span>
                        <span class="text-base font-bold text-sky-600 dark:text-sky-400">AUC 0.908 &bull; TSS 0.712</span>
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>
</x-layouts.public>

