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
        <div class="h-16 px-4 border-b border-[#244456] flex items-center justify-between gap-2 shrink-0">
            <a href="{{ route('home') }}" class="flex items-center justify-between gap-2 min-w-0 flex-1 group focus:outline-none">
                <div class="flex flex-col leading-none min-w-0">
                    <span class="font-black tracking-tight text-lg text-white uppercase group-hover:text-cyan-300 transition-colors truncate">SEACAST</span>
                    <span class="text-[10px] font-mono text-slate-400 tracking-wider uppercase truncate mt-0.5">Ocean Intelligence</span>
                </div>
                <span class="px-1.5 py-0.5 rounded bg-[#12364A] border border-[#244456] text-[10px] font-mono font-bold text-cyan-300 tracking-wider uppercase shrink-0">WPP 573</span>
            </a>
            <button @click="mobileOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1 shrink-0" aria-label="Tutup Menu">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <div class="flex-1 overflow-y-auto px-4 py-5 space-y-6">
            <!-- Operational Pillars Group -->
            <div>
                <p class="px-2 text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400 mb-2">Pilar Operasional</p>
                <nav class="space-y-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('dashboard') && !request()->routeIs('dashboard.*') ? 'bg-[#12364A] text-[#13B8C8] font-bold shadow-sm' : 'text-slate-300 hover:bg-[#0D2B3E] hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Ringkasan Eksekutif</span>
                    </a>
                    <a href="{{ route('dashboard.dpi-radar') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('dashboard.dpi-radar') ? 'bg-[#12364A] text-[#13B8C8] font-bold shadow-sm' : 'text-slate-300 hover:bg-[#0D2B3E] hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v9l6 3"/></svg>
                        <span>DPI Radar Spasial</span>
                    </a>
                    <a href="{{ route('dashboard.trip-planner') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('dashboard.trip-planner*') ? 'bg-[#12364A] text-[#13B8C8] font-bold shadow-sm' : 'text-slate-300 hover:bg-[#0D2B3E] hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <span>Rencana Melaut & Slip</span>
                    </a>
                    <a href="{{ route('dashboard.fleet-analytics') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('dashboard.fleet-analytics*') || request()->routeIs('dashboard.cpue-analysis') ? 'bg-[#12364A] text-[#13B8C8] font-bold shadow-sm' : 'text-slate-300 hover:bg-[#0D2B3E] hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                        <span>Analitik Armada & CPUE</span>
                    </a>
                </nav>
            </div>

            <!-- Platform Administration -->
            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasRole('syahbandar') || auth()->user()->hasRole('peneliti')))
            <div>
                <p class="px-2 text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400 mb-2">Administrasi</p>
                <nav class="space-y-1">
                    <a href="{{ route('dashboard.system-status') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('dashboard.system-status') ? 'bg-[#12364A] text-[#13B8C8] font-bold' : 'text-slate-300 hover:bg-[#0D2B3E] hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Status Sistem & DB</span>
                    </a>
                </nav>
            </div>
            @endif
            <!-- Portal & Referensi -->
            <div>
                <p class="px-2 text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400 mb-2">Portal & Sains</p>
                <nav class="space-y-1">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:bg-[#0D2B3E] hover:text-white transition-colors">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Beranda Publik</span>
                    </a>
                    <a href="{{ route('science') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:bg-[#0D2B3E] hover:text-white transition-colors">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Metodologi & Model</span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Sidebar Footer Status -->
        <div class="p-4 border-t border-[#244456] bg-[#071B2B]">
            <div class="flex items-center gap-2.5">
                <span class="inline-flex rounded-full h-2 w-2 bg-cyan-400 shrink-0"></span>
                <div class="text-[11px] text-slate-300 font-mono truncate">
                    <span class="text-white font-bold">PostGIS</span> &bull; 8.129 Sel Grid
                </div>
            </div>
        </div>
    </div>
</aside>

