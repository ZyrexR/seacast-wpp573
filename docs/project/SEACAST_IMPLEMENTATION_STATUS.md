# SEACAST PRIGI V3 — IMPLEMENTATION STATUS MATRIX
## MATRIKS STATUS IMPLEMENTASI FITUR, MODUL, DATASET & ARSITEKTUR SISTEM

**Dokumen ID:** SEACAST-DOC-STATUS-MATRIX-V3  
**Standar Klasifikasi:** 6-Tier Implementation Taxonomy  
**Status Evaluasi:** Berdasarkan Bukti Kode Sumber, Skema Basis Data, dan Rangkaian Uji Otomatis Aktual  
**Tanggal Audit:** 4 November 2026  
**Auditor:** Lead Technical Auditor & Software Quality Assurance Specialist  

---

## 1. DEFINISI TAKSONOMI STATUS IMPLEMENTASI

Setiap komponen dievaluasi secara ketat menggunakan salah satu dari 6 predikat berikut:

1. **`IMPLEMENTED & VERIFIED`**: Sudah selesai diimplementasikan dalam kode sumber, terhubung dengan basis data/model yang valid, dan telah lulus pengujian otomatis (automated test) dengan bukti empiris yang terdokumentasi.
2. **`IMPLEMENTED — NEEDS VALIDATION`**: Sudah diimplementasikan secara fungsional dalam kode sumber, namun bukti validasi empiris di lapangan (*ground-truthing*) atau validasi data biologis independen masih belum memadai.
3. **`PARTIALLY IMPLEMENTED`**: Sebagian fungsi atau komponen antarmuka sudah tersedia, namun pipeline pendukung (misalnya live API streaming atau otomatisasi ingest) masih memerlukan intervensi manual atau fungsi simulasi.
4. **`PLANNED`**: Telah disepakati dalam dokumen rancangan arsitektur atau roadmap, namun implementasi kode sumber belum dimulai.
5. **`NOT AVAILABLE`**: Belum tersedia sama sekali di dalam repositori atau belum ada data yang dapat diproses.
6. **`BLOCKED`**: Terhambat oleh dependensi eksternal yang tidak dapat diakses (misalnya ketiadaan izin akses data atau kredensial API berbayar).

---

## 2. MATRIKS STATUS FITUR DAN MODUL UTAMA

