<x-layouts.public title="Katalog Data Oseanografi Satelit — SEACAST">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-12">
        <!-- Header & Breadcrumbs -->
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Data Oseanografi', 'url' => null]]" />
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                Katalog Data Oseanografi Satelit
            </h1>
            <p class="text-sm sm:text-base text-cyan-700 dark:text-cyan-400 font-semibold tracking-wide">
                Feed Observasi Bumi, Parameter Bio-Fisik & Telemetri Spasial Kanonikal WPPNRI 573
            </p>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 max-w-4xl leading-relaxed">
                Platform SEACAST mengasimilasi lapisan data bio-oseanografi harian dari konstelasi satelit penginderaan jauh (Copernicus, NASA, NOAA, JAXA, dan BMKG). Seluruh lapisan data dipetakan ke dalam 8.129 grid kanonikal beresolusi 0,10° (&plusmn;11 km) untuk mendukung pemodelan kesesuaian habitat proxy dan evaluasi keselamatan maritim.
            </p>
        </div>

        <!-- Live Telemetry Status Bar -->
        <div class="p-6 rounded-3xl bg-slate-900 text-white border border-cyan-500/30 shadow-xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                    <span class="text-xs font-mono font-bold tracking-wider uppercase text-cyan-400">TELEMETRI HARIAN AKTIF BASIS DATA POSTGIS</span>
                </div>
                <div class="text-xs font-mono text-slate-400">
                    Siklus Sinkronisasi: <strong class="text-white">{{ $latestDate }}</strong> &bull; Cakupan: WPPNRI 573
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 pt-1 text-center">
                <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700">
                    <span class="text-[11px] font-mono text-slate-400 uppercase block">RATA-RATA SST</span>
                    <span class="text-xl sm:text-2xl font-black font-mono text-cyan-400 mt-0.5 block">{{ $avgSst }} °C</span>
                    <span class="text-[10px] text-slate-400">NOAA OISST / VIIRS</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700">
                    <span class="text-[11px] font-mono text-slate-400 uppercase block">KLOROFIL-A</span>
                    <span class="text-xl sm:text-2xl font-black font-mono text-emerald-400 mt-0.5 block">{{ $avgChla }} mg/m³</span>
                    <span class="text-[10px] text-slate-400">Sentinel-3 / MODIS</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700">
                    <span class="text-[11px] font-mono text-slate-400 uppercase block">GELOMBANG RATA-RATA</span>
                    <span class="text-xl sm:text-2xl font-black font-mono text-amber-400 mt-0.5 block">{{ $avgWave }} m</span>
                    <span class="text-[10px] text-slate-400">BMKG Ina-WAVES</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700">
                    <span class="text-[11px] font-mono text-slate-400 uppercase block">KECEPATAN ARUS</span>
                    <span class="text-xl sm:text-2xl font-black font-mono text-sky-400 mt-0.5 block">{{ $avgCurrent }} m/s</span>
                    <span class="text-[10px] text-slate-400">HYCOM / CMEMS</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700 col-span-2 sm:col-span-1">
                    <span class="text-[11px] font-mono text-slate-400 uppercase block">SEL DIPANTAU</span>
                    <span class="text-xl sm:text-2xl font-black font-mono text-white mt-0.5 block">{{ number_format($totalCells) }}</span>
                    <span class="text-[10px] text-slate-400">Grid Kanonikal 0,10°</span>
                </div>
            </div>
        </div>

        <!-- 6 Core Environmental Variables Matrix -->
        <div class="space-y-6">
            <div class="border-b border-slate-200 dark:border-[#244456] pb-3">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Matriks Spesifikasi Variabel Oseanografi
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">
                    Enam prediktor fisik dan biogeokimia laut yang digunakan mesin prediksi SEACAST.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- SST -->
                <x-ui.card title="Suhu Permukaan Laut (SST)">
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                            <span class="text-slate-500">Satuan & Resolusi:</span>
                            <span class="font-bold text-cyan-600 dark:text-cyan-400">°C (0,10° Grid Kanonikal)</span>
                        </div>
                        <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                            <span class="text-slate-500">Sensor & Sumber:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">NOAA OISST v2.1 & MODIS</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed pt-1">
                            Mengukur suhu lapisan kulit laut (*skin temperature*). Indikator paling krusial untuk mendeteksi intrusi fenomena upwelling dingin musiman (23,5–26,0°C) di selatan Jawa versus kolam hangat tropis (> 29,0°C).
                        </p>
                    </div>
                </x-ui.card>

                <!-- Chlorophyll-a -->
                <x-ui.card title="Konsentrasi Klorofil-a (Chl-a)">
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                            <span class="text-slate-500">Satuan & Resolusi:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">mg/m³ (0,10° Grid Kanonikal)</span>
                        </div>
                        <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                            <span class="text-slate-500">Sensor & Sumber:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">Copernicus Sentinel-3 OLCI</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed pt-1">
                            Mengukur biomassa fitoplankton sebagai produsen primer rantai pakan laut. Kelimpahan klorofil-a di pesisir dan batas paparan benua menandai area berkumpulnya mangsa alami (teri, krill, dan cumi-cumi).
                        </p>
                    </div>
                </x-ui.card>

                <!-- Surface Currents -->
                <x-ui.card title="Kecepatan & Arah Arus Laut">
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                            <span class="text-slate-500">Satuan & Resolusi:</span>
                            <span class="font-bold text-sky-600 dark:text-sky-400">m/s & Derajat (1/12° Model)</span>
                        </div>
                        <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                            <span class="text-slate-500">Sensor & Sumber:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">HYCOM Global & Altimetri CMEMS</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed pt-1">
                            Kecepatan arus permukaan dan transport geostropik yang mengarahkan migrasi gerombolan ikan pelagis serta mengakumulasi mangsa pada pusaran arus mesoskala (*cyclonic and anticyclonic eddies*).
                        </p>
                    </div>
                </x-ui.card>

                <!-- Wave Height -->
                <x-ui.card title="Prakiraan Tinggi Gelombang (SWH)">
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                            <span class="text-slate-500">Satuan & Resolusi:</span>
                            <span class="font-bold text-amber-600 dark:text-amber-400">Meter (0,05° Grid Maritim)</span>
                        </div>
                        <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                            <span class="text-slate-500">Sensor & Sumber:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">BMKG Maritim Ina-WAVES</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed pt-1">
                            Prakiraan kondisi fisik laut harian untuk menjamin keselamatan navigasi armada kapal nelayan. Mengatur pembatasan pelayaran operasional saat gelombang tinggi (&ge; 2,0 m waspada, &ge; 3,5 m bahaya ekstrem).
                        </p>
                    </div>
                </x-ui.card>

                <!-- Wind Speed -->
                <x-ui.card title="Kecepatan & Arah Angin Permukaan">
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                            <span class="text-slate-500">Satuan & Resolusi:</span>
                            <span class="font-bold text-purple-600 dark:text-purple-400">Knot & m/s (0,25° GFS Model)</span>
                        </div>
                        <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                            <span class="text-slate-500">Sensor & Sumber:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">MetOp ASCAT & NOAA GFS</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed pt-1">
                            Dinamika angin permukaan laut sebagai penggerak utama transpor Ekman dan pemicu gelombang alun (*swell*) Samudera Hindia yang mempengaruhi stabilitas olah gerak kapal nelayan di laut lepas.
                        </p>
                    </div>
                </x-ui.card>

                <!-- Bathymetry -->
                <x-ui.card title="Batimetri & Morfologi Dasar Laut">
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                            <span class="text-slate-500">Satuan & Resolusi:</span>
                            <span class="font-bold text-teal-600 dark:text-teal-400">Meter Bawah Laut (15-arc-sec)</span>
                        </div>
                        <div class="flex justify-between font-mono pb-2 border-b border-slate-200 dark:border-slate-800">
                            <span class="text-slate-500">Sensor & Sumber:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">GEBCO 2024 Global Grid</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed pt-1">
                            Kedalaman laut statis kanonikal yang membedakan zona paparan benua dangkal (&le; 200 m) tempat berkumpulnya Tongkol Krai dari jurang tubir benua samudera dalam (&gt; 1.000 m) habitat utama Tuna Sirip Kuning.
                        </p>
                    </div>
                </x-ui.card>
            </div>
        </div>

        <!-- Official Satellite Missions Constellation Table -->
        <x-ui.card title="Konstelasi Misi Satelit Observasi Bumi yang Diintegrasikan">
            <x-ui.table :headers="['Misi Satelit', 'Badan Antariksa / Sumber', 'Instrumen Sensor', 'Variabel Terkait', 'Siklus Pembaruan']">
                <tr>
                    <td class="px-5 py-3.5 font-bold font-mono">Sentinel-3A & 3B</td>
                    <td class="px-5 py-3.5">Copernicus / ESA</td>
                    <td class="px-5 py-3.5 font-mono text-cyan-600 dark:text-cyan-400">OLCI & SLSTR</td>
                    <td class="px-5 py-3.5 text-xs">Klorofil-a, Warna Laut, SST</td>
                    <td class="px-5 py-3.5 font-mono text-xs">Harian (10:00 UTC)</td>
                </tr>
                <tr>
                    <td class="px-5 py-3.5 font-bold font-mono">NOAA-20 / SNPP</td>
                    <td class="px-5 py-3.5">NOAA / NASA</td>
                    <td class="px-5 py-3.5 font-mono text-cyan-600 dark:text-cyan-400">VIIRS & OISST v2.1</td>
                    <td class="px-5 py-3.5 text-xs">Suhu Permukaan Laut (SST)</td>
                    <td class="px-5 py-3.5 font-mono text-xs">Harian (Referensi NOAA)</td>
                </tr>
                <tr>
                    <td class="px-5 py-3.5 font-bold font-mono">Aqua & Terra</td>
                    <td class="px-5 py-3.5">NASA GSFC</td>
                    <td class="px-5 py-3.5 font-mono text-cyan-600 dark:text-cyan-400">MODIS</td>
                    <td class="px-5 py-3.5 text-xs">SST, Klorofil-a, Fluks Radiasi</td>
                    <td class="px-5 py-3.5 font-mono text-xs">Harian</td>
                </tr>
                <tr>
                    <td class="px-5 py-3.5 font-bold font-mono">Ina-WAVES Maritim</td>
                    <td class="px-5 py-3.5">BMKG Indonesia</td>
                    <td class="px-5 py-3.5 font-mono text-cyan-600 dark:text-cyan-400">Model Hidrodinamika 0,05°</td>
                    <td class="px-5 py-3.5 text-xs">Tinggi Gelombang & Angin Laut</td>
                    <td class="px-5 py-3.5 font-mono text-xs">Interval 6 Jam</td>
                </tr>
                <tr>
                    <td class="px-5 py-3.5 font-bold font-mono">HYCOM Global</td>
                    <td class="px-5 py-3.5">Consortium / CMEMS</td>
                    <td class="px-5 py-3.5 font-mono text-cyan-600 dark:text-cyan-400">Altimetri Radar NRT</td>
                    <td class="px-5 py-3.5 text-xs">Vektor Arus Permukaan (u, v)</td>
                    <td class="px-5 py-3.5 font-mono text-xs">Harian 1/12°</td>
                </tr>
            </x-ui.table>
        </x-ui.card>

        <!-- Spatial Pipeline & Data Architecture -->
        <div class="p-6 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Arsitektur Data Spasial PostGIS & Standar Interoperabilitas</h3>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                Setiap petak perairan di SEACAST direferensikan ke dalam grid kanonikal berdimensi 0,10° &times; 0,10° dengan proyeksi standar WGS 84 (EPSG:4326). Data geospasial diproses menggunakan indeks spasial GiST (<em>Generalized Search Tree</em>) pada PostgreSQL 17 / PostGIS 3.6, memungkinkan kalkulasi jarak pelabuhan ke titik hotspot dan evaluasi tumpang-tindih zonasi hukum berlangsung dalam hitungan milidetik.
            </p>
            <div class="flex flex-wrap gap-3 pt-2">
                <span class="px-3 py-1 rounded-lg bg-slate-100 dark:bg-[#12364A] text-slate-700 dark:text-cyan-300 font-mono text-xs">GeoJSON Format</span>
                <span class="px-3 py-1 rounded-lg bg-slate-100 dark:bg-[#12364A] text-slate-700 dark:text-cyan-300 font-mono text-xs">NetCDF-4 Grid</span>
                <span class="px-3 py-1 rounded-lg bg-slate-100 dark:bg-[#12364A] text-slate-700 dark:text-cyan-300 font-mono text-xs">EPSG:4326 PostGIS</span>
                <span class="px-3 py-1 rounded-lg bg-slate-100 dark:bg-[#12364A] text-slate-700 dark:text-cyan-300 font-mono text-xs">REST API Ready</span>
            </div>
        </div>
    </div>
</x-layouts.public>
