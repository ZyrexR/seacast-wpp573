<x-layouts.dashboard title="Rencana Operasional Melaut — SEACAST">
    <div class="space-y-6">
        <div class="border-b border-[#244456] pb-4">
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2.5">
                <span class="inline-block w-2.5 h-6 bg-cyan-500 rounded-full"></span>
                Rencana Operasional Melaut
                <x-ui.badge variant="cyan" :dot="true">Kalkulator Maritim</x-ui.badge>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Kalkulator Terpadu Efisiensi Bahan Bakar, Keselamatan Maritim & Kepatuhan Zonasi Permen KP 59/2020
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 bg-[#091F30] border border-[#244456] rounded-xl p-5 space-y-4">
                <h3 class="text-base font-semibold text-white">Profil Pelayaran</h3>
                <p class="text-xs text-slate-400">Pilih pelabuhan tolak, tanggal melaut, dan armada kapal.</p>
            </div>
            <div class="lg:col-span-2 bg-[#091F30] border border-[#244456] rounded-xl p-5 space-y-4">
                <h3 class="text-base font-semibold text-white">Hasil Kalkulasi Keputusan</h3>
                <p class="text-xs text-slate-400">Estimasi konsumsi solar, tinggi gelombang, fase bulan, dan proyeksi hasil tangkapan.</p>
            </div>
        </div>
    </div>
</x-layouts.dashboard>