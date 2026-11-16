<x-layouts.public title="Tentang SEACAST — Scientific Ocean Intelligence & Fisheries Decision Support">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-12">
        <!-- Header & Breadcrumbs -->
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Tentang SEACAST', 'url' => null]]" />
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                Tentang Platform SEACAST
            </h1>
            <p class="text-sm sm:text-base text-cyan-700 dark:text-cyan-400 font-semibold tracking-wide">
                Scientific Ocean Intelligence & Fisheries Decision Support Platform
            </p>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 max-w-4xl leading-relaxed">
                SEACAST adalah platform kecerdasan maritim digital yang dirancang untuk mentransformasi penangkapan ikan tradisional dan semi-industri menjadi perikanan presisi berbasis sains. Dengan menggabungkan data observasi satelit bumi beresolusi tinggi, pemodelan habitat berbasis kecerdasan buatan (<em>Ensemble Species Distribution Model</em>), dan komputasi spasial kanonikal PostGIS 17, SEACAST memberikan panduan navigasi yang presisi, hemat bahan bakar, mengutamakan keselamatan pelayaran, dan patuh pada zonasi konservasi kelautan.
            </p>
        </div>

        <!-- 3 Core Application Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] shadow-sm">
                <span class="text-2xl sm:text-3xl font-black font-mono text-cyan-600 dark:text-cyan-400">8.129 Sel</span>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-1">Grid Spasial Kanonikal</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Resolusi presisi 0,10° (&plusmn;11 km) mencakup seluruh koridor Samudera Hindia Selatan Jawa (WPPNRI 573).</p>
            </div>
            <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] shadow-sm">
                <span class="text-2xl sm:text-3xl font-black font-mono text-emerald-600 dark:text-emerald-400">20% – 30%</span>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-1">Estimasi Efisiensi BBM</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pengurangan waktu jelajah spekulatif dengan perutean langsung menuju titik hotspot kesesuaian habitat tertinggi.</p>
            </div>
            <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] shadow-sm">
                <span class="text-2xl sm:text-3xl font-black font-mono text-amber-600 dark:text-amber-400">3 Spesies Utama</span>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-1">Prediksi Presisi Pelagis</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pemodelan relung ekologi komposit terpisah untuk Tongkol Krai (pesisir), Cakalang (upwelling), dan Tuna Sirip Kuning (laut dalam).</p>
            </div>
        </div>

        <!-- Three Value Pillars -->
        <div class="space-y-6">
            <div class="border-b border-slate-200 dark:border-[#244456] pb-3">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Tiga Pilar Nilai Aplikasi
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">
                    Bagaimana SEACAST menyelesaikan tantangan mendasar industri perikanan tangkap nasional.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-ui.card title="1. Efisiensi Bahan Bakar & Penangkapan Presisi">
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Biaya bahan bakar solar mencakup 60–70% dari total pengeluaran operasional kapal. SEACAST menghilangkan kebiasaan mencari ikan secara acak dengan memproyeksikan Daerah Penangkapan Ikan (DPI) potensial berindeks HSI tinggi (&ge; 0,70), memperpendek durasi pencarian dan memaksimalkan hasil per trip (CPUE).
                    </p>
                </x-ui.card>

                <x-ui.card title="2. Keselamatan Maritim & Mitigasi Risiko Cuaca">
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Mendukung perlindungan nahkoda dan kru dengan memadukan data prakiraan tinggi gelombang signifikan dan angin harian. Sistem secara otomatis memberikan status rekomendasi kelayakan berlayar (Aman, Waspada, Bahaya Ekstrem) sebelum kapal berlayar.
                    </p>
                </x-ui.card>

                <x-ui.card title="3. Kepatuhan Zonasi & Penataan Ruang Laut Dinamis">
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Mengintegrasikan ketentuan batas jalur penangkapan ikan Permen KP No. 59/2020 sesuai ukuran kapal (GT) serta kerangka kerja <em>Dynamic Ocean Management</em> (DOM) untuk mencegah tangkapan sampingan (<em>bycatch</em>) pada zona asuhan dan migrasi satwa laut dilindungi.
                    </p>
                </x-ui.card>
            </div>
        </div>

        <!-- 5 Core Features and Modules -->
        <div class="space-y-6">
            <div class="border-b border-slate-200 dark:border-[#244456] pb-3">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Modul & Fitur Unggulan Aplikasi
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">
                    Instrumen teknologi yang siap digunakan oleh armada dan pemangku kepentingan maritim.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center font-bold text-lg mb-3">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">DPI Radar Spasial Interaktif</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Peta Web-GIS kanonikal dengan kontur gradien HSI multi-spesies, lapisan suhu permukaan laut (SST), klorofil-a, arus laut, dan penanda hotspot harian real-time.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-lg mb-3">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Trip Planner & Kalkulator Melaut</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Kalkulator operasional terpadu yang menghitung jarak nautika (NM), estimasi liter bahan bakar, durasi pelayaran bolak-balik, dan proyeksi kelayakan ekonomi penangkapan.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-lg mb-3">
                        <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Penerbitan Dokumen Sailing Advisory</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Menghasilkan lembar rekomendasi melaut resmi berformat PDF satu lembar yang memuat rute, koordinat sasaran, ringkasan cuaca, dan verifikasi kepatuhan hukum untuk syahbandar.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center font-bold text-lg mb-3">
                        <svg class="w-5 h-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Dasbor Analitik Armada Terpadu</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Menganalisis tren pendaratan pelagis historis multi-tahun, sebaran armada menurut kelas GT, dan evaluasi produktivitas tangkapan per trip (CPUE nominal).
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center font-bold text-lg mb-3">
                        <svg class="w-5 h-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Dynamic Ocean Management (DOM)</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Klasifikasi ruang laut harian menjadi 5 zona dinamis (Z1–Z5) untuk memisahkan daerah penangkapan produktif dari area asuhan bibit ikan dan koridor satwa sensitif.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center font-bold text-lg mb-3">
                        <svg class="w-5 h-5 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Monitoring Telemetri Satelit</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Feed telemetri oseanografi harian yang dipetakan dari referensi satelit Copernicus Sentinel-3, NASA MODIS/VIIRS, NOAA OISST, dan model hidrodinamika arus laut global.
                    </p>
                </div>
            </div>
        </div>

        <!-- How SEACAST Works (Workflow) -->
        <div class="space-y-6">
            <div class="border-b border-slate-200 dark:border-[#244456] pb-3">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Alur Kerja Aplikasi (End-to-End Workflow)
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">
                    Bagaimana data mentah satelit diproses hingga menjadi panduan melaut di tangan nahkoda.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456] relative">
                    <span class="text-xs font-mono font-bold text-cyan-500">TAHAP 01</span>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white mt-1">Ingesti Satelit Otomatis</h4>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
                        Data observasi bumi (SST, Klorofil-a, Arus, Angin, Gelombang) diekstraksi harian dari penyedia satelit global melalui Google Earth Engine (GEE).
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456] relative">
                    <span class="text-xs font-mono font-bold text-cyan-500">TAHAP 02</span>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white mt-1">Ensemble SDM Scoring</h4>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
                        Tiga algoritma pemodelan relung ekologi (MaxEnt, Random Forest, GLM) menghitung indeks probabilitas kesesuaian habitat (HSI) untuk setiap spesies sasaran.
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456] relative">
                    <span class="text-xs font-mono font-bold text-cyan-500">TAHAP 03</span>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white mt-1">PostGIS Grid Integration</h4>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
                        Nilai prediksi diasosiasikan secara relasional ke 8.129 poligon sel kanonikal WPPNRI 573 berproyeksi WGS 84 (EPSG:4326) pada basis data PostgreSQL 17.
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456] relative">
                    <span class="text-xs font-mono font-bold text-cyan-500">TAHAP 04</span>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white mt-1">Eksekusi Keputusan Melaut</h4>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
                        Pengguna mengakses peta radar interaktif, merencanakan rute dengan kalkulator bahan bakar, dan mengunduh lembar slip izin melaut resmi secara langsung.
                    </p>
                </div>
            </div>
        </div>

        <!-- Target Users (Who It Is Built For) -->
        <div class="space-y-6">
            <div class="border-b border-slate-200 dark:border-[#244456] pb-3">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Pengguna Sasaran Aplikasi
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">
                    Dirancang untuk memenuhi kebutuhan spesifik berbagai pemangku kepentingan maritim.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-3">
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-cyan-500/10 text-cyan-400 text-xs font-bold font-mono">
                        NAHKODA & NELAYAN
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Efisiensi & Keselamatan Melaut</h3>
                    <ul class="text-xs text-slate-600 dark:text-slate-300 space-y-2 list-disc list-inside leading-relaxed">
                        <li>Menentukan arah dan rute kapal langsung menuju sel berkepadatan ikan tinggi.</li>
                        <li>Memperhitungkan cadangan solar dan perbekalan trip secara presisi.</li>
                        <li>Menghindari area laut berbahaya dengan gelombang melebihi batas aman kapal.</li>
                    </ul>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-3">
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 text-xs font-bold font-mono">
                        SYAHBANDAR & OTORITAS PELABUHAN
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Validasi Surat Persetujuan Berlayar (SPB)</h3>
                    <ul class="text-xs text-slate-600 dark:text-slate-300 space-y-2 list-disc list-inside leading-relaxed">
                        <li>Memvalidasi rute berlayar kapal sesuai jalur zonasi Permen KP No. 59/2020.</li>
                        <li>Memverifikasi kelayakan cuaca berbasis telemetri gelombang dan angin BMKG.</li>
                        <li>Menerima lembar <em>Sailing Advisory Slip</em> resmi sebagai lampiran SPB.</li>
                    </ul>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-3">
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-400 text-xs font-bold font-mono">
                        PENELITI & DINAS KELAUTAN
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Pemantauan Stok & Analitik Sains</h3>
                    <ul class="text-xs text-slate-600 dark:text-slate-300 space-y-2 list-disc list-inside leading-relaxed">
                        <li>Mengevaluasi dinamika pergeseran daerah penangkapan ikan antar-musim.</li>
                        <li>Menganalisis tren produktivitas tangkapan (CPUE) dan kesehatan stok pelagis.</li>
                        <li>Mengembangkan rekomendasi kebijakan Dynamic Ocean Management (DOM).</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Tech Stack Highlights -->
        <x-ui.card title="Fondasi Arsitektur Teknologi">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456]">
                    <span class="block font-bold text-slate-900 dark:text-white">Aplikasi Monolit Modern</span>
                    <span class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5 block">Laravel 13, Blade Components, Alpine.js, Tailwind CSS v4</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456]">
                    <span class="block font-bold text-slate-900 dark:text-white">Mesin Geospasial Spasial</span>
                    <span class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5 block">PostgreSQL 17 + PostGIS 3.6, EPSG:4326 Canonical Grid</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456]">
                    <span class="block font-bold text-slate-900 dark:text-white">Kecerdasan Buatan (SDM)</span>
                    <span class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5 block">MaxEnt, Random Forest & GLM Ensemble Voting</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456]">
                    <span class="block font-bold text-slate-900 dark:text-white">Observasi Bumi Satelit</span>
                    <span class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5 block">Google Earth Engine, Copernicus CMEMS, NOAA, NASA, BMKG</span>
                </div>
            </div>
        </x-ui.card>

        <!-- Final Call to Action -->
        <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-[#071B2B] via-[#09263a] to-[#0D384D] border border-cyan-500/30 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-1">
                <h3 class="text-lg sm:text-xl font-bold text-white">Siap Menggunakan SEACAST untuk Operasional Maritim?</h3>
                <p class="text-xs sm:text-sm text-slate-300 max-w-xl">
                    Masuk ke Ruang Kendali Eksekutif untuk memantau radar geospasial real-time atau mulai susun rencana pelayaran hari ini.
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <x-ui.button href="{{ route('dashboard') }}" variant="primary" size="md" class="shadow-lg shadow-cyan-500/20 font-bold">
                    Masuk Ruang Kendali
                </x-ui.button>
                <x-ui.button href="{{ route('dashboard.trip-planner') }}" variant="outline" size="md" class="border-cyan-500/40 text-slate-200 hover:text-white">
                    Rencanakan Melaut
                </x-ui.button>
            </div>
        </div>
    </div>
</x-layouts.public>
