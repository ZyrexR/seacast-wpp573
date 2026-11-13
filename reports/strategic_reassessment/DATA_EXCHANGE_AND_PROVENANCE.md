# DATA EXCHANGE AND PROVENANCE ARCHITECTURE
## Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi — WPPNRI 573

- **Document ID:** `STRAT-REASSESS-05`
- **Data Flow Pipeline:** `GEE → Google Drive / Storage → Google Colab → Google Drive / Storage → Antigravity Local Workspace → WebGIS Dashboard`
- **Core Governance Rule:** **Immutable Baseline Preservation** — Seluruh hasil Phase 1–3 dan Phase 4.1.1 (`v1.1.1`) dipertahankan secara utuh dan tidak boleh ditimpa (*non-destructive versioning*). Semua output baru dari GEE dan Colab diberi namespace `v2.0`.

---

## 1. Struktur Direktori Terpadu (Local Workspace & Google Drive Mirror)

Struktur folder dirancang simetris antara Google Drive (`SEACAST_PRIGI_WPP573/`) dan workspace lokal (`c:\\Users\\ThinkPad\\.gemini\\antigravity\\scratch\\prigi-pelagis\\`):

```text
prigi-pelagis/ (dan Mirror di Google Drive: SEACAST_PRIGI_WPP573/)
├── backups/
│   ├── phase_4_1_pre_remediation/              # [FROZEN] Historical Phase 4.1 backup (48 files)
│   ├── phase_4_1_1_pre_patch/                  # [FROZEN] Phase 4.1.1 pre-patch snapshot (65 files)
│   └── phase_4_1_1_baseline_manifest.json      # [FROZEN] Canonical v1.1.1 SHA-256 Baseline Manifest
│
├── data/
│   ├── boundaries/                             # [FROZEN v1.1.1] Official BIG WPPNRI 573 & Sub-AOI
│   ├── master_prigi_all_inclusive.csv          # [FROZEN v1.1.1] Canonical 132-month 12-taxon landings
│   ├── prigi_fleet_by_gt_2015_2025.csv         # [FROZEN v1.1.1] Canonical Sheet 2 fleet GT table
│   ├── oceanographic/                          # [FROZEN v1.1.1] Phase 2-4.1.1 baseline grids & series
│   └── gee_v2/                                 # [NEW PHASE 4.2] Reproducible GEE Satellite Exports
│       ├── raw_rasters/                        # GeoTIFF (.tif) exports from GEE (EPSG:4326)
│       ├── monthly_132m/                       # 132-month GEE zonal statistics CSV/JSON (2015-2025)
│       ├── seasonal_scenarios/                 # 6 canonical scenarios x 2,050 cells CSV/JSON
│       ├── bathymetry_terrain/                 # ETOPO1/GEBCO depth, slope, -200m shelf-break
│       └── gee_export_manifest_v2.json         # GEE task IDs, collection IDs, QC & SHA-256 hashes
│
├── gee/                                        # [NEW PHASE 4.2] Modular Google Earth Engine Codebase
│   ├── js/                                     # GEE Code Editor JavaScript modules (00..05.js)
│   ├── py/                                     # Earth Engine Python API automation modules (m00..m05.py)
│   └── config/gee_pipeline_config.json         # AOI bounds, date ranges, catalog IDs, scale parameters
│
├── colab/                                      # [NEW PHASE 4.3] Modular Google Colab AI/ML Codebase
│   ├── notebooks/                              # 15-section reproducible .ipynb notebooks (01..03.ipynb)
│   ├── configs/                                # Hyperparameter grids, CV block definitions, random seeds
│   └── colab_execution_manifest_v2.json        # Execution logs, library versions, runtime & SHA-256
│
├── models/
│   ├── sdm/ensemble/                           # [FROZEN v1.1.1] Heuristic RHSI baseline metadata
│   └── colab_v2/                               # [NEW PHASE 4.3] Trained Colab Statistical/ML Models
│       ├── track_a_temporal_cpue/              # Trained temporal regression models & out-of-sample metrics
│       ├── track_b_spatial_rhsi/               # Trained MaxEnt, RF, GLM model artifacts (.joblib/.json)
│       └── evaluation_matrices/                # 5-Fold Spatial Block CV, Boyce, TSS, PR-AUC, SHAP
│
├── outputs/
│   ├── sdm/                                    # [FROZEN v1.1.1] Phase 3 / 4.1.1 Heuristic RHSI projections & zones
│   └── colab_v2/                               # [NEW PHASE 4.3-4.4] Reproducible Colab Spatial Predictions
│       ├── hsi/                                # v2.0 projections across 6 scenarios x 3 species x 2,050 cells
│       ├── zones/                              # v2.0 clipped functional & bycatch screening GeoJSONs
│       └── comparison_v1_vs_v2.json            # Cell-by-cell & metric comparison (v1.1.1 Heuristic vs v2.0 Colab)
│
└── reports/
    ├── phase_4_1_1/                            # [FROZEN] Completed Phase 4.1.1 reconciliation reports
    ├── strategic_reassessment/                 # [CURRENT] 8 Strategic Reassessment architecture documents
    └── validation_gates/                       # Human Validation Gate status logs per phase
```

