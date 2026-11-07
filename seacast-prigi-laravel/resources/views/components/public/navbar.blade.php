<header x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-40 w-full border-b border-slate-200/80 dark:border-[#244456] bg-white/90 dark:bg-[#071B2B]/90 backdrop-blur-md transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-[4.25rem] flex items-center justify-between gap-4">
        <!-- Brand Identity Lockup (Logo + SEACAST + Scientific Tagline) -->
        <x-ui.brand-logo size="sm" :withText="true" :withLink="true" />

        <!-- Generalized Navigation Links: Beranda -> Sains & Model -> Data Oseanografi -> Dokumentasi -> Tentang SEACAST -->
        <nav class="hidden md:flex items-center gap-5 lg:gap-7 text-xs sm:text-sm font-semibold">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-[#0D8195] dark:text-cyan-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400' }} transition-colors">
                Beranda
            </a>
            <a href="{{ route('science') }}" class="{{ request()->routeIs('science*') ? 'text-[#0D8195] dark:text-cyan-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400' }} transition-colors">
                Sains & Model
            </a>
            <a href="{{ route('ocean-data') }}" class="{{ request()->routeIs('ocean-data') ? 'text-[#0D8195] dark:text-cyan-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400' }} transition-colors">
                Data Oseanografi
            </a>
            <a href="{{ route('documentation') }}" class="{{ request()->routeIs('documentation') ? 'text-[#0D8195] dark:text-cyan-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400' }} transition-colors">
                Dokumentasi
            </a>
            <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'text-[#0D8195] dark:text-cyan-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400' }} transition-colors">
                Tentang SEACAST
            </a>
        </nav>

        <!-- Right Side: Unified Authentication CTA (Single 'Login' Button) & Mobile Toggle -->
        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs sm:text-sm shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Ruang Kendali
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 rounded-xl bg-[#0D8195] hover:bg-[#0b6d7e] dark:bg-[#13B8C8] dark:hover:bg-[#10A1B0] text-white dark:text-slate-950 font-bold text-xs sm:text-sm shadow-md hover:shadow-cyan-500/20 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Login
                </a>
            @endauth

            <!-- Mobile Hamburger Toggle -->
            <button 
                @click="mobileMenuOpen = !mobileMenuOpen" 
                type="button" 
                class="md:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-[#12364A] transition-colors"
                aria-label="Toggle Mobile Menu"
            >
                <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileMenuOpen" style="display: none;" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Dropdown Drawer -->
    <div 
        x-show="mobileMenuOpen" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        style="display: none;" 
        class="md:hidden border-b border-[#244456] bg-[#071B2B] px-4 pt-2 pb-4 space-y-1"
    >
        <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('home') ? 'bg-[#12364A] text-cyan-300' : 'text-slate-300 hover:bg-[#0D2B3E]' }}">
            Beranda
        </a>
        <a href="{{ route('science') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('science*') ? 'bg-[#12364A] text-cyan-300' : 'text-slate-300 hover:bg-[#0D2B3E]' }}">
            Sains & Model
        </a>
        <a href="{{ route('ocean-data') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('ocean-data') ? 'bg-[#12364A] text-cyan-300' : 'text-slate-300 hover:bg-[#0D2B3E]' }}">
            Data Oseanografi
        </a>
        <a href="{{ route('documentation') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('documentation') ? 'bg-[#12364A] text-cyan-300' : 'text-slate-300 hover:bg-[#0D2B3E]' }}">
            Dokumentasi
        </a>
        <a href="{{ route('about') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('about') ? 'bg-[#12364A] text-cyan-300' : 'text-slate-300 hover:bg-[#0D2B3E]' }}">
            Tentang SEACAST
        </a>
    </div>
</header>
