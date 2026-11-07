<x-layouts.public title="Sumber Data Satelit & Telemetri — SEACAST">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        <!-- Header -->
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Arsitektur Platform', 'url' => route('platform')], ['label' => 'Sumber Data', 'url' => null]]" />
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Sumber Data Satelit & Protokol Telemetri
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-3xl leading-relaxed">
                Rincian penyedia data satelit oseanografi, frekuensi pembaruan, protokol integrasi, dan status verifikasi sistem di SEACAST.
            </p>
        </div>

        <!-- Sources Table -->
        <x-ui.card title="Katalog Penyedia Data Penginderaan Jauh Kelautan">
            <x-ui.table :headers="['Penyedia & Sensor', 'Parameter Biofisik', 'Resolusi Asli', 'Frekuensi', 'Status Feed']">
                <tr class="hover:bg-slate-50 dark:hover:bg-[#12364A]/50 transition-colors">
                    <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white">Copernicus Marine (CMEMS)</td>
                    <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300">Suhu Muka Laut & Arus HYCOM</td>
                    <td class="px-5 py-3.5 font-mono text-cyan-600 dark:text-cyan-400">0,01° (~1,1 km)</td>
                    <td class="px-5 py-3.5 text-xs font-mono">Harian (03:30 WIB)</td>
                    <td class="px-5 py-3.5"><span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-500/30">TERVERIFIKASI</span></td>
                </tr>
                <tr class="hover:bg-slate-50 dark:hover:bg-[#12364A]/50 transition-colors">
                    <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white">NASA OceanColor (MODIS/VIIRS)</td>
                    <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300">Konsentrasi Klorofil-a (Chl-a)</td>
                    <td class="px-5 py-3.5 font-mono text-emerald-600 dark:text-emerald-400">4 km / 1 km</td>
                    <td class="px-5 py-3.5 text-xs font-mono">Harian (03:30 WIB)</td>
                    <td class="px-5 py-3.5"><span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-500/30">TERVERIFIKASI</span></td>
                </tr>
                <tr class="hover:bg-slate-50 dark:hover:bg-[#12364A]/50 transition-colors">
                    <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white">JAXA EORC (GCOM-C SGLI)</td>
                    <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300">Klorofil-a Cadangan (Failover)</td>
                    <td class="px-5 py-3.5 font-mono text-cyan-600 dark:text-cyan-400">250 m / 1 km</td>
                    <td class="px-5 py-3.5 text-xs font-mono">Harian (Pagi)</td>
                    <td class="px-5 py-3.5"><span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-500/30">TERVERIFIKASI</span></td>
                </tr>
                <tr class="hover:bg-slate-50 dark:hover:bg-[#12364A]/50 transition-colors">
                    <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white">GEBCO 2024 Bathymetric Grid</td>
                    <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300">Kedalaman Dasar Laut & Tubir</td>
                    <td class="px-5 py-3.5 font-mono text-sky-600 dark:text-sky-400">15 busur detik</td>
                    <td class="px-5 py-3.5 text-xs font-mono">Statik Kanonik</td>
                    <td class="px-5 py-3.5"><span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-500/30">TERVERIFIKASI</span></td>
                </tr>
                <tr class="hover:bg-slate-50 dark:hover:bg-[#12364A]/50 transition-colors">
                    <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white">BMKG INA-OFS & Ina-WAVES</td>
                    <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300">Tinggi Gelombang & Angin Laut</td>
                    <td class="px-5 py-3.5 font-mono text-amber-600 dark:text-amber-400">0,05° (~5,5 km)</td>
                    <td class="px-5 py-3.5 text-xs font-mono">Harian (Prediksi)</td>
                    <td class="px-5 py-3.5"><span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-500/30">TERVERIFIKASI</span></td>
                </tr>
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.public>

