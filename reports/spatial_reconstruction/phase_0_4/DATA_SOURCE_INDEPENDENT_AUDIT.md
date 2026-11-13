# DATA SOURCE INDEPENDENT AUDIT & SCIENTIFIC VERIFICATION REPORT
**Project:** SEACAST PRIGI — Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi, WPPNRI 573  
**Phase:** 0.4 — Independent Data Source Verification  
**GEE Execution Project:** `seca-513505`  
**GEE API Version:** `1.7.50`  
**Execution Timestamp (UTC):** `2026-11-02T07:08:28.671049+00:00`  
**Audit Status:** COMPLETE — 100% EMPIRICALLY VERIFIED IN GOOGLE EARTH ENGINE  

---

## 1. EXECUTIVE SUMMARY & AUDIT MANDATE

Berdasarkan mandat **Phase 0.4**, tim auditor teknis dan saintifik independen telah melakukan evaluasi langsung terhadap seluruh sumber data oseanografi dan atmosfer melalui Google Earth Engine API pada Google Cloud Project `seca-513505`.

Setiap dataset diuji secara komputasional terhadap geometri resmi WPPNRI 573 (`AOI_WPP573_SIMPLIFIED_0p01deg_FOR_GEE.geojson`), mencakup:
1. Keberadaan koleksi (existence & catalog ID resolution).
2. Rentang waktu aktual (`first` dan `last` image date).
3. Resolusi spasial nominal dan CRS native.
4. Kelengkapan temporal bulanan (132 bulan, Januari 2015 s.d. Desember 2025).
5. Validitas rentang nilai fisik (pemeriksaan anomali/outlier dan faktor skala).
6. Fraksi piksel valid spasial (penetrasi tutupan awan dan land-masking).

Audit independen ini menghasilkan **lima penemuan ilmiah kritis** yang mengoreksi asumsi-asumsi desain terdahulu.

---

## 2. LIMA TEMUAN AUDIT KRITIS (CRITICAL AUDIT FINDINGS)

### Finding 1: ERA5-Land HANYA Mencakup 1.9% dari AOI WPPNRI 573 (REJECTED FOR MARINE)
- **Kondisi:** Koleksi `ECMWF/ERA5_LAND/MONTHLY_AGGR` sebelumnya diusulkan untuk angin laut.
- **Hasil Audit Aktual:** Pengukuran fraksi piksel valid atas geometri WPPNRI 573 membuktikan bahwa nilai valid ERA5-Land hanya **1.8987%** (hanya pesisir pantai pulau Jawa, Bali, dan Nusa Tenggara). Sebanyak **98.1% wilayah perairan WPPNRI 573 adalah `NULL` (masked)**.
- **Penjelasan Fisik:** ERA5-Land adalah model reanalisis permukaan darat (*land-surface model*). Model ini memotong laut lepas.
- **Keputusan:** **REJECTED TOTAL** untuk variabel oseanografi WPPNRI 573. Angin laut wajib menggunakan `NOAA/CFSV2/FOR6H_HARMONIZED` atau `ECMWF/ERA5/HOURLY`.

### Finding 2: CFSV2 Memiliki Outlier Numerik Ekstrem (>450 m/s) Wajib di-Clamp
- **Kondisi:** Koleksi `NOAA/CFSV2/FOR6H_HARMONIZED` memiliki kelengkapan 100% (16.061 frame 6-jam selama 132 bulan, 0 bulan kosong).
- **Hasil Audit Aktual:** Pemeriksaan nilai ekstrim sepanjang 2015–2025 mendeteksi nilai maksimum absolut komponen angin mencapai:
  - $U_{max} = 465.26\text{ m/s}$ (~1.675 km/jam)
  - $V_{max} = 458.27\text{ m/s}$ (~1.650 km/jam)
  Sementara nilai minimum riil berada pada $-23.28\text{ m/s}$ dan $-29.85\text{ m/s}$.
