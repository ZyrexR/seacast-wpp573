# Rencana Implementasi: Generalisasi Beranda WPPNRI 573 & Pembersihan Total Breadcrumb/Disclaimer Dashboard

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menggeneralisasi beranda publik SEACAST ke skala multi-pelabuhan WPPNRI 573 dengan centered cinematic hero, menyajikan 3 komoditas pelagis utama empiris (Tongkol, Cakalang, Madidihang), serta membersihkan seluruh breadcrumb kaku dan banner disclaimer akademis di antarmuka dashboard eksekutif.

**Architecture:** Laravel 13 Modular Monolith (Blade + Alpine.js + Tailwind CSS) dengan PostgreSQL 17 / PostGIS 3.6. Transformasi tampilan publik dari tata letak split-map menjadi centered cinematic hero dengan visual kapal pelagis samudera lepas; restrukturisasi topbar dan overview dashboard agar bebas dari teks redundan seperti `WPPNRI 573 / Executive Overview — SEACAST` dan alert disclaimer akademis.

**Tech Stack:** PHP 8.3 / Laravel 13, Blade Components, Alpine.js, Tailwind CSS v4, Leaflet GIS, PostgreSQL 17 / PostGIS.

**Spec:** `docs/superpowers/specs/2026-11-07-generalize-homepage-wpp573-design.md`

## Global Constraints
- **Multi-Port Scope:** WPPNRI 573 mencakup 3 pelabuhan pangkalan aktif: PPN Prigi (Jatim), PPS Cilacap (Jateng), PPN Palabuhanratu (Jabar).
- **Spesies Fokus:** 3 Komoditas pelagis utama: Tongkol Lisong/Krai (*Auxis thazard / Euthynnus* spp.), Cakalang (*Katsuwonus pelamis*), Madidihang (*Thunnus albacares*).
- **Pembersihan Teks:** Hapus teks `WPPNRI 573 / ...`, `Executive Overview — SEACAST`, `DB Active: 8.129 Sel`, `Pelabelan Ilmiah Mandatori`, dan `Scientific Caveats Alert Banner`.
- **Integritas Tes:** Pertahankan 100% kelulusan seluruh tes PHPUnit (170+ tests passed).

## Review Focus
1. Halaman Beranda Publik (`/`) memuat tanpa error JS/CSS dan menampilkan hero sinematik terpusat tanpa container peta.
2. Kartu 3 spesies di beranda publik menampilkan Tongkol, Cakalang, dan Madidihang dengan nilai HSI yang valid.
3. Header dashboard (`topbar.blade.php`) hanya menampilkan judul bersih tanpa prefix `WPPNRI 573 /` dan tanpa pill `DB Active: 8.129 Sel`.
4. Dashboard Overview (`/dashboard`) menampilkan peta berpusat WPPNRI 573 dengan 3 marker pelabuhan (Prigi, Cilacap, Palabuhanratu) dan kartu KPI Tongkol menggantikan Albakor.
5. Halaman `fleet-analytics` dan `trip-planner` tidak lagi memiliki baris breadcrumb kaku atau banner peringatan berulang.

---

### Task 1: Controller & Data Layer Updates for Tongkol & Multi-Port Aggregates

**Files:**
- Modify: `app/Http/Controllers/Web/HomeController.php`
- Modify: `app/Http/Controllers/Web/DashboardController.php`
- Modify: `tests/Feature/PublicPagesTest.php`
- Modify: `tests/Feature/DashboardOverviewTest.php`

- [ ] **Step 1: Update test assertions for Public Home and Dashboard Overview**
  - Pastikan tes memeriksa kehadiran kata "Tongkol", rincian pelabuhan "Prigi", "Cilacap", "Palabuhanratu", dan ketiadaan teks `Pelabelan Ilmiah Mandatori`.
- [ ] **Step 2: Update `HomeController.php`**
  - Query `AVG(hsi_tongkol)` dari `daily_predictions` (dengan fallback 0.556).
  - Susun agregasi armada multi-pelabuhan: PPN Prigi (1.269 kapal), PPS Cilacap (818 kapal), PPN Palabuhanratu (363 kapal) -> total 2.450 kapal WPPNRI 573.
  - Kirim variabel `tongkolHsi`, `multiPortFleet`, `portsList` ke view `public.home`.
- [ ] **Step 3: Update `DashboardController.php` (`index()` method)**
  - Hitung `avg_tongkol` bersama dengan hotspot, gelombang, dan bycatch.
  - Sediakan array koordinat dan metadata 3 pelabuhan pangkalan (Prigi, Cilacap, Palabuhanratu) untuk dikirim ke view `dashboard.overview`.
- [ ] **Step 4: Jalankan tes feature untuk verifikasi data layer**
  - Jalankan `php ./vendor/bin/phpunit tests/Feature/PublicPagesTest.php tests/Feature/DashboardOverviewTest.php` dan pastikan lulus.
- [ ] **Step 5: Commit Task 1**
  - Git commit dengan pesan deskriptif.

---

### Task 2: Public Homepage Re-Architecture (Centered Cinematic Hero & 3 Pelagic Species)

**Files:**
- Modify: `resources/views/public/home.blade.php`
- Create/Verify: Visual hero background styling (cinematic marine dark styling with ambient flare)

