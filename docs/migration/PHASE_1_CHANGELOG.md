# SEACAST PRIGI V3 — PHASE 1 CHANGELOG
## FULL MIGRATION TO LARAVEL 13 (MODULAR MONOLITH)

Dokumen ini mencatat seluruh perubahan, penambahan berkas, dan konfigurasi yang dilakukan selama implementasi **Phase 1 — Laravel Foundation**.

---

### [1.0.0] — 2026-11-04

#### 1. Setup Lingkungan & Toolchain Host Windows
- Mengunduh dan memasang `PHP.PHP.8.3` (v8.3.36) via WinGet pada direktori `C:\Users\ThinkPad\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\`.
- Mengonfigurasi `php.ini` dengan mengaktifkan modul kritis: `pdo_pgsql`, `pgsql`, `pdo_sqlite`, `sqlite3`, `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `sodium`, `zip`, `exif`, serta `zend_extension=opcache` (`opcache.enable_cli=1`).
- Memasang Composer v2.11.1 dengan pembungkus `composer.bat` dan menghubungkan ke User Environment Path.

#### 2. Inisialisasi Proyek Laravel 13
- Menginisialisasi proyek baru `seacast-prigi-laravel/` menggunakan `composer create-project laravel/laravel`.
- Framework versi target: `laravel/framework:v13.40.0`.
- Memasang Livewire 4 via `composer require livewire/livewire` (v4.4.8).
- Memasang dependency build frontend via npm: `maplibre-gl@^5.1.0` dan `alpinejs@^3.14.9`.

#### 3. Arsitektur Modular Monolith
- Membuat direktori domain bisnis pada `app/Domain/`:
  - `Authentication/`
  - `Oceanography/`
  - `Spatial/`
  - `Prediction/`
  - `Species/`
  - `DOM/`
  - `Bycatch/`
  - `Fleet/`
  - `CPUE/`
  - `TimeMachine/`
  - `Simulation/`
  - `Monitoring/`
- Mengorganisasi controller berdasarkan konteks:
  - `app/Http/Controllers/Web/` (Public & Dashboard)
  - `app/Http/Controllers/Api/` (API Endpoints)
- Membuat layer service dan repository terstruktur di `app/Services/` dan `app/Repositories/`.

#### 4. PostGIS 3.6 & Database Architecture
- Mengonfigurasi dual-database pada `config/database.php`:
  - Default connection: `sqlite` (`database/database.sqlite`) untuk auth, sessions, cache, and queues.
  - Read-only scientific connection: `pgsql` dan `pgsql_readonly` terhubung langsung ke PostgreSQL 17.11 / PostGIS 3.6.2 (`seacast_prigi`).
- Membuat 5 Eloquent Read-Only Models:
  - `app/Models/CanonicalGrid.php`
  - `app/Models/DailyPrediction.php`
  - `app/Models/GridMonthlyPrediction.php`
  - `app/Models/PrigiPortLanding.php`
  - `app/Models/PrigiFleetByGt.php`
  - Semua model dikunci dengan `protected $guarded = ['*']` dan `$connection = 'pgsql'`.
- Mengimplementasikan `App\Services\DatabaseHealthService` untuk inspeksi PostGIS runtime (SRID 4326, validitas geometri, GIST index, row counts).
- Mengimplementasikan `App\Services\PostGisService` untuk query spasial parameterized (`ST_MakeEnvelope`, `ST_AsGeoJSON`, `ST_Extent`).

#### 5. Sistem Antarmuka & 16 Komponen Blade UI
- Mengonfigurasi Tailwind CSS v4 pada `resources/css/app.css` dengan token warna oseanografi:
  - Deep Navy: `#071B2B`, `#091F30`, `#0D2B3E`, `#12364A`
  - Bright Cyan: `#0D8195`, `#13B8C8`
  - DOM Alert Accents: Emerald (`#10B981`), Amber (`#F59E0B`), Rose (`#F43F5E`), Purple (`#A855F7`)
  - Standar Tipografi: Batas bawah ukuran font minimal 12px (`text-floor`).
- Membuat 16 komponen antarmuka mandiri berbasis Blade Anonymous Components di `resources/views/components/ui/`:
  - `alert.blade.php` (4 varian: info, success, warning, danger)
  - `badge.blade.php` (varian umum & 5 zona DOM: Z1, Z2, Z3, Z4, Z5)
  - `breadcrumbs.blade.php`
  - `button.blade.php` (5 varian, 3 ukuran)
  - `card.blade.php` (header, slot, footer)
  - `empty-state.blade.php`
  - `error-state.blade.php`
  - `input.blade.php` (label, placeholder, error message)
  - `map-toolbar.blade.php` (zoom in, zoom out, reset view)
  - `modal.blade.php` (dialog interaktif berbasis Alpine.js)
  - `scientific-legend.blade.php` (skala HSI & Bycatch Traffic Light)
  - `skeleton.blade.php`
  - `stat-card.blade.php` (indikator metrik, nilai, subtitle, delta badge)
  - `table.blade.php` (tabel data ilmiah responsif)
  - `tabs.blade.php` (navigasi tab berbasis Alpine.js)
  - `tooltip.blade.php`
