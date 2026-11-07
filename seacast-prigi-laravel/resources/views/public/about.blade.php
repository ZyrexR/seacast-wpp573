<x-layouts.public title="Tentang SEACAST — Scientific Ocean Intelligence & Fisheries Decision Support">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        <!-- Header -->
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Tentang SEACAST', 'url' => null]]" />
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Pelabuhan Perikanan Nusantara (PPN) Prigi & Koridor WPPNRI 573
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-3xl leading-relaxed">
                PPN Prigi yang terletak di Teluk Prigi, Kabupaten Trenggalek, Jawa Timur (Koordinat 111.7275° E, -8.2833° S), merupakan pelabuhan perikanan samudera strategis yang melayani armada penangkap ikan pelagis di koridor selatan Jawa hingga Nusa Tenggara (WPPNRI 573).
            </p>
        </div>

        <!-- 3 KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.stat-card
                title="Produksi Pelagis 11 Tahun"
                value="{{ number_format($totalTons) }} Ton"
                subtitle="Arsip 132 Bulan (2015 - 2025)"
                accent="cyan"
            />
            <x-ui.stat-card
                title="Total Trip Terdata"
                value="{{ number_format($totalTrips) }}"
                subtitle="Pencatatan Resmi Syahbandar"
                accent="emerald"
            />
            <x-ui.stat-card
                title="Rata-rata CPUE Proxy"
                value="{{ $avgCpue }} kg"
                subtitle="Proksi CPUE Tahunan (Nominal) per Trip"
                accent="amber"
            />
        </div>

        <!-- Fleet Structure Table -->
        <x-ui.card title="Struktur Armada Penangkap Ikan PPN Prigi (1.269 Kapal)">
            <x-ui.table :headers="['Kelas Gross Tonnage (GT)', 'Jumlah Unit', 'Porsi Armada', 'Jelajah Aman', 'Profil Operasional']">
                @foreach($fleet as $f)
                    <tr class="hover:bg-slate-50 dark:hover:bg-[#12364A]/50 transition-colors">
                        <td class="px-5 py-3.5 font-bold font-mono text-cyan-600 dark:text-cyan-400">{{ $f->gt_class }}</td>
                        <td class="px-5 py-3.5 font-mono font-bold">{{ number_format($f->vessel_count) }} Unit</td>
                        <td class="px-5 py-3.5 font-mono">{{ $f->pct_fleet }}%</td>
                        <td class="px-5 py-3.5 font-mono text-slate-600 dark:text-slate-300">&le; {{ $f->max_range_nm }} NM</td>
                        <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-400">{{ $f->operational_profile }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>

        <!-- Scientific Disclaimer Alert -->
        <x-ui.alert variant="info" title="Transparansi Ilmiah: Proksi CPUE Nominal">
            Metrik hasil tangkapan dihitung sebagai <strong>Proksi CPUE Tahunan (Nominal)</strong> berdasarkan total pendaratan dibagi jumlah trip resmi pendaratan di PPN Prigi. Metrik ini tidak mencakup standardisasi <em>hook rate</em> atau <em>soaking time</em> per kapal.
        </x-ui.alert>
    </div>
</x-layouts.public>

