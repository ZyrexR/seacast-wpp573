<x-layouts.public title="Model Prediksi Komoditas TCT & Nilai Evaluasi — SEACAST">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Kerangka Sains', 'url' => route('science')], ['label' => 'Model Prediksi Komoditas', 'url' => null]]" />
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Model Prediksi Komoditas TCT & Evaluasi Akurasi
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-3xl leading-relaxed">
                Evaluasi performa model prediktif TCT: Cakalang (<em>Katsuwonus pelamis</em>), Tuna Sirip Kuning (<em>Thunnus albacares</em>), dan Tongkol Krai (<em>Auxis thazard</em>).
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.card title="Cakalang (Skipjack)">
                <div class="space-y-3 text-xs">
                    <span class="text-cyan-600 dark:text-cyan-400 font-serif italic text-xs block">Katsuwonus pelamis</span>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071B2B] font-mono space-y-1">
                        <div class="flex justify-between"><span class="text-slate-500">ROC-AUC:</span><span class="font-bold text-cyan-600 dark:text-cyan-400">0.892</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">TSS:</span><span class="font-bold text-slate-700 dark:text-slate-300">0.684</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Ambang HSI:</span><span class="font-bold text-emerald-600 dark:text-emerald-400">&ge; 0.75</span></div>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed">Tuna epipelagis dengan preferensi suhu 27–29,5°C dan klorofil-a tinggi di dekat front upwelling pesisir Prigi.</p>
                </div>
            </x-ui.card>

            <x-ui.card title="Tuna Sirip Kuning (Yellowfin)">
                <div class="space-y-3 text-xs">
                    <span class="text-amber-600 dark:text-amber-400 font-serif italic text-xs block">Thunnus albacares</span>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071B2B] font-mono space-y-1">
                        <div class="flex justify-between"><span class="text-slate-500">ROC-AUC:</span><span class="font-bold text-amber-600 dark:text-amber-400">0.874</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">TSS:</span><span class="font-bold text-slate-700 dark:text-slate-300">0.648</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Ambang HSI:</span><span class="font-bold text-emerald-600 dark:text-emerald-400">&ge; 0.75</span></div>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed">Tuna oseanik bernilai komersial tinggi yang menyukai batas tubir benua dan gradien termal tajam.</p>
                </div>
            </x-ui.card>

            <x-ui.card title="Tongkol Krai (Frigate Tuna)">
                <div class="space-y-3 text-xs">
                    <span class="text-sky-600 dark:text-sky-400 font-serif italic text-xs block">Auxis thazard</span>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071B2B] font-mono space-y-1">
                        <div class="flex justify-between"><span class="text-slate-500">Metode:</span><span class="font-bold text-sky-600 dark:text-sky-400">Rule-based HSI</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Bobot SST / Chl-a / Depth:</span><span class="font-bold text-slate-700 dark:text-slate-300">0.45 / 0.40 / 0.15</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Ambang HSI:</span><span class="font-bold text-emerald-600 dark:text-emerald-400">&ge; 0.70</span></div>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed">Komoditas neritik pesisir dengan preferensi SST 27–30,5°C, klorofil-a 0,20–0,50 mg/m³, dan kedalaman dangkal.</p>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.public>
