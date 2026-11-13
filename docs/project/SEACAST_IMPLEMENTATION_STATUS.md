# SEACAST PRIGI & CILACAP V3 — IMPLEMENTATION STATUS MATRIX
## MATRIKS STATUS IMPLEMENTASI FITUR, MODUL, DATASET & ARSITEKTUR SISTEM

**Dokumen ID:** SEACAST-DOC-STATUS-MATRIX-V3  
**Standar Klasifikasi:** 6-Tier Implementation Taxonomy  
**Status Evaluasi:** Berdasarkan Bukti Kode Sumber, Skema Basis Data PostGIS, Live API BMKG, dan Rangkaian Uji Otomatis Aktual  
**Tanggal Audit:** 13 November 2026 (Diperbarui dari Baseline 4 November 2026)  
**Auditor:** Lead Technical Architect & Marine Spatial Intelligence Specialist  

---

## 1. DEFINISI TAKSONOMI STATUS IMPLEMENTASI

Setiap komponen dievaluasi secara ketat menggunakan salah satu dari 6 predikat berikut:

1. **`IMPLEMENTED & VERIFIED`**: Sudah selesai diimplementasikan dalam kode sumber, terhubung dengan basis data/model yang valid, dan telah lulus pengujian otomatis (*automated test*) serta uji lapangan dengan bukti empiris terdokumentasi.
2. **`IMPLEMENTED — NEEDS VALIDATION`**: Sudah diimplementasikan secara fungsional dalam kode sumber, namun bukti validasi empiris di laut (*ground-truthing GPS logbook*) masih memerlukan pengumpulan data lanjutan.
3. **`PARTIALLY IMPLEMENTED`**: Sebagian fungsi atau komponen antarmuka sudah tersedia, namun sub-komponen perluasan masih dalam proses integrasi bertahap.
4. **`PLANNED`**: Telah disepakati dalam rancangan arsitektur atau roadmap, namun implementasi kode sumber belum dimulai.
5. **`NOT AVAILABLE`**: Belum tersedia sama sekali di dalam repositori atau belum ada data yang dapat diproses.
6. **`BLOCKED`**: Terhambat oleh dependensi eksternal yang tidak dapat diakses.

---

## 2. MATRIKS STATUS FITUR DAN MODUL UTAMA

