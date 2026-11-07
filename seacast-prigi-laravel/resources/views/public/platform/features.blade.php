<x-layouts.public title="Fitur Berbasis Armada — SEACAST">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        <!-- Header -->
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Arsitektur Platform', 'url' => route('platform')], ['label' => 'Fitur Pengguna', 'url' => null]]" />
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Fitur Spesifik Berbasis Profil Armada Nelayan
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-3xl leading-relaxed">
                Platform SEACAST dirancang modular untuk merespon kebutuhan operasional spesifik nelayan skala kecil (&lt;10 GT), kapal penangkap skala menengah (20–30 GT), serta otoritas Syahbandar Pelabuhan PPN Prigi.
            </p>
        </div>

        <!-- Personas Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.card title="Nelayan Tradisional (< 10 GT)">
                <div class="space-y-4 text-xs">
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-500/30">
                        67% Armada PPN Prigi
                    </span>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                        Fokus pada daerah penangkapan pesisir Z1 (&le;12 NM), penghematan konsumsi solar, dan panduan gelombang BMKG harian.
                    </p>
                    <ul class="space-y-2 text-slate-700 dark:text-slate-300 font-mono list-disc list-inside">
                        <li>Filter radius &le; 12 NM otomatis</li>
                        <li>Estimasi liter solar per trip</li>
                        <li>Peringatan gelombang ekstrem &ge; 2m</li>
                        <li>Lembar cetak A4 siap tempel TPI</li>
                    </ul>
                </div>
            </x-ui.card>

            <x-ui.card title="Kapal Motor / Purse Seine (20–30 GT)">
                <div class="space-y-4 text-xs">
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-300 border border-blue-500/30">
                        20% Armada PPN Prigi
                    </span>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                        Menyasar hotspot lepas pantai Z2 dan Z3 di sekitar lereng benua (*shelf break*) dengan estimasi durasi tempuh dan hasil tangkap optimal.
                    </p>
                    <ul class="space-y-2 text-slate-700 dark:text-slate-300 font-mono list-disc list-inside">
                        <li>Eksplorasi zona Z2 & Z3 tubir benua</li>
                        <li>Pelacak batas front termal tuna</li>
                        <li>Kalkulator jelajah What-If</li>
                        <li>Deteksi dini bycatch penyu/hiu Z5</li>
                    </ul>
                </div>
            </x-ui.card>

            <x-ui.card title="Syahbandar & Otoritas Pelabuhan">
                <div class="space-y-4 text-xs">
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300 border border-amber-500/30">
                        Kantor Pelabuhan Prigi
                    </span>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                        Monitoring keselamatan cuaca berlayar, evaluasi izin tangkap, dan rekonsiliasi data pendaratan ikan bulanan.
                    </p>
                    <ul class="space-y-2 text-slate-700 dark:text-slate-300 font-mono list-disc list-inside">
                        <li>Pemantauan cuaca sebelum SPB terbit</li>
                        <li>Rekonsiliasi pendaratan 132 bulan</li>
                        <li>Audit kepatuhan mitigasi bycatch IOTC</li>
                        <li>Ekspor data GIS & GeoJSON resmi</li>
                    </ul>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.public>

