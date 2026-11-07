<x-layouts.dashboard title="Analitik Armada & Pendaratan Multi-Pelabuhan — SEACAST">
    
    <div class="space-y-6 max-w-7xl mx-auto pb-12">
        
        <!-- Header & Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#244456] pb-5">
            <div>
                <div class="flex items-center gap-2 text-xs font-mono text-cyan-400 mb-1">
                    <span>SEACAST</span>
                    <span>/</span>
                    <span class="uppercase">{{ $currentPortMeta['name'] }}</span>
                    <span>/</span>
                    <span class="text-slate-400">{{ $currentPortMeta['province'] }}</span>
                    <span>/</span>
                    <span class="text-slate-400">{{ $currentPortMeta['wpp'] }}</span>
                </div>
                <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2.5">
                    Analitik Armada & Pendaratan Multi-Pelabuhan
                    <x-ui.badge variant="cyan">{{ $currentPortMeta['name'] }}</x-ui.badge>
                </h1>
                <p class="text-xs text-slate-400 mt-1">
                    Observasi Empiris {{ $currentPortMeta['years_range'] }} • Komoditas: {{ $currentPortMeta['commodities'] }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard.trip-planner') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold rounded-xl text-xs transition active:scale-95 shadow-md">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    Buka Trip Planner
                </a>
            </div>
        </div>

        <!-- Port Selector Bar -->
        <div class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-2xl bg-[#091F30] border border-[#244456]">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-400">Pangkalan Pelabuhan:</span>
                <div class="inline-flex rounded-xl bg-[#071B2B] p-1 border border-[#244456]">
                    @foreach ($availablePorts as $code => $port)
                        <a href="{{ route('dashboard.fleet-analytics', ['port' => $code]) }}" 
                           class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $selectedPort === $code ? 'bg-[#13B8C8] text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-[#12364A]' }}">
                            {{ $port['name'] }} ({{ $port['province'] }})
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs font-mono text-slate-400">
                <span>Cakupan Observasi:</span>
                <span class="text-cyan-400 font-bold">{{ $currentPortMeta['years_range'] }}</span>
            </div>
        </div>

        <!-- Scientific Caveats Alert Banner -->
        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-200">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="space-y-1 text-xs">
                    <h4 class="font-bold text-amber-300 uppercase tracking-wider text-[11px]">{{ $caveats['disclaimer_title'] }}</h4>
                    <ul class="list-disc list-inside space-y-0.5 text-slate-300">
                        @foreach ($caveats['statements'] as $statement)
                            <li>{{ $statement }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Key Metrics KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Total Production -->
            <div class="p-5 rounded-2xl bg-[#091F30] border border-[#244456] shadow-xl backdrop-blur-md">
                <div class="flex items-center justify-between text-xs text-slate-400 mb-2 font-mono">
                    <span>TOTAL PENDARATAN</span>
                    <span class="text-cyan-400 font-semibold">{{ $currentPortMeta['name'] }}</span>
                </div>
                <div class="text-3xl font-bold font-mono text-white tracking-tight">
                    {{ number_format($annual['overall_total_tons'], 2) }}
                    <span class="text-sm font-sans font-normal text-slate-400">ton</span>
                </div>
                <p class="text-xs text-slate-400 mt-2">
                    Akumulasi {{ $annual['total_months_recorded'] }} bulan pendaratan komoditas pelagis utama.
                </p>
            </div>

            <!-- Total Recorded Trips -->
            <div class="p-5 rounded-2xl bg-[#091F30] border border-[#244456] shadow-xl backdrop-blur-md">
                <div class="flex items-center justify-between text-xs text-slate-400 mb-2 font-mono">
                    <span>OPERASIONAL TRIP</span>
                    <span class="text-emerald-400 font-semibold">{{ $selectedPort === 'PPS_CILACAP' ? 'TPI CILACAP' : 'TPI PRIGI' }}</span>
                </div>
                <div class="text-3xl font-bold font-mono text-white tracking-tight">
                    {{ number_format($annual['overall_total_trips']) }}
                    <span class="text-sm font-sans font-normal text-slate-400">trip</span>
                </div>
                <p class="text-xs text-slate-400 mt-2">
                    Frekuensi pelayaran penangkapan yang mendarat di pelabuhan pangkalan.
                </p>
            </div>

            <!-- Registered Fishing Fleet -->
            <div class="p-5 rounded-2xl bg-[#091F30] border border-[#244456] shadow-xl backdrop-blur-md">
                <div class="flex items-center justify-between text-xs text-slate-400 mb-2 font-mono">
                    <span>ARMADA TERDAFTAR</span>
                    <span class="text-indigo-400 font-semibold">{{ $fleet['registration_year'] }} REG</span>
                </div>
                <div class="text-3xl font-bold font-mono text-white tracking-tight">
                    {{ number_format($fleet['total_vessels']) }}
                    <span class="text-sm font-sans font-normal text-slate-400">unit kapal</span>
                </div>
                <p class="text-xs text-slate-400 mt-2">
                    Terdistribusi ke dalam {{ $fleet['classes_count'] }} kelas tonase kotor kapal motor.
                </p>
            </div>

            <!-- Average CPUE Proxy -->
            <div class="p-5 rounded-2xl bg-[#091F30] border border-[#244456] shadow-xl backdrop-blur-md">
                <div class="flex items-center justify-between text-xs text-slate-400 mb-2 font-mono">
                    <span>CPUE PROXY NOMINAL</span>
                    <span class="text-amber-400 font-semibold">RATA-RATA</span>
                </div>
                <div class="text-3xl font-bold font-mono text-white tracking-tight">
                    {{ $annual['overall_total_trips'] > 0 ? number_format(($annual['overall_total_tons'] * 1000) / $annual['overall_total_trips'], 2) : '110.06' }}
                    <span class="text-sm font-sans font-normal text-slate-400">kg / trip</span>
                </div>
                <p class="text-xs text-slate-400 mt-2">
                    Rasio nominal hasil tangkapan dibagi jumlah trip tercatat.
                </p>
            </div>

        </div>

        <!-- Section: Fleet Composition by Gross Tonnage -->
        <div class="p-6 rounded-2xl bg-[#091F30] border border-[#244456] shadow-xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#244456] pb-3">
                <div>
                    <h3 class="text-base font-semibold text-white">Komposisi Armada Nelayan {{ $currentPortMeta['name'] }} per Kelas GT</h3>
                    <p class="text-xs text-slate-400">Data agregasi registrasi kapal resmi pelabuhan per kelas tonase kotor dan estimasi daya jangkau operasional.</p>
                </div>
                <span class="px-2.5 py-1 rounded-md text-xs font-mono bg-cyan-500/10 border border-cyan-500/30 text-cyan-300">
                    {{ $fleet['classes_count'] }} Kategori Tonase
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($fleet['classes'] as $cls)
                    <div class="p-4 rounded-xl bg-[#071B2B] border border-[#244456] space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded border border-cyan-500/20">
                                KELAS {{ $cls['gt_class'] }}
                            </span>
                            <span class="text-lg font-bold font-mono text-white">{{ $cls['vessel_count'] }} <span class="text-xs font-normal text-slate-400">unit</span></span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs text-slate-400">
                                <span>Porsi Armada</span>
                                <span class="font-mono text-slate-200 font-semibold">{{ $cls['pct_fleet'] }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-cyan-500 rounded-full" style="width: {{ min(100, $cls['pct_fleet']) }}%"></div>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-800/60 text-xs space-y-1.5">
                            <div class="flex justify-between text-slate-400">
                                <span>Jangkauan Melaut:</span>
                                <span class="font-mono font-medium text-slate-200">&le; {{ $cls['max_range_nm'] }} NM</span>
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-500 block mb-1">Rekomendasi Zonasi DOM:</span>
                                <div class="flex flex-wrap gap-1">
                                    @if (is_array($cls['recommended_dom_zones']))
                                        @foreach ($cls['recommended_dom_zones'] as $zone)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-[#091F30] border border-[#244456] text-slate-300">
                                                {{ $zone }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Section: Multi-Year Annual Landings Table -->
        <div class="p-6 rounded-2xl bg-[#091F30] border border-[#244456] shadow-xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#244456] pb-3">
                <div>
                    <h3 class="text-base font-semibold text-white">Tren Pendaratan Tahunan — {{ $currentPortMeta['name'] }}</h3>
                    <p class="text-xs text-slate-400">Rekam jejak pendaratan ikan pelagis per tahun berdasarkan statistik resmi {{ $currentPortMeta['name'] }}.</p>
                </div>
                <span class="text-xs font-mono text-slate-400">{{ count($annual['years']) }} Periode Tahunan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-[11px] uppercase font-mono text-slate-400 bg-[#071B2B] border-b border-[#244456]">
                        <tr>
                            <th class="py-3 px-4">Tahun</th>
                            <th class="py-3 px-4 text-right">Cakalang (Ton)</th>
                            <th class="py-3 px-4 text-right">{{ $selectedPort === 'PPS_CILACAP' ? 'Yellowfin Tuna (Ton)' : 'Madidihang (Ton)' }}</th>
                            <th class="py-3 px-4 text-right">{{ $selectedPort === 'PPS_CILACAP' ? 'Tongkol Krai (Ton)' : 'Tongkol (Ton)' }}</th>
                            <th class="py-3 px-4 text-right font-bold text-white">Total Pelagis (Ton)</th>
                            <th class="py-3 px-4 text-right">Trip Melaut</th>
                            <th class="py-3 px-4 text-right">CPUE Proxy (kg/trip)</th>
                            <th class="py-3 px-4 text-right">Rata-rata SST (°C)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#244456]/60 font-mono">
                        @foreach ($annual['years'] as $row)
                            <tr class="hover:bg-[#12364A]/40 transition">
                                <td class="py-3 px-4 font-bold text-white">{{ data_get($row, 'year') }}</td>
                                <td class="py-3 px-4 text-right text-cyan-300">{{ number_format((float)data_get($row, 'cakalang_tons'), 2) }}</td>
                                <td class="py-3 px-4 text-right text-amber-300">{{ number_format((float)data_get($row, 'madidihang_tons'), 2) }}</td>
                                <td class="py-3 px-4 text-right text-slate-300">{{ number_format((float)data_get($row, 'tongkol_tons'), 2) }}</td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-400">{{ number_format((float)data_get($row, 'total_pelagic_tons'), 2) }}</td>
                                <td class="py-3 px-4 text-right text-slate-300">{{ number_format((int)data_get($row, 'total_trips')) }}</td>
                                <td class="py-3 px-4 text-right text-cyan-300">{{ number_format((float)data_get($row, 'avg_cpue_kg_trip'), 2) }}</td>
                                <td class="py-3 px-4 text-right text-slate-400">{{ data_get($row, 'avg_sst') }}°C</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section: Monthly Seasonality -->
        <div class="p-6 rounded-2xl bg-[#091F30] border border-[#244456] shadow-xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#244456] pb-3">
                <div>
                    <h3 class="text-base font-semibold text-white">Pola Musiman Pendaratan Bulanan ({{ $currentPortMeta['name'] }})</h3>
                    <p class="text-xs text-slate-400">Rata-rata bulanan produksi pelagis dan korelasi dinamika oseanografi perairan {{ $currentPortMeta['province'] }}.</p>
                </div>
                <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                    Siklus Oseanografi Musiman
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                @foreach ($seasonality as $m)
                    <div class="p-3 rounded-xl bg-[#071B2B] border border-[#244456] text-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-200">{{ $m['month_name'] }}</span>
                            <span class="text-[10px] font-mono text-slate-500">B{{ $m['month_num'] }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Rata-rata Produksi:</span>
                            <span class="text-sm font-mono font-bold text-cyan-300">{{ number_format($m['avg_tons'], 1) }} <span class="text-[10px] font-normal text-slate-400">ton</span></span>
                        </div>
                        <div class="pt-1.5 border-t border-[#244456]/60 flex justify-between text-[11px] font-mono text-slate-400">
                            <span>SST:</span>
                            <span class="text-emerald-400">{{ $m['avg_sst'] }}°C</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</x-layouts.dashboard>
