# SEACAST PRIGI V3 — BATASAN & RUANG LINGKUP PHASE 1 (KNOWN LIMITATIONS)
## TRANSISI ARSITEKTUR MENUJU PHASE 2

Dokumen ini mendokumentasikan batasan yang disengaja (*intentional limitations*), batas ruang lingkup (*scope boundaries*), dan status modul dalam implementasi **Phase 1 — Laravel Foundation**.

---

## 1. BATAS RUANG LINGKUP (PHASE 1 SCOPE BOUNDARY)

Tujuan utama Phase 1 adalah meletakkan fondasi framework Laravel 13, sistem desain, komponen antarmuka, tata letak, koneksi database PostGIS read-only, autentikasi sesi persona, dan pengujian otomatis tanpa mengimplementasikan logika interaktif kompleks dari modul spesifik yang dijadwalkan untuk Phase 2 hingga Phase 5.

Berikut rincian batasan sistem pada Phase 1:

| Area / Modul | Status Implementasi Phase 1 | Perilaku Saat Ini | Rencana Implementasi Penuh |
| :--- | :---: | :--- | :--- |
| **DPI Radar & Dynamic Filter** | *Placeholder Berinformasi* | Menampilkan kartu pratinjau arsitektur, parameter filter target, dan disclaimer data. | **Phase 2 (Core Radar Engine)** |
| **DOM 5-Zone Spatial Engine** | *Placeholder Berinformasi* | Menampilkan matriks prioritas ilmiah (`Z4 -> Z5 -> Z3 -> Z1 -> Z2 -> GEN`). | **Phase 3 (Spatial & DOM Engine)** |
| **Bycatch Risk Classifier** | *Placeholder Berinformasi* | Menampilkan indikator statis formula ambang batas bathymetry & SST. | **Phase 3 (Spatial & DOM Engine)** |
| **CPUE Analysis & Fleet Economics** | *Placeholder Berinformasi* | Menampilkan label *Proksi CPUE Tahunan (Nominal)* dan skema agregasi armada. | **Phase 4 (Advanced Analytics)** |
| **Species Models Dossier** | *Informasional Statis* | Parameter model MaxEnt & GAM terdokumentasi lengkap (Legacy species removed from TCT scope MaxEnt weight = 0.000). Interaktivitas model dinonaktifkan. | **Phase 4 (Advanced Analytics)** |
| **Oceanographic Explorer** | *Placeholder Berinformasi* | Kontainer visualisasi geospasial siap pakai dengan basemap CartoDB. Query raster ditunda ke Phase 3. | **Phase 3 (Spatial & DOM Engine)** |
| **Time Machine (Hindcast)** | *Placeholder Berinformasi* | Menampilkan rentang data 2021–2025 dan pemilih tanggal terkalibrasi. | **Phase 4 (Advanced Analytics)** |
| **What-If Ocean Simulator** | *Placeholder Berinformasi* | Menampilkan kontrol slider parameter SST/Chla teoritis tanpa eksekusi inferensi. | **Phase 4 (Advanced Analytics)** |
| **Data Ingestion Satelit Langsung** | *Calibrated Snapshot / Fallback* | Menggunakan snapshot historis NOAA OISST & Sentinel-3 OLCI. BMKG ditandai sebagai *Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)*. | **Phase 5 (Pipeline & Hardening)** |
| **Autentikasi Produksi** | *Demo Persona Tersemai* | 4 persona bawaan aktif di SQLite lokal untuk keperluan testing dan demo evaluator. SSO/OAuth dinonaktifkan. | **Phase 5 (Hardening)** |

---

## 2. DETAIL BATASAN TEKNIS

### A. Basis Data PostGIS 17 / PostGIS 3.6
- **Strict Read-Only**: Laravel 13 tidak menjalankan migrasi tabel apapun pada basis data `seacast_prigi`. Semua operasi create/alter dilarang keras.
- **Tabel Internal**: Tabel `users`, `sessions`, `cache`, `jobs`, dan `failed_jobs` disimpan pada SQLite lokal (`database/database.sqlite`). Pada deployment produksi multi-node di masa mendatang, tabel ini dapat diarahkan ke schema terpisah `laravel_internal` di PostgreSQL tanpa mengganggu tabel spasial kanonikal.

### B. Komponen Peta MapLibre GL JS
- **Tanpa Data Tiruan (No Fake Ocean Data)**: Peta pada homepage dan dashboard saat ini hanya merender basemap vektor CartoDB Positron / OpenStreetMap dan penanda lokasi resmi PPN Prigi. Layer heatmap acak atau poligon tiruan ditiadakan agar mematuhi integritas ilmiah.
- **Interaktivitas Tile Vektor**: Pemanggilan layer tile vektor kustom (MVT/GeoJSON 8.129 sel langsung dari PostGIS menggunakan `ST_AsMVT`) dirancang untuk diintegrasikan pada **Phase 2 & Phase 3**.

### C. Pipeline Python & Orkestrasi
- **Python CLI Runtime**: Runtime Python 3.14.7 terdeteksi dan terdaftar dalam pemeriksaan sistem `GET /api/monitoring`.
- **Inference Cron Job**: Penjadwalan inferensi otomatis harian pukul 02:00 UTC telah disiapkan blueprint-nya di `routes/console.php` dan `SystemMonitoringService`, tetapi eksekusi sub-proses python dinonaktifkan hingga Phase 5.

### D. Penanganan Aset Vite
- Node.js dan npm dipertahankan **hanya** sebagai alat bantu kompilasi aset pada fase build.
- Di lingkungan runtime server port 8000, tidak ada service Node.js yang berjalan; berkas JavaScript dan CSS disajikan langsung dari direktori `public/build/`.

---

## 3. CHECKLIST KESIAPAN MENUJU PHASE 2

Sebelum memulai Phase 2 (Core Radar Engine & Dynamic Filter):
- [x] Fondasi Laravel 13 beroperasi penuh dan stabil.
- [x] 17 automated tests lulus 100%.
- [x] Sesi autentikasi persona siap digunakan.
- [x] Koneksi spasial ke 8.129 sel kanonikal terverifikasi.
- [x] Desain sistem 16 komponen Blade siap di-reuse.
- [ ] **Menunggu persetujuan eksplisit (*User Sign-Off*) dari pengguna untuk membuka Phase 2.**

