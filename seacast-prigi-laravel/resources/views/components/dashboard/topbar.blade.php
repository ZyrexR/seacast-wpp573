<header class="h-16 bg-[#091F30] border-b border-[#244456] px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30">
    <div class="flex items-center gap-3">
        <!-- Mobile Sidebar Toggle -->
        <button 
            @click="$dispatch('toggle-sidebar')" 
            class="lg:hidden p-2 rounded-xl text-slate-300 hover:text-white hover:bg-[#12364A] transition-colors"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Current Page Title -->
        <div class="flex items-center gap-2 text-xs sm:text-sm font-bold text-slate-100">
            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
            <span>{{ $title ?? 'Ruang Kendali Operasional' }}</span>
        </div>
    </div>

    <!-- Right Controls -->
    <div class="flex items-center gap-3 shrink-0">
        <!-- Dual Hub Scope Badge -->
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#0D2B3E] border border-[#244456] text-[11px] text-slate-300">
            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
            <span class="font-mono text-cyan-300">PPN Prigi &bull; PPS Cilacap</span>
        </div>

        <!-- User Persona / Session -->
        @auth
            <div class="flex items-center gap-3">
                <div class="text-right hidden md:block">
                    <p class="text-xs font-bold text-slate-200">{{ auth()->user()->name }}</p>
                    <p class="text-floor text-cyan-400 font-mono uppercase">{{ auth()->user()->role ?? 'Operator' }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Keluar Akun" class="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-[#12364A] transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        @else
            <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-xl bg-[#12364A] hover:bg-[#1a4a63] border border-[#244456] text-xs font-semibold text-[#13B8C8] transition-colors">
                Masuk Akun
            </a>
        @endauth
    </div>
</header>

