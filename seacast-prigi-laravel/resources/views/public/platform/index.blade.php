<x-layouts.public title="Visi & Arsitektur Platform — SEACAST">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        <!-- Header -->
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Arsitektur Platform', 'url' => null]]" />
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Arsitektur Platform End-to-End
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-3xl leading-relaxed">
                SEACAST dirancang dengan pemisahan tanggung jawab (*separation of concerns*) yang ketat antara lapisan akuisisi satelit, repositori spasial PostGIS terindeks GiST, mesin inferensi machine learning Python, dan antarmuka analitis performa tinggi berbasis Laravel 13 & MapLibre GL JS.
            </p>
        </div>

        <!-- 4-Tier Pipeline Cards -->
        <div class="space-y-6">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Alur Pipa Data 4 Tingkat (4-Tier Data Pipeline)</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <x-ui.card title="Tingkat 1: Ingesti Satelit">
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Ekstraksi harian otomatis pukul 04:00 WIB dari Copernicus Marine, NASA OceanColor, NOAA OISST, dan BMKG INA-OFS.
                    </p>
                </x-ui.card>

                <x-ui.card title="Tingkat 2: PostGIS Spasial">
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Grid kanonikal 8.129 sel poligon (SRID 4326) dengan indeks GiST untuk query spasial dan agregasi ubin vektor MVT instan.
                    </p>
                </x-ui.card>

                <x-ui.card title="Tingkat 3: Mesin Inferensi">
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Model ensemble SDM (MaxEnt, Random Forest, GLM) terlatih scikit-learn menghitung nilai HSI tiga tuna dan zonasi DOM.
                    </p>
                </x-ui.card>

                <x-ui.card title="Tingkat 4: Visualisasi WebGL">
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Antarmuka web performa tinggi Laravel 13 Blade, Alpine.js, dan MapLibre GL JS v6 dengan latensi filter &lt;50ms.
                    </p>
                </x-ui.card>
            </div>
        </div>

        <!-- Links to Sub-pages -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="{{ route('platform.data-sources') }}" class="p-6 rounded-2xl bg-white dark:bg-[#091F30] border border-slate-200 dark:border-[#244456] hover:border-cyan-500 transition-all block group">
                <span class="text-xs font-mono font-bold text-cyan-600 dark:text-cyan-400 block mb-1">TELEMETRI SATELIT</span>
                <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-cyan-400 transition-colors">Katalog Sumber Data Satelit &rarr;</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">Lihat detail penyedia data citra satelit, frekuensi update, dan validasi ground truth.</p>
            </a>

            <a href="{{ route('platform.features') }}" class="p-6 rounded-2xl bg-white dark:bg-[#091F30] border border-slate-200 dark:border-[#244456] hover:border-cyan-500 transition-all block group">
                <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400 block mb-1">PROFIL PENGGUNA</span>
                <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-emerald-400 transition-colors">Fitur Berbasis Armada Nelayan &rarr;</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">Solusi yang disesuaikan untuk nelayan &lt;10 GT, kapal motor 20–30 GT, dan kantor syahbandar.</p>
            </a>
        </div>
    </div>
</x-layouts.public>

