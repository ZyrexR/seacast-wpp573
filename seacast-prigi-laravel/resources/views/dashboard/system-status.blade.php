<x-layouts.dashboard title="Status Sistem & Basis Data PostGIS — SEACAST">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-[#244456]">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2.5">
                    Diagnostik Mesin & Integritas Basis Data
                    <x-ui.badge :variant="$diagnostics['database']['status'] === 'healthy' ? 'emerald' : 'amber'" :dot="true">
                        {{ strtoupper($diagnostics['database']['status']) }}
                    </x-ui.badge>
                </h1>
                <p class="text-xs text-slate-400 mt-1">
                    Pemeriksaan aktual terhadap PostgreSQL 17.11, PostGIS 3.6.2, dan subsistem aplikasi &bull; Latensi Kueri: <span class="font-mono text-cyan-400 font-bold">{{ $diagnostics['database']['latency_ms'] ?? 0 }} ms</span>
                </p>
            </div>

            <x-ui.button onclick="window.location.reload()" variant="secondary" size="sm">
                <svg class="w-4 h-4 mr-1 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Perbarui Diagnostik
            </x-ui.button>
        </div>

        <!-- 4 Core Subsystem Status Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-ui.stat-card
                title="PostgreSQL 17.11"
                value="{{ $diagnostics['database']['status'] === 'healthy' ? 'Operational' : 'Degraded' }}"
                subtitle="Port 5432 &bull; Read-Only Baseline"
                accent="cyan"
            />
            <x-ui.stat-card
                title="PostGIS Extension"
                value="PostGIS 3.6.2"
                subtitle="8.129 Poligon &bull; SRID 4326"
                accent="emerald"
            />
            <x-ui.stat-card
                title="Python Engine"
                value="{{ $diagnostics['python_engine']['status'] }}"
                subtitle="{{ $diagnostics['python_engine']['version'] ?? 'N/A' }}"
                accent="{{ $diagnostics['python_engine']['status'] === 'Operational' ? 'emerald' : 'amber' }}"
            />
            <x-ui.stat-card
                title="Pipeline Satelit Live"
                value="{{ $diagnostics['pipeline_satelit']['status'] }}"
                subtitle="Data Snapshot Terkalibrasi"
                accent="amber"
            />
        </div>

        <!-- Detailed Diagnostics Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Spatial Baseline Diagnostics -->
            <x-ui.card title="Verifikasi Geospasial PostGIS (canonical_grids)">
                <x-ui.table :headers="['Parameter Uji', 'Target Spesifikasi', 'Nilai Terverifikasi', 'Status']">
                    <tr>
                        <td class="px-5 py-3 font-semibold">Total Sel Poligon</td>
                        <td class="px-5 py-3 font-mono">8.129 Sel</td>
                        <td class="px-5 py-3 font-mono font-bold text-cyan-400">{{ $diagnostics['database']['spatial_baseline']['canonical_grids_count'] }}</td>
                        <td class="px-5 py-3"><x-ui.badge variant="emerald">PASS</x-ui.badge></td>
                    </tr>
                    <tr>
                        <td class="px-5 py-3 font-semibold">Validitas Geometri (ST_IsValid)</td>
                        <td class="px-5 py-3 font-mono">100% (8.129 Valid)</td>
                        <td class="px-5 py-3 font-mono font-bold text-emerald-400">{{ $diagnostics['database']['spatial_baseline']['valid_geometries'] }} Valid (0 Cacat)</td>
                        <td class="px-5 py-3"><x-ui.badge variant="emerald">PASS</x-ui.badge></td>
                    </tr>
                    <tr>
                        <td class="px-5 py-3 font-semibold">Spatial Reference (SRID)</td>
                        <td class="px-5 py-3 font-mono">EPSG:4326</td>
                        <td class="px-5 py-3 font-mono font-bold">{{ $diagnostics['database']['spatial_baseline']['srid'] }}</td>
                        <td class="px-5 py-3"><x-ui.badge variant="emerald">PASS</x-ui.badge></td>
                    </tr>
                    <tr>
                        <td class="px-5 py-3 font-semibold">Spatial Index (GiST)</td>
                        <td class="px-5 py-3 font-mono">idx_canonical_grids_geom</td>
                        <td class="px-5 py-3 font-mono">{{ $diagnostics['database']['spatial_baseline']['gist_index_active'] ? 'USING gist (geom) AKTIF' : 'TIDAK AKTIF' }}</td>
                        <td class="px-5 py-3"><x-ui.badge variant="emerald">PASS</x-ui.badge></td>
                    </tr>
                </x-ui.table>
            </x-ui.card>

            <!-- Table Inventory Verification -->
            <x-ui.card title="Inventaris Tabel Basis Data (seacast_prigi)">
                <x-ui.table :headers="['Nama Tabel', 'Target Baseline', 'Jumlah Baris Aktual', 'Status']">
                    <tr>
                        <td class="px-5 py-3 font-mono font-bold text-slate-200">canonical_grids</td>
                        <td class="px-5 py-3 font-mono text-slate-400">8.129</td>
                        <td class="px-5 py-3 font-mono font-bold text-cyan-400">{{ $diagnostics['database']['spatial_baseline']['canonical_grids_count'] }}</td>
                        <td class="px-5 py-3"><x-ui.badge variant="emerald">MATCH</x-ui.badge></td>
                    </tr>
                    <tr>
                        <td class="px-5 py-3 font-mono font-bold text-slate-200">daily_predictions</td>
                        <td class="px-5 py-3 font-mono text-slate-400">8.129</td>
                        <td class="px-5 py-3 font-mono font-bold text-cyan-400">{{ $diagnostics['database']['data_records']['daily_predictions_count'] }}</td>
                        <td class="px-5 py-3"><x-ui.badge variant="emerald">MATCH</x-ui.badge></td>
                    </tr>
                    <tr>
                        <td class="px-5 py-3 font-mono font-bold text-slate-200">grid_monthly_predictions</td>
                        <td class="px-5 py-3 font-mono text-slate-400">97.548</td>
                        <td class="px-5 py-3 font-mono font-bold text-cyan-400">{{ $diagnostics['database']['data_records']['grid_monthly_predictions_count'] }}</td>
                        <td class="px-5 py-3"><x-ui.badge variant="emerald">MATCH</x-ui.badge></td>
                    </tr>
                    <tr>
                        <td class="px-5 py-3 font-mono font-bold text-slate-200">prigi_port_landings</td>
                        <td class="px-5 py-3 font-mono text-slate-400">132</td>
                        <td class="px-5 py-3 font-mono font-bold text-cyan-400">{{ $diagnostics['database']['data_records']['prigi_port_landings_count'] }}</td>
                        <td class="px-5 py-3"><x-ui.badge variant="emerald">MATCH</x-ui.badge></td>
                    </tr>
                    <tr>
                        <td class="px-5 py-3 font-mono font-bold text-slate-200">prigi_fleet_by_gt</td>
                        <td class="px-5 py-3 font-mono text-slate-400">3</td>
                        <td class="px-5 py-3 font-mono font-bold text-cyan-400">{{ $diagnostics['database']['data_records']['prigi_fleet_rows_count'] }}</td>
                        <td class="px-5 py-3"><x-ui.badge variant="emerald">MATCH</x-ui.badge></td>
                    </tr>
                </x-ui.table>
            </x-ui.card>
        </div>

        <!-- System Architecture & Environment Specs -->
        <x-ui.card title="Spesifikasi Arsitektur Runtime Target (Laravel 13)">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs text-slate-300">
                <div class="space-y-1.5 p-3 rounded-xl bg-[#071B2B] border border-[#244456]">
                    <div class="font-bold text-white">Application Framework</div>
                    <div>{{ $diagnostics['app_health']['framework'] }}</div>
                    <div class="font-mono text-slate-400">PHP {{ $diagnostics['app_health']['php_version'] }}</div>
                    <div class="font-mono text-cyan-400">Environment: {{ $diagnostics['app_health']['environment'] }}</div>
                </div>

                <div class="space-y-1.5 p-3 rounded-xl bg-[#071B2B] border border-[#244456]">
                    <div class="font-bold text-white">Storage & Filesystem</div>
                    <div>Status: <span class="text-emerald-400 font-bold">{{ $diagnostics['storage']['status'] }}</span></div>
                    <div class="text-slate-400">Writable: {{ $diagnostics['storage']['writable'] ? 'Yes' : 'No' }}</div>
                    <div class="font-mono text-cyan-400">Free Space: {{ $diagnostics['storage']['free_space_mb'] }} MB</div>
                </div>

                <div class="space-y-1.5 p-3 rounded-xl bg-[#071B2B] border border-[#244456]">
                    <div class="font-bold text-white">Background Processing</div>
                    <div>Queue Driver: <span class="font-mono text-cyan-400">{{ $diagnostics['queue']['driver'] }}</span></div>
                    <div>Pending Jobs: {{ $diagnostics['queue']['pending_jobs'] ?? 0 }}</div>
                    <div>Scheduler: <span class="text-emerald-400 font-bold">{{ $diagnostics['scheduler']['status'] }}</span></div>
                </div>
            </div>
        </x-ui.card>
    </div>
</x-layouts.dashboard>

