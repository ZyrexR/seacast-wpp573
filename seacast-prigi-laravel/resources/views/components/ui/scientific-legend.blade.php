@props([
    'type' => 'hsi', // hsi, dom, bycatch, bathymetry
    'title' => 'Skala Kesesuaian Habitat (HSI)',
])

<div {{ $attributes->merge(['class' => 'bg-white/95 dark:bg-[#0D2B3E]/95 backdrop-blur-md p-4 rounded-2xl border border-slate-200 dark:border-[#244456] shadow-md text-xs']) }}>
    <div class="font-bold text-slate-900 dark:text-slate-100 mb-2 flex items-center justify-between">
        <span>{{ $title }}</span>
        <span class="text-floor text-cyan-600 dark:text-cyan-400 font-mono">0.0 - 1.0</span>
    </div>

    @if($type === 'hsi')
        <div class="h-3 w-full rounded-lg bg-gradient-to-r from-blue-900 via-cyan-500 via-amber-400 to-rose-500 mb-2"></div>
        <div class="flex justify-between text-floor text-slate-500 dark:text-slate-400 font-mono">
            <span>0.0 Rendah</span>
            <span>0.5 Sedang</span>
            <span class="font-bold text-rose-500">0.7+ Hotspot</span>
        </div>
    @elseif($type === 'dom')
        <div class="space-y-1.5 pt-1">
            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span><span class="font-semibold text-purple-400">Z4</span> Nursery Conservation Ground</div>
            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span><span class="font-semibold text-rose-400">Z5</span> Bycatch Risk Screening</div>
            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span><span class="font-semibold text-cyan-400">Z3</span> Feeding Core Ground</div>
            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span><span class="font-semibold text-sky-400">Z1</span> PFG Inshore (&le; 12 NM)</div>
            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span><span class="font-semibold text-blue-400">Z2</span> PFG Offshore (12-60 NM)</div>
        </div>
    @elseif($type === 'bycatch')
        <div class="space-y-1.5 pt-1">
            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span><span class="font-semibold text-emerald-400">GREEN</span> Aman (&lt;150m / &gt;1200m)</div>
            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span><span class="font-semibold text-amber-400">YELLOW</span> Waspada Lereng Benua</div>
            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span><span class="font-semibold text-rose-400">RED</span> Risiko Tinggi Bycatch IOTC</div>
        </div>
    @endif
</div>

