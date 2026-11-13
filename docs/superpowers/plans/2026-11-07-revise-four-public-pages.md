# Rencana Implementasi: Revisi Menyeluruh 4 Halaman Publik SEACAST

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Merevisi secara menyeluruh 4 halaman publik utama SEACAST (`/about`, `/science`, `/ocean-data`, dan `/documentation`) agar berorientasi 100% pada platform aplikasi, pemodelan sains 3 spesies pelagis, telemetri satelit live, dan panduan operasional multi-peran tanpa keterikatan monografi lokal PPN Prigi.

**Architecture:** Laravel 13 Monolith, Blade Components, Tailwind CSS v4, Alpine.js, PostGIS 17 Telemetry Aggregates, dan PHPUnit Test Suite.

**Tech Stack:** PHP 8.3, Laravel 13, Blade, Tailwind CSS v4, PostGIS 17, PHPUnit 12.

**Spec:** `docs/superpowers/specs/2026-11-07-public-pages-full-revision-design.md`

## Global Constraints
- **About Page:** Menghilangkan seluruh monografi/tabel GT lokal PPN Prigi, berfokus penuh menjelaskan aplikasi SEACAST (definisi, nilai pilar, 5 fitur unggulan, alur kerja, pengguna sasaran, arsitektur teknologi).
- **Science Page:** Menjelaskan diferensiasi relung 3 spesies (Tongkol, Cakalang, Madidihang), formulasi matematis HSI komposit ensemble, 5 zona DOM, dan audit akurasi.
- **Ocean Data Page:** Menghubungkan telemetri live dari basis data (SST, Klorofil-a, tinggi gelombang) serta spesifikasi sensor satelit Copernicus, NASA, NOAA, dan BMKG.
- **Documentation Page:** Menyajikan panduan multi-peran (Nelayan, Syahbandar, Peneliti), kepatuhan zonasi Permen KP 59/2020, dan glosarium oseanografi lengkap.
- **Integritas Tes:** Seluruh tes PHPUnit wajib 100% lulus.

---

### Task 1: Revisi Halaman `/about` — Fokus Penuh Aplikasi SEACAST

**Files:**
- Modify: `seacast-prigi-laravel/app/Http/Controllers/Web/PublicPageController.php`
- Modify: `seacast-prigi-laravel/resources/views/public/about.blade.php`

- [ ] **Step 1: Mutakhirkan controller `PublicPageController::about()`**
  - Bersihkan ketergantungan pada `PrigiFleetByGt` dan `PrigiPortLanding`.
  - Teruskan data platform tingkat sistem jika dibutuhkan (total sel pantauan kanonik 8.129, estimasi armada WPP 573 terintegrasi 2.450+, total titik hotspot aktif).
- [ ] **Step 2: Susun ulang Blade view `about.blade.php`**
  - Buat hero identity aplikasi SEACAST (*Scientific Ocean Intelligence & Fisheries Decision Support Platform*).
  - Tampilkan 3 pilar nilai utama (Efisiensi BBM, Keselamatan Maritim, Kepatuhan Zonasi DOM).
  - Tampilkan kartu 5 fitur unggulan (DPI Radar, Trip Planner, Sailing Advisory PDF, Analitik Armada, DOM).
  - Tampilkan visualisasi alur kerja aplikasi (Satelit $\to$ GEE $\to$ PostGIS $\to$ AI $\to$ Web UI).
  - Tampilkan profil target pengguna (Nelayan, Syahbandar, Peneliti).
  - Tampilkan ringkasan spesifikasi stack teknologi.
- [ ] **Step 3: Uji render `/about` via curl**
  - Pastikan respons HTTP 200 OK dan tidak ada string "PPN Prigi yang terletak di Teluk Prigi".

---

### Task 2: Revisi Halaman `/science` — Metodologi Bio-Oseanografi, 3 Spesies, HSI & DOM

**Files:**
- Modify: `seacast-prigi-laravel/resources/views/public/science.blade.php`

- [ ] **Step 1: Susun konten sains komprehensif pada `science.blade.php`**
  - Buat penjelasan teori relung ekologi Hutchinson untuk 3 spesies pelagis utama:
    - Tongkol (*Auxis thazard*): Neritik pesisir $0-12$ NM, klorofil-a pesisir tinggi.
    - Cakalang (*Katsuwonus pelamis*): Tubir benua / batas upwelling $12-50$ NM.
    - Madidihang (*Thunnus albacares*): Oseanik laut dalam $>50$ NM.
  - Tampilkan formulasi matematis HSI komposit ensemble ($\text{MaxEnt} + \text{RF} + \text{GLM}$) dan penjelasan normalisasi probabilitas $[0,000 - 1,000]$.
  - Tampilkan tabel audit performa model kuantitatif (AUC, TSS) untuk ketiga spesies.
  - Tampilkan visualisasi 5 zona Dynamic Ocean Management (Z1 Inshore PFG, Z2 Offshore PFG, Z3 Feeding Core, Z4 Nursery Ground, Z5 High Bycatch Risk).
