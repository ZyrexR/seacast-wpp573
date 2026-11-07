@props([
    'title' => 'Terjadi Kesalahan Komputasi / Jaringan',
    'message' => 'Gagal memuat telemetri atau data geospasial dari server.',
    'retryAction' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center p-8 rounded-2xl border border-rose-200 dark:border-rose-900/60 bg-rose-50/40 dark:bg-rose-950/20']) }}>
    <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-950/80 text-rose-600 dark:text-rose-400 flex items-center justify-center mb-3">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
    </div>
    <h5 class="text-sm font-bold text-rose-900 dark:text-rose-200 mb-1">{{ $title }}</h5>
    <p class="text-xs text-rose-700/80 dark:text-rose-300/80 max-w-sm mb-4">{{ $message }}</p>
    @if($retryAction)
        <div>{{ $retryAction }}</div>
    @endif
</div>