| No | Modul / Komponen | Status Implementasi | Bukti Empiris Implementasi | Lokasi File / Basis Data / Tes |
| :---: | :--- | :---: | :--- | :--- |
| **1** | **Grid Kanonikal WPPNRI 573** | `IMPLEMENTED & VERIFIED` | 8.129 sel poligon 100% valid, SRID 4326, GiST spatial index. | `canonical_grids` (PostgreSQL 17), `SpatialGeometryValidationTest.php` |
| **2** | **Model Ensemble SDM Cakalang** | `IMPLEMENTED — NEEDS VALIDATION` | MaxEnt (0,320) + RF (0,355) + GLM (0,325) = 1,000. Skor AUC 0,795. Model terlatih tersedia, namun evaluasi berbasis proxy lingkungan. | `models/cakalang_ensemble_model.joblib`, `model_evaluations_summary.json` |
| **3** | **Model Ensemble SDM Madidihang**| `IMPLEMENTED — NEEDS VALIDATION` | MaxEnt (0,295) + RF (0,365) + GLM (0,340) = 1,000. Skor AUC 0,784. Evaluasi berbasis proxy deterministik. | `models/madidihang_ensemble_model.joblib`, `model_evaluations_summary.json` |
| **4** | **Model Ensemble SDM Albakor** | `IMPLEMENTED — NEEDS VALIDATION` | MaxEnt (0,000) + RF (0,517) + GLM (0,483) = 1,000. MaxEnt dinolkan mutlak karena AUC 0,507. | `models/albakor_ensemble_model.joblib`, `ScientificToleranceRegressionTest.php` |
| **5** | **Zonasi DOM (Z1 - Z5 + GEN)** | `IMPLEMENTED & VERIFIED` | Algoritma resolusi prioritas berjenjang ($Z4 \succ Z5 \succ Z3 \succ Z1 \succ Z2 \succ GEN$) teruji lulus 100%. | `DomClassificationService.php`, `ScientificToleranceRegressionTest.php` |
| **6** | **Bycatch Safety Traffic Light** | `IMPLEMENTED & VERIFIED` | Klasifikasi Hijau/Kuning/Merah berbasis lereng benua (150–1200m), front dingin, dan HSI Cakalang $\ge 0,70$. | `BycatchRiskService.php`, `ScientificToleranceRegressionTest.php` |
| **7** | **Filter Keselamatan Kapal BMKG** | `IMPLEMENTED & VERIFIED` | Filter spesifik GT: $<10\text{ GT}$ waspada pada $H_s \ge 2,0\text{ m}$; bahaya ekstrem pada $H_s \ge 3,5\text{ m}$. | `scripts/daily_inference.py:158-164`, `bycatch-safety.blade.php` |
| **8** | **Peta WebGL MapLibre DPI Radar** | `IMPLEMENTED & VERIFIED` | 60 FPS WebGL rendering via Alpine.js memuat PostGIS MVT Vector Tiles. Desain adopsi `mapcn`. | `dpi-radar.blade.php`, `VectorTileApiTest.php`, `DpiRadarViewTest.php` |
| **9** | **Vector Tile Server (ST_AsMVT)** | `IMPLEMENTED & VERIFIED` | Endpoint `/api/tiles/{layer}/{z}/{x}/{y}.pbf` menyajikan binary PBF langsung dari PostGIS dengan latensi $<50\text{ ms}$. | `TileApiController.php`, `VectorTileApiTest.php` |
| **10**| **Time Machine Spasio-Temporal**| `IMPLEMENTED & VERIFIED` | Slider 12 bulan historis tahun 2025 memuat 97.548 baris data spasial PostGIS dan endpoint `/api/timemachine`. | `time-machine.blade.php`, `TimeMachineApiController.php`, `TimeMachineApiTest.php` |
| **11**| **What-If Scenario Simulator** | `IMPLEMENTED & VERIFIED` | Simulasi pertubasi iklim ($\Delta\text{SST}, \times\text{Chl-a}$) dan kalkulator keekonomian armada (solar, jarak, GT). | `what-if-simulator.blade.php`, `SimulationApiController.php`, `SimulationApiTest.php` |
| **12**| **Analitik Armada & Pendaratan** | `IMPLEMENTED & VERIFIED` | 132 bulan observasi empiris 2015–2025 PPN Prigi dan profil komposisi armada kapal per kelompok GT. | `fleet-analytics.blade.php`, `FleetApiController.php`, `FleetAnalyticsTest.php` |
| **13**| **Katalog Data Oseanografi** | `IMPLEMENTED & VERIFIED` | Tampilan tabel parameter biofisik, sensor satelit, dan penjelasan upwelling Jawa. | `ocean-data.blade.php`, `PublicOceanPlatformPagesTest.php` |
| **14**| **Mesin Ekspor Data Ilmiah** | `IMPLEMENTED & VERIFIED` | Dukungan ekspor GeoJSON, CSV, dan GIS Metadata Package melalui API terkompresi. | `ExportApiController.php`, `ScientificExportTest.php` |
| **15**| **Otentikasi Sesi & Multi-Persona**| `IMPLEMENTED & VERIFIED` | Multi-role RBAC (`UserRole`: Nelayan, Syahbandar, Peneliti, Admin), CSRF protection, sanitasi cookie. | `AuthController.php`, `ProductionSecurityHardeningTest.php` |
| **16**| **Security Headers Middleware** | `IMPLEMENTED & VERIFIED` | Header OWASP (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`). | `SecurityHeadersMiddleware.php`, `ProductionSecurityHardeningTest.php` |
| **17**| **Otomatisasi Scheduler Harian** | `IMPLEMENTED & VERIFIED` | Scheduler Laravel harian 04:00 WIB memanggil Queue Job ber-retry 3x dan autoinvalidasi cache geospasial. | `routes/console.php`, `ProcessDailyInferenceJob.php`, `InferenceQueueJobTest.php` |
| **18**| **Eliminasi Runtime Next.js/React**| `IMPLEMENTED & VERIFIED` | 0 paket React di runtime produksi, proses Next.js 100% mati, kompilasi Vite v8 selesai 2,43s. | `package.json`, `PHASE_9_PRODUCTION_CUTOVER_REPORT.md` |
| **19**| **Pipeline Ingest GEE to Web** | `IMPLEMENTED & VERIFIED` | Pipeline otomatis GEE v2.3.2 ke Laravel via `GeeDataIngestionService`, Artisan CLI `seacast:ingest-gee`, imputasi median ilmiah, dan endpoint telemetri `/api/monitoring/pipeline`. | `GeeDataIngestionService.php`, `IngestGeeDataCommand.php`, `GeeDataPipelineTest.php` |

---

## 3. MATRIKS STATUS SUMBER DATA EKSTERNAL

| Sumber Data | Deskripsi Data | Status Aktual | Keterangan & Keterbatasan |
| :--- | :--- | :---: | :--- |
| **Statistik PPN Prigi** | Pendaratan bulanan & armada (2015–2025) | `IMPLEMENTED & VERIFIED` | 132 record pendaratan dan 11 tahun armada terverifikasi di PostgreSQL dan file CSV. |
| **Statistik PPN Palabuhanratu** | Pendaratan & armada Jawa Barat | `PLANNED` | Proposal pengajuan data telah disusun; data belum tersedia di repositori. |
| **Statistik PPN Pengambengan** | Pendaratan & armada Bali / Selat Bali | `PLANNED` | Proposal pengajuan data telah disusun; data belum tersedia di repositori. |
| **Batas WPPNRI 573 (BIG)** | Geometri batas laut resmi BIG Layer 21 | `IMPLEMENTED & VERIFIED` | Tersimpan di `data/aoi/raw/WPP573_BIG_ORIGINAL.geojson` dan di PostGIS. |
| **SST NASA MODIS-Aqua** | Suhu muka laut bulanan 4,63 km | `IMPLEMENTED & VERIFIED` | Diekstrak via GEE ke dalam 132 file CSV bulanan (2015–2025). |
| **SST NOAA OISST v2.1** | Suhu muka laut harian 0,25° | `IMPLEMENTED & VERIFIED` | Digunakan untuk baseline anomali klimatologi 11 tahun bebas tutupan awan. |
| **Klorofil-a NASA MODIS**| Konsentrasi klorofil-a 4,63 km | `IMPLEMENTED & VERIFIED` | Diekstrak via GEE ke dalam 132 file CSV bulanan (2015–2025). |
| **Batimetri GEBCO 2023** | Kedalaman laut 15 arc-seconds | `IMPLEMENTED & VERIFIED` | Terintegrasi permanen pada atribut `depth_m` tabel `canonical_grids`. |
| **Angin ECMWF ERA5** | Vektor angin $u_{10}, v_{10}$ 0,25° | `IMPLEMENTED & VERIFIED` | Digunakan untuk estimasi kecepatan angin dan kecepatan upwelling Ekman. |
| **Copernicus Marine API**| Arus laut global GLORYS12V1 | `PARTIALLY IMPLEMENTED` | Data historis tersedia dalam CSV; koneksi pipeline real-time API baru berupa desain. |
| **BMKG Ina-WAVES API** | Tinggi gelombang harian operasional | `PARTIALLY IMPLEMENTED` | Menggunakan pemodelan simulasi klimatologis analitik; token live API BMKG belum aktif. |
| **Logbook GPS Nelayan** | Koordinat GPS tangkapan aktual per kapal | `NOT AVAILABLE` | Belum tersedia data koordinat penangkapan kapal in-situ untuk validasi independen. |

---

## 4. MATRIKS RISIKO & TIKET BLOKIR TEKNIS (TECHNICAL DEBT)

| ID Tiket | Kategori | Tingkat Risiko | Status Tiket | Penjelasan & Rencana Mitigasi |
| :---: | :---: | :---: | :---: | :--- |
| **TD-01** | Sains | TINGGI | `NEEDS VALIDATION` | **Proxy Niche Validation:** Titik latih model SDM dievaluasi terhadap proxy suhu-klorofil ideal teoretis. Perlu pengujian *ground-truthing* koordinat pancing nelayan Prigi. |
| **TD-02** | Oseanografi | RENDAH | `RESOLVED` | **GEE Ingestion & Transformation Gateway:** Berhasil diimplementasikan pada Phase 3 GEE Integration melalui `scripts/gee_pipeline_transformer.py` dan `seacast:ingest-gee` dengan dukungan dataset bulanan GEE 104-kolom dan dataset pilot 51-kolom. |
| **TD-03** | Data | SEDANG | `PLANNED` | **Kesenjangan Multi-Pelabuhan:** Belum adanya data dari Palabuhanratu dan Pengambengan membatasi validitas spasial model hanya pada sektor Jawa Timur. |
| **TD-04** | Infrastruktur| RENDAH | `RESOLVED` | **Ketergantungan Next.js:** Berhasil dituntaskan pada Phase 9 dengan migrasi total ke Laravel 13 Standalone (0 dependensi React). |
| **TD-05** | Keamanan | RENDAH | `RESOLVED` | **Akses Basis Data PostGIS:** Berhasil diamankan menggunakan hak akses baca murni `seacast_readonly` dan proteksi sesi multi-role RBAC. |