- [ ] **Step 2: Uji render `/science` via curl**
  - Pastikan halaman merespons 200 OK dan memuat Tongkol, Cakalang, Madidihang, serta kerangka DOM.

---

### Task 3: Revisi Halaman `/ocean-data` — Katalog Data Satelit & Telemetri Live

**Files:**
- Modify: `seacast-prigi-laravel/app/Http/Controllers/Web/PublicPageController.php`
- Modify: `seacast-prigi-laravel/resources/views/public/ocean-data.blade.php`

- [ ] **Step 1: Kirimkan telemetri oseanografi aktual dari `PublicPageController::oceanData()`**
  - Ambil rata-rata SST, Klorofil-a, tinggi gelombang rata-rata, dan total sel pantauan ($8.129$) dari database.
- [ ] **Step 2: Desain ulang `ocean-data.blade.php`**
  - Pasang *Live Telemetry Bar* aktual di bagian atas.
  - Buat matriks katalog parameter oseanografi lengkap (SST, Klorofil-a, Arus & Vortisitas, Gelombang & Angin, Batimetri & Jarak ke Pantai, Salinitas & MLD).
  - Tampilkan daftar misi satelit & instrumen resmi (Copernicus Sentinel-3, NASA Aqua MODIS/VIIRS, NOAA OISST v2.1, HYCOM Global $1/12^\circ$, BMKG Ina-WAVES).
  - Jelaskan arsitektur data geospasial PostGIS 17 kanonikal dan format ekspor GeoJSON/NetCDF.
- [ ] **Step 3: Uji render `/ocean-data` via curl**
  - Pastikan respons HTTP 200 OK dan telemetri tampil dinamis.

---

### Task 4: Revisi Halaman `/documentation` — Glosarium & Panduan Multi-Peran

**Files:**
- Modify: `seacast-prigi-laravel/resources/views/public/documentation.blade.php`

- [ ] **Step 1: Desain ulang `documentation.blade.php`**
  - Buat panduan alur kerja multi-peran:
    - Nelayan & Nahkoda: Membaca titik koordinat hotspot, merencanakan rute hemat bahan bakar, membaca status keselamatan gelombang, dan mencetak advisory slip.
    - Syahbandar: Validasi SPB resmi, verifikasi batas keselamatan BMKG ($\ge 2,0$ m waspada, $\ge 3,5$ m bahaya), dan pengawasan zonasi.
    - Peneliti: Interpretasi indeks HSI, threshold klasifikasi hotspot ($\ge 0,70$), dan analisis dinamika habitat.
  - Sertakan matriks kepatuhan jalur penangkapan ikan Permen KP No. 59/2020 (Jalur I, II, III).
  - Tambahkan standar mitigasi tangkapan sampingan (*bycatch mitigation*) sesuai resolusi IOTC.
  - Sediakan glosarium oseanografi lengkap (*Upwelling, Front Termal, CPUE Nominal, Eddy, MLD, Thermocline*).
  - Tambahkan FAQ interaktif / accordions ringkas.
- [ ] **Step 2: Uji render `/documentation` via curl**
  - Pastikan respons HTTP 200 OK.

---

### Task 5: Rangkaian Pengujian PHPUnit Komprehensif & Build

**Files:**
- Create: `seacast-prigi-laravel/tests/Feature/PublicPagesComprehensiveTest.php`

- [ ] **Step 1: Buat pengujian fitur PHPUnit**
  - Uji seluruh rute: `/about`, `/science`, `/ocean-data`, `/documentation`.
  - Verifikasi bahwa `/about` memuat deskripsi aplikasi SEACAST dan tidak memuat narasi tunggal PPN Prigi.
  - Verifikasi bahwa `/science` memuat ketiga spesies (Tongkol, Cakalang, Madidihang) dan kerangka DOM.
  - Verifikasi bahwa `/ocean-data` memuat telemetri live dan sensor satelit resmi.
  - Verifikasi bahwa `/documentation` memuat panduan peranan, Permen KP 59/2020, dan glosarium.
- [ ] **Step 2: Jalankan PHPUnit dan Vite build**
  - Jalankan `php ./vendor/bin/phpunit tests/Feature/PublicPagesComprehensiveTest.php`.
  - Jalankan `npm run build`.
- [ ] **Step 3: Git Commit seluruh pembaruan**