- **Penjelasan Fisik:** Nilai ~460 m/s merupakan *fill value* / *model artifact* / divergensi numerik lokal pada beberapa frame raw CFSV2.
- **Keputusan:** CFSV2 **APPROVED WITH MANDATORY CLAMPING**. Pipeline reduksi wajib menerapkan filter fisik:
  $$\text{valid\_wind} = (u \ge -35.0) \land (u \le 35.0) \land (v \ge -35.0) \land (v \le 35.0)$$
  Piksel di luar ambang ini wajib digantikan dengan interpolasi temporal atau dibuang sebelum perhitungan *wind stress* $\tau$ dan *Ekman transport*.

### Finding 3: MODIS-Aqua Memiliki 3 Bulan Kosong di GEE & Penetrasi Awan Musim Hujan Rendah
- **Kondisi:** Koleksi `NASA/OCEANDATA/MODIS-Aqua/L3SMI` digunakan untuk klorofil-a dan SST optik.
- **Hasil Audit Aktual:**
  1. Terdapat **3 bulan kosong total (0 gambar)** dalam katalog publik GEE:
     - `2021-07` (0 gambar)
     - `2021-11` (0 gambar)
     - `2025-12` (0 gambar; ingest GEE terakhir 2025-11-30).
  2. Pada skala harian, tutupan awan tropis musim hujan (*wet monsoon*, Desember–Februari) menyebabkan kehilangan piksel hingga 95%:
     - 15 Jan 2020: valid fraction = **10.78%**
     - 15 Jan 2023: valid fraction = **4.64%**
  3. Namun pada skala komposit bulanan (*monthly mean composite*), fraksi piksel valid membaik hingga **78.3% – 96.9%** (Januari) dan **97.5% – 98.8%** (Juli).
- **Keputusan:** **APPROVED WITH MULTI-SENSOR FALLBACK & MONTHLY COMPOSITING**. Untuk bulan `2021-07` dan `2021-11`, gunakan data substitusi dari `JAXA/GCOM-C/L3/OCEAN/CHLA/V3` (tersedia di GEE) atau interpolasi temporal tertimbang.

### Finding 4: HYCOM Public GEE Berhenti di 05 September 2024 (Temporal Gap)
- **Kondisi:** Koleksi `HYCOM/sea_water_velocity` menyediakan arus laut 3D (0 s.d. 5000m).
- **Hasil Audit Aktual:**
  - Rentang data: 1992-10-02 s.d. 2024-09-05 (18.938 frame terverifikasi).
  - Terdapat kesenjangan temporal 15 bulan untuk akhir periode studi (`2024-10` s.d. `2025-12`) di katalog publik Google Earth Engine.
- **Keputusan:** 
  - Periode 2015-01 s.d. 2024-08 (116 bulan): Menggunakan HYCOM GEE secara penuh.
  - Periode 2024-09 s.d. 2025-12 (16 bulan): Gunakan Cop-marine Global Analysis Forecast (`cmems_mod_glo_phy_anfc_0.083deg_P1D-m`) via Python script / Drive ingestion atau klimatologi bulanan HYCOM (2015-2023) sebagai baseline.

### Finding 5: ETOPO1 Memiliki Konvensi Kedalaman Negatif Terverifikasi
- **Kondisi:** `NOAA/NGDC/ETOPO1` digunakan untuk batimetri dan gradient kemiringan (*slope*).
- **Hasil Audit Aktual:**
  - Puncak Gunung Semeru (112.922° BT, -8.108° LS): **+3.027 m**
  - Palung Jawa (110.500° BT, -10.600° LS): **-5.858 m**
  - Laut Terbuka Selatan (115.000° BT, -12.000° LS): **-5.223 m**
  - Teluk Prigi (111.730° BT, -8.300° LS): **+2 m / 0 m**
- **Keputusan:** **PASS (100% VERIFIED)**. Konvensi: Kedalaman laut bertanda negatif. Dalam analisis kedalaman pelagis, $\text{Depth} = -\text{bedrock}$. Slope dihitung dari $\nabla(\text{bedrock})$.

