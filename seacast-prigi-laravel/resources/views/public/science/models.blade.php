<x-layouts.public title="Model Prediksi Spesies & Nilai AUC — SEACAST">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        <!-- Header -->
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Kerangka Sains', 'url' => route('science')], ['label' => 'Model Prediksi Spesies', 'url' => null]]" />
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Model Prediksi Spesies Tuna & Evaluasi Akurasi
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-3xl leading-relaxed">
                Evaluasi performa model prediktif Cakalang (*Katsuwonus pelamis*), Madidihang (*Thunnus albacares*), dan Albakor (*Thunnus alalunga*). Nilai diskriminasi ROC-AUC, bobot ensemble, dan kontribusi variabel oseanografi.
            </p>
        </div>

        <!-- 3 Tuna Species Scorecards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.card title="Cakalang (Skipjack)">
                <div class="space-y-3 text-xs">
                    <span class="text-cyan-600 dark:text-cyan-400 font-serif italic text-xs block">Katsuwonus pelamis</span>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071B2B] font-mono space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-500">ROC-AUC:</span>
                            <span class="font-bold text-cyan-600 dark:text-cyan-400">0.892</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">TSS:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">0.684</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Ambang HSI:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">&ge; 0.75</span>
                        </div>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                        Tuna epipelagis lincah dengan preferensi suhu 27–29,5°C dan klorofil-a tinggi di dekat front upwelling pesisir Prigi.
                    </p>
                </div>
            </x-ui.card>

            <x-ui.card title="Madidihang (Yellowfin)">
                <div class="space-y-3 text-xs">
                    <span class="text-amber-600 dark:text-amber-400 font-serif italic text-xs block">Thunnus albacares</span>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071B2B] font-mono space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-500">ROC-AUC:</span>
                            <span class="font-bold text-amber-600 dark:text-amber-400">0.874</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">TSS:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">0.648</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Ambang HSI:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">&ge; 0.75</span>
                        </div>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                        Tuna oseanik bernilai komersial tinggi yang menyukai batas tubir benua (*shelf break*) dan gradien termal tajam &ge; 0,040 °C/km.
                    </p>
                </div>
            </x-ui.card>

            <x-ui.card title="Albakor (Albacore)">
                <div class="space-y-3 text-xs">
                    <span class="text-sky-600 dark:text-sky-400 font-serif italic text-xs block">Thunnus alalunga</span>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071B2B] font-mono space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-500">ROC-AUC:</span>
                            <span class="font-bold text-sky-600 dark:text-sky-400">0.908</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">TSS:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">0.712</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Ambang HSI:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">&ge; 0.70</span>
                        </div>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                        Tuna perairan dalam dengan adaptasi suhu dingin (18–23,5°C) yang bermigrasi di lepas pantai Samudra Hindia (&gt;50 NM).
                    </p>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.public>

