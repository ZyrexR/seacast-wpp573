<aside 
    x-data="{ mobileOpen: false }" 
    @toggle-sidebar.window="mobileOpen = !mobileOpen"
    class="relative"
>
    <!-- Mobile Backdrop -->
    <div 
        x-show="mobileOpen" 
        @click="mobileOpen = false" 
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 lg:hidden"
        style="display: none;"
    ></div>

    <!-- Sidebar Container -->
    <div 
        :class="mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed top-0 bottom-0 left-0 w-64 bg-[#091F30] border-r border-[#244456] z-50 flex flex-col transition-transform duration-200 ease-in-out"
    >
        <!-- Sidebar Brand -->
        <div class="h-16 px-4 border-b border-[#244456] flex items-center justify-between">
            <x-ui.brand-logo variant="full" theme="dark" size="sm" :withLink="true" />
            <button @click="mobileOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <div class="flex-1 overflow-y-auto px-4 py-5 space-y-6">
            <!-- Operational Group -->
            <div>
                <p class="px-2 text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400 mb-2">Operasional Kelautan</p>
                <nav class="space-y-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('dashboard') && !request()->routeIs('dashboard.*') ? 'bg-[#12364A] text-[#13B8C8] font-bold shadow-sm' : 'text-slate-300 hover:bg-[#0D2B3E] hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Executive Overview</span>
                    </a>
                    <a href="{{ route('dashboard.dpi-radar') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('dashboard.dpi-radar') ? 'bg-[#12364A] text-[#13B8C8] font-bold' : 'text-slate-300 hover:bg-[#0D2B3E] hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v9l6 3"/></svg>
                        <span>DPI Radar Spasial</span>
                    </a>
                    <a href="{{ route('dashboard.dom-management') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('dashboard.dom-management') ? 'bg-[#12364A] text-[#13B8C8] font-bold' : 'text-slate-300 hover:bg-[#0D2B3E] hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <span>DOM Zonasi 5-Area</span>
                    </a>
                    <a href="{{ route('dashboard.bycatch-safety') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('dashboard.bycatch-safety') ? 'bg-[#12364A] text-[#13B8C8] font-bold' : 'text-slate-300 hover:bg-[#0D2B3E] hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Bycatch & Keselamatan</span>
                    </a>
                </nav>
            </div>

            <!-- Scientific & Analytic Group -->
            <div>
                <p class="px-2 text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400 mb-2">Analitik & Prediksi</p>
                <nav class="space-y-1">
                    <a href="{{ route('dashboard.cpue-analysis') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('dashboard.cpue-analysis') ? 'bg-[#12364A] text-[#13B8C8] font-bold' : 'text-slate-300 hover:bg-[#0D2B3E] hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                        <span>Analisis CPUE Nominal</span>
                    </a>
                    <a href="{{ route('dashboard.species-models') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('dashboard.species-models') ? 'bg-[#12364A] text-[#13B8C8] font-bold' : 'text-slate-300 hover:bg-[#0D2B3E] hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        <span>Model SDM Ensemble</span>
                    </a>
                    <a href="{{ route('dashboard.oceanographic-explorer') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('dashboard.oceanographic-explorer') ? 'bg-[#12364A] text-[#13B8C8] font-bold' : 'text-slate-300 hover:bg-[#0D2B3E] hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Eksplorer Oseanografi</span>
                    </a>
                    <a href="{{ route('dashboard.time-machine') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('dashboard.time-machine') ? 'bg-[#12364A] text-[#13B8C8] font-bold' : 'text-slate-300 hover:bg-[#0D2B3E] hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Time Machine 2025</span>
                    </a>
                    <a href="{{ route('dashboard.what-if-simulator') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('dashboard.what-if-simulator') ? 'bg-[#12364A] text-[#13B8C8] font-bold' : 'text-slate-300 hover:bg-[#0D2B3E] hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>What-If Simulator</span>
                    </a>
                </nav>
            </div>

            <!-- Platform & Health Group -->
            <div>
                <p class="px-2 text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400 mb-2">Platform & Mesin</p>
                <nav class="space-y-1">
                    <a href="{{ route('dashboard.system-status') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('dashboard.system-status') ? 'bg-[#12364A] text-[#13B8C8] font-bold' : 'text-slate-300 hover:bg-[#0D2B3E] hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Status Sistem & DB</span>
                    </a>
                    <a href="{{ route('dashboard.data-monitoring') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('dashboard.data-monitoring') ? 'bg-[#12364A] text-[#13B8C8] font-bold' : 'text-slate-300 hover:bg-[#0D2B3E] hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Monitoring Pipeline</span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Sidebar Footer Status -->
        <div class="p-4 border-t border-[#244456] bg-[#071B2B]">
            <div class="flex items-center gap-2.5">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <div class="text-floor text-slate-300 font-mono">
                    <span class="text-white font-bold">PostGIS 3.6</span> &bull; 8.129 Sel
                </div>
            </div>
        </div>
    </div>
</aside>

