# SEACAST — Implementation Status

**Updated:** 14 November 2026
**Release posture:** `SCIENTIFIC REVIEW / CONTROLLED WEB INTEGRATION`
**Domain:** WPPNRI 573; Tuna Sirip Kuning, Cakalang, Tongkol Krai.

## Status matrix

| Area | Status | Kondisi aktual |
|---|---|---|
| Canonical grid | IMPLEMENTED & VERIFIED | 8.129 polygon valid, EPSG:4326, PostGIS/GiST |
| Spatial HSI/DOM output | IMPLEMENTED — PROXY | Tiga komoditas publik tersedia; HSI adalah kesesuaian relatif, bukan presence/biomassa |
| Temporal TCT calibration | IMPLEMENTED — PER COMMODITY GATE | YFT `ENABLED`; Cakalang dan Tongkol Krai `BASELINE_ONLY` |
| Occurrence audit full grid | IMPLEMENTED & VERIFIED | 31/31 match dalam 8 NM terhadap grid kanonikal |
| Occurrence audit operational ≤60 NM | IMPLEMENTED — PARTIAL | 3/31 match; status `PARTIAL_SPATIAL_VALIDATION` |
| MaxEnt production ensemble | NOT ENABLED | Memerlukan occurrence koordinat dan spatial-block CV; workflow dipisahkan |
| Trip Planner | IMPLEMENTED — SCENARIO OUTPUT | `cell_id`, sumber estimasi, evidence, fallback, GT/biaya/cuaca tersedia |
| Dual operational hubs | IMPLEMENTED | UI mengekspos PPN Prigi dan PPS Cilacap |
| PPN Palabuhanratu | LEGACY COMPATIBILITY ONLY | Tetap di service internal; tidak ditampilkan pada UI dual-hub |
| WebGIS/API/export | IMPLEMENTED — CONTRACT COMPATIBLE | Alias TCT publik, provenance, MVT properties, cache key v2 |
| Database provenance columns | DEFERRED | Metadata dirakit di service/API dari manifest ingest, model, dan `prediction_date`; belum ada migrasi `daily_predictions` |

## Kontrak keluaran

### 1. Spatial HSI/DOM

Sumber: grid kanonikal, fitur GEE/BMKG, EOG sebagai aktivitas kapal, dan model HSI yang sudah ada. Output dapat digunakan untuk menampilkan gradien kesesuaian, DOM, fishery/feeding/nursery potential proxy, serta evidence badge. Tanpa koordinat tangkapan yang memadai, label wajib tetap `PROXY_HSI` atau `PARTIAL_SPATIAL_VALIDATION`.

### 2. Trip Planner

Sumber: pelabuhan, `cell_id` dari peta, jarak/koordinat, HSI sel, upaya/GT/alat tangkap, dan data cuaca. Formula BBM, biaya, catch scenario, pemilihan kapal, dan safety check adalah dukungan keputusan. Nilai fallback diberi `target_source`, `estimate_source`, dan `data_source_mode`; tidak lagi dianggap sebagai observasi live diam-diam.

Koreksi temporal hanya boleh berlabel `TEMPORAL_CPUE_CALIBRATED` jika manifest menyediakan faktor koreksi numerik yang benar-benar diterapkan ke proyeksi tangkapan. Run saat ini belum menyediakan faktor tersebut, sehingga Trip Planner tetap menggunakan `FORMULA_SCENARIO`.

## Provenance tanpa migrasi database

`ScientificDataService` dan repository membaca:

- manifest ingest GEE/BMKG untuk mode sumber dan timestamp;
- manifest model/evaluasi untuk versi model;
- `prediction_date` untuk tanggal prediksi;
- hasil validator occurrence untuk evidence dan validation status.

Jika manifest tidak tersedia, payload menggunakan `unknown`/`UNVERIFIED`, bukan mengarang status live atau tervalidasi. Kolom tabel lama dan alias `hsi_madidihang`/`hsi_tongkol` dipertahankan.

## Kriteria sebelum rilis operasional

1. occurrence berkoordinat per komoditas mencakup zona operasi yang relevan;
2. spatial-block validation dan audit temporal independen tersedia;
3. model temporal mengalahkan baseline pada skala riil, bukan hanya R² log;
4. semua API dan UI tetap menampilkan evidence/source mode;
5. data 2025 tidak digunakan untuk tuning setelah evaluasi holdout.

Status saat ini belum memenuhi seluruh kriteria tersebut; karena itu `HOLD_FOR_SCIENTIFIC_REVIEW` adalah keputusan yang disengaja.
