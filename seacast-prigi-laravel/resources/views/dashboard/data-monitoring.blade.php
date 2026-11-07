<x-layouts.dashboard title="Monitoring Pipeline & Telemetri Satelit — SEACAST">
    <div class="space-y-8 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="pb-6 border-b border-[#16445A]">
            <div class="flex items-center gap-2 text-xs font-bold text-cyan-400 mb-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Integritas Database PostGIS & Telemetri Ingest</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Monitoring Pipeline Data & Integritas PostGIS
                    </h1>
                    <p class="text-xs text-[#A4BAC8] mt-1">
                        Pusat audit kesehatan tabel database PostgreSQL 17.11 + PostGIS 3.6.2 dan telemetri feed satelit.
                    </p>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-xs font-mono text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Database Status: HEALTHY</span>
                </div>
            </div>
        </div>

        <!-- Database Table Completeness Matrix -->
        <div class="bg-[#0D2B3E] rounded-3xl border border-[#16445A] p-6 lg:p-8 space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-[#16445A]">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" /></svg>
                    <span>Integritas Skema & Kelengkapan Baris Database</span>
                </h2>
                <span class="text-xs font-mono text-cyan-300">Target: PostgreSQL 17.11 / PostGIS 3.6.2</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-[#071B2B] text-[#A4BAC8] border-b border-[#16445A]">
                        <tr>
                            <th class="p-3.5">Nama Tabel</th>
                            <th class="p-3.5">Deskripsi Entitas</th>
                            <th class="p-3.5">Baris Aktual</th>
                            <th class="p-3.5">Ekspektasi PRD</th>
                            <th class="p-3.5">Kelengkapan</th>
                            <th class="p-3.5">Sistem Koordinat (SRID)</th>
                            <th class="p-3.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#16445A] text-slate-300">
                        @foreach($tables as $t)
                            <tr class="hover:bg-[#071B2B]/40">
                                <td class="p-3.5 font-bold text-white">{{ $t['name'] }}</td>
                                <td class="p-3.5 text-[#A4BAC8]">{{ $t['desc'] }}</td>
                                <td class="p-3.5 font-bold text-cyan-300">{{ $t['rows'] }}</td>
                                <td class="p-3.5 text-slate-400">{{ $t['expected'] }}</td>
                                <td class="p-3.5 text-emerald-400 font-bold">{{ $t['completeness'] }}</td>
                                <td class="p-3.5 text-sky-300">{{ $t['srid'] }}</td>
                                <td class="p-3.5">
                                    <span class="px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 font-bold">
                                        {{ $t['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Telemetry Pipeline & Satellite Health -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-[#0D2B3E] rounded-3xl border border-[#16445A] p-6 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider font-mono flex items-center gap-2">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    <span>Status Feed Satelit & Latensi Pipeline</span>
                </h3>
                <div class="space-y-3 text-xs font-mono">
                    <div class="p-3 rounded-xl bg-[#071B2B] border border-[#16445A] flex justify-between items-center">
                        <div>
                            <span class="text-white font-bold block">NOAA OISST AVHRR</span>
                            <span class="text-[10px] text-[#A4BAC8]">Suhu Muka Laut Harian</span>
                        </div>
                        <div class="text-right">
                            <span class="text-emerald-400 font-bold block">NORMAL (3.8 jam)</span>
                            <span class="text-[10px] text-slate-400">Sinkronisasi 04:00 WIB</span>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-[#071B2B] border border-[#16445A] flex justify-between items-center">
                        <div>
                            <span class="text-white font-bold block">Copernicus Sentinel-3 OLCI</span>
                            <span class="text-[10px] text-[#A4BAC8]">Klorofil-a Konsentrasi</span>
                        </div>
                        <div class="text-right">
                            <span class="text-emerald-400 font-bold block">NORMAL (5.2 jam)</span>
                            <span class="text-[10px] text-slate-400">Komposit Cloud-Free</span>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-[#071B2B] border border-[#16445A] flex justify-between items-center">
                        <div>
                            <span class="text-white font-bold block">BMKG Ina-WAVES Ocean Wave</span>
                            <span class="text-[10px] text-[#A4BAC8]">Tinggi Gelombang & Swell</span>
                        </div>
                        <div class="text-right">
                            <span class="text-emerald-400 font-bold block">NORMAL (1.5 jam)</span>
                            <span class="text-[10px] text-slate-400">Model Prediksi 24 Jam</span>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-[#071B2B] border border-[#16445A] flex justify-between items-center">
                        <div>
                            <span class="text-white font-bold block">Python Scientific Bridge</span>
                            <span class="text-[10px] text-[#A4BAC8]">scikit-learn 1.9.1 / Python 3.14</span>
                        </div>
                        <div class="text-right">
                            <span class="text-emerald-400 font-bold block">READY (Exit 0)</span>
                            <span class="text-[10px] text-slate-400">Ensemble SDM Execution</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-[#0D2B3E] rounded-3xl border border-[#16445A] p-6 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider font-mono flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span>Protokol Sensor Fallback & Disclaimer Ilmiah</span>
                </h3>
                <div class="p-4 rounded-xl bg-[#071B2B] border border-[#16445A] text-xs text-[#A4BAC8] space-y-3 leading-relaxed">
                    <p>
                        <strong class="text-white font-mono">Ketentuan Tutupan Awan (Cloud Masking):</strong> Apabila citra satelit optik (Sentinel-3) mengalami oklusi awan &gt; 60% di WPPNRI 573, pipeline inferensi secara otomatis mengaktifkan fallback interpolasi spasial berbasis kalibrasi klimatologis historis dan data in-situ BMKG.
                    </p>
                    <p>
                        <strong class="text-white font-mono">Integritas Read-Only:</strong> Seluruh modul dashboard beroperasi di bawah koneksi database berhak akses baca murni (<code class="text-cyan-300">seacast_readonly</code>). Tidak ada proses visualisasi yang dapat memodifikasi atau menghapus data observasi asli.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>