- Membuat 2 Master Layout:
  - `resources/views/layouts/public.blade.php` & `components/layouts/public.blade.php`
  - `resources/views/layouts/dashboard.blade.php` & `components/layouts/dashboard.blade.php`
- Membuat komponen navigasi:
  - `resources/views/components/public/navbar.blade.php`
  - `resources/views/components/public/footer.blade.php`
  - `resources/views/components/dashboard/sidebar.blade.php`
  - `resources/views/components/dashboard/topbar.blade.php`

#### 6. Autentikasi & Ruang Masuk Persona
- Mengimplementasikan `App\Http\Controllers\Web\AuthController.php`:
  - Login session-based dengan proteksi CSRF.
  - Rate limiting (maksimal 5 percobaan gagal per IP/email per menit).
  - Session regeneration saat login dan invalidasi saat logout.
  - Tampilan login dengan 4 tombol pemilih cepat persona operasional.
- Menyemai 4 pengguna demo pada SQLite via `DatabaseSeeder.php`:
  - Nelayan (`nelayan@seacast.id`)
  - Syahbandar (`syahbandar@seacast.id`)
  - Peneliti (`peneliti@seacast.id`)
  - Admin (`admin@seacast.id`)

#### 7. Halaman Publik & Dashboard
- Membuat `App\Http\Controllers\Web\HomeController.php` dan `resources/views/public/home.blade.php`:
  - Hero editorial dengan telemetri aktual PostGIS (8.129 sel, rata-rata SST 29.2°C, gelombang 3.36 m, 1.269 kapal).
  - MapLibre GL JS live container interaktif.
  - Dossier 3 spesies pelagis (Cakalang, Madidihang, Albakor MaxEnt weight = 0.000).
  - Matriks hierarki 5 zona DOM (`Z4 -> Z5 -> Z3 -> Z1 -> Z2 -> GEN`).
- Membuat `App\Http\Controllers\Web\PublicPageController.php` dan views:
  - `resources/views/public/about.blade.php`
  - `resources/views/public/science.blade.php`
  - `resources/views/public/documentation.blade.php`
  - `resources/views/public/design-system.blade.php` (Showroom 16 komponen)
- Membuat `App\Http\Controllers\Web\DashboardController.php` dan views:
  - `resources/views/dashboard/overview.blade.php` (4 KPI dinamis terhubung PostGIS)
  - `resources/views/dashboard/system-status.blade.php` (status database, disk, python, queues)
  - `resources/views/dashboard/module-placeholder.blade.php` (placeholder informatif untuk 8 modul migrasi Phase 2)

#### 8. Integrasi MapLibre GL JS
- Membungkus MapLibre GL JS ke dalam Alpine.js component `seacastMap` pada `resources/js/app.js`.
- Konfigurasi basemap CartoDB Positron / OSM tanpa data oseanografi tiruan (*no fake data*).
- Homeport marker otomatis pada koordinat Pelabuhan Perikanan Nusantara (PPN) Prigi: Lon 111.7275°, Lat -8.2833°.
- Bounding box navigasi dikunci pada batas WPPNRI 573: Lon 102.5° to 126.5°, Lat -14.0° to -6.8°.

#### 9. Endpoint Pemantauan Sistem
- Mendaftarkan route API pada `routes/api.php`: `GET /api/monitoring`.
- Mengimplementasikan `App\Http\Controllers\Api\MonitoringApiController.php` dan `App\Services\SystemMonitoringService.php`.
- Mengembalikan JSON kesehatan sistem, latensi query database, baseline spasial 8.129 sel, status runtime Python, serta status pipeline satelit.
- Memasukkan label resmi: `Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)`.

#### 10. Automated Testing
- Menulis dan memverifikasi 4 Feature Test Suites:
  - `tests/Feature/EnvironmentTest.php` (3 tests, 11 assertions)
  - `tests/Feature/DatabaseHealthTest.php` (3 tests, 31 assertions)
  - `tests/Feature/AuthenticationTest.php` (6 tests, 21 assertions)
  - `tests/Feature/FrontendComponentsTest.php` (2 tests, 38 assertions)
  - `tests/Feature/MonitoringApiTest.php` (1 test, 14 assertions)
  - `tests/Unit/ExampleTest.php` & `tests/Feature/ExampleTest.php` (2 tests, 2 assertions)
- **Total Pengujian**: 17 tests, 167 assertions, 100% PASS (durasi ~5-7 detik).

