# SEACAST PRIGI V3 — REKONSILIASI BASELINE ILMIAH (PHASE 1.1)
## OCEANOGRAPHIC SCIENTIFIC INTEGRITY, MODELS & SPATIAL BASELINE

**Tanggal Rekonsiliasi:** 4 November 2026  
**Objek Rekonsiliasi:** Spasial WPPNRI 573, Model SDM, Formula HSI, Profil Armada, dan Telemetri  
**Status Evaluasi:** **PASS (100% RECONCILED & VERIFIED)**  

---

## 1. REKONSILIASI CAKUPAN SPASIAL & GRID KANONIKAL

Pemeriksaan spasial dijalankan langsung terhadap PostgreSQL 17 / PostGIS 3.6 database `seacast_prigi`:

```sql
SELECT 
    ST_Extent(geom) as actual_extent,
    COUNT(*) as total_cells,
    COUNT(*) FILTER (WHERE ST_IsValid(geom)) as valid_cells,
    COUNT(*) FILTER (WHERE NOT ST_IsValid(geom)) as invalid_cells,
    ST_SRID(geom) as srid,
    GeometryType(geom) as geom_type
FROM canonical_grids
GROUP BY ST_SRID(geom), GeometryType(geom);
```

| Parameter Spasial | Nilai Target Spesifikasi | Nilai Database Aktual Terverifikasi | Status |
| :--- | :---: | :---: | :---: |
| **Total Sel Kanonikal** | 8.129 Poligon | **8.129 Poligon** | **VERIFIED** |
| **Geometri Valid** | 100% (8.129) | **8.129 (100%)** | **VERIFIED** |
| **Geometri Tidak Valid** | 0 | **0** | **VERIFIED** |
| **Sistem Referensi Koordinat** | EPSG:4326 (WGS 84) | **4326** | **VERIFIED** |
| **Tipe Geometri** | POLYGON | **POLYGON** | **VERIFIED** |
| **Resolusi Grid Spasial** | 0.10° (~11.1 km) | **0.10°** | **VERIFIED** |
| **Extent Aktual (`ST_Extent`)** | `BOX(102.5 -14,126.5 -6.8)` | `BOX(102.5 -14,126.5 -6.8)` | **VERIFIED** |
| **Bounding Box Bujur (Lon)** | 102.50°E – 126.50°E | 102.50°E – 126.50°E | **VERIFIED** |
| **Bounding Box Lintang (Lat)**| -14.00°S – -6.80°S | -14.00°S – -6.80°S | **VERIFIED** |
| **Indeks Spasial GiST** | `idx_canonical_grids_geom` | `idx_canonical_grids_geom` (Aktif) | **VERIFIED** |

---

## 2. REKONSILIASI MODEL SPESIES & ENSEMBLE HSI

### A. Algoritma Species Distribution Model (SDM)
Platform SEACAST PRIGI V3 secara baku menggunakan perpaduan tiga algoritma model relung ekologis:
1. **MaxEnt** (Maximum Entropy Model)
2. **Random Forest** (Machine Learning Ensemble)
3. **GLM** (Generalized Linear Model)

> [!IMPORTANT]
> **Keputusan Rekonsiliasi Model GLM vs GAM:**
> Seluruh kode sumber, pengujian, dan dokumentasi resmi SEACAST PRIGI menggunakan **GLM** (*Generalized Linear Model*). Tidak ada referensi *Generalized Additive Model* (GAM) dalam pipeline produksi ini. Seluruh penyebutan GAM di dokumen lama dikoreksi secara definitif menjadi GLM.

### B. Formula Habitat Suitability Index (HSI)
Formula ensemble pembobotan HSI per sel kanonikal:
$$\text{HSI}_{\text{spesies}} = w_{\text{maxent}} \times P_{\text{maxent}} + w_{\text{rf}} \times P_{\text{rf}} + w_{\text{glm}} \times P_{\text{glm}}$$

### C. Alasan Ilmiah Bobot MaxEnt Albakor = 0,000
Berdasarkan audit evaluasi model (`model_evaluations_summary.json`), bobot ensemble untuk tiga spesies target adalah sebagai berikut:

| Spesies Target | Bobot MaxEnt | Bobot Random Forest | Bobot GLM | Ensemble AUC | Ensemble TSS | Status Bobot |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **Cakalang** (*Katsuwonus pelamis*) | 0,342 | 0,381 | 0,277 | 0,892 | 0,684 | Valid Multimodel |
| **Madidihang** (*Thunnus albacares*) | 0,315 | 0,395 | 0,290 | 0,876 | 0,651 | Valid Multimodel |
| **Albakor** (*Thunnus alalunga*) | **0,000** | **0,517** | **0,483** | **0,841** | **0,592** | **MaxEnt Discarded** |

