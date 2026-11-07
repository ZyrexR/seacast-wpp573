<footer class="border-t border-slate-200 dark:border-[#244456] bg-slate-50 dark:bg-[#061524] py-12 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <div class="md:col-span-2 space-y-3">
                <x-ui.brand-logo size="sm" :withText="true" :withLink="true" />
                <p class="text-xs text-slate-600 dark:text-slate-400 max-w-md leading-relaxed">
                    Platform Intelijen Oseanografi dan Prediksi Kesesuaian Daerah Penangkapan Ikan Pelagis (Cakalang, Madidihang, Albakor) berbasiskan 8.129 sel kanonikal WPPNRI 573 dengan rujukan Pelabuhan Perikanan Nusantara (PPN) Prigi, Trenggalek.
                </p>
                <div class="flex items-center gap-2 text-floor text-slate-500 font-mono">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>PostGIS 3.6 Kanonik &bull; 8.129 Poligon EPSG:4326</span>
                </div>
            </div>

            <div>
                <h5 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-3">Portal Informasi</h5>
                <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-400">
                    <li><a href="{{ route('home') }}" class="hover:text-cyan-500 transition-colors">Beranda Utama</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-cyan-500 transition-colors">Tentang SEACAST</a></li>
                    <li><a href="{{ route('science') }}" class="hover:text-cyan-500 transition-colors">Sains & Metodologi</a></li>
                    <li><a href="{{ route('documentation') }}" class="hover:text-cyan-500 transition-colors">Data & Dokumentasi</a></li>
                </ul>
            </div>

            <div>
                <h5 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-3">Akses Operasional</h5>
                <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-400">
                    <li><a href="{{ route('dashboard') }}" class="hover:text-cyan-500 transition-colors">Executive Overview</a></li>
                    <li><a href="{{ route('dashboard.system-status') }}" class="hover:text-cyan-500 transition-colors">Status Mesin & PostGIS</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-cyan-500 transition-colors">Login Ruang Kendali</a></li>
                </ul>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-200 dark:border-[#244456]/60 flex flex-col sm:flex-row items-center justify-between gap-4 text-floor text-slate-500 dark:text-slate-400">
            <div>
                &copy; {{ date('Y') }} SEACAST — Scientific Ocean Intelligence & Fisheries Decision Support. Seluruh hak cipta dilindungi.
            </div>
            <div class="font-mono">
                Laravel 13 &bull; PHP 8.3 &bull; Livewire 4 &bull; PostGIS 3.6
            </div>
        </div>
    </div>
</footer>

