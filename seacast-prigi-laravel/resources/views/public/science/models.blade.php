<x-layouts.public title="Model Prediksi Komoditas TCT & Nilai Evaluasi — SEACAST">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Kerangka Sains', 'url' => route('science')], ['label' => 'Model Prediksi Komoditas', 'url' => null]]" />
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Model Prediksi Komoditas TCT &amp; Evaluasi Proxy
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-3xl leading-relaxed">
                Ringkasan parameter kesesuaian habitat komoditas TCT: Cakalang (<em>Katsuwonus pelamis</em>), Tuna Sirip Kuning (<em>Thunnus albacares</em>), dan Tongkol Krai (<em>Auxis thazard</em>). Nilai AUC/TSS di bawah berasal dari evaluasi titik proxy lingkungan (<code>PROXY_HSI</code>), bukan konfirmasi lokasi tangkapan operasional.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.card title="Cakalang (Skipjack)">
                <div class="space-y-3 text-xs">
                    <span class="text-cyan-600 dark:text-cyan-400 font-serif italic text-xs block">Katsuwonus pelamis</span>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071B2B] font-mono space-y-1">
                        <div class="flex justify-between"><span class="text-slate-500">Proxy ROC-AUC:</span><span class="font-bold text-cyan-600 dark:text-cyan-400">0.795*</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Proxy TSS:</span><span class="font-bold text-slate-700 dark:text-slate-300">0.630*</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Ambang Hotspot HSI:</span><span class="font-bold text-emerald-600 dark:text-emerald-400">&ge; 0.70</span></div>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed">Tuna epipelagis dengan bobot ensemble provisional (0.320 / 0.355 / 0.325) dan preferensi suhu 27–29,5°C di dekat front upwelling selatan Jawa.</p>
                </div>
            </x-ui.card>

            <x-ui.card title="Tuna Sirip Kuning (Yellowfin)">
                <div class="space-y-3 text-xs">
                    <span class="text-amber-600 dark:text-amber-400 font-serif italic text-xs block">Thunnus albacares</span>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071B2B] font-mono space-y-1">
                        <div class="flex justify-between"><span class="text-slate-500">Proxy ROC-AUC:</span><span class="font-bold text-amber-600 dark:text-amber-400">0.784*</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Proxy TSS:</span><span class="font-bold text-slate-700 dark:text-slate-300">0.606*</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Ambang Hotspot HSI:</span><span class="font-bold text-emerald-600 dark:text-emerald-400">&ge; 0.70</span></div>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed">Tuna oseanik bernilai komersial tinggi dengan bobot provisional (0.295 / 0.365 / 0.340) di sepanjang batas tubir benua dan gradien termal tajam.</p>
                </div>
            </x-ui.card>

            <x-ui.card title="Tongkol Krai (Frigate Tuna)">
                <div class="space-y-3 text-xs">
                    <span class="text-sky-600 dark:text-sky-400 font-serif italic text-xs block">Auxis thazard</span>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071B2B] font-mono space-y-1">
                        <div class="flex justify-between"><span class="text-slate-500">Proxy ROC-AUC:</span><span class="font-bold text-sky-600 dark:text-sky-400">0.892*</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Proxy TSS:</span><span class="font-bold text-slate-700 dark:text-slate-300">0.752*</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Ambang Hotspot HSI:</span><span class="font-bold text-emerald-600 dark:text-emerald-400">&ge; 0.70</span></div>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed">Komoditas neritik pesisir dengan ensemble SDM provisional (0.332 / 0.335 / 0.333) yang dilatih pada sampel proxy oseanografi 2015–2025 WPPNRI 573 (SST 27–30,5°C, kedalaman paparan &le;150 m).</p>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.public>
