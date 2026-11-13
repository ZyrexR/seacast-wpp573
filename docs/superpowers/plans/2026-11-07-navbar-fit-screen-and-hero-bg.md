# Rencana Implementasi: Penataan Navbar, Hero Fit-to-Screen, dan Background Foto Sinematik Samudera

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menata ulang navigasi publik navbar dengan komposisi logis ("Tentang SEACAST" di kanan samping Login), merapikan layout hero section menjadi pas di viewport (*fit-to-screen*) tanpa terpotong kartu metriknya, dan mengintegrasikan foto maritim kapal di samudera lepas sebagai latar belakang sinematik.

**Architecture:** Laravel 13 Blade Component (`navbar.blade.php`, `home.blade.php`), Alpine.js (mobile drawer toggle), Tailwind CSS v4 responsive utilities, dan PostGIS live telemetry metrics.

**Tech Stack:** PHP 8.3 / Laravel 13, Blade Components, Alpine.js, Tailwind CSS v4.

**Spec:** `docs/superpowers/specs/2026-11-07-navbar-fit-screen-and-hero-bg-design.md`

## Global Constraints
- **Navbar Links Order:** `Beranda` $\rightarrow$ `Sains & Model` $\rightarrow$ `Data Oseanografi` $\rightarrow$ `Dokumentasi` $\rightarrow$ `Tentang SEACAST` $\rightarrow$ `[ Login ]`.
- **Fit to Screen:** Seluruh hero section (termasuk 3 kartu metrik) harus masuk dalam 1 layar (*first fold*) pada resolusi laptop standar (~768p/1080p).
- **Background Image:** Menggunakan `public/images/hero-marine.jpg` dengan peredup gradien gelap (`rgba(7, 27, 43, 0.88)`) agar teks tetap tajam dan kontras.
- **Integritas Tes:** Seluruh tes PHPUnit wajib tetap 100% lulus.

---

### Task 1: Restrukturisasi Navbar Publik & Mobile Drawer (`navbar.blade.php`)

**Files:**
- Modify: `seacast-prigi-laravel/resources/views/components/public/navbar.blade.php`

- [ ] **Step 1: Tata ulang navigasi desktop**
  - Susun urutan:
    1. Beranda (`route('home')`)
    2. Sains & Model (`route('science*')`)
    3. Data Oseanografi (`route('ocean-data')`)
    4. Dokumentasi (`route('documentation')`)
    5. Tentang SEACAST (`route('about')`)
    6. Tombol Login
- [ ] **Step 2: Tambahkan Mobile Hamburger Toggle & Drawer**
  - Buat state Alpine.js `x-data="{ mobileOpen: false }"` pada header.
  - Tambahkan tombol hamburger untuk layar `md:hidden`.
  - Tambahkan menu drawer animasi transisi dengan seluruh 5 tautan + tombol login untuk pengguna mobile.

---

### Task 2: Re-proporsi Hero Section Fit-to-Screen & Background Foto Sinematik (`home.blade.php`)

**Files:**
- Modify: `seacast-prigi-laravel/resources/views/public/home.blade.php`

- [ ] **Step 1: Terapkan Background Foto Sinematik Maritim**
  - Pasang `style="background-image: url('{{ asset('images/hero-marine.jpg') }}'); background-size: cover; background-position: center;"` pada section hero.
  - Berikan overlay ganda (`absolute inset-0 bg-gradient-to-b from-[#071B2B]/90 via-[#071B2B]/85 to-[#071B2B]/95`) ditambah ambient cyan flare untuk memastikan visibilitas gambar sekaligus keterbacaan teks 100%.
- [ ] **Step 2: Sesuaikan Spasi Vertikal Fit-to-Screen**
  - Gunakan `min-h-[calc(100vh-4rem)] flex flex-col justify-between py-6 sm:py-8 lg:py-10`.
  - Sesuaikan ukuran heading `text-2xl sm:text-4xl lg:text-[2.65rem]` dengan margin seimbang.
  - Jadikan 3 kartu metrik di bagian bawah berada di dalam kontainer yang pas di viewport tanpa terpotong di bagian bawah.

---

### Task 3: Verifikasi Komprehensif & Pengujian Regresi

**Files:**
- Modify: `seacast-prigi-laravel/tests/Feature/Wpp573GeneralizationTest.php`

- [ ] **Step 1: Perbarui assertions pengujian**
  - Tambahkan pengecekan kehadiran `Sains & Model`, `Data Oseanografi`, `Dokumentasi`, dan `Tentang SEACAST`.
- [ ] **Step 2: Kompilasi aset Vite**
  - Jalankan `npm run build` di direktori `seacast-prigi-laravel`.
- [ ] **Step 3: Eksekusi PHPUnit**
  - Jalankan tes `Wpp573GeneralizationTest` dan `ComprehensiveRouteCoverageTest`.
- [ ] **Step 4: Verifikasi HTTP Status & Commit**
  - Pastikan server live merespons 200 OK dan commit perubahan ke git.

