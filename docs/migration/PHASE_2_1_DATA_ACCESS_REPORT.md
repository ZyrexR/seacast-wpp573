# PHASE 2.1 — SCIENTIFIC DATA ACCESS LAYER REPORT
## SEACAST PRIGI V3 — Core Radar Engine & Dynamic Filter

**Tanggal:** 2026-11-04  
**Arsitektur:** Laravel 13.40.0 Modular Monolith  
**Namespace Domain:** `App\Domain\ScientificData`  
**Database:** PostgreSQL 17.11 + PostGIS 3.6.2 (`seacast_prigi`)  
**Least-Privilege User:** `seacast_readonly` (Zero Write Privileges)  
**Status Evaluasi:** **VERIFIED & IMPLEMENTED (100% PASS)**  

---

### 1. Ringkasan Implementasi Layer Akses Data Ilmiah

Lapisan akses data ilmiah telah berhasil dibangun dengan memisahkan domain abstraksi secara bersih dan menggunakan native PostGIS geodetic query untuk integritas matematis tanpa melakukan scan tabel penuh yang tidak perlu.

Struktur file yang diimplementasikan:
- Interface Kontrak: [`ScientificDataRepositoryInterface.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/ScientificData/Repositories/ScientificDataRepositoryInterface.php)
- Implementasi PostGIS: [`PostGisScientificDataRepository.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/ScientificData/Repositories/PostGisScientificDataRepository.php)
- Service Domain: [`ScientificDataService.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/ScientificData/Services/ScientificDataService.php)
- Registrasi DI Container: [`AppServiceProvider.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Providers/AppServiceProvider.php)

---

### 2. Verifikasi Data Sumber & Kesesuaian Baseline

| Dataset Ilmiah | Hasil Query Aktual | Baseline Audit Terkunci | Status | Keterangan |
|---|---:|---:|:---:|---|
| `canonical_grids` | 8.129 baris | 8.129 baris | **VERIFIED** | SRID 4326, Indeks GiST aktif |
| `daily_predictions` | 8.129 baris (2026-11-03) | 8.129 baris | **VERIFIED** | HSI Cakalang, Madidihang, Legacy species removed from TCT scope, DOM, Bycatch |
| `grid_monthly_predictions` | 97.548 baris | 97.548 baris | **VERIFIED** | 12 bulan penuh tahun 2025 (`2025_01` s.d. `2025_12`) |
| `prigi_port_landings` | 132 baris | 132 baris | **VERIFIED** | Periode 2015 s.d. 2025 (132 bulan) |
| `prigi_fleet_by_gt` | 3 agregasi kelas GT | 3 agregasi kelas GT | **VERIFIED** | Total 1.269 kapal terwakili |

---

### 3. Operasi Spasial PostGIS & Formula Jarak Geodetik

#### 3.1. Bounding Box Query Menggunakan GiST Index
Query spasial memanfaatkan operator `&&` dan `ST_MakeEnvelope` untuk memastikan pencarian berbasis indeks spasial, bukan scan sekuensial:
```sql
SELECT cell_id, cell_class, center_lon, center_lat, depth_m, dist_port_km, dist_port_nm,
       is_shelf_break, is_prigi_inshore, is_prigi_offshore, sector,
       ST_AsGeoJSON(geom) as geojson_geom
FROM canonical_grids
WHERE geom && ST_MakeEnvelope(?, ?, ?, ?, 4326)
ORDER BY cell_id;
```

#### 3.2. Perhitungan Jarak Geodetik ke PPN Prigi Homeport
Homeport Prigi berada pada koordinat `111.7275°E, -8.2833°S`. Perhitungan jarak menggunakan PostGIS `geography` tipe lingkaran bola WGS 84 dan dikonversi ke Nautical Miles (1 NM = 1.852 meter):
```sql
SELECT ST_Distance(
    ST_SetSRID(ST_Point(?, ?), 4326)::geography,
    ST_SetSRID(ST_Point(111.7275, -8.2833), 4326)::geography
) / 1852.0 as dist_nm;
```
Hasil uji pada koordinat tepat homeport: `0.000 NM` (selisih < 0.001 NM).

---

### 4. Jaminan Keamanan Akses Read-Only (`seacast_readonly`)

Pengujian dilakukan untuk membuktikan bahwa privilege write ditolak keras oleh PostgreSQL:
- **Command:** `UPDATE canonical_grids SET cell_class = 'MUTATION_TEST' WHERE cell_id = 'CELL_0001'`
- **Hasil:** `SQLSTATE[42501]: Insufficient privilege: 7 ERROR: permission denied for table canonical_grids`
- **Status:** **PASS** (Perlindungan zero-mutation bekerja sempurna).

---

### 5. Bukti Pengujian Unit & Feature

Dua test suite komprehensif dijalankan menggunakan PHPUnit:

1. [`tests/Unit/ScientificDataAccessTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Unit/ScientificDataAccessTest.php):
   - `test_can_retrieve_canonical_grids_count` -> PASS
   - `test_can_retrieve_grids_by_bounding_box` -> PASS
   - `test_can_retrieve_daily_prediction_summary` -> PASS
   - `test_can_retrieve_daily_predictions_with_geojson_features` -> PASS
   - `test_geodesic_distance_to_prigi_port` -> PASS
   - **Hasil:** 5 tests, 1.185 assertions, 0 failures (1,41s).

2. [`tests/Feature/ScientificDataRepositoryTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Feature/ScientificDataRepositoryTest.php):
   - `test_read_only_privilege_strictly_blocks_mutations` -> PASS
   - `test_spatial_bounding_box_utilizes_envelope_filter` -> PASS
   - `test_daily_geojson_features_respect_filters` -> PASS
   - `test_monthly_predictions_can_be_retrieved` -> PASS
   - `test_landings_and_fleet_aggregation` -> PASS
   - **Hasil:** 5 tests, 97 assertions, 0 failures (1,31s).

**Total Keseluruhan Pengujian Phase 2.1:** 10 tests, 1.282 assertions, **100% PASS**.

