@props([
    'tabs' => [],
    'default' => null,
])

@php
$defaultTab = $default ?? ($tabs[0]['id'] ?? '');
@endphp

<div x-data="{ activeTab: '{{ $defaultTab }}' }" class="w-full">
    <!-- Tab Navigation -->
    <div class="flex items-center gap-1 p-1 bg-slate-100 dark:bg-[#091F30] rounded-xl border border-slate-200 dark:border-[#244456] overflow-x-auto select-none mb-4">
        @foreach($tabs as $tab)
            <button
                type="button"
                @click="activeTab = '{{ $tab['id'] }}'"
                :class="activeTab === '{{ $tab['id'] }}' ? 'bg-white dark:bg-[#12364A] text-slate-900 dark:text-cyan-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-medium'"
                class="px-4 py-2 text-xs rounded-lg transition-all duration-150 whitespace-nowrap"
            >
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>

    <!-- Tab Panels Container -->
    <div>
        {{ $slot }}
    </div>
</div>

