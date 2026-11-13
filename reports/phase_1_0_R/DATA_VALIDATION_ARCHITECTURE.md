# DATA VALIDATION ARCHITECTURE & DATA INTEGRITY SPECIFICATION
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** ARCH-VAL-01  
**Phase:** 1.0-R — Remediation R-07  
**Date:** 2026-11-02  

---

## 1. ARSITEKTUR INTEGRITAS DATA DUA JALUR (TWO-TRACK VALIDATION ARCHITECTURE)

Sesuai amanat audit Phase 1.0-R, arsitektur validasi sistem SEACAST PRIGI dibagi menjadi **DUA JALUR YANG TERISOLASI SECARA TOTAL**:

```
+-----------------------------------------------------------------------------------+
|                        SEACAST PRIGI SYSTEM ARCHITECTURE                          |
+-----------------------------------------------------------------------------------+
                                          |
        +---------------------------------+---------------------------------+
        |                                                                   |
        v                                                                   v
+------------------------------------+             +------------------------------------+
|  TRACK 1: OCEANOGRAPHIC QA ENGINE  |             | TRACK 2: FISHERY OCCURRENCE ENGINE |
+------------------------------------+             +------------------------------------+
| - Spatial consistency check        |             | - Independent GPS catch logbooks   |
| - Temporal calendar (132 months)   |             | - On-board observer records        |
| - 9-State missing-data taxonomy    |             | - Taxonomic verification           |
| - Per-variable full provenance     |             | - Spatial uncertainty <= 10 km     |
| - Physical range & outlier masking |             | - Verified fishing effort (CPUE)   |
+------------------------------------+             +------------------------------------+
        |                                                                   |
        v                                                                   v
[STATUS: SPECIFIED & VALIDATED]                    [STATUS: SPECIES_MODEL_NOT_READY]
(Ready for pilot specification)                    (Port landings limited to macro trend)
```

---

## 2. TAKSONOMI 9-STATUS DATA HILANG & ANOMALI (R7.1)

Setiap nilai dalam matriks data spasial $240 \times 72 \times 132$ bulan wajib memiliki status data eksplisit yang mencegah distorsi matematis:

| Kode Status | Kategori | Penjelasan Operasional | Perlakuan Algoritmik |
|---|---|---|---|
| `OBSERVED_VALID` | Observasi | Piksel lolos seluruh penyaringan QA sensor dan rentang fisik. | Digunakan langsung dalam pemodelan. |
| `OBSERVED_PARTIAL` | Observasi | Nilai komposit dari sebagian piksel valid dalam sel kisi ($0,30 \le f_{{\text{{valid}}}} < 1,0$). | Digunakan dengan bobot fraksi valid. |
| `MISSING_NO_IMAGE` | Data Hilang | Tidak ada lintasan satelit (*swath*) atau granule pada periode tersebut. | Bernilai `null`; dilarang disamakan dengan 0. |
| `MISSING_ALL_MASKED`| Data Hilang | Citra tersedia tetapi 100% piksel tertutup awan, es, atau terhalang kualitas optik. | Bernilai `null`; memicu pengecekan fallback. |
| `INVALID_QA` | Anomali Sensor | Gagal uji flag kualitas sensor (misal QA bit MODIS `bad_pixel`). | Dikeluarkan dari komposit bulanan. |
| `INVALID_PHYSICAL` | Anomali Fisik | Melanggar batas batas fisik bumi (misal angin CFSv2 $> 30\text{{ m/s}}$ atau SST $< 10^\circ\text{{C}}$). | Dimask menjadi NaN; dicatat dalam log anomali. |
| `INTERPOLATED` | Turunan Numerik | Nilai hasil estimasi interpolasi spasial/temporal. | Ditandai eksplisit; tidak boleh dianggap observasi. |
| `CLIMATOLOGY` | Referensi | Nilai rata-rata historis jangka panjang (2015–2024). | Hanya untuk baseline anomali / EKE. |
| `SENSOR_FALLBACK` | Multi-Sensor | Nilai bersumber dari sensor pengganti yang tervalidasi (GCOM-C untuk MODIS; CMEMS untuk HYCOM). | Dicatat sensor asal dan scaling yang diterapkan. |

---

## 3. PROVENANS LENGKAP TINGKAT VARIABEL (PER-VARIABLE PROVENANCE)

Setiap sel grid menyimpan blok metadata yang merekam sejarah pengolahan:
1. `source_dataset` & `source_product`: ID repositori resmi (GEE asset path atau CMEMS product identifier).
2. `source_version`: Versi produk (misal MODIS Collection 6.1, OISST v2.1).
3. `native_spatial_resolution_deg`: Resolusi bawaan sensor sebelum disampel ulang.
4. `sensor_identifier`: Kode satelit/instrumen (misal `TERRA_AQUA_MODIS`, `SGLI_GCOMC`, `HYCOM_GLBy0.08`).
5. `processing_method`: Nama algoritma (misal `monthly_log10_mean_composite`).
6. `scaling_factor`: Pengali konversi digital number ke nilai fisik (misal $0,0016$ pada GCOM-C).
7. `qa_rule_applied`: ID aturan pembersihan anomali (misal `RULE_WND_01_PHYSICAL_BOUNDS`).
8. `processing_timestamp_utc`: Waktu komputasi ISO-8601.

---

## 4. PENANGGULANGAN KALENDER TEMPORAL 132 BULAN (TEMPORAL INTEGRITY)

Skema menjamin tidak ada baris kalender yang dibuang (*zero row omission*):
- Matriks kalender terdiri dari tepat $132\text{{ bulan}}$ (Januari 2015 s.d. Desember 2025).
- Jika pada bulan tertentu suatu sensor mengalami kekosongan total (misal MODIS pada Juli 2021), record `grid_id + 2021 + 7` tetap dibuat dengan atribut `missing_state: SENSOR_FALLBACK` dan nilai diambil dari GCOM-C.
- Jika kedua sensor tidak tersedia, record tetap disimpan dengan nilai `null` dan `missing_state: MISSING_NO_IMAGE`.
