"""
SEACAST PRIGI - Phase 1.0-R Remediation
R-07: Data Validation Architecture & Revised Schema
"""

import os
import json
import csv
from datetime import datetime

WORKSPACE = r"C:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis"
OUT_DIR = os.path.join(WORKSPACE, "reports", "phase_1_0_R")
os.makedirs(OUT_DIR, exist_ok=True)

# 1. FINAL_DATA_QA_SCHEMA.json
schema = {
    "$schema": "https://json-schema.org/draft/2020-12/schema",
    "title": "SEACAST_PRIGI_GRID_DATA_QA_SCHEMA",
    "description": "Multi-sensor, multi-resolution spatial grid data schema with explicit missing states and per-variable provenance for SEACAST PRIGI 132-month study period (2015-2025).",
    "type": "object",
    "primary_key": ["grid_id", "year", "month"],
    "required": [
        "grid_id",
        "grid_resolution_deg",
        "year",
        "month",
        "time_start_iso",
        "time_end_iso",
        "cell_lon_center",
        "cell_lat_center",
        "cell_area_km2",
        "aoi_intersection_area_km2",
        "aoi_intersection_fraction",
        "ocean_fraction",
        "land_fraction",
        "cell_classification",
        "variables"
    ],
    "properties": {
        "grid_id": {
            "type": "string",
            "pattern": "^WPP573_(025|R010|PRIGI005)_C[0-9]{3}_R[0-9]{3}$"
        },
        "grid_resolution_deg": {
            "type": "number",
            "enum": [0.25, 0.10, 0.05]
        },
        "year": {
            "type": "integer",
            "minimum": 2015,
            "maximum": 2025
        },
        "month": {
            "type": "integer",
            "minimum": 1,
            "maximum": 12
        },
        "time_start_iso": {"type": "string", "format": "date"},
        "time_end_iso": {"type": "string", "format": "date"},
        "cell_lon_center": {"type": "number"},
        "cell_lat_center": {"type": "number"},
        "cell_area_km2": {"type": "number"},
        "aoi_intersection_area_km2": {"type": "number"},
        "aoi_intersection_fraction": {"type": "number", "minimum": 0.0, "maximum": 1.0},
        "ocean_fraction": {"type": "number", "minimum": 0.0, "maximum": 1.0},
        "land_fraction": {"type": "number", "minimum": 0.0, "maximum": 1.0},
        "cell_classification": {
            "type": "string",
            "enum": [
                "PURE_OCEAN",
                "COASTAL_BOUNDARY",
                "OUTER_EEZ_BOUNDARY",
                "LAND_EXCLUDED",
                "OUTSIDE_AOI"
            ]
        },
        "variables": {
            "type": "object",
            "description": "Per-variable value and full provenance metadata block.",
            "properties": {
                "sst": {"$ref": "#/$defs/variable_provenance_block"},
                "chlorophyll_a": {"$ref": "#/$defs/variable_provenance_block"},
                "wind_speed": {"$ref": "#/$defs/variable_provenance_block"},
                "wind_stress": {"$ref": "#/$defs/variable_provenance_block"},
                "ekman_pumping": {"$ref": "#/$defs/variable_provenance_block"},
                "current_velocity": {"$ref": "#/$defs/variable_provenance_block"},
                "eke": {"$ref": "#/$defs/variable_provenance_block"},
                "bathymetry": {"$ref": "#/$defs/variable_provenance_block"}
            }
        }
    },
    "$defs": {
        "variable_provenance_block": {
            "type": "object",
            "required": [
                "value",
                "unit",
                "missing_state",
                "source_dataset",
                "source_product",
                "source_version",
                "native_spatial_resolution_deg",
                "native_temporal_resolution",
                "sensor_identifier",
                "processing_method",
                "scaling_factor",
                "qa_rule_applied",
                "valid_pixel_count",
                "valid_pixel_fraction",
                "interpolation_flag",
                "processing_timestamp_utc"
            ],
            "properties": {
                "value": {
                    "type": ["number", "null"],
                    "description": "Calculated value. Strictly null if missing_state is not OBSERVED_VALID or OBSERVED_PARTIAL or INTERPOLATED."
                },
                "unit": {"type": "string"},
                "missing_state": {
                    "type": "string",
                    "enum": [
                        "OBSERVED_VALID",
                        "OBSERVED_PARTIAL",
                        "MISSING_NO_IMAGE",
                        "MISSING_ALL_MASKED",
                        "INVALID_QA",
                        "INVALID_PHYSICAL",
                        "INTERPOLATED",
                        "CLIMATOLOGY",
                        "SENSOR_FALLBACK"
                    ]
                },
                "source_dataset": {"type": "string"},
                "source_product": {"type": "string"},
                "source_version": {"type": "string"},
                "native_spatial_resolution_deg": {"type": "number"},
                "native_temporal_resolution": {"type": "string"},
                "sensor_identifier": {"type": "string"},
                "processing_method": {"type": "string"},
                "scaling_factor": {"type": "number"},
                "qa_rule_applied": {"type": "string"},
                "valid_pixel_count": {"type": "integer"},
                "valid_pixel_fraction": {"type": "number", "minimum": 0.0, "maximum": 1.0},
                "interpolation_flag": {"type": "boolean"},
                "processing_timestamp_utc": {"type": "string", "format": "date-time"}
            }
        }
    }
}