- [ ] **Step 1: Redesign Hero Section di `home.blade.php`**
  - Ganti grid 2 kolom dengan 1 kolom terpusat (*Centered Cinematic Hero*).
  - Gunakan background gelap sinematik maritim (`bg-[#071B2B]` bergradien ke `#091F30` dengan ambient overlay cyan & mesh halus).
  - Tampilkan judul kontras tinggi: "Kecerdasan Oseanografi & Prediksi Presisi Pelagis Samudera Hindia (WPP 573)".
  - Subtitle mencakup 3 pelabuhan pangkalan: PPN Prigi, PPS Cilacap, dan PPN Palabuhanratu.
  - Tombol aksi utama (CTA): "Masuk Ruang Kendali" & "Rencanakan Melaut".
  - Metrik strip terintegrasi: 8.129 Sel Kanonik, Hotspot HSI >= 0.70, dan 2.450+ Kapal Armada WPPNRI 573 (dengan pill rincian Prigi • Cilacap • Palabuhanratu).
  - Hapus elemen kontainer peta mini Leaflet dari hero publik agar tampilan lega dan bersih.
- [ ] **Step 2: Update Kartu Spesies Unggulan (Species Dossiers)**
  - Perbarui 3 kartu spesies menjadi:
    1. **Tongkol Lisong / Krai (*Auxis thazard / Euthynnus* spp.)** — Komoditas pendaratan terbesar dengan nilai HSI terkalibrasi.
    2. **Cakalang (*Katsuwonus pelamis*)** — Pelagis perenang cepat permukaan.
    3. **Madidihang (*Thunnus albacares*)** — Tuna epipelagis bernilai ekonomi tinggi.
- [ ] **Step 3: Verifikasi rendering halaman beranda publik**
  - Jalankan tes dan buka via browser / curl untuk memastikan kode status 200 OK tanpa error sintaks Blade.
- [ ] **Step 4: Commit Task 2**
  - Git commit dengan pesan deskriptif.

---

### Task 3: Total Decluttering of Breadcrumbs and Academic Disclaimers across Dashboard

**Files:**
- Modify: `resources/views/components/dashboard/topbar.blade.php`
- Modify: `resources/views/dashboard/overview.blade.php`
- Modify: `resources/views/dashboard/fleet-analytics.blade.php`
- Modify: `resources/views/dashboard/trip-planner.blade.php`
- Modify: `resources/views/components/public/footer.blade.php`

- [ ] **Step 1: Declutter Topbar (`topbar.blade.php`)**
  - Hapus teks breadcrumb kaku `WPPNRI 573 /`. Tampilkan langsung judul halaman (`{{ $title ?? 'Ruang Kendali Operasional' }}`) dengan tipografi bersih.
  - Hapus badge `DB Active: 8.129 Sel` di sisi kanan topbar.
- [ ] **Step 2: Restrukturisasi Dashboard Overview (`overview.blade.php`)**
  - Perbarui judul komponen dashboard: `<x-layouts.dashboard title="Ruang Kendali Operasional">`.
  - Bersihkan deskripsi sub-header agar mencakup "WPPNRI 573 (PPN Prigi, PPS Cilacap, PPN Palabuhanratu)".
  - Ganti kartu KPI Albakor dengan **Kesesuaian Habitat Tongkol** (`$tongkolHsi`).
  - Hapus teks "Simulasi Oseanografi Musiman BMKG Terkalibrasi" pada subtitle kartu gelombang, ganti dengan "Prakiraan Rata-Rata WPPNRI 573".
  - Hapus alert banner `Pelabelan Ilmiah Mandatori`.
  - Perbarui peta Leaflet di Overview: berpusat di `[110.5, -8.8]` dengan zoom 6.8 - 7.0, tambahkan 3 pin marker pelabuhan pangkalan (Prigi, Cilacap, Palabuhanratu), dan perbarui label menjadi "Canvas Geospasial Samudera Hindia (WPPNRI 573)".
- [ ] **Step 3: Bersihkan `fleet-analytics.blade.php`**
  - Hapus baris breadcrumb redundan `SEACAST / PPN_PRIGI / ...`.
  - Hapus banner amber `Scientific Caveats Alert Banner`.
- [ ] **Step 4: Bersihkan `trip-planner.blade.php`**
  - Hapus baris breadcrumb redundan `SEACAST / WPPNRI 573 / Rencana Operasional Melaut`.
- [ ] **Step 5: Bersihkan footer & tautan sisa**
  - Perbarui teks link `Executive Overview` menjadi `Ruang Kendali Operasional` di `footer.blade.php`.
- [ ] **Step 6: Commit Task 3**
  - Git commit dengan pesan deskriptif.

---

### Task 4: Asset Compilation, Regression Testing & Visual Verification

**Files:**
- Test scripts & build artifacts

- [ ] **Step 1: Kompilasi aset Vite**
  - Jalankan `npm run build` di direktori `seacast-prigi-laravel`.
- [ ] **Step 2: Eksekusi seluruh tes PHPUnit**
  - Jalankan `php ./vendor/bin/phpunit` dan pastikan seluruh 170+ tes lulus tanpa error (100% green).
- [ ] **Step 3: Verifikasi live server**
  - Lakukan request HTTP ke `http://127.0.0.1:8000/` dan `http://127.0.0.1:8000/dashboard` untuk memverifikasi halaman termuat dengan respons 200 OK.
- [ ] **Step 4: Commit Task 4**
  - Git commit final untuk penyesuaian integrasi.

