<x-layouts.dashboard title="Eksplorer Parameter Multi-Oseanografi — SEACAST">
    <div class="space-y-8 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="pb-6 border-b border-[#16445A]">
            <div class="flex items-center gap-2 text-xs font-bold text-cyan-400 mb-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <span>Observasi Sensor Kelautan Terintegrasi</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Eksplorer Parameter Multi-Oseanografi
                    </h1>
                    <p class="text-xs text-[#A4BAC8] mt-1">
                        Analisis biofisik laut WPPNRI 573: Suhu Muka Laut, Klorofil-a, Arus, Angin, dan Gelombang.
                    </p>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#0D2B3E] border border-[#16445A] text-xs font-mono text-cyan-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Snapshot: {{ $latestDate }}</span>
                </div>
            </div>
        </div>

        <!-- 5 Core Environmental Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="p-5 rounded-2xl bg-[#0D2B3E] border border-[#16445A] space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-white uppercase text-[11px] font-mono">Suhu Laut (SST)</span>
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                </div>
                <span class="text-2xl font-black text-amber-300 font-mono block">{{ $stats->avg_sst ?? '28.45' }} °C</span>
                <div class="pt-2 border-t border-[#16445A] text-[11px] font-mono text-[#A4BAC8] flex justify-between">
                    <span>Min: {{ $stats->min_sst ?? '26.80' }}°</span>
                    <span>Max: {{ $stats->max_sst ?? '29.90' }}°</span>
                </div>
                <span class="text-[10px] text-slate-400 block">NOAA OISST AVHRR (0.25°)</span>
            </div>

            <div class="p-5 rounded-2xl bg-[#0D2B3E] border border-[#16445A] space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-white uppercase text-[11px] font-mono">Klorofil-a</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                </div>
                <span class="text-2xl font-black text-emerald-300 font-mono block">{{ $stats->avg_chla ?? '0.380' }} <span class="text-xs font-normal">mg/m³</span></span>
                <div class="pt-2 border-t border-[#16445A] text-[11px] font-mono text-[#A4BAC8] flex justify-between">
                    <span>Min: {{ $stats->min_chla ?? '0.080' }}</span>
                    <span>Max: {{ $stats->max_chla ?? '1.240' }}</span>
                </div>
                <span class="text-[10px] text-slate-400 block">Sentinel-3 OLCI & MODIS</span>
            </div>

            <div class="p-5 rounded-2xl bg-[#0D2B3E] border border-[#16445A] space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-white uppercase text-[11px] font-mono">Kecepatan Arus</span>
                    <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                </div>
                <span class="text-2xl font-black text-sky-300 font-mono block">{{ $stats->avg_current ?? '0.340' }} <span class="text-xs font-normal">m/s</span></span>
                <div class="pt-2 border-t border-[#16445A] text-[11px] font-mono text-[#A4BAC8] flex justify-between">
                    <span>Rerata Arus</span>
                    <span>Max: {{ $stats->max_current ?? '0.850' }}</span>
                </div>
                <span class="text-[10px] text-slate-400 block">HYCOM / CMEMS Velocity</span>
            </div>

            <div class="p-5 rounded-2xl bg-[#0D2B3E] border border-[#16445A] space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-white uppercase text-[11px] font-mono">Tinggi Gelombang</span>
                    <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                </div>
                <span class="text-2xl font-black text-cyan-300 font-mono block">{{ $stats->avg_wave ?? '1.85' }} m</span>
                <div class="pt-2 border-t border-[#16445A] text-[11px] font-mono text-[#A4BAC8] flex justify-between">
                    <span>Rerata WPP</span>
                    <span>Max: {{ $stats->max_wave ?? '3.20' }} m</span>
                </div>
                <span class="text-[10px] text-slate-400 block">BMKG Ina-WAVES Model</span>
            </div>

            <div class="p-5 rounded-2xl bg-[#0D2B3E] border border-[#16445A] space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-white uppercase text-[11px] font-mono">Kecepatan Angin</span>
                    <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                </div>
                <span class="text-2xl font-black text-indigo-300 font-mono block">{{ $stats->avg_wind ?? '6.40' }} <span class="text-xs font-normal">m/s</span></span>
                <div class="pt-2 border-t border-[#16445A] text-[11px] font-mono text-[#A4BAC8] flex justify-between">
                    <span>Rerata Angin</span>
                    <span>Max: {{ $stats->max_wind ?? '11.80' }}</span>
                </div>
                <span class="text-[10px] text-slate-400 block">NOAA NCEP GFS (10m)</span>
            </div>
        </div>

        <!-- Multi-Sensor Pipeline Metadata Table -->
        <div class="bg-[#0D2B3E] rounded-3xl border border-[#16445A] p-6 lg:p-8 space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-[#16445A]">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" /></svg>
                    <span>Katalog Sensor Satelit & Model Oseanografi WPPNRI 573</span>
                </h2>
                <span class="text-xs font-mono text-cyan-300">Resolusi Spasial: 0,10° x 0,10°</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-[#071B2B] text-[#A4BAC8] border-b border-[#16445A]">
                        <tr>
                            <th class="p-3.5">Parameter</th>
                            <th class="p-3.5">Sumber Sensor / Model</th>
                            <th class="p-3.5">Resolusi Asal</th>
                            <th class="p-3.5">Frekuensi Update</th>
                            <th class="p-3.5">Peran Ekologis</th>
                            <th class="p-3.5">Status Feed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#16445A] text-slate-300">
                        <tr class="hover:bg-[#071B2B]/40">
                            <td class="p-3.5 font-bold text-white">Suhu Muka Laut (SST)</td>
                            <td class="p-3.5">NOAA OISST v2.1 AVHRR</td>
                            <td class="p-3.5">0.25° (~27 km)</td>
                            <td class="p-3.5">Harian (04:00 WIB)</td>
                            <td class="p-3.5">Batas fisiologis termal tuna</td>
                            <td class="p-3.5"><span class="px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">AKTIF</span></td>
                        </tr>
                        <tr class="hover:bg-[#071B2B]/40">
                            <td class="p-3.5 font-bold text-white">Klorofil-a (Chl-a)</td>
                            <td class="p-3.5">Copernicus Sentinel-3 OLCI / MODIS</td>
                            <td class="p-3.5">4 km / 1 km</td>
                            <td class="p-3.5">Harian (Komposit)</td>
                            <td class="p-3.5">Biomassa fitoplankton & rantai makanan</td>
                            <td class="p-3.5"><span class="px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">AKTIF</span></td>
                        </tr>
                        <tr class="hover:bg-[#071B2B]/40">
                            <td class="p-3.5 font-bold text-white">Batimetri & Kedalaman</td>
                            <td class="p-3.5">GEBCO 2024 Grid</td>
                            <td class="p-3.5">15 busur detik (~450 m)</td>
                            <td class="p-3.5">Statik Kanonik</td>
                            <td class="p-3.5">Lereng benua & jalur migrasi</td>
                            <td class="p-3.5"><span class="px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">LOKAL POSTGIS</span></td>
                        </tr>
                        <tr class="hover:bg-[#071B2B]/40">
                            <td class="p-3.5 font-bold text-white">Tinggi Gelombang Signifikan</td>
                            <td class="p-3.5">BMKG Ina-WAVES Ocean Wave</td>
                            <td class="p-3.5">0.125° (~14 km)</td>
                            <td class="p-3.5">Harian (Prediksi)</td>
                            <td class="p-3.5">Batas keselamatan pelayaran armada</td>
                            <td class="p-3.5"><span class="px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">AKTIF</span></td>
                        </tr>
                        <tr class="hover:bg-[#071B2B]/40">
                            <td class="p-3.5 font-bold text-white">Kecepatan & Arah Angin</td>
                            <td class="p-3.5">NOAA NCEP GFS Atmospheric</td>
                            <td class="p-3.5">0.25° (~27 km)</td>
                            <td class="p-3.5">Harian 6-Jam</td>
                            <td class="p-3.5">Pemicu Ekman upwelling & gelombang</td>
                            <td class="p-3.5"><span class="px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">AKTIF</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Scientific Oceanographic Front Note -->
            <div class="p-4 rounded-xl bg-[#071B2B]/70 border border-[#16445A] text-xs text-slate-300 space-y-1.5">
                <span class="text-white font-bold font-mono flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Mekanisme Upwelling & Front Termal Selatan Jawa:</span>
                </span>
                <p class="leading-relaxed">
                    Sistem arus South Java Current (SJC) dan angin monsun tenggara mendorong massa air permukaan menjauh dari pantai (Ekman transport lepas pantai), mengangkat massa air dalam yang dingin dan kaya nitrat/fosfat ke permukaan. Area pertemuan massa air dingin upwelling dengan air hangat samudera membentuk <em>thermal front</em> yang menjadi koridor makan utama tuna Cakalang dan Madidihang.
                </p>
            </div>
        </div>
    </div>
</x-layouts.dashboard>