with open(os.path.join(OUT_DIR, "FINAL_DATA_QA_SCHEMA.json"), "w", encoding="utf-8") as f:
    json.dump(schema, f, indent=2)

# 2. SPECIES_OCCURRENCE_READINESS.csv
species_readiness = [
    {
        "species_common": "Cakalang",
        "scientific_name": "Katsuwonus pelamis",
        "taxonomic_id_itis": "166240",
        "target_fishery": "PPN Prigi / WPPNRI 573",
        "available_landing_data": "Monthly aggregated catch weight (tons) at port",
        "spatial_coordinates_available": "NO (0 catch points with GPS in workspace)",
        "temporal_dates_available": "Monthly only (no trip-level haul timestamps)",
        "sampling_method": "Commercial port unloading record (aggregat non-spasial)",
        "spatial_uncertainty_km": "> 500 km (catch location unknown within WPP 573)",
        "habitat_model_readiness": "SPECIES_MODEL_NOT_READY",
        "auditor_directive": "STRICTLY FORBIDDEN to train species distribution or habitat suitability models using port landing weight as spatial occurrence label."
    },
    {
        "species_common": "Tuna Sirip Kuning",
        "scientific_name": "Thunnus albacares",
        "taxonomic_id_itis": "166277",
        "target_fishery": "PPN Prigi / WPPNRI 573",
        "available_landing_data": "Monthly aggregated catch weight (tons) at port",
        "spatial_coordinates_available": "NO (0 catch points with GPS in workspace)",
        "temporal_dates_available": "Monthly only (no trip-level haul timestamps)",
        "sampling_method": "Commercial port unloading record (aggregat non-spasial)",
        "spatial_uncertainty_km": "> 500 km (catch location unknown within WPP 573)",
        "habitat_model_readiness": "SPECIES_MODEL_NOT_READY",
        "auditor_directive": "STRICTLY FORBIDDEN to train species distribution or habitat suitability models using port landing weight as spatial occurrence label."
    },
    {
        "species_common": "Tongkol Krai",
        "scientific_name": "Auxis thazard",
        "taxonomic_id_itis": "172420",
        "target_fishery": "PPN Prigi / WPPNRI 573",
        "available_landing_data": "Monthly aggregated catch weight (tons) at port",
        "spatial_coordinates_available": "NO (0 catch points with GPS in workspace)",
        "temporal_dates_available": "Monthly only (no trip-level haul timestamps)",
        "sampling_method": "Commercial port unloading record (aggregat non-spasial)",
        "spatial_uncertainty_km": "> 500 km (catch location unknown within WPP 573)",
        "habitat_model_readiness": "RULE_BASED_SCREENING_ONLY",
        "auditor_directive": "STRICTLY FORBIDDEN to train species distribution or habitat suitability models using port landing weight as spatial occurrence label."
    }
]

