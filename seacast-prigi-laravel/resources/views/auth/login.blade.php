<x-layouts.public title="Login Ruang Kendali — SEACAST">
    <div class="max-w-4xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <!-- Left Editorial & Persona Quick Select -->
            <div class="space-y-6">
                <div>
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-cyan-600 dark:text-cyan-400">Portal Autentikasi Persona</span>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                        Ruang Kendali Operasional Kelautan WPPNRI 573
                    </h1>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
                        Akses sistem intelijen oseanografi terintegrasi untuk koridor operasi PPN Prigi (Jawa Timur) &amp; PPS Cilacap (Jawa Tengah).
                    </p>
                </div>

                <!-- Demo Personas Notice -->
                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-900 dark:text-amber-200 space-y-1.5">
                    <div class="font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Status Autentikasi Demo / Testing
                    </div>
                    <p class="text-[11px] leading-relaxed text-amber-800 dark:text-amber-300">
                        Akun berikut merupakan role simulasi pengembangan. Kata sandi default untuk seluruh akun demo adalah: <code class="bg-amber-100 dark:bg-amber-950/80 px-1.5 py-0.5 rounded font-mono font-bold">seacast2026</code>
                    </p>
                </div>

                <!-- Persona Quick Selector -->
                <div class="space-y-2">
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Pilih Persona untuk Mengisi Formulir:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @foreach($personas as $p)
                            <button
                                type="button"
                                onclick="document.getElementById('email').value = '{{ $p['email'] }}'; document.getElementById('password').value = 'seacast2026';"
                                class="text-left p-3 rounded-xl border border-slate-200 dark:border-[#244456] bg-white dark:bg-[#0D2B3E] hover:border-cyan-500 transition-colors group"
                            >
                                <div class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-cyan-500 transition-colors">{{ $p['role'] }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono mt-0.5 truncate">{{ $p['email'] }}</div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Login Form Card -->
            <div>
                <x-ui.card title="Masuk Akun Pengguna" subtitle="Gunakan email terdaftar dan kata sandi Anda">
                    <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                        @csrf

                        <x-ui.input
                            id="email"
                            name="email"
                            type="email"
                            label="Alamat Email Resmi"
                            value="{{ old('email') }}"
                            placeholder="nama@seacast.id"
                            :error="$errors->first('email')"
                            required
                            autofocus
                        />

                        <x-ui.input
                            id="password"
                            name="password"
                            type="password"
                            label="Kata Sandi Keamanan"
                            placeholder="••••••••••••"
                            :error="$errors->first('password')"
                            required
                        />

                        <div class="flex items-center justify-between text-xs">
                            <label class="flex items-center gap-2 text-slate-600 dark:text-slate-400 cursor-pointer">
                                <input type="checkbox" name="remember" class="rounded border-slate-300 dark:border-slate-700 text-cyan-600 focus:ring-cyan-500">
                                <span>Ingat sesi saya</span>
                            </label>
                            <span class="text-slate-400 text-floor">Session CSRF Protected</span>
                        </div>

                        <div class="pt-2">
                            <x-ui.button type="submit" variant="primary" size="md" class="w-full">
                                Masuk ke Ruang Kendali
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.public>

