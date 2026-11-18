<x-layouts.public title="Sains & Pemodelan Habitat Pelagis — SEACAST">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-12">
        <!-- Header & Breadcrumbs -->
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Sains & Model', 'url' => null]]" />
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                Metodologi Bio-Oseanografi & Pemodelan Presisi
            </h1>
            <p class="text-sm sm:text-base text-cyan-700 dark:text-cyan-400 font-semibold tracking-wide">
                Ensemble Species Distribution Model (SDM) & Dynamic Ocean Management (DOM)
            </p>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 max-w-4xl leading-relaxed">
                Platform SEACAST menerapkan teori relung ekologi Hutchinson multidimensi yang memetakan korelasi non-linear antara variabel biofisik lingkungan laut dengan probabilitas kehadiran ikan pelagis. Melalui pendekatan ensemble tiga algoritma teruji (<em>MaxEnt</em>, <em>Random Forest</em>, dan <em>Generalized Linear Model</em>), sistem menghitung indeks kesesuaian habitat (HSI) harian pada resolusi kanonikal 0,10° (&plusmn;11 km) untuk mendukung penangkapan ikan presisi dan konservasi laut berkelanjutan.
            </p>
        </div>

        <!-- Real-Time Model Telemetry Indicators -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="p-4 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] shadow-sm">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block">Kesesuaian Rata-rata Tongkol</span>
                <span class="text-2xl font-black font-mono text-cyan-600 dark:text-cyan-400 mt-1 block">{{ $avgTongkolHsi }}</span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400">Dominan Pesisir &bull; HSI Neritik</span>
            </div>
            <div class="p-4 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] shadow-sm">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block">Kesesuaian Rata-rata Cakalang</span>
                <span class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $avgCakalangHsi }}</span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400">Front Upwelling &bull; 12-50 NM</span>
            </div>
            <div class="p-4 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] shadow-sm">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block">Kesesuaian Rata-rata Tuna Sirip Kuning</span>
                <span class="text-2xl font-black font-mono text-amber-600 dark:text-amber-400 mt-1 block">{{ $avgMadidihangHsi }}</span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400">Samudera Dalam &bull; &gt;50 NM</span>
            </div>
            <div class="p-4 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] shadow-sm">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block">Hotspot Teridentifikasi</span>
                <span class="text-2xl font-black font-mono text-rose-500 mt-1 block">{{ $hotspotsCount }} Sel</span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400">Threshold HSI &ge; 0,70</span>
            </div>
        </div>

        <!-- 3 Target Species Niche Differentiation -->
        <div class="space-y-6">
            <div class="border-b border-slate-200 dark:border-[#244456] pb-3">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Diferensiasi Relung Ekologi 3 Komoditas Pelagis
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">
                    Setiap spesies memiliki preferensi oseanografi unik yang dipisahkan secara spasial menurut batimetri dan jarak ke daratan.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Tongkol -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-lg bg-cyan-500/10 text-cyan-400 text-xs font-bold font-mono">ZONA NERITIK</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">0 – 12 NM</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        Tongkol Krai (<em class="font-normal text-slate-500 dark:text-slate-400">Auxis thazard</em>)
                    </h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Spesies pelagis neritik yang mendominasi lebih dari 60% volume pendaratan di koridor selatan Jawa. Berkelompok aktif di kolom air hangat-sedang (26,5°C – 29,5°C) dengan konsentrasi biomassa klorofil-a pesisir yang tinggi (> 0,30 mg/m³).
                    </p>
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] font-mono space-y-1 text-slate-500 dark:text-slate-400">
                        <div class="flex justify-between"><span>Preferensi SST:</span><strong class="text-slate-700 dark:text-slate-200">26,5 – 29,5 °C</strong></div>
                        <div class="flex justify-between"><span>Klorofil-a Optimal:</span><strong class="text-slate-700 dark:text-slate-200">&ge; 0,30 mg/m³</strong></div>
                        <div class="flex justify-between"><span>Kedalaman Perairan:</span><strong class="text-slate-700 dark:text-slate-200">&le; 200 meter</strong></div>
                    </div>
                </div>

                <!-- Cakalang -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 text-xs font-bold font-mono">TUBIR BENUA</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">12 – 50 NM</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        Cakalang (<em class="font-normal text-slate-500 dark:text-slate-400">Katsuwonus pelamis</em>)
                    </h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Perenang cepat samudera tropis yang sangat responsif terhadap batas termal upwelling dingin (24,0°C – 28,0°C) di pinggiran paparan benua. Mangsa utamanya adalah teri, cumi-cumi, dan krustasea yang terkonsentrasi pada tepi pusaran arus.
                    </p>
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] font-mono space-y-1 text-slate-500 dark:text-slate-400">
                        <div class="flex justify-between"><span>Preferensi SST:</span><strong class="text-slate-700 dark:text-slate-200">24,0 – 28,0 °C</strong></div>
                        <div class="flex justify-between"><span>Klorofil-a Optimal:</span><strong class="text-slate-700 dark:text-slate-200">0,15 – 0,45 mg/m³</strong></div>
                        <div class="flex justify-between"><span>Kedalaman Perairan:</span><strong class="text-slate-700 dark:text-slate-200">200 – 1.500 meter</strong></div>
                    </div>
                </div>

                <!-- Tuna Sirip Kuning -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-slate-200 dark:border-[#244456] space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-400 text-xs font-bold font-mono">SAMUDERA DALAM</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">&gt; 50 NM</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        Tuna Sirip Kuning (<em class="font-normal text-slate-500 dark:text-slate-400">Thunnus albacares</em>)
                    </h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Tuna sirip kuning oseanik bernilai ekonomis tinggi yang bermigrasi di laut lepas hingga ZEEI. Mampu menyelam melintasi lapisan termoklin dan menyukai konvergensi arus laut dalam serta struktur vortisitas mesoskala (*eddies*).
                    </p>
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] font-mono space-y-1 text-slate-500 dark:text-slate-400">
                        <div class="flex justify-between"><span>Preferensi SST:</span><strong class="text-slate-700 dark:text-slate-200">25,0 – 29,0 °C</strong></div>
                        <div class="flex justify-between"><span>Klorofil-a Optimal:</span><strong class="text-slate-700 dark:text-slate-200">0,10 – 0,35 mg/m³</strong></div>
                        <div class="flex justify-between"><span>Kedalaman Perairan:</span><strong class="text-slate-700 dark:text-slate-200">&gt; 1.000 meter</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- HSI Composite Formulation Card -->
        <x-ui.card title="Formulasi Matematis Habitat Suitability Index (HSI) Komposit">
            <div class="space-y-4">
                <div class="p-5 rounded-2xl bg-slate-900 text-center space-y-2 border border-cyan-500/20 shadow-inner">
                    <span class="text-xs font-mono text-cyan-400 uppercase tracking-widest block font-bold">PERSAMAAN ENSEMBLE KOMPOSIT MULTI-ALGORITMA</span>
                    <div class="text-lg sm:text-2xl font-mono font-bold text-white tracking-wide">
                        HSI<sub>spesies</sub> = w<sub>MaxEnt</sub> &times; P<sub>MaxEnt</sub> + w<sub>RF</sub> &times; P<sub>RF</sub> + w<sub>GLM</sub> &times; P<sub>GLM</sub>
                    </div>
                    <p class="text-xs text-slate-400 font-mono mt-1">
                        dengan kendala optimasi bobot: &Sigma;w = w<sub>MaxEnt</sub> + w<sub>RF</sub> + w<sub>GLM</sub> = 1,000 &bull; P &isin; [0,000 – 1,000]
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456] text-xs">
                        <strong class="text-cyan-600 dark:text-cyan-400 block mb-1 font-mono">1. MaxEnt (Entropi Maksimum)</strong>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                            Mencegah over-estimasi pada data ketiadaan kehadiran semu (*presence-only data*), menjaga distribusi probabilitas paling seragam di bawah batasan lingkungan.
                        </p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456] text-xs">
                        <strong class="text-emerald-600 dark:text-emerald-400 block mb-1 font-mono">2. Random Forest (RF)</strong>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                            Menangkap interaksi multi-variabel non-linear yang kompleks dan toleran terhadap korelasi silang (*collinearity*) antar-parameter oseanografi.
                        </p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456] text-xs">
                        <strong class="text-amber-600 dark:text-amber-400 block mb-1 font-mono">3. GLM (Generalized Linear Model)</strong>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                            Memberikan dasar respon parametrik yang stabil dengan fungsi tautan logit (*logistic link function*) untuk mengendalikan bias variansi ekstrem.
                        </p>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-cyan-50 dark:bg-[#12364A]/40 border border-cyan-200 dark:border-cyan-500/20 text-xs text-slate-700 dark:text-slate-300 space-y-1">
                    <strong class="text-cyan-800 dark:text-cyan-300 font-bold block">Kriteria Penentuan Hotspot Penangkapan:</strong>
                    <p class="leading-relaxed">
                        Sebuah sel grid 0,10° diklasifikasikan sebagai <code>is_hotspot = true</code> apabila nilai kesesuaian habitat tertinggi di antara ketiga spesies memenuhi batas ambang: 
                        <code class="font-bold text-cyan-600 dark:text-cyan-400">max(HSI<sub>Tongkol Krai</sub>, HSI<sub>Cakalang</sub>, HSI<sub>Tuna Sirip Kuning</sub>) &ge; 0,70</code>.
                    </p>
                </div>
            </div>
        </x-ui.card>

        <!-- Model Evaluation Summary Table -->
        <x-ui.card title="Ringkasan Parameter & Evaluasi Proxy Model Spasial (Provisional)">
            <x-ui.table :headers="['Spesies Target', 'Bobot MaxEnt', 'Bobot Random Forest', 'Bobot GLM', 'Proxy AUC', 'Proxy TSS', 'Status Ilmiah']">
                <tr>
                    <td class="px-5 py-3.5 font-bold">Tongkol Krai (<em>Auxis thazard</em>)</td>
                    <td class="px-5 py-3.5 font-mono">0.332</td>
                    <td class="px-5 py-3.5 font-mono">0.335</td>
                    <td class="px-5 py-3.5 font-mono">0.333</td>
                    <td class="px-5 py-3.5 font-mono font-bold text-cyan-500">0.892*</td>
                    <td class="px-5 py-3.5 font-mono">0.752*</td>
                    <td class="px-5 py-3.5 text-xs text-amber-600 dark:text-amber-400 font-semibold">Provisional Proxy (Belum Uji Blok Spasial)</td>
                </tr>
                <tr>
                    <td class="px-5 py-3.5 font-bold">Cakalang (<em>Katsuwonus pelamis</em>)</td>
                    <td class="px-5 py-3.5 font-mono">0.320</td>
                    <td class="px-5 py-3.5 font-mono">0.355</td>
                    <td class="px-5 py-3.5 font-mono">0.325</td>
                    <td class="px-5 py-3.5 font-mono font-bold text-cyan-500">0.795*</td>
                    <td class="px-5 py-3.5 font-mono">0.630*</td>
                    <td class="px-5 py-3.5 text-xs text-amber-600 dark:text-amber-400 font-semibold">Provisional Proxy (Belum Uji Blok Spasial)</td>
                </tr>
                <tr>
                    <td class="px-5 py-3.5 font-bold">Tuna Sirip Kuning (<em>Thunnus albacares</em>)</td>
                    <td class="px-5 py-3.5 font-mono">0.295</td>
                    <td class="px-5 py-3.5 font-mono">0.365</td>
                    <td class="px-5 py-3.5 font-mono">0.340</td>
                    <td class="px-5 py-3.5 font-mono font-bold text-cyan-500">0.784*</td>
                    <td class="px-5 py-3.5 font-mono">0.606*</td>
                    <td class="px-5 py-3.5 text-xs text-amber-600 dark:text-amber-400 font-semibold">Provisional Proxy (Belum Uji Blok Spasial)</td>
                </tr>
            </x-ui.table>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-3 leading-relaxed">
                * Catatan Transparansi Ilmiah: Bobot ensemble Cakalang dan Tuna Sirip Kuning merupakan bobot provisional hasil seleksi analis dan dievaluasi terhadap titik proxy lingkungan deterministik (<code>PROXY_HSI</code>), bukan validasi blok spasial dari koordinat tangkapan operasional lapangan. Tongkol Krai dilatih menggunakan ensemble provisional (MaxEnt, Random Forest, GLM) pada 2.500 sampel proxy neritik 132 bulan WPPNRI 573 (5-fold CV) dan tetap berstatus <code>PROXY_HSI</code> tanpa uji blok spasial lapangan.
            </p>
        </x-ui.card>

        <!-- 5-Zone Dynamic Ocean Management (DOM) Framework -->
        <div class="space-y-6">
            <div class="border-b border-slate-200 dark:border-[#244456] pb-3">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Kerangka Kerja Dynamic Ocean Management (DOM) 5-Zona
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">
                    Penataan ruang laut adaptif yang bergeser mengikuti kondisi fisik oseanografi harian untuk menyeimbangkan kepentingan ekonomi dan konservasi.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-cyan-500/30 space-y-2">
                    <span class="px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-400 font-mono font-bold text-xs">ZONA Z1</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Inshore Pelagic Fishing Ground</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Wilayah perairan pesisir (&le; 12 NM) yang ramah bagi armada perahu kecil tradisional (&lt; 10 GT). Sasaran utama: gerombolan Tongkol dan pelagis kecil lokal.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-sky-500/30 space-y-2">
                    <span class="px-2 py-0.5 rounded bg-sky-500/10 text-sky-400 font-mono font-bold text-xs">ZONA Z2</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Offshore Pelagic Fishing Ground</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Wilayah laut lepas (&gt; 12 NM hingga ZEEI) untuk armada kapal penangkap 10–30 GT dan &gt; 30 GT. Sasaran utama: Cakalang dan Tuna Sirip Kuning.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-emerald-500/30 space-y-2">
                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-mono font-bold text-xs">ZONA Z3</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Pelagic Feeding Core</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Pusat perairan kaya nutrien hasil divergensi arus dan upwelling dengan kelimpahan pakan alami melimpah tempat berkumpulnya tuna dewasa.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-amber-500/30 space-y-2">
                    <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 font-mono font-bold text-xs">ZONA Z4</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Pelagic Nursery Ground</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Daerah asuhan dan pembesaran anakan ikan pelagis. Disertai anjuran selektivitas ukuran mata jaring demi keberlanjutan regenerasi stok.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-rose-500/30 space-y-2">
                    <span class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 font-mono font-bold text-xs">ZONA Z5</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">High Bycatch Risk Zone</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Wilayah dengan risiko tinggi interaksi satwa laut non-sasaran (penyu, hiu, dan mamalia laut) yang mewajibkan penerapan teknik mitigasi standar IOTC.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#071B2B] border border-purple-500/30 space-y-2">
                    <span class="px-2 py-0.5 rounded bg-purple-500/10 text-purple-400 font-mono font-bold text-xs">SISTEM KENDALI</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Adaptasi Temporal Harian</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Batas zonasi dievaluasi ulang setiap hari mengikuti dinamika satelit bumi sehingga perlindungan laut bersifat dinamis dan tidak mengekang nelayan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.public>
