@props([
    'text',
    'position' => 'top',
])

<div x-data="{ show: false }" class="relative inline-flex items-center" @mouseenter="show = true" @mouseleave="show = false">
    {{ $slot }}

    <div
        x-show="show"
        x-transition:enter="ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        style="display: none;"
        class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 px-3 py-1.5 rounded-lg bg-slate-900 dark:bg-slate-800 text-white text-floor font-medium shadow-xl border border-slate-700 whitespace-nowrap z-50 pointer-events-none"
    >
        {{ $text }}
        <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900 dark:border-t-slate-800"></div>
    </div>
</div>