| No | Modul / Komponen | Status Implementasi | Bukti Empiris Implementasi | Lokasi File / Basis Data / Tes |
| :---: | :--- | :---: | :--- | :--- |
| **1** | **Grid Kanonikal WPPNRI 573** | `IMPLEMENTED & VERIFIED` | 8.129 sel poligon 100% valid, SRID 4326, GiST spatial index. 16.258 record prediksi di PostgreSQL 17. | `canonical_grids`, `daily_predictions`, `SpatialGeometryValidationTest.php` |
| **2** | **Model Ensemble SDM Cakalang** | `IMPLEMENTED — NEEDS VALIDATION` | MaxEnt (0,320) + RF (0,355) + GLM (0,325) = 1,000. Skor ROC-AUC 0,795. Model self-contained .joblib. | `models/cakalang_ensemble_model.joblib`, `model_evaluations_summary.json` |
| **3** | **Model Ensemble SDM Madidihang**| `IMPLEMENTED — NEEDS VALIDATION` | MaxEnt (0,295) + RF (0,365) + GLM (0,340) = 1,000. Skor ROC-AUC 0,784. Model self-contained .joblib. | `models/madidihang_ensemble_model.joblib`, `model_evaluations_summary.json` |
| **4** | **Model Ensemble SDM Albakor** | `IMPLEMENTED — NEEDS VALIDATION` | MaxEnt (0,000) + RF (0,517) + GLM (0,483) = 1,000. MaxEnt dinolkan mutlak karena karakteristik termoklin. | `models/albakor_ensemble_model.joblib`, `ScientificToleranceRegressionTest.php` |
| **5** | **Zonasi DOM (Z1 - Z5 + GEN)** | `IMPLEMENTED & VERIFIED` | Algoritma resolusi prioritas berjenjang ($Z4 \succ Z5 \succ Z3 \succ Z1 \succ Z2 \succ GEN$) teruji lulus 100%. | `DomClassificationService.php`, `ScientificToleranceRegressionTest.php` |
| **6** | **Bycatch Safety Traffic Light** | `IMPLEMENTED & VERIFIED` | Klasifikasi Hijau/Kuning/Merah berbasis lereng benua (150–1200m), front dingin, dan HSI Cakalang $\ge 0,70$. | `BycatchRiskService.php`, `ScientificToleranceRegressionTest.php` |
| **7** | **Integrasi Live BMKG Maritim** | `IMPLEMENTED & VERIFIED` | Penarikan data cuaca real-time tanpa token dari open API resmi BMKG (`XO026`, `XM001`, `M.12`, `M.10`). Peringatan gelombang spesifik GT. | `app/Services/BmkgMaritimService.php`, `dpi-radar.blade.php`, `BmkgMaritimTest.php` |
| **8** | **Koridor Dual-Hub Prigi & Cilacap** | `IMPLEMENTED & VERIFIED` | Dukungan multi-pelabuhan penuh: switching PPN Prigi & PPS Cilacap, seeder 132 bulan, jarak Haversine dinamis, range rings (12, 30, 60 NM), camera flyTo. | `PpsCilacapLandingsSeeder.php`, `dpi-radar.blade.php`, `FleetApiController.php` |
| **9** | **Leaflet WebGIS 60 FPS Streamlines** | `IMPLEMENTED & VERIFIED` | Animasi arus laut partikel 60 FPS di `currentsPane` transparan penuh, clipping batas pantai Samudra Hindia (`getSouthCoastLat`), scrubber 13 bulan sub-50ms, ESRI switcher. | `dpi-radar.blade.php`, `public/build/assets/app-*.css`, `DpiRadarViewTest.php` |
| **10**| **Vector Tile Server (ST_AsMVT)** | `IMPLEMENTED & VERIFIED` | Endpoint `/api/tiles/{layer}/{z}/{x}/{y}.pbf` menyajikan binary PBF langsung dari PostGIS dengan latensi $<50\text{ ms}$. | `TileApiController.php`, `VectorTileApiTest.php` |
| **11**| **Time Machine Spasio-Temporal**| `IMPLEMENTED & VERIFIED` | Slider 12 bulan historis tahun 2025 memuat 97.548 baris data spasial PostGIS dan endpoint `/api/timemachine`. | `time-machine.blade.php`, `TimeMachineApiController.php`, `TimeMachineApiTest.php` |
| **12**| **What-If Scenario Simulator** | `IMPLEMENTED & VERIFIED` | Simulasi pertubasi iklim ($\Delta\text{SST}, \times\text{Chl-a}$) dan kalkulator keekonomian armada (solar, jarak, GT). | `what-if-simulator.blade.php`, `SimulationApiController.php`, `SimulationApiTest.php` |
| **13**| **Analitik Armada & Pendaratan** | `IMPLEMENTED & VERIFIED` | 132 bulan pendaratan PPN Prigi & PPS Cilacap 2015–2025 serta profil komposisi armada kapal per kelompok GT. | `fleet-analytics.blade.php`, `FleetApiController.php`, `FleetAnalyticsTest.php` |
| **14**| **Katalog Data Oseanografi** | `IMPLEMENTED & VERIFIED` | Tampilan tabel parameter biofisik, sensor satelit, dan penjelasan fenomena upwelling Jawa. | `ocean-data.blade.php`, `PublicOceanPlatformPagesTest.php` |
| **15**| **Mesin Ekspor Data Ilmiah** | `IMPLEMENTED & VERIFIED` | Dukungan ekspor GeoJSON, CSV, dan GIS Metadata Package melalui API terkompresi. | `ExportApiController.php`, `ScientificExportTest.php` |
| **16**| **Otentikasi Sesi & Multi-Persona**| `IMPLEMENTED & VERIFIED` | Multi-role RBAC (`UserRole`: Nelayan, Syahbandar, Peneliti, Admin), CSRF protection, sanitasi cookie. | `AuthController.php`, `ProductionSecurityHardeningTest.php` |
| **17**| **Security Headers Middleware** | `IMPLEMENTED & VERIFIED` | Header OWASP (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`). | `SecurityHeadersMiddleware.php`, `ProductionSecurityHardeningTest.php` |
| **18**| **Otomatisasi Headless GEE Extractor** | `IMPLEMENTED & VERIFIED` | Ekstraksi harian 8.129 sel dalam 11,3s via partisi bujur $114.5^\circ\text{ E}$, latensi dinamis NOAA ($T-2$ to $T-3$), komposit median 8-hari JAXA Chl-a. | `scripts/gee_daily_extractor.py`, `scripts/gee_pipeline_transformer.py` |
| **19**| **Orkestrator Harian Terpadu** | `IMPLEMENTED & VERIFIED` | Artisan `php artisan seacast:sync-live` mengeksekusi GEE + BMKG + Model + PostGIS + Cache flush pada pukul 04:00 WIB via scheduler Laravel. | `app/Console/Commands/SyncLiveOceanDataCommand.php`, `routes/console.php` |
| **20**| **Kesiapan CloudPanel & VPS Hostinger** | `IMPLEMENTED & VERIFIED` | Runbook lengkap arsitektur deploy produksi Ubuntu 24.04 LTS, PHP 8.3-FPM, PostgreSQL 17, PostGIS 3.6, Nginx, Python venv, cron. | `docs/deployment/HOSTINGER_CLOUDPANEL_DEPLOYMENT_GUIDE.md`, `.env.production.example` |

---

## 3. MATRIKS STATUS SUMBER DATA EKSTERNAL

| Sumber Data | Deskripsi Data | Status Aktual | Keterangan & Keterbatasan |
| :--- | :--- | :---: | :--- |
| **Statistik PPN Prigi** | Pendaratan bulanan & armada (2015–2025) | `IMPLEMENTED & VERIFIED` | 132 record pendaratan dan 11 tahun armada terverifikasi di PostgreSQL dan file CSV. |
| **Statistik PPS Cilacap** | Pendaratan bulanan Jawa Tengah (2015–2025) | `IMPLEMENTED & VERIFIED` | 132 record pendaratan dimasukkan via `PpsCilacapLandingsSeeder.php` sebagai simpul koridor barat. |
| **Statistik PPN Palabuhanratu** | Pendaratan & armada Jawa Barat | `PLANNED` | Masuk ke dalam roadmap fase berikutnya setelah stabilisasi Dual-Hub Cilacap. |
| **Statistik PPN Pengambengan** | Pendaratan & armada Bali / Selat Bali | `PLANNED` | Masuk ke dalam roadmap fase berikutnya untuk simpul timur. |
| **Batas WPPNRI 573 (BIG)** | Geometri batas laut resmi BIG Layer 21 | `IMPLEMENTED & VERIFIED` | Tersimpan di `data/aoi/raw/WPP573_BIG_ORIGINAL.geojson` dan di PostGIS. |
| **SST NOAA OISST v2.1** | Suhu muka laut harian 0,25° | `IMPLEMENTED & VERIFIED` | Ditarik otomatis via `scripts/gee_daily_extractor.py` dengan deteksi latensi dinamis $T-2$ s/d $T-3$. |
| **Klorofil-a JAXA & MODIS**| Konsentrasi klorofil-a optik satelit | `IMPLEMENTED & VERIFIED` | Ditarik otomatis via GEE dengan komposit median 8-hari bebas tutupan awan. |
| **Batimetri GEBCO 2023** | Kedalaman laut 15 arc-seconds | `IMPLEMENTED & VERIFIED` | Terintegrasi permanen pada atribut `depth_m` tabel `canonical_grids`. |
| **Angin NOAA CFSv2 / ERA5** | Vektor angin $u_{10}, v_{10}$ 0,25° | `IMPLEMENTED & VERIFIED` | Ditarik via GEE untuk kalkulasi kecepatan angin dan upwelling Ekman. |
| **Arus Laut Permukaan** | HYCOM & CMEMS / Leaflet Streamlines | `IMPLEMENTED & VERIFIED` | Ditransformasikan menjadi partikel animasi 60 FPS di atas canvas transparan WebGIS. |
| **BMKG Maritim Open API** | Cuaca pelabuhan, tinggi gelombang & angin | `IMPLEMENTED & VERIFIED` | Ditarik real-time dari endpoint resmi `XO026`, `XM001`, `M.12`, `M.10` tanpa ketergantungan token. |
| **Logbook GPS Nelayan** | Koordinat GPS tangkapan aktual per kapal | `NOT AVAILABLE` | Menjadi fokus utama Tahap 1 Roadmap untuk validasi biologis independen di lapangan. |

---

## 4. MATRIKS RISIKO & TIKET BLOKIR TEKNIS (TECHNICAL DEBT)

| ID Tiket | Kategori | Tingkat Risiko | Status Tiket | Penjelasan & Rencana Mitigasi |
| :---: | :---: | :---: | :---: | :--- |
| **TD-01** | Sains | TINGGI | `NEEDS VALIDATION` | **Proxy Niche Validation:** Titik latih model SDM dievaluasi terhadap proxy suhu-klorofil ideal teoretis. Mitigasi: Program pemasangan GPS logger pada armada nelayan Prigi dan Cilacap (Tahap 1 Roadmap). |
| **TD-02** | Oseanografi | RENDAH | `RESOLVED` | **Automated GEE Daily Telemetry:** Tuntas diimplementasikan melalui `scripts/gee_daily_extractor.py` dengan partisi bujur $114.5^\circ\text{ E}$ (11,3 detik runtime) dan deteksi latensi dinamis. |
| **TD-03** | Data | SEDANG | `PARTIALLY RESOLVED` | **Ekspansi Multi-Pelabuhan WPPNRI 573:** Berhasil diperluas dari simpul tunggal Prigi menjadi Koridor Dual-Hub Prigi–Cilacap. PPN Palabuhanratu dan PPN Pengambengan dijadwalkan pada tahap ekspansi berikutnya. |
| **TD-04** | Infrastruktur| RENDAH | `RESOLVED` | **Ketergantungan Next.js:** 100% tuntas tereliminasi; aplikasi berjalan di atas Laravel 13 Standalone dengan Blade, Alpine.js, dan Tailwind CSS. |
| **TD-05** | Keamanan | RENDAH | `RESOLVED` | **Akses Basis Data PostGIS:** Diamankan menggunakan hak akses baca murni `seacast_readonly` untuk publik dan writer backend terkontrol. |
| **TD-06** | Meteorologi | RENDAH | `RESOLVED` | **Live BMKG Ingestion:** Tuntas diimplementasikan via `app/Services/BmkgMaritimService.php` yang mengonsumsi open authoritative endpoints BMKG tanpa perlu token privat berbayar. |
| **TD-07** | Frontend | RENDAH | `RESOLVED` | **Visualisasi WebGIS & Canvas Masking:** Tuntas diperbaiki dengan memindahkan canvas ke `currentsPane` (`z-index: 450`), `ctx.clearRect()` transparan penuh, clipping pantai Jawa, dan scrubber 13 bulan. |
| **TD-08** | Deployment | RENDAH | `RESOLVED` | **Target Infrastruktur Cloud:** Runbook arsitektur Hostinger VPS (Ubuntu 24.04 LTS) + CloudPanel tuntas disusun di `docs/deployment/HOSTINGER_CLOUDPANEL_DEPLOYMENT_GUIDE.md`. |