with open(os.path.join(OUT_DIR, "SPECIES_OCCURRENCE_READINESS.csv"), "w", newline="", encoding="utf-8") as f:
    writer = csv.DictWriter(f, fieldnames=list(species_readiness[0].keys()))
    writer.writeheader()
    writer.writerows(species_readiness)

# 3. PPN_LANDING_DATA_USAGE_POLICY.md
policy_md = """# PPN PRIGI LANDING DATA USAGE POLICY & SCIENTIFIC BOUNDARIES
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** POL-LAND-01  
**Phase:** 1.0-R — Remediation R-07  
**Date:** 2026-11-02  

---

## 1. NATURE & PROVENANCE OF PPN PRIGI LANDING DATA

Data pendaratan perikanan PPN Prigi berasal dari pencatatan statistik pelabuhan perikanan harian/bulanan yang mencatat:
- Volume pendaratan total ikan per kelompok/spesies (kg atau ton).
- Jumlah trip kapal yang mendarat berdasarkan jenis alat tangkap (*Purse Seine*, *Gill Net*, dsb.).
- Nilai produksi perikanan (Rupiah).

> [!CAUTION]
> **Batasan Spasial Kritis:**
> Data pendaratan pelabuhan dicatat di dermaga pendaratan (*port dock*). Data ini **TIDAK MEMILIKI ATRIBUT KOORDINAT LOKASI PENANGKAPAN (FISHING GROUND)**, lintang/bujur setting alat tangkap, atau rekaman *Vessel Monitoring System* (VMS) berbasis koordinat GPS per penarikan jaring.

---

## 2. PENGGUNAAN YANG DIIZINKAN (PERMITTED USE CASES)

Data pendaratan bulanan PPN Prigi **SAH SECARA ILMIAH DAN DIIZINKAN** untuk:
1. **Analisis Tren Temporal Regional:**
    - Menggambarkan fluktuasi musiman volume pendaratan Cakalang, Tuna Sirip Kuning, dan Tongkol Krai (siklus musiman barat vs musiman timur).
   - Analisis tren produksi multi-tahun (2015–2025).
2. **Karakterisasi Komposisi Spesies:**
   - Menghitung persentase kontribusi masing-masing spesies pelagis terhadap total hasil tangkapan tahunan.
3. **Korelasi Makro Temporal Oseanografi:**
   - Melakukan analisis korelasi silang temporal (time-series cross-correlation) antara indeks oseanografi rata-rata regional WPPNRI 573 (misalnya IOD/DMI, ENSO/ONI, rata-rata upwelling index) dengan volume pendaratan bulanan di tingkat pelabuhan.
4. **Konteks Sosial-Ekonomi & Kebijakan:**
   - Memberikan gambaran dampak fluktuasi iklim terhadap ketahanan pangan dan ekonomi nelayan Prigi.

---

## 3. PENGGUNAAN YANG DILARANG KERAS (PROHIBITED USES)

Sesuai kaidah integritas riset geospasial dan ekologi spasial perikanan:

1. **DILARANG MENGGUNAKAN SEBAGAI LABEL SPASIAL PRESENSI SPESIES:**
   - Dilarang merekayasa titik koordinat acak (*random pseudo-coordinates*) di sekitar Prigi atau di laut selatan Jawa lalu memberinya label keberadaan ikan berdasarkan data pendaratan pelabuhan.
2. **DILARANG MELATIH MODEL DISTRIBUSI SPESIES (SDM / HABITAT SUITABILITY MODEL):**
   - Melatih algoritma *Maximum Entropy* (MaxEnt), *Random Forest*, atau *Gradient Boosting* untuk memprediksi keberadaan ikan (*species occurrence*) menggunakan data pendaratan pelabuhan tanpa koordinat riil adalah **TIDAK SAH SECARA METODOLOGI (*SCIENTIFICALLY INVALID*)**.
3. **DILARANG MENGKLAIM ZONA POTENSIAL PENANGKAPAN IKAN (ZPPI) SEBAGAI ZONA AKTUAL:**
   - Zona oseanografi potensial (berdasarkan pertemuan upwelling, thermal front, dan klorofil-a) hanya boleh dinamai **"Zona Oseanografi Potensial"** (*Oceanographic Potential Zones*), BUKAN *"Fishing Ground Aktual Nelayan"*.

---

## 4. PERSYARATAN MINIMUM UNTUK MEMBUKA MODEL HABITAT SPESIFIK SPESIES

Model kesesuaian habitat spesifik spesies (*Species-Specific Habitat Suitability Model*) hanya dapat diaktifkan jika data kejadian independen memenuhi kriteria:
1. Memiliki koordinat GPS ($x, y$) penarikan alat tangkap dengan ketidakpastian spasial $< 10\text{ km}$.
2. Memiliki tanggal dan jam penangkapan ($t$) yang sinkron dengan data satelit harian/mingguan.
3. Identifikasi taksonomi spesies terverifikasi di atas kapal (*on-board observer / scientific logbook*).
4. Catatan upaya penangkapan (*Catch per Unit Effort / CPUE*) yang terstandardisasi.
"""

