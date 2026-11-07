<x-layouts.public title="Data Oseanografi Satelit — SEACAST">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        <!-- Header -->
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Data Oseanografi', 'url' => null]]" />
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Katalog Data Oseanografi & Feed Satelit
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-3xl leading-relaxed">
                Variabel biofisik oseanografi terkalibrasi untuk domain WPPNRI 573: Suhu Permukaan Laut (SST), Klorofil-a, Arus Laut, Angin, dan Gelombang yang bersumber dari NASA, Copernicus (CMEMS), JAXA, NOAA, dan BMKG.
            </p>
        </div>

        <!-- Environmental Parameters List -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-ui.card title="Suhu Permukaan Laut (SST)">
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500">Satuan & Resolusi:</span>
                        <span class="font-bold text-cyan-600 dark:text-cyan-400">°C (0,10° Grid Kanonik)</span>
                    </div>
                    <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500">Sensor & Sumber:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">NOAA OISST / MODIS / VIIRS</span>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed pt-1">
                        Menangkap variasi suhu lapisan kulit laut (*skin temperature*). Menjadi indikator utama deteksi fenomena upwelling dingin musiman di selatan Jawa (23,5–26,0°C) versus kolam hangat tropis (&gt;29,0°C).
                    </p>
                </div>
            </x-ui.card>

            <x-ui.card title="Konsentrasi Klorofil-a (Chl-a)">
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500">Satuan & Resolusi:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">mg/m³ (0,10° Grid Kanonik)</span>
                    </div>
                    <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500">Sensor & Sumber:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">Copernicus Sentinel-3 / MODIS Aqua</span>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed pt-1">
                        Mengukur biomassa fitoplankton sebagai basis rantai makanan laut. Peningkatan konsentrasi klorofil-a di zona pesisir dan tubir benua menandai ketersediaan pakan alami gerombolan tuna.
                    </p>
                </div>
            </x-ui.card>

            <x-ui.card title="Kecepatan & Arah Arus Laut">
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500">Satuan & Resolusi:</span>
                        <span class="font-bold text-sky-600 dark:text-sky-400">m/s & Derajat (1/12° Model)</span>
                    </div>
                    <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500">Sensor & Sumber:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">HYCOM / CMEMS Altimetry</span>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed pt-1">
                        Kecepatan arus permukaan dan transport geostropik yang mengarahkan pergerakan gerombolan ikan pelagis serta mengakumulasi mangsa di pusaran arus (*eddy features*).
                    </p>
                </div>
            </x-ui.card>

            <x-ui.card title="Tinggi Gelombang & Angin BMKG">
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500">Satuan & Resolusi:</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400">Meter & m/s (0,05° Ina-WAVES)</span>
                    </div>
                    <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500">Sensor & Sumber:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">BMKG Maritim / NOAA GFS</span>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed pt-1">
                        Prakiraan kondisi fisik laut harian untuk menjamin keselamatan navigasi armada perahu nelayan tradisional dan pembatasan operasional saat gelombang ekstrem (&ge; 2,0 meter).
                    </p>
                </div>
            </x-ui.card>
        </div>

        <!-- Ingestion Architecture CTA -->
        <div class="p-6 rounded-2xl bg-slate-100 dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#16445A] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Ingin Mengetahui Pipeline Ingest Data?</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Pelajari alur data otomatis 4-tier dari satelit hingga grid geospasial PostGIS.</p>
            </div>
            <a href="{{ route('platform.data-sources') }}" class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs shrink-0 text-center transition-colors">
                Lihat Sumber Data Satelit &rarr;
            </a>
        </div>
    </div>
</x-layouts.public>

