# SEACAST PRIGI V3 — PANDUAN PENYIAPAN LINGKUNGAN PENGEMBANGAN (PHASE 1)
## PHP 8.3 + LARAVEL 13 + POSTGIS 3.6 ARCHITECTURE

Dokumen ini menjelaskan tata cara instalasi, konfigurasi, dan verifikasi toolchain lingkungan pengembangan lokal SEACAST PRIGI V3 berbasis PHP 8.3, Laravel 13, Livewire 4, PostGIS 3.6, dan Tailwind CSS v4 pada sistem operasi Windows.

---

## 1. PERSYARATAN SISTEM & TOOLCHAIN

| Komponen | Versi Teruji | Sumber / Metode Instalasi | Keterangan |
| :--- | :--- | :--- | :--- |
| **Sistem Operasi** | Windows 10/11 x64 | Host Native | PowerShell Shell |
| **PHP Runtime** | 8.3.36 (cli) | WinGet (`PHP.PHP.8.3`) | Ekstensi `pdo_pgsql`, `pgsql`, `pdo_sqlite`, `intl`, `opcache` aktif |
| **Dependency Manager** | Composer 2.11.1 | Wrapper `composer.bat` | Terhubung ke Packagist PHP |
| **Database Spasial** | PostgreSQL 17.11 + PostGIS 3.6.2 | Localhost:5432 (`seacast_prigi`) | Read-Only Scientific Binding |
| **Internal Database** | SQLite 3.x | `database/database.sqlite` | Auth, sessions, cache, queues |
| **Frontend Tooling** | Node.js v20+ & npm 11.6+ | Build-time only | Kompilasi aset Vite ke `public/build/` |
| **Python Engine** | Python 3.14.7 | Host Native (`C:\Python314\python.exe`) | Inferensi harian & skrip spasial |

---

## 2. INSTALASI & KONFIGURASI PHP 8.3 (WINDOWS)

### A. Instalasi via WinGet
Jalankan perintah berikut pada terminal PowerShell Administrator atau User:
```powershell
winget install --id PHP.PHP.8.3 --silent --accept-package-agreements --accept-source-agreements
```
Lokasi instalasi default:
```
C:\Users\ThinkPad\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\
```

### B. Konfigurasi `php.ini`
Buka atau buat berkas `php.ini` dari `php.ini-development` pada direktori instalasi PHP, pastikan ekstensi berikut diaktifkan:
```ini
extension_dir = "ext"

extension=curl
extension=fileinfo
extension=gd
extension=intl
extension=mbstring
extension=openssl
extension=pdo_pgsql
extension=pgsql
extension=pdo_sqlite
extension=sqlite3
extension=sodium
extension=zip
extension=exif

zend_extension=opcache
opcache.enable=1
opcache.enable_cli=1
opcache.jit=1255
opcache.jit_buffer_size=64M
```

### C. Instalasi Composer
Unduh `composer.phar` resmi dan tempatkan pembungkus `composer.bat` pada direktori instalasi PHP:
```cmd
@echo off
php "%~dp0composer.phar" %*
```
Tambahkan direktori PHP ke User Environment Variable `Path`.

---

## 3. STRUKTUR DATABASE DUAL-CONNECTION

SEACAST PRIGI V3 menerapkan arsitektur dual-database terisolasi:

```
+-----------------------------------------------------------+
|                      LARAVEL 13                           |
+-----------------------------+-----------------------------+
                              |
        +---------------------+---------------------+
        |                                           |
        v                                           v
[ SQLite Internal DB ]                   [ PostgreSQL 17.11 + PostGIS 3.6.2 ]
- Path: database/database.sqlite         - Host: 127.0.0.1:5432
- Connection: 'sqlite' (Default)         - Database: seacast_prigi
- Kegunaan:                              - Connection: 'pgsql' / 'pgsql_readonly'
  * users (Personas)                     - Kegunaan:
  * sessions (Cookie driver)               * canonical_grids (8.129 poligon)
  * cache                                  * daily_predictions (8.129 baris)
  * jobs & failed_jobs                     * grid_monthly_predictions (97.548 baris)
                                           * prigi_port_landings (132 baris)
                                           * prigi_fleet_by_gt (3 baris)
                                         - KEBIJAKAN: STRICT READ-ONLY
```

Konfigurasi `.env` pada project `seacast-prigi-laravel`:
```env
APP_NAME="SEACAST PRIGI V3"
APP_ENV=local
APP_KEY=base64:7K5gB0qC2uI2G7s4l9hG1l9+9tq9vK0N7xY...
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://localhost:8000
APP_LOCALE=id

DB_CONNECTION=sqlite
DB_SQLITE_DATABASE=database/database.sqlite

DB_PGSQL_HOST=127.0.0.1
DB_PGSQL_PORT=5432
DB_PGSQL_DATABASE=seacast_prigi
DB_PGSQL_USERNAME=postgres
DB_PGSQL_PASSWORD=postgres
```

---

## 4. BUILD ASET FRONTEND (VITE)

Kompilasi aset frontend (Tailwind CSS v4 + MapLibre GL JS + Alpine.js) dilakukan via npm:
```powershell
cd seacast-prigi-laravel
npm install
npm run build
```
Hasil kompilasi disimpan di `public/build/assets/` dengan manifest `public/build/manifest.json`.  
Pada runtime produksi, tidak ada proses Node.js yang berjalan; seluruh aset disajikan langsung oleh web server (Nginx/PHP).

---

## 5. SEEDING PENGGUNA DEMO

Inisialisasi tabel internal SQLite dan buat 4 persona operasional:
```powershell
cd seacast-prigi-laravel
php artisan migrate --force
php artisan db:seed --force
```

Akun Persona Bawaan:
1. **Nelayan / Nakhoda**: `nelayan@seacast.id` | Sandi: `seacast2026`
2. **Syahbandar**: `syahbandar@seacast.id` | Sandi: `seacast2026`
3. **Peneliti Oseanografi**: `peneliti@seacast.id` | Sandi: `seacast2026`
4. **Administrator**: `admin@seacast.id` | Sandi: `seacast2026`

---

## 6. MENJALANKAN AUTOMATED TEST SUITE

Jalankan pengujian end-to-end fitur Phase 1:
```powershell
cd seacast-prigi-laravel
php artisan test
```
Atau dengan format PHPUnit Testdox:
```powershell
./vendor/bin/phpunit --testdox
```
Hasil yang diharapkan: **17 tests, 167 assertions, 100% PASS**.

---

## 7. MENJALANKAN DEVELOPMENT SERVER (PORT 8000)

Jalankan server aplikasi pada port 8000 (berdampingan dengan aplikasi legacy di port 3000 tanpa konflik port):
```powershell
cd seacast-prigi-laravel
php artisan serve --host=127.0.0.1 --port=8000
```

Akses URL:
- **Portal Publik**: `http://127.0.0.1:8000/`
- **Profil PPN Prigi**: `http://127.0.0.1:8000/about`
- **Landasan Ilmiah**: `http://127.0.0.1:8000/science`
- **Dokumentasi Oseanografi**: `http://127.0.0.1:8000/documentation`
- **Showroom Design System**: `http://127.0.0.1:8000/design-system`
- **Autentikasi Persona**: `http://127.0.0.1:8000/login`
- **Executive Overview (Auth)**: `http://127.0.0.1:8000/dashboard`
- **Status Mesin & DB (Auth)**: `http://127.0.0.1:8000/dashboard/system-status`
- **Health Check Endpoint**: `http://127.0.0.1:8000/api/monitoring`

