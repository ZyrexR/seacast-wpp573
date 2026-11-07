# SEACAST PRIGI V3 — MIGRATION ROADMAP
**Strategy:** Incremental Migration with Verification Gates  
**Status:** Phase 0 Completed — Awaiting User Approval to Begin Phase 1  

---

## 1. PETA JALAN MIGRASI 10 FASE (PHASE 0 – PHASE 9)

```
[Phase 0: Forensic Audit] ◄── KITA BERADA DI SINI (10 Dokumen Lengkap)
       │
       ▼ (Approval Gate 1)
[Phase 1: Laravel 13 Foundation & Tooling Setup]
       │
       ▼ (Approval Gate 2)
[Phase 2: Database Layer & 100% API Parity]
       │
       ▼ (Approval Gate 3)
[Phase 3: Public Portal & Editorial Pages (Blade + Livewire)]
       │
       ▼ (Approval Gate 4)
[Phase 4: Operational Dashboard & MapLibre GL Geospatial Engine]
       │
       ▼ (Approval Gate 5)
[Phase 5: Scientific Analytics Modules (DOM, HSI, CPUE, Time Machine)]
       │
       ▼ (Approval Gate 6)
[Phase 6: Python Scientific Engine Orchestration (Process / Queue)]
       │
       ▼ (Approval Gate 7)
[Phase 7: Production Authentication, Roles & Security Hardening]
       │
       ▼ (Approval Gate 8)
[Phase 8: Comprehensive QA, Spatial Validation, & Scientific Regression]
       │
       ▼ (Approval Gate 9)
[Phase 9: Production Cutover & Legacy Framework Retirement]
```

---

## 2. DETAIL TAHAPAN & CRITICAL GATES

### PHASE 0: FORENSIC AUDIT (STATUS: COMPLETED)
- **Cakupan:** Audit 100% terhadap 24 rute halaman, 6 endpoint API, database PostGIS 17, model SDM, skrip Python, dan komponen UI.
- **Deliverables:** 10 dokumen audit lengkap pada direktori `docs/migration/`.
- **Gate Review:** Pengguna meninjau dokumen audit dan memberikan persetujuan eksplisit untuk memulai Phase 1.

### PHASE 1: LARAVEL 13 FOUNDATION & TOOLING SETUP
- **Aksi:** Inisialisasi project Laravel 13, instalasi dependensi Composer (Livewire 4), konfigurasi Tailwind CSS, setup koneksi read-only PostgreSQL 17, pembuatan master layouts (`base.blade.php`, `public.blade.php`, `dashboard.blade.php`), porting 16 komponen UI tokens.
- **Kriteria Keberhasilan:** Skeleton aplikasi berjalan di port lokal, design system page me-render 16 komponen Blade identik dengan versi React.

### PHASE 2: DATABASE LAYER & 100% API PARITY
- **Aksi:** Pembuatan Eloquent models (`CanonicalGrid`, `DailyPrediction`, `GridMonthlyPrediction`, `PrigiPortLanding`, `PrigiFleetByGt`), repository spatial, dan implementasi 6 endpoint API (`/api/predictions`, `/api/grid`, `/api/landings`, `/api/timemachine`, `/api/fleet`, `/api/simulate`).
- **Kriteria Keberhasilan:** Uji perbandingan JSON snapshot otomatis antara Next.js dan Laravel: 100% match untuk schema, count baris, dan metadata.

### PHASE 3: PUBLIC PORTAL & EDITORIAL PAGES
- **Aksi:** Migrasi 13 halaman publik ke Blade templates, termasuk 7 seksi homepage ilmiah baru (`HeroOceanIntelligence`, `OperationalSnapshotStrip`, `OceanographicIntelligenceExplorer`, dll.).
- **Kriteria Keberhasilan:** Halaman beranda dan sub-halaman me-render dengan data aktual PostGIS, font floor $\ge$ 12px, WCAG AA, dan lulus uji responsif.

### PHASE 4: OPERATIONAL DASHBOARD & MAPLIBRE ENGINE
- **Aksi:** Migrasi `/dashboard` dan `/dashboard/dpi-radar`. Integrasi canvas WebGL MapLibre GL JS dibungkus Alpine.js. Render 8.129 sel poligon kanonikal dan layer switchers.
- **Kriteria Keberhasilan:** Peta interaktif dapat di-pan/zoom dengan responsif, inspeksi sel bekerja, dan filter armada aktif.

### PHASE 5: SCIENTIFIC ANALYTICS MODULES
- **Aksi:** Migrasi 9 modul analitis dashboard: Bycatch Safety, CPUE Analysis, DOM Management, Oceanographic Explorer, Species Models, System Status, Time Machine (slider 12 bulan 2025), What-If Simulator, dan Data Monitoring.
- **Kriteria Keberhasilan:** Formula HSI dan prioritas zonasi DOM tereksekusi identik dengan PRD v2.3.2.

### PHASE 6: PYTHON SCIENTIFIC ENGINE ORCHESTRATION
- **Aksi:** Konfigurasi Laravel Process dan Queue Job untuk memanggil `scripts/daily_inference.py` pada pukul 04:00 WIB, menangani input parameter tervalidasi, error handling, dan auto-invalidation cache API.
- **Kriteria Keberhasilan:** Script Python berhasil dieksekusi via Artisan command `php artisan seacast:infer-daily` dengan exit code 0.

### PHASE 7: AUTHENTICATION & SECURITY HARDENING
- **Aksi:** Mengganti mock `localStorage` dengan Laravel Session Authentication native, User model di database, middleware `EnsureRole` (Nelayan, Syahbandar, Peneliti, Admin), CSRF tokens, dan rate limiting API.
- **Kriteria Keberhasilan:** Akses route dashboard terproteksi middleware session; bypass tanpa login dialihkan ke `/login`.

### PHASE 8: COMPREHENSIVE QA, SPATIAL & REGRESSION TESTING
- **Aksi:** Eksekusi rangkaian automated tests (PHPUnit / Pest): Feature tests (24 routes 200 OK, 6 APIs), Unit tests (DOM priority, bycatch logic), Spatial tests (validitas geometri, SRID 4326), dan Scientific tolerance regression.
- **Kriteria Keberhasilan:** 100% test pass tanpa toleransi deviasi numerik di luar ambang batas presisi floating-point.

### PHASE 9: PRODUCTION CUTOVER & RETIREMENT
- **Aksi:** Backup database penuh, switch traffic server web utama ke Laravel, pengujian live operasional pendaratan TPI Prigi, pemantauan error logs, dan pembersihan artefak framework lama (Next.js/React).
- **Kriteria Keberhasilan:** Laravel menjadi satu-satunya framework yang melayani platform SEACAST PRIGI secara mandiri.

