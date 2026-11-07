# SEACAST PRIGI V3 — DATABASE & POSTGIS AUDIT
**Audit Execution Date:** 4 November 2026  
**Auditor:** Geospatial Database Engineer  
**Database Name:** `seacast_prigi` (Host: `localhost:5432`)  

---

## 1. POSTGRESQL & POSTGIS LIVE ENGINE SPECS

```text
PostgreSQL Version: PostgreSQL 17.11 on x86_64-windows, compiled by msvc-19.44.35229, 64-bit
PostGIS Version:    POSTGIS="3.6.2 3.6.2" [EXTENSION] PGSQL="170" 
                    GEOS="3.14.1dev-CAPI-1.20.4" PROJ="8.2.1" 
                    LIBXML="2.12.5" LIBJSON="0.12" LIBPROTOBUF="1.2.1" WAGYU="0.5.0"
```

---

## 2. INVENTARIS TABEL & JUMLAH BARIS AKTUAL

| Nama Tabel | Tipe Data Utama | Jumlah Baris Aktual | Integritas Kunci / Relasi | Status Migrasi |
| :--- | :--- | :---: | :--- | :---: |
| `canonical_grids` | Spasial (GEOMETRY Polygon 4326) | **8.129** | PK: `cell_id` (VARCHAR 32) | **RETAIN (ZERO TOUCH)** |
| `daily_predictions` | Prediksi Harian & Telemetri | **8.129** | PK: `(prediction_date, cell_id)` -> FK to `canonical_grids` | **RETAIN (ZERO TOUCH)** |
| `grid_monthly_predictions` | Arsip Spasio-Temporal Bulanan | **97.548** | PK: `(year_month, cell_id)` -> FK to `canonical_grids` | **RETAIN (ZERO TOUCH)** |
| `prigi_port_landings` | Statistik Pendaratan Empiris | **132** | PK: `year_month` (VARCHAR 7) | **RETAIN (ZERO TOUCH)** |
| `prigi_fleet_by_gt` | Profil Armada Prigi per GT | **3** | PK: `(year, gt_class)` | **RETAIN (ZERO TOUCH)** |

---

## 3. AUDIT KUALITAS & GEOMETRI SPASIAL (CANONICAL GRIDS)

- **Total Poligon:** 8.129 sel.
- **Validitas Geometri:** 100% valid (`COUNT(*) FILTER (WHERE ST_IsValid(geom)) = 8129`).
- **Spatial Reference System (SRID):** `4326` (WGS 84).
- **Bounding Box Spasial:**
  - Bujur (Longitude): `102.5400° E` hingga `126.4400° E`
  - Lintang (Latitude): `-13.9600° S` hingga `-6.8600° S`
- **Resolusi Grid Terukur:**
  - Delta Lon: `0.1000°` (~11.13 km pada ekuator)
  - Delta Lat: `0.1000°` (~11.08 km)
- **Distribusi Sektoral Wilayah:**
  - `BALI_NUSA_TENGGARA`: 4.561 sel (56,1%)
  - `JABAR_JATENG`: 2.121 sel (26,1%)
  - `JATIM`: 1.447 sel (17,8%)
- **Indeks Spasial:**
  - `idx_canonical_grids_geom`: GiST Index aktif untuk optimasi kueri `ST_MakeEnvelope`, `ST_Intersects`, `&&`.
  - `idx_canonical_grids_dist`: B-Tree index pada `dist_port_nm`.
  - `idx_canonical_grids_sector`: B-Tree index pada `sector`.

---

## 4. AUDIT ARSIP DATA PREDIKSI

### 4.1. Data Prediksi Harian (`daily_predictions`)
- **Tanggal Tersedia:** `2026-11-03` (8.129 baris sinkron penuh).
- **Distribusi Hotspot Harian:** 6 sel aktif (`is_hotspot = true`, kriteria HSI $\ge$ 0,70).
- **Distribusi Zona DOM Harian:**
  - `Z1_PFG_INSHORE`: 2 sel
  - `Z2_PFG_OFFSHORE`: 27 sel
  - `Z3_FEEDING_CORE`: 200 sel
  - `Z4_NURSERY_GROUND`: 3 sel
  - `GENERAL_OCEANIC`: 7.897 sel
- **Distribusi Bycatch Traffic Light:**
  - Hijau (GREEN): 7.013 sel (86,3%)
  - Kuning (YELLOW): 1.116 sel (13,7%)
  - Merah (RED): 0 sel (0,0%)

### 4.2. Data Prediksi Bulanan (`grid_monthly_predictions`)
- **Tahun Tersedia:** Tahun **2025** (12 bulan penuh: `2025_01` s.d. `2025_12`).
- **Volume Data:** 12 bulan $\times$ 8.129 sel = **97.548 baris**.
- **Kepatuhan Transparansi:** Tidak ada klaim palsu bahwa data spasial mencakup 11 tahun penuh; 11 tahun hanya berlaku untuk arsip pendaratan pelabuhan (`prigi_port_landings`).

### 4.3. Data Pendaratan Pelabuhan Prigi (`prigi_port_landings`)
- **Rentang Waktu:** Januari 2015 s.d. Desember 2025 (**132 bulan penuh**).
- **Total Produksi Pelagis:** 18.405,33 ton.
- **Rata-rata Nominal CPUE Proxy:** 110,06 kg/trip.

### 4.4. Profil Armada (`prigi_fleet_by_gt`)
- **Tahun Registrasi:** 2026.
- `< 10 GT`: 850 unit (67,00%, jangkauan $\le$ 12 NM).
- `10 - 20 GT`: 165 unit (13,00%, jangkauan $\le$ 30 NM).
- `20 - 30 GT`: 254 unit (20,00%, jangkauan $\le$ 60 NM).
- **Total Armada:** 1.269 unit kapal nelayan teregistrasi di PPN Prigi.

---

## 5. STRATEGI KONEKSI LARAVEL KE POSTGIS

Laravel 13 akan mengelola database yang sudah ada dengan protokol:

1. **Aturan Akses Read-Only Baseline:**
   Selama Phase 1 hingga Phase 7, Laravel terhubung menggunakan role database yang memiliki hak `SELECT` murni, mencegah modifikasi struktural yang tidak disengaja.
2. **Larangan Perintah Destruktif:**
   Perintah `php artisan migrate:fresh`, `db:wipe`, `DROP TABLE`, atau `TRUNCATE` dilarang dijalankan pada database operasional.
3. **Eloquent Models & Raw PostGIS Expressions:**
   - Model `CanonicalGrid` memanfaatkan `ST_AsGeoJSON(geom)` untuk rendering spasial.
   - Kueri spasial bounding-box menggunakan binding parameterized:
     `whereRaw("geom && ST_MakeEnvelope(?, ?, ?, ?, 4326)", [$minLon, $minLat, $maxLon, $maxLat])`.
4. **Zero Migration Loss:**
   Skema tabel dan seluruh 8.129 geometri kanonik tidak akan di-recreate; tabel yang ada langsung dimapping ke model Eloquent.