---

## 3. AUDIT MATRIX SUMMARY TABLE

| Indikator | Dataset Resmi GEE | Resolusi Spasial | Resolusi Temporal | Kelengkapan Temporal | Status Audit | Keputusan Desain |
|---|---|---|---|---|---|---|
| **SST & SST Anomaly** | `NOAA/CDR/OISST/V2_1` | 27.830 m (0,25°) | Harian (1981–2026) | 4.017 / 4.017 hari (132/132 bln) | **PASS** | Primary SST |
| **Klorofil-a** | `NASA/OCEANDATA/MODIS-Aqua/L3SMI` | 9.260 m (0,083°) | Harian (2002–2025) | 3.879 hari (129/132 bln) | **CONDITIONAL** | Primary Chl-a (GCOM-C fallback) |
| **Angin Permukaan (10m)** | `NOAA/CFSV2/FOR6H_HARMONIZED` | 22.264 m (0,20°) | 6-Jam (1979–now) | 16.061 frame (132/132 bln) | **CONDITIONAL** | Primary Wind (Clamp <= 35 m/s) |
| **Arus Laut (u, v)** | `HYCOM/sea_water_velocity` | 8.905 m (0,08°) | 3-Jam/Harian | 18.938 frame (116/132 bln) | **PARTIAL** | Primary Current (CMEMS for 2024+) |
| **Batimetri & Slope** | `NOAA/NGDC/ETOPO1` | 1.855 m (1 arc-min) | Statis | 1 Frame Global (100%) | **PASS** | Primary Topography |
| **Angin Alternatif** | `ECMWF/ERA5/HOURLY` | 27.830 m (0,25°) | Per Jam (1979–2026) | Tersedia di GEE (Probe: PASS) | **PASS** | Validated Secondary |
| **Klorofil Alternatif** | `JAXA/GCOM-C/L3/OCEAN/CHLA/V3` | 4.630 m (0,04°) | Harian/Bulanan (2018–2026) | Tersedia di GEE (Probe: PASS) | **PASS** | Validated Gap Filler |
| **Angin Ditolak** | `ECMWF/ERA5_LAND/MONTHLY_AGGR` | 11.132 m (0,10°) | Bulanan | Validitas Laut: **1.9%** | **FAIL** | **REJECTED (Land only)** |

---

## 4. SCIENTIFIC RECONCILIATION DIRECTIVE FOR PHASE 0.5–1.0

1. **WPPNRI 573 Grid Architecture:** Penentuan ukuran grid utama didasarkan pada *coarsest primary indicator* yaitu OISST (0,25°) dan CFSV2 (0,20°), diselaraskan dengan HYCOM (0,08°) dan MODIS (0,083°).
2. **Multi-Scale Nested Grids:**
   - Regional Macro-Scale (WPPNRI 573 Total): **0,10° x 0,10°** (~7.500 sel laut) atau **0,25° x 0,25°** (~1.200 sel laut).
   - Local High-Resolution Sub-AOI (PPN Prigi Fleet Domain): **0,05° x 0,05°** (2.050 sel).
3. **Data Quality Assurance Pipeline:**
   - SST: Interpolasi spatial-bilinear pada sel pesisir.
   - Chl-a: Monthly log-mean compositing dengan threshold awan + fallback GCOM-C untuk bulan 2021-07 dan 2021-11.
   - Wind: Clamping fisik $U, V \in [-35, +35]\text{ m/s}$, dilanjutkan perhitungan *wind stress* $\tau = \rho_a C_d |W| W$ dan *Ekman pumping velocity* $W_e = \frac{1}{\rho_w f} \nabla \times \tau$.
   - Current: HYCOM depth-averaged barotropic / surface velocity untuk 2015–2024; CMEMS/klimatologi untuk 2024–2025.
   - Bathymetry: Slope $\theta = \arctan(|\nabla z|)$ dalam derajat dan klasifikasi zona neritik vs oseanik.