with open(os.path.join(OUT_DIR, "PPN_LANDING_DATA_USAGE_POLICY.md"), "w", encoding="utf-8") as f:
    f.write(policy_md)

# 4. DATA_VALIDATION_ARCHITECTURE.md
arch_md = """# DATA VALIDATION ARCHITECTURE & DATA INTEGRITY SPECIFICATION
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

Setiap nilai dalam matriks data spasial $240 \\times 72 \\times 132$ bulan wajib memiliki status data eksplisit yang mencegah distorsi matematis:

| Kode Status | Kategori | Penjelasan Operasional | Perlakuan Algoritmik |
|---|---|---|---|
| `OBSERVED_VALID` | Observasi | Piksel lolos seluruh penyaringan QA sensor dan rentang fisik. | Digunakan langsung dalam pemodelan. |
| `OBSERVED_PARTIAL` | Observasi | Nilai komposit dari sebagian piksel valid dalam sel kisi ($0,30 \\le f_{{\\text{{valid}}}} < 1,0$). | Digunakan dengan bobot fraksi valid. |
| `MISSING_NO_IMAGE` | Data Hilang | Tidak ada lintasan satelit (*swath*) atau granule pada periode tersebut. | Bernilai `null`; dilarang disamakan dengan 0. |
| `MISSING_ALL_MASKED`| Data Hilang | Citra tersedia tetapi 100% piksel tertutup awan, es, atau terhalang kualitas optik. | Bernilai `null`; memicu pengecekan fallback. |
| `INVALID_QA` | Anomali Sensor | Gagal uji flag kualitas sensor (misal QA bit MODIS `bad_pixel`). | Dikeluarkan dari komposit bulanan. |
| `INVALID_PHYSICAL` | Anomali Fisik | Melanggar batas batas fisik bumi (misal angin CFSv2 $> 30\\text{{ m/s}}$ atau SST $< 10^\\circ\\text{{C}}$). | Dimask menjadi NaN; dicatat dalam log anomali. |
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
- Matriks kalender terdiri dari tepat $132\\text{{ bulan}}$ (Januari 2015 s.d. Desember 2025).
- Jika pada bulan tertentu suatu sensor mengalami kekosongan total (misal MODIS pada Juli 2021), record `grid_id + 2021 + 7` tetap dibuat dengan atribut `missing_state: SENSOR_FALLBACK` dan nilai diambil dari GCOM-C.
- Jika kedua sensor tidak tersedia, record tetap disimpan dengan nilai `null` dan `missing_state: MISSING_NO_IMAGE`.
"""

with open(os.path.join(OUT_DIR, "DATA_VALIDATION_ARCHITECTURE.md"), "w", encoding="utf-8") as f:
    f.write(arch_md)

print("[R-07] Successfully generated all Phase R-07 deliverables.")