---

## 2. Standar Format Dataset & Konvensi Penamaan (Naming Convention)

Seluruh file pertukaran lintas platform wajib mengikuti pola penamaan deterministik:

`{platform}_{domain}_{entity}_{spatial_or_temporal_scope}_{version}.{ext}`

| Jenis Artefak | Format Standar | Contoh Nama File Kanonis | Kunci Utama (Primary Keys) |
| :--- | :--- | :--- | :--- |
| **GEE Raster Stack** | Cloud-Optimized GeoTIFF (`.tif`, `EPSG:4326`, `Float32`) | `gee_ocean_stack_east_monsoon_jja_v2_0.tif` | Koordinat piksel `(lon, lat)` dalam `[111.00, 113.00] × [-10.45, -8.00]` |
| **GEE Monthly Time-Series** | UTF-8 CSV & JSON (`.csv`, `.json`) | `gee_ocean_monthly_prigi_132m_v2_0.csv` | `year_month` (`2015-01` s/d `2025-12`, `132` baris) |
| **GEE Spatial Predictor Stack** | UTF-8 CSV & Parquet/JSON | `gee_predictor_stack_6scenarios_2050cells_v2_0.csv` | `(scenario_id, cell_id, lat, lon)` (`6 × 2.050 = 12.300` baris) |
| **Colab Trained Model Artifact** | `.joblib` + Ringkasan Parameter `.json` | `colab_sdm_rf_cakalang_spatialcv_v2_0.joblib` | `model_id`, `species_key`, `track_id`, `random_seed` |
| **Colab Spatial Predictions** | JSON & RFC 7946 GeoJSON | `colab_hsi_projections_all_scenarios_v2_0.json` | `(scenario_id, species_key, cell_id)` (`36.900` rekaman) |
| **Colab Validation Matrix** | UTF-8 CSV & JSON | `colab_sdm_spatial_block_cv_matrix_v2_0.csv` | `(model_id, track_id, species_key, algorithm, fold_id)` |

---

## 3. Skema Manifest & Pelacakan Provenance End-to-End

Setiap pertukaran data antara GEE, Google Drive, Google Colab, dan Antigravity wajib disertai berkas manifest JSON yang divalidasi secara otomatis sebelum tahap berikutnya diizinkan berjalan:

```json
{
  "manifest_id": "SEACAST-DATA-EXCHANGE-MANIFEST-V2.0",
  "generated_at": "ISO-8601 UTC timestamp",
  "source_platform": "GEE | COLAB | ANTIGRAVITY",
  "git_commit_or_baseline_ref": "PHASE_4_1_1_BASELINE_MANIFEST",
  "human_validation_gate_status": "READY_FOR_REVIEW | APPROVED",
  "artifacts": [
    {
      "artifact_id": "GEE-PRED-STACK-V2",
      "relative_path": "data/gee_v2/seasonal_scenarios/gee_predictor_stack_6scenarios_2050cells_v2_0.csv",
      "drive_path": "SEACAST_PRIGI_WPP573/01_gee_exports/gee_predictor_stack_6scenarios_2050cells_v2_0.csv",
      "generating_script": "gee/py/m05_scenario_predictor_stacks.py",
      "gee_task_id": "GEE_TASK_ID_FROM_API",
      "source_collections": [
        "NASA/OCEANDATA/MODIS-Aqua/L3SMI",
        "NOAA/CDR/OISST/V2_1",
        "ECMWF/ERA5/MONTHLY",
        "NOAA/NGDC/ETOPO1"
      ],
      "spatial_bounds": {"west": 111.0, "east": 113.0, "south": -10.45, "north": -8.0},
      "row_count": 12300,
      "null_value_count": 0,
      "size_bytes": 0,
      "sha256": "64-char-hex-sha256"
    }
  ]
}
```

### Aturan Perlindungan Baseline (`Non-Destructive Coexistence`):
1. Berkas-berkas Phase 3 & Phase 4.1.1 (`v1.1.1`) di `data/oceanographic/`, `models/sdm/ensemble/`, dan `outputs/sdm/` **dikunci dengan verifikasi SHA-256 terhadap [`backups/phase_4_1_1_baseline_manifest.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/backups/phase_4_1_1_baseline_manifest.json)**.
2. Pada Phase 4.4 dan Phase 4.5, dashboard WebGIS akan menyediakan **pemilih versi sumber model (*Model Source Toggle*)**:
   - `v1.1.1 — Heuristic RHSI Baseline (Phase 4.1.1 Audited)`
   - `v2.0 — GEE + Colab Statistical/ML Pipeline (Phase 4.2–4.3 Replicated)`
   sehingga peneliti dan penguji dapat membandingkan kedua pendekatan secara berdampingan (*side-by-side scientific comparison*).
