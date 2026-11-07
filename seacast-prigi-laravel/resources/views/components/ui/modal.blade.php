@props([
    'name',
    'title' => null,
    'maxWidth' => 'md',
])

@php
$maxWidths = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
    '4xl' => 'max-w-4xl',
];
$widthClass = $maxWidths[$maxWidth] ?? 'max-w-md';
@endphp

<div
    x-data="{ open: false }"
    x-show="open"
    x-on:open-modal.window="if ($event.detail.name === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail.name === '{{ $name }}') open = false"
    x-on:keydown.escape.window="open = false"
    style="display: none;"
    class="fixed inset-0 z-50 overflow-y-auto"
>
    <!-- Backdrop -->
    <div 
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm"
        @click="open = false"
    ></div>

    <!-- Modal Dialog -->
    <div class="flex min-h-full items-center justify-center p-4 text-center">
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full {{ $widthClass }} transform overflow-hidden rounded-2xl bg-white dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456] p-6 text-left align-middle shadow-2xl transition-all"
        >
            @if($title)
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-[#244456]/60 mb-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">{{ $title }}</h3>
                    <button @click="open = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            <div class="text-xs text-slate-700 dark:text-slate-200">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>

