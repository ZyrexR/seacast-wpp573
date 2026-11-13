<x-layouts.public title="Dynamic Ocean Management (DOM) — SEACAST">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        <!-- Header -->
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Kerangka Sains', 'url' => route('science')], ['label' => 'Dynamic Ocean Management', 'url' => null]]" />
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Kerangka Kerja Dynamic Ocean Management (DOM)
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-3xl leading-relaxed">
                Tata kelola spasial kelautan bergerak yang memadukan perlindungan daerah asuhan benih tuna (*nursery ground*) dan mitigasi tangkapan sampingan megafauna laut (*bycatch*) dengan efisiensi operasi penangkapan nelayan di WPPNRI 573.
            </p>
        </div>

        <!-- 5 DOM Zones Cards -->
        <div class="space-y-6">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Alokasi 5 Zona Fungsional Kelautan</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-ui.card title="Zona Z1 — PFG Inshore (Pesisir Prigi)">
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#38BDF8]"></span>
                            <span class="font-bold text-cyan-600 dark:text-cyan-400 font-mono">Prioritas Nelayan Kecil</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                            Jarak &le; 12 NM dari PPN Prigi, HSI Tuna &ge; 0,55, dan risiko bycatch rendah. Dikhususkan untuk perahu motor &lt;10 GT dengan alat tangkap pancing ulur (*handline*).
                        </p>
                    </div>
                </x-ui.card>

                <x-ui.card title="Zona Z2 — PFG Offshore (Lepas Pantai)">
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#2563EB]"></span>
                            <span class="font-bold text-blue-600 dark:text-blue-400 font-mono">Armada Kapal Menengah</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                            Jarak 12–60 NM dari pelabuhan di atas kedalaman tubir benua (&gt;200 m). Menghadirkan biomassa Tuna Sirip Kuning dan Cakalang dewasa untuk kapal pukat cincin (*purse seine*).
                        </p>
                    </div>
                </x-ui.card>

                <x-ui.card title="Zona Z3 — Feeding Ground Core (Upwelling)">
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#06B6D4]"></span>
                            <span class="font-bold text-cyan-600 dark:text-cyan-400 font-mono">Inti Kesuburan Plankton</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                            Gradien termal &ge; 0,035 °C/km dan klorofil-a &ge; 0,25 mg/m³. Pusat agregasi pakan alami tuna (teri, cumi pelagis) akibat pengadukan massa air kaya nutrien.
                        </p>
                    </div>
                </x-ui.card>

                <x-ui.card title="Zona Z4 — Nursery Ground Conservation">
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#A855F7]"></span>
                            <span class="font-bold text-purple-600 dark:text-purple-400 font-mono">Perlindungan Asuhan Juvenil</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                            Perairan paparan dangkal (&lt;150 m) dengan indikasi anakan tuna positif. Dilarang menggunakan jaring pukat berkantong rapat guna menjaga regenerasi stok ikan.
                        </p>
                    </div>
                </x-ui.card>

                <x-ui.card title="Zona Z5 — Bycatch Risk Screening Advisory" class="md:col-span-2">
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#F43F5E]"></span>
                            <span class="font-bold text-rose-600 dark:text-rose-400 font-mono">Mitigasi Megafauna IOTC</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                            Area perlintasan rentan penyu lekang dan hiu martil (&gt;40% probabilitas interaksi). Dilarang menurunkan pancing rawai permukaan dan diwajibkan menggunakan mata pancing melingkar (*circle hooks* &ge; 16/0).
                        </p>
                    </div>
                </x-ui.card>
            </div>
        </div>

        <!-- Waterfall Conflict Resolution Panel -->
        <div class="p-6 rounded-2xl bg-slate-100 dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#16445A] space-y-3">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white font-mono flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                <span>Hierarki Resolusi Konflik Waterfall DOM</span>
            </h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                Algoritma zonasi mengevaluasi setiap sel secara sekuensial: <strong class="text-slate-900 dark:text-white font-mono">Z4 &gt; Z5 &gt; Z3 &gt; Z1 &gt; Z2 &gt; GEN</strong>. Perlindungan benih dan kepatuhan konservasi megafauna dilindungi terlebih dahulu sebelum suatu sel dialokasikan sebagai fishing ground.
            </p>
        </div>
    </div>
</x-layouts.public>

