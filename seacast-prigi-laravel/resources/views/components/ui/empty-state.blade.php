@props([
    'title' => 'Tidak Ada Data Ditemukan',
    'description' => 'Tidak ada rekaman spasial atau telemetri yang cocok dengan kriteria pencarian.',
    'action' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center p-8 rounded-2xl border border-dashed border-slate-300 dark:border-[#244456] bg-slate-50/50 dark:bg-[#071B2B]/40']) }}>
    <div class="w-12 h-12 rounded-2xl bg-cyan-100 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 flex items-center justify-center mb-3">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
    </div>
    <h5 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-1">{{ $title }}</h5>
    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mb-4">{{ $description }}</p>
    @if($action)
        <div>{{ $action }}</div>
    @endif
</div>

