<x-layouts.dashboard title="{{ $moduleInfo['title'] }} — SEACAST">
    <div class="space-y-6 max-w-4xl mx-auto py-8">
        <!-- Breadcrumb -->
        <x-ui.breadcrumbs :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => $moduleInfo['title'], 'url' => null]
        ]" />

        <!-- Status Card -->
        <div class="p-8 rounded-3xl bg-[#091F30] border border-[#244456] shadow-xl text-center space-y-6">
            <div class="w-16 h-16 rounded-2xl bg-[#12364A] text-cyan-400 flex items-center justify-center mx-auto shadow-lg shadow-cyan-500/10">
                <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-950/60 border border-cyan-500/30 text-xs font-mono font-bold text-cyan-300">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                    <span>Status: MODULE UNDER MIGRATION</span>
                </div>
                <h1 class="text-2xl font-black text-white tracking-tight">
                    {{ $moduleInfo['title'] }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 max-w-xl mx-auto leading-relaxed">
                    {{ $moduleInfo['desc'] }}
                </p>
            </div>

            <!-- Migration Roadmap Box -->
            <div class="p-4 rounded-2xl bg-[#071B2B] border border-[#244456] text-xs text-left max-w-lg mx-auto space-y-2">
                <div class="flex items-center justify-between border-b border-[#244456] pb-2">
                    <span class="text-slate-400 font-mono">Fase Target Implementasi:</span>
                    <span class="font-bold text-cyan-400 font-mono">{{ $moduleInfo['phase'] }}</span>
                </div>
                <div class="flex items-center justify-between pt-1">
                    <span class="text-slate-400 font-mono">Status Data Ilmiah:</span>
                    <span class="font-bold text-emerald-400 font-mono">Database PostGIS 3.6 Ready</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400 font-mono">Ketersediaan Data:</span>
                    <span class="text-amber-400 font-bold">Data Unavailable in Phase 1 (Foundation Only)</span>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-center gap-4">
                <x-ui.button href="{{ route('dashboard') }}" variant="primary" size="md">
                    Kembali ke Executive Overview
                </x-ui.button>
                <x-ui.button href="{{ route('dashboard.system-status') }}" variant="secondary" size="md">
                    Periksa Status PostGIS
                </x-ui.button>
            </div>
        </div>
    </div>
</x-layouts.dashboard>

