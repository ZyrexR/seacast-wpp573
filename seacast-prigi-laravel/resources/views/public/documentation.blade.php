<x-layouts.public title="Dokumentasi Teknis & Panduan — SEACAST">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-12">
        <!-- Header & Breadcrumbs -->
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Dokumentasi', 'url' => null]]" />
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                Dokumentasi Teknis & Panduan Platform
            </h1>
            <p class="text-sm sm:text-base text-cyan-700 dark:text-cyan-400 font-semibold tracking-wide">
                Pedoman Operasional Multi-Peran, Kepatuhan Regulasi Permen KP 59/2020 & Glosarium Sains
            </p>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 max-w-4xl leading-relaxed">
                Panduan komprehensif untuk nahkoda, syahbandar, dan peneliti dalam mengoperasikan platform SEACAST. Dokumen ini merangkum prosedur navigasi hemat bahan bakar, tata cara penerbitan lembar izin berlayar resmi, standar keselamatan gelombang BMKG, batasan hukum zonasi perikanan tangkap, serta glosarium oseanografi terstandarisasi.
            </p>
        </div>

        <!-- Role-Based Workflows -->
        <div class="space-y-6">
            <div class="border-b border-slate-200 dark:border-[#244456] pb-3">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Panduan Alur Operasional Berdasarkan Peran
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">
                    Langkah-langkah praktis pemanfaatan instrumen SEACAST sesuai fungsi pengguna.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Nahkoda -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-cyan-500/30 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 text-xs font-bold flex items-center justify-center font-mono">1</span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Nahkoda & Nelayan</h3>
                    </div>
                    <ol class="text-xs text-slate-600 dark:text-slate-300 space-y-2.5 list-decimal list-inside leading-relaxed">
                        <li>Buka menu <strong>DPI Radar</strong> untuk mengidentifikasi koordinat sel hotspot (merah/oranye) dengan HSI &ge; 0,70 terdekat dari pelabuhan asal.</li>
                        <li>Gunakan <strong>Trip Planner</strong>: masukkan kelas kapal (GT) dan kapasitas tangki solar untuk menghitung kebutuhan bahan bakar pergi-pulang.</li>
                        <li>Periksa status keselamatan: pastikan gelombang berada di bawah ambang batas bahaya (&lt; 2,0 m).</li>
                        <li>Cetak atau unduh <strong>Sailing Advisory Slip</strong> resmi untuk dibawa ke laut sebagai panduan titik tangkap presisi.</li>
                    </ol>
                </div>

                <!-- Syahbandar -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-emerald-500/30 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-bold flex items-center justify-center font-mono">2</span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Syahbandar & Otoritas Pelabuhan</h3>
                    </div>
                    <ol class="text-xs text-slate-600 dark:text-slate-300 space-y-2.5 list-decimal list-inside leading-relaxed">
                        <li>Menerima dan memeriksa lembar <strong>Sailing Advisory Slip</strong> yang dilampirkan nahkoda pada permohonan SPB.</li>
                        <li>Verifikasi batas jalur hukum: pastikan kapal &lt; 10 GT tidak diarahkan melampaui batas jelajah aman dan kapal &gt; 30 GT tidak menangkap di Jalur I/II.</li>
                        <li>Tinjau peringatan gelombang terkini BMKG: tolak atau tunda keberangkatan apabila status berada pada level <em>Bahaya Ekstrem</em> (&ge; 3,5 m).</li>
                        <li>Arsipkan data trip untuk pelaporan statistik kepatuhan dan keselamatan pelabuhan.</li>
                    </ol>
                </div>

                <!-- Peneliti -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-amber-500/30 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-amber-500/20 text-amber-400 text-xs font-bold flex items-center justify-center font-mono">3</span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Peneliti & Pengelola Sumberdaya</h3>
                    </div>
                    <ol class="text-xs text-slate-600 dark:text-slate-300 space-y-2.5 list-decimal list-inside leading-relaxed">
                        <li>Akses data telemetri di halaman <strong>Data Oseanografi</strong> untuk mengevaluasi fluktuasi SST dan sebaran klorofil-a.</li>
                        <li>Analisis sebaran sel hotspot musiman untuk memantau pergeseran ruaya ikan pelagis akibat fenomena IOD/ENSO.</li>
                        <li>Gunakan modul <strong>Analitik Armada</strong> untuk meneliti tren CPUE historis multi-tahun di pelabuhan pendaratan.</li>
                        <li>Evaluasi efektivitas batas zonasi DOM (Z1–Z5) terhadap penurunan laju tangkapan sampingan (<em>bycatch</em>).</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Fisheries Zoning Compliance (Permen KP No. 59/2020) -->
        <x-ui.card title="Kepatuhan Batas Jalur Penangkapan Ikan (Permen KP No. 59/PERMEN-KP/2020)">
            <p class="text-xs text-slate-600 dark:text-slate-400 mb-4 leading-relaxed">
                SEACAST secara otomatis memvalidasi apakah rute pelayaran dan sel sasaran tangkap memenuhi ketentuan alokasi jalur penangkapan ikan di Wilayah Pengelolaan Perikanan Negara Republik Indonesia (WPPNRI 573):
            </p>
            <x-ui.table :headers="['Jalur Penangkapan', 'Batas Jarak Laut', 'Kategori Ukuran Kapal (GT)', 'Alat Tangkap yang Diizinkan', 'Ketentuan Operasional SEACAST']">
                <tr>
                    <td class="px-5 py-3.5 font-bold font-mono text-cyan-600 dark:text-cyan-400">Jalur I</td>
                    <td class="px-5 py-3.5 font-mono text-xs">0 – 4 Mil Laut</td>
                    <td class="px-5 py-3.5 text-xs">Kapal &le; 5 GT (Perahu Tradisional)</td>
                    <td class="px-5 py-3.5 text-xs">Pancing ulur, jaring insang statis</td>
                    <td class="px-5 py-3.5 text-xs text-emerald-600 dark:text-emerald-400 font-semibold">Zona Eksklusif Nelayan Tradisional</td>
                </tr>
                <tr>
                    <td class="px-5 py-3.5 font-bold font-mono text-cyan-600 dark:text-cyan-400">Jalur II</td>
                    <td class="px-5 py-3.5 font-mono text-xs">4 – 12 Mil Laut</td>
                    <td class="px-5 py-3.5 text-xs">Kapal &gt; 5 GT hingga 30 GT</td>
                    <td class="px-5 py-3.5 text-xs">Purse seine mini, rawai hanyut</td>
                    <td class="px-5 py-3.5 text-xs text-emerald-600 dark:text-emerald-400 font-semibold">Koridor Pelagis Kecil & Cakalang</td>
                </tr>
                <tr>
                    <td class="px-5 py-3.5 font-bold font-mono text-cyan-600 dark:text-cyan-400">Jalur III</td>
                    <td class="px-5 py-3.5 font-mono text-xs">&gt; 12 Mil Laut & ZEEI</td>
                    <td class="px-5 py-3.5 text-xs">Kapal &gt; 30 GT (Izin Pusat KKP)</td>
                    <td class="px-5 py-3.5 text-xs">Purse seine pelagis besar, longline tuna</td>
                    <td class="px-5 py-3.5 text-xs text-cyan-600 dark:text-cyan-400 font-semibold">Perairan Oseanik Lepas (Tuna Samudera)</td>
                </tr>
            </x-ui.table>
        </x-ui.card>

        <!-- Marine Safety & Bycatch Mitigation Standards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-ui.card title="Standar Keselamatan Pelayaran BMKG">
                <div class="space-y-3 text-xs leading-relaxed">
                    <div class="p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300">
                        <strong>Tinggi Gelombang &lt; 1,5 meter:</strong> Status <strong>AMAN</strong> untuk seluruh armada perikanan.
                    </div>
                    <div class="p-2.5 rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 text-amber-800 dark:text-amber-300">
                        <strong>Tinggi Gelombang 1,5 – 2,5 meter:</strong> Status <strong>WASPADA</strong>. Perahu kecil &lt; 10 GT disarankan beroperasi di dalam teluk/pesisir dekat.
                    </div>
                    <div class="p-2.5 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/40 text-rose-800 dark:text-rose-300">
                        <strong>Tinggi Gelombang &ge; 3,5 meter:</strong> Status <strong>BAHAYA EKSTREM</strong>. Larangan melaut mandatori demi keselamatan jiwa nahkoda dan awak kapal.
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Standar Mitigasi Tangkapan Sampingan (Bycatch IOTC)">
                <div class="space-y-3 text-xs leading-relaxed text-slate-600 dark:text-slate-300">
                    <p>
                        Sesuai pedoman komisi tuna Samudera Hindia (<em>Indian Ocean Tuna Commission - IOTC</em>), SEACAST mengintegrasikan aturan mitigasi tangkapan sampingan:
                    </p>
                    <ul class="space-y-2 list-disc list-inside">
                        <li>Penggunaan mata pancing melingkar (<em>circle hooks</em>) untuk meminimalkan kematian penyu laut.</li>
                        <li>Larangan penebaran jaring pada sel yang terklasifikasi sebagai <strong>Zona Z5 (High Bycatch Risk)</strong>.</li>
                        <li>Kewajiban pelepasan kembali satwa laut dilindungi (hiu martil, lumba-lumba, pari manta) dalam kondisi hidup.</li>
                    </ul>
                </div>
            </x-ui.card>
        </div>

        <!-- Comprehensive Oceanographic Glossary -->
        <div class="space-y-6">
            <div class="border-b border-slate-200 dark:border-[#244456] pb-3">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Glosarium Oseanografi & Istilah Teknis
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">
                    Definisi baku variabel lingkungan, pemodelan habitat, dan terminologi oseanografi satelit.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="p-4 rounded-xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-1">
                    <strong class="text-sm font-bold text-cyan-600 dark:text-cyan-400 font-mono block">Habitat Suitability Index (HSI)</strong>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Indeks numerik tanpa satuan bernilai antara 0,000 hingga 1,000 yang mengkuantifikasi tingkat kesesuaian parameter fisik-biologi lingkungan laut bagi ruaya dan konsentrasi ikan pelagis sasaran.
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-1">
                    <strong class="text-sm font-bold text-cyan-600 dark:text-cyan-400 font-mono block">Dynamic Ocean Management (DOM)</strong>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Kerangka kerja penataan ruang laut yang batas-batas zonanya bergeser mengikuti dinamika oseanografi harian, bukan batas administratif statis, untuk menyeimbangkan penangkapan ikan dengan perlindungan ekosistem.
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-1">
                    <strong class="text-sm font-bold text-cyan-600 dark:text-cyan-400 font-mono block">Upwelling (Penaikan Massa Air)</strong>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Fenomena oseanografi di mana massa air laut dalam yang dingin dan kaya unsur hara (nutrien) terangkat ke lapisan permukaan, memicu ledakan populasi fitoplankton sebagai basis pakan tuna.
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-1">
                    <strong class="text-sm font-bold text-cyan-600 dark:text-cyan-400 font-mono block">Thermal Front (Front Termal)</strong>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Garis batas konvergensi antara dua massa air laut dengan gradien perbedaan suhu tajam. Merupakan area akumulasi biomassa plankton dan lokasi mencari makan favorit bagi gerombolan Cakalang.
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-1">
                    <strong class="text-sm font-bold text-cyan-600 dark:text-cyan-400 font-mono block">Catch Per Unit Effort (CPUE Nominal)</strong>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Ukuran produktivitas penangkapan ikan yang dinyatakan dalam rasio berat ikan mendarat (kg) per satuan trip berlayar resmi, digunakan sebagai proksi relatif kelimpahan stok ikan tahunan.
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-1">
                    <strong class="text-sm font-bold text-cyan-600 dark:text-cyan-400 font-mono block">Mesoscale Eddies (Pusaran Arus)</strong>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Struktur arus melingkar berdiameter 50–200 km di samudera. Pusaran siklonik mengangkat massa air dingin ke tengah, sementara antisiklonik memerangkap air hangat dan mengkonsentrasikan tuna di batas tepiannya.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.public>
