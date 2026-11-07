<x-layouts.public title="Data & Dokumentasi Teknis — SEACAST">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Dokumentasi', 'url' => null]]" />
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Glosarium & Panduan Teknis Platform
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-3xl leading-relaxed">
                Kamus istilah oseanografi, batasan model, variabel prediktor satelit, dan panduan zonasi perikanan tangkap WPPNRI 573.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-ui.card title="Habitat Suitability Index (HSI)">
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    Indeks numerik tanpa satuan bernilai antara 0,000 hingga 1,000 yang mengkuantifikasi tingkat kesesuaian parameter lingkungan fisik-biologi laut (SST, Klorofil-a, Batimetri, Front Termal) bagi habitat makan atau ruaya tuna pelagis.
                </p>
            </x-ui.card>

            <x-ui.card title="Dynamic Ocean Management (DOM)">
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    Kerangka kerja penataan ruang laut yang batas-batas zonanya bergeser mengikuti dinamika oseanografi harian, bukan batas administratif statis, untuk menyeimbangkan penangkapan ikan ekonomis dengan konservasi.
                </p>
            </x-ui.card>

            <x-ui.card title="Kriteria Hotspot Penangkapan">
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    Sebuah sel grid 0,10° kanonikal diklasifikasikan sebagai <code>is_hotspot = true</code> jika nilai HSI Cakalang &ge; 0,70 ATAU nilai HSI Madidihang &ge; 0,70.
                </p>
            </x-ui.card>

            <x-ui.card title="Simulasi Cadangan Gelombang BMKG">
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    Sebelum API live BMKG dihubungkan, metrik gelombang dihitung menggunakan modul cadangan terkalibrasi musiman dengan batas keselamatan: &ge; 2,0m (Waspada Kapal Kecil) dan &ge; 3,5m (Bahaya Ekstrem).
                </p>
            </x-ui.card>
        </div>
    </div>
</x-layouts.public>

