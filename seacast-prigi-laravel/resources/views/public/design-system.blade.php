<x-layouts.public title="Design System Showroom — SEACAST">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        <!-- Header -->
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Design System Showroom', 'url' => null]]" />
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Design System & Katalog 16 Komponen UI
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-3xl leading-relaxed">
                Showroom komponen antarmuka mandiri berbasis Laravel Blade Anonymous Components, Tailwind CSS, dan Alpine.js. Sepenuhnya terbebas dari ketergantungan runtime React/Node.js di sisi server.
            </p>
        </div>

        <!-- 0. Brand Identity & Logo Suite -->
        <section class="space-y-6">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#244456] pb-2">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>0. Identitas Brand & Logo SEACAST</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-cyan-100 dark:bg-cyan-950 text-cyan-800 dark:text-cyan-300 font-mono font-bold">Official V3</span>
                </h3>
                <span class="text-xs text-slate-500 font-mono"><code>&lt;x-ui.brand-logo /&gt;</code></span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Master Logo (Light Mode Canvas) -->
                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">Master Logo (Light Canvas)</span>
                        <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-slate-100 text-slate-700">variant="full" theme="light"</span>
                    </div>
                    <div class="flex items-center justify-center p-6 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        <x-ui.brand-logo variant="full" size="lg" theme="light" />
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Logo resmi transparan dengan aksen tipografi navy gelap yang tegas, kompas oseanografi, dan ilustrasi pelagis tuna yang dinamis untuk media berlatar belakang terang.
                    </p>
                </div>

                <!-- Master Logo (Dark Mode Canvas) -->
                <div class="p-6 rounded-2xl bg-[#071B2B] border border-[#244456] shadow-xl space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-cyan-400 font-mono">Master Logo (Dark Canvas)</span>
                        <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-[#12364A] text-cyan-300">variant="full" theme="dark"</span>
                    </div>
                    <div class="flex items-center justify-center p-6 bg-[#091F30] rounded-xl border border-dashed border-[#244456]">
                        <x-ui.brand-logo variant="full" size="lg" theme="dark" />
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Varian terkalibrasi khusus tema gelap dengan tipografi es putih <em>high-contrast</em>, pendaran ombak biru laut, dan kontras sempurna pada ruang kendali operasional malam hari.
                    </p>
                </div>
            </div>

            <!-- Horizontal & Mark Variants Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Horizontal Navbar Lockup -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456] space-y-3">
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300 font-mono">Horizontal Navbar</div>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#091F30] flex items-center justify-center">
                        <x-ui.brand-logo variant="horizontal" size="sm" />
                    </div>
                    <span class="block text-floor text-slate-500 font-mono">size="sm" variant="horizontal"</span>
                </div>

                <!-- Compact Brand Lockup -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456] space-y-3">
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300 font-mono">Compact Title</div>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#091F30] flex items-center justify-center">
                        <x-ui.brand-logo variant="compact" size="md" />
                    </div>
                    <span class="block text-floor text-slate-500 font-mono">size="md" variant="compact"</span>
                </div>

                <!-- Mark Icon Sizes -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456] space-y-3">
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300 font-mono">Emblem Mark Sizes</div>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#091F30] flex items-center justify-around gap-2">
                        <x-ui.brand-logo variant="mark" size="xs" title="xs (24px)" />
                        <x-ui.brand-logo variant="mark" size="sm" title="sm (32px)" />
                        <x-ui.brand-logo variant="mark" size="md" title="md (40px)" />
                        <x-ui.brand-logo variant="mark" size="lg" title="lg (56px)" />
                    </div>
                    <span class="block text-floor text-slate-500 font-mono">variant="mark" (Favicon, Avatar, App Icon)</span>
                </div>
            </div>
        </section>

        <!-- 1. Alerts -->
        <section class="space-y-4">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-[#244456] pb-2">1. Alert (4 Varian)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-ui.alert variant="info" title="Pemberitahuan Sistem" :dismissible="true">
                    Sinkronisasi data satelit NOAA OISST berjalan otomatis setiap pukul 02:00 UTC.
                </x-ui.alert>
                <x-ui.alert variant="success" title="Integritas Basis Data Valid" :dismissible="true">
                    Seluruh 8.129 sel kanonikal WPPNRI 573 terverifikasi 100% valid pada PostGIS 3.6.
                </x-ui.alert>
                <x-ui.alert variant="warning" title="Peringatan Gelombang Tinggi" :dismissible="true">
                    Tinggi gelombang signifikan melebihi 2,0 meter. Armada kelas &lt; 10 GT diimbau berhati-hati.
                </x-ui.alert>
                <x-ui.alert variant="danger" title="Deteksi Risiko Bycatch Tinggi" :dismissible="true">
                    Zona Z5 terdeteksi di koordinat lereng kontinental. Larangan setting rawai hanyut aktif.
                </x-ui.alert>
            </div>
        </section>

        <!-- 2. Badges -->
        <section class="space-y-4">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-[#244456] pb-2">2. Badge (Varian Ilmiah & DOM)</h3>
            <div class="flex flex-wrap items-center gap-3 p-5 rounded-2xl bg-white dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456]">
                <x-ui.badge variant="default">Default</x-ui.badge>
                <x-ui.badge variant="cyan" :dot="true" :pulse="true">Live Telemetri</x-ui.badge>
                <x-ui.badge variant="emerald" :dot="true">Aman Melaut</x-ui.badge>
                <x-ui.badge variant="amber" :dot="true">Waspada Gelombang</x-ui.badge>
                <x-ui.badge variant="rose" :dot="true">Bahaya Ekstrem</x-ui.badge>
                <x-ui.badge variant="z1">Z1 Inshore PFG</x-ui.badge>
                <x-ui.badge variant="z2">Z2 Offshore PFG</x-ui.badge>
                <x-ui.badge variant="z3">Z3 Feeding Core</x-ui.badge>
                <x-ui.badge variant="z4">Z4 Nursery Ground</x-ui.badge>
                <x-ui.badge variant="z5">Z5 Bycatch Risk</x-ui.badge>
            </div>
        </section>

        <!-- 3. Buttons & 8. Input -->
        <section class="space-y-4">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-[#244456] pb-2">3. Button & 8. Input</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-5 rounded-2xl bg-white dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456]">
                <div class="space-y-3">
                    <p class="text-xs font-semibold text-slate-500">Varian Tombol</p>
                    <div class="flex flex-wrap gap-2.5">
                        <x-ui.button variant="primary">Primary</x-ui.button>
                        <x-ui.button variant="secondary">Secondary</x-ui.button>
                        <x-ui.button variant="outline">Outline</x-ui.button>
                        <x-ui.button variant="ghost">Ghost</x-ui.button>
                        <x-ui.button variant="danger">Danger</x-ui.button>
                    </div>
                    <div class="flex items-center gap-2 pt-2">
                        <x-ui.button size="sm">Small</x-ui.button>
                        <x-ui.button size="md">Medium</x-ui.button>
                        <x-ui.button size="lg">Large Size</x-ui.button>
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-xs font-semibold text-slate-500">Komponen Input</p>
                    <x-ui.input label="Koordinat Bujur (°E)" placeholder="111.7275" value="111.7275" />
                    <x-ui.input label="Threshold Kesesuaian HSI" placeholder="0.70" error="Nilai HSI harus berada di antara 0.00 dan 1.00" />
                </div>
            </div>
        </section>

        <!-- 5. Card, 13. StatCard & 12. Skeleton -->
        <section class="space-y-4">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-[#244456] pb-2">5. Card, 12. Skeleton, & 13. StatCard</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-ui.stat-card
                    title="Total Sel Terkoneksi"
                    value="8.129"
                    subtitle="Grid WPPNRI 573 PostGIS"
                    delta="100% Valid"
                    deltaType="positive"
                    accent="cyan"
                />

                <x-ui.card title="Card Kontainer Standar" subtitle="Dukungan header dan footer">
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Komponen Card membungkus konten operasional dengan batas halus, border radius 16px, dan mode gelap otomatis.
                    </p>
                    <x-slot:footer>
                        Footer Card Reusable &bull; SEACAST Blade
                    </x-slot:footer>
                </x-ui.card>

                <x-ui.card title="Skeleton Loader">
                    <x-ui.skeleton variant="text" :count="3" />
                    <div class="pt-3">
                        <x-ui.skeleton variant="rect" />
                    </div>
                </x-ui.card>
            </div>
        </section>

        <!-- 6. EmptyState & 7. ErrorState -->
        <section class="space-y-4">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-[#244456] pb-2">6. EmptyState & 7. ErrorState</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-ui.empty-state
                    title="Tidak Ada Tangkapan Tercatat"
                    description="Belum ada pendaratan ikan untuk bulan dan sektor yang Anda pilih."
                />
                <x-ui.error-state
                    title="Koneksi Satelit Terputus"
                    message="Gagal mengunduh tile citra Sentinel-3 OLCI. Silakan coba kembali."
                />
            </div>
        </section>

        <!-- 9. MapToolbar, 10. Modal, 11. ScientificLegend, 15. Tabs, 16. Tooltip -->
        <section class="space-y-4">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-[#244456] pb-2">
                9. MapToolbar, 10. Modal, 11. Legend, 14. Table, 15. Tabs, & 16. Tooltip
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left: Tabs & Tooltip & MapToolbar & Modal Trigger -->
                <div class="space-y-6 p-5 rounded-2xl bg-white dark:bg-[#0D2B3E] border border-slate-200 dark:border-[#244456]">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 mb-2">15. Tabs Interaktif (Alpine.js)</p>
                        <x-ui.tabs :tabs="[['id' => 'tab1', 'label' => 'Cakalang'], ['id' => 'tab2', 'label' => 'Madidihang'], ['id' => 'tab3', 'label' => 'Albakor']]">
                            <div x-show="activeTab === 'tab1'" class="text-xs text-slate-600 dark:text-slate-300 p-2">
                                Konten kesesuaian habitat <strong>Cakalang</strong>.
                            </div>
                            <div x-show="activeTab === 'tab2'" class="text-xs text-slate-600 dark:text-slate-300 p-2">
                                Konten kesesuaian habitat <strong>Madidihang</strong>.
                            </div>
                            <div x-show="activeTab === 'tab3'" class="text-xs text-slate-600 dark:text-slate-300 p-2">
                                Konten kesesuaian habitat <strong>Albakor</strong>.
                            </div>
                        </x-ui.tabs>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-[#244456]">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-slate-500">16. Tooltip & 9. MapToolbar</p>
                            <div class="flex items-center gap-3">
                                <x-ui.tooltip text="PPN Prigi: Lat -8.2833°, Lon 111.7275°">
                                    <span class="px-2.5 py-1 rounded-lg bg-cyan-100 dark:bg-cyan-950/60 text-cyan-800 dark:text-cyan-300 text-xs font-semibold cursor-help">
                                        Arahkan kursor ke sini
                                    </span>
                                </x-ui.tooltip>
                                <x-ui.map-toolbar />
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-slate-500 mb-1">10. Modal Dialog</p>
                            <x-ui.button @click="$dispatch('open-modal', { name: 'demo-modal' })" variant="secondary" size="sm">
                                Buka Modal Dialog
                            </x-ui.button>
                        </div>
                    </div>
                </div>

                <!-- Right: Scientific Legend -->
                <div>
                    <x-ui.scientific-legend type="hsi" title="11. ScientificLegend: Skala Kesesuaian HSI" />
                    <div class="mt-4">
                        <x-ui.scientific-legend type="bycatch" title="11. ScientificLegend: Bycatch Traffic Light" />
                    </div>
                </div>
            </div>
        </section>

        <!-- 14. Table Example -->
        <section class="space-y-4">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-[#244456] pb-2">14. Table Reusable</h3>
            <x-ui.table :headers="['ID Sel', 'Sektor', 'Kedalaman', 'Jarak Pelabuhan', 'Status Hotspot']">
                <tr>
                    <td class="px-5 py-3 font-mono font-bold text-cyan-500">CELL_0001</td>
                    <td class="px-5 py-3">JATIM</td>
                    <td class="px-5 py-3 font-mono">1.250 m</td>
                    <td class="px-5 py-3 font-mono">14.2 NM</td>
                    <td class="px-5 py-3"><x-ui.badge variant="emerald">Aman Tangkap</x-ui.badge></td>
                </tr>
                <tr>
                    <td class="px-5 py-3 font-mono font-bold text-cyan-500">CELL_0042</td>
                    <td class="px-5 py-3">JATIM</td>
                    <td class="px-5 py-3 font-mono">340 m</td>
                    <td class="px-5 py-3 font-mono">8.5 NM</td>
                    <td class="px-5 py-3"><x-ui.badge variant="rose" :pulse="true">Hotspot Aktif</x-ui.badge></td>
                </tr>
            </x-ui.table>
        </section>
    </div>

    <!-- Reusable Modal Instance -->
    <x-ui.modal name="demo-modal" title="Modal Interaktif SEACAST">
        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
            Ini adalah implementasi komponen modal dialog murni ditenagai Alpine.js dan Tailwind CSS v4 tanpa dependensi pihak ketiga atau framework React.
        </p>
        <div class="flex justify-end gap-2">
            <x-ui.button @click="$dispatch('close-modal', { name: 'demo-modal' })" variant="secondary" size="sm">
                Tutup Dialog
            </x-ui.button>
            <x-ui.button @click="$dispatch('close-modal', { name: 'demo-modal' })" variant="primary" size="sm">
                Konfirmasi
            </x-ui.button>
        </div>
    </x-ui.modal>
</x-layouts.public>