**Justifikasi Saintifik Penolakan MaxEnt Albakor:**
Evaluasi proxy independen MaxEnt untuk Albakor menghasilkan nilai AUC sebesar **0,507** ($\le 0,510$), yang secara statistik setara dengan tebakan acak (*random guessing*). Untuk menjaga integritas prediksi, bobot MaxEnt Albakor sengaja di-nolkan ($w = 0,000$) dan proporsi dialokasikan secara proporsional kepada Random Forest (0,517) dan GLM (0,483).

---

## 3. REKONSILIASI CATATAN DATA ILMIAH & ARMADA

| Entitas Data | Nilai Database Aktual | Interpretasi Saintifik yang Benar | Koreksi / Catatan Kritis |
| :--- | :---: | :--- | :--- |
| **daily_predictions** | 8.129 baris | Prediksi harian snapshot 2026-11-03 | 1 baris per sel kanonikal WPPNRI 573 |
| **grid_monthly_predictions** | 97.548 baris | Prediksi bulanan spasial (8.129 sel $\times$ 12 bulan) | Mencakup 12 bulan kalender tahun 2025 |
| **prigi_port_landings** | 132 baris | Rekam jejak pendaratan bulanan PPN Prigi | Rentang 11 tahun: 2015 s.d. 2025 ($11 \times 12 = 132$) |
| **prigi_fleet_by_gt** | 3 baris | 3 kelas tonase kotor (GT) armada | **Mewakili 1.269 kapal terdaftar**, BUKAN 3 kapal! |

### Rincian Profil Armada Perikanan PPN Prigi:
- Kelas `< 10 GT` (Perahu Sekoci / Motor Tempel): **850 kapal** (67,0%) — Operasional zona inshore $\le 12$ NM (Z1).
- Kelas `10–30 GT` (Kapal Pancing Ulur / Rawai Kecil): **165 kapal** (13,0%) — Operasional zona feeding core $\le 30$ NM (Z3).
- Kelas `> 30 GT` (Kapal Purse Seine / Rawai Tuna): **254 kapal** (20,0%) — Operasional zona offshore hingga 60 NM (Z2).
- **Total Armada Terdaftar:** **1.269 kapal**.

---

## 4. REKONSILIASI JADWAL INFERENSI & BASEMAP

### A. Jadwal Orkestrasi Inferensi Harian
- **Jadwal Legacy Lama:** 04.00 WIB (Berjalan sebelum data satelit level 3/4 tersedia).
- **Jadwal Target Terkalibrasi:** **02.00 UTC (09.00 WIB)** — Waktu optimal setelah perilisan produk harian NOAA CoralTemp OISST dan Sentinel-3 OLCI Chla.
- **Status Saat Ini (Phase 1 & 1.1):** **INAKTIF (SCHEDULED FOR PHASE 2/5)**. Scheduler belum menjalankan script Python harian secara otomatis sampai seluruh pipeline di-hardening pada Phase 5.

### B. Standar Basemap Geospasial
- Standar visualisasi ditetapkan menggunakan **CartoDB Dark Matter** (`https://basemaps.cartocdn.com/gl/dark-matter-gl-style/style.json`).
- Menjamin kontras tinggi terhadap palet zona DOM (Z1–Z5), gradien HSI cyan-to-teal, dan sesuai dengan tema gelap pusat komando oseanografi.
- Koneksi endpoint tile telah diuji dan merespon `200 OK`.

---

## 5. PENETAPAN LABEL & DISCLAIMER FORMAL

Seluruh antarmuka dan laporan wajib mematuhi konvensi label berikut:
1. **Metrik CPUE**: Wajib dilabeli secara eksplisit sebagai **Proksi CPUE Tahunan (Nominal)**. Dilarang menyebutnya sebagai CPUE terstandarisasi karena belum memperhitungkan variasi soaking time atau hook rate per trip.
2. **Data Cadangan Gelombang**: Wajib dilabeli sebagai **Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)**. Dilarang mengklaim sebagai data BMKG live streaming sebelum koneksi API resmi terhubung.
3. **Time Machine Spasial**: Mencakup 12 bulan spasial tahun 2025 (97.548 baris), sedangkan data pendaratan pelabuhan mencakup tren historis 2015–2025 (132 baris).

**Status Akhir Pekerjaan D:** **PASS (VERIFIED & RECONCILED)**

