<header class="sticky top-0 z-40 w-full border-b border-slate-200/80 dark:border-[#244456] bg-white/85 dark:bg-[#071B2B]/85 backdrop-blur-md transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 min-h-[4.5rem] sm:min-h-[5rem] flex items-center justify-between gap-4">
        <!-- Brand Identity Lockup (Logo + SEACAST + Scientific Tagline) -->
        <x-ui.brand-logo size="sm" :withText="true" :withLink="true" />

        <!-- Generalized Navigation Links (14-16px font size, clean hierarchy) -->
        <nav class="hidden md:flex items-center gap-6 lg:gap-8 text-sm lg:text-[15px] font-semibold">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-[#0D8195] dark:text-cyan-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400' }} transition-colors">
                Beranda
            </a>
            <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'text-[#0D8195] dark:text-cyan-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400' }} transition-colors">
                Tentang SEACAST
            </a>
            <a href="{{ route('science') }}" class="{{ request()->routeIs('science*') ? 'text-[#0D8195] dark:text-cyan-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400' }} transition-colors">
                Sains & Metodologi
            </a>
            <a href="{{ route('documentation') }}" class="{{ request()->routeIs('documentation') ? 'text-[#0D8195] dark:text-cyan-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400' }} transition-colors">
                Data & Dokumentasi
            </a>
        </nav>

        <!-- Right Side: Unified Authentication CTA (Single 'Login' Button) -->
        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-600 text-slate-950 font-bold text-sm shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0D8195] hover:bg-[#0b6d7e] dark:bg-[#13B8C8] dark:hover:bg-[#10A1B0] text-white dark:text-slate-950 font-bold text-sm shadow-md hover:shadow-cyan-500/20 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Login
                </a>
            @endauth
        </div>
    </div>
</header>
