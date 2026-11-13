# PHASE 4.1.1 — RHSI WEIGHT RECONCILIATION REPORT (`R-01`)
## Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi — WPPNRI 573

- **Audit Area:** `R-01` — RHSI Ensemble Weight Provenance & Cross-File Synchronization
- **Audit Status:** `PASS` (`0` cross-file weight conflicts; `sum(w_i) = 1.000` for all species)
- **Weight Provenance Classification:** `PROVISIONAL_ANALYST_SELECTED_WEIGHTS`

---

## 1. Root-Cause Audit of Prior Three Weight Versions

Independent inspection of all Phase 3 and Phase 4.1 scripts, CSVs, and JSON outputs identified the exact provenance of the three conflicting weight sets observed prior to Phase 4.1.1:

| Version ID | Cakalang (`w_max / w_rf / w_glm`) | Madidihang (`w_max / w_rf / w_glm`) | Albakor (`w_max / w_rf / w_glm`) | Exact File / Script Origin | Mathematical / Technical Origin |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Version 1 (Legacy Phase 3 Squared-AUC Proxy Weights)** | `0.326 / 0.403 / 0.271` | `0.238 / 0.447 / 0.315` | `0.000 / 0.554 / 0.446` | [`scratch/execute_stage_3_4_to_3_7.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/execute_stage_3_4_to_3_7.js) (lines 229–238) & [`FINAL_SDM_VALIDATION_MATRIX.csv`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/FINAL_SDM_VALIDATION_MATRIX.csv) | Computed via squared proxy-AUC skill weighting $w_i = \max(0, \text{AUC}_i - 0.50)^2 / \sum_j \max(0, \text{AUC}_j - 0.50)^2$ on deterministic presence-proxy AUCs (`Cakalang: 0.774, 0.805, 0.750`; `Madidihang: 0.722, 0.804, 0.755`; `Albakor: 0.507 [zeroed], 0.862, 0.825`). |
| **Version 2 (Phase 4.1 Active Computation Weights — Canonical)** | **`0.320 / 0.355 / 0.325`** | **`0.295 / 0.365 / 0.340`** | **`0.000 / 0.517 / 0.483`** | [`models/sdm/ensemble/model_evaluations_summary.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/models/sdm/ensemble/model_evaluations_summary.json), [`outputs/sdm/hsi/hsi_all_scenarios_projections.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/outputs/sdm/hsi/hsi_all_scenarios_projections.json), [`outputs/sdm/hsi/rhsi_sensitivity_analysis.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/outputs/sdm/hsi/rhsi_sensitivity_analysis.json), [`html/sdm_data.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/html/sdm_data.js) | Balanced linear proxy-skill weights across active heuristic response equations (`Albakor w_maxent = 0.000` because Component 1 circular proxy AUC `0.507 <= 0.51`). Actively used to compute all `36,900` scenario-species-cell records. |
| **Version 3 (Stale Test Comment String)** | `0.320 / 0.355 / 0.325` | `0.325 / 0.350 / 0.325` | `0.000 / 0.550 / 0.450` | [`scratch/test_phase_4_1_integrity.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/test_phase_4_1_integrity.py) (`PG-03["notes"]`) | Manual text comment typo in `PG-03["notes"]` that contradicted `PG-03["actual_value"]` directly above it; never used in any numerical calculation. |

---

## 2. Scientific Provenance Decision (`Section 4.3`)

Because the component AUCs in [`FINAL_SDM_VALIDATION_MATRIX.csv`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/FINAL_SDM_VALIDATION_MATRIX.csv) were evaluated against deterministic environmental presence-proxy points (`HIGH_CIRCULARITY`, `spatial_cv_executed = FALSE`), **neither Version 1 nor Version 2 represents an empirically calibrated field-validation weight set**.

In accordance with **Section 4.3**, the canonical ensemble weights (`Version 2`, actively used across all `36,900` cell projections) are standardized across all files and explicitly tagged as:

- `weight_provenance_status`: **`"PROVISIONAL_ANALYST_SELECTED_WEIGHTS"`**
- `legacy_phase3_squared_auc_weights`: Documented alongside the canonical weights in metadata, CSV, and sensitivity analysis for complete audit traceability.

| Target Species | Component 1 (`hsi_maxent` / Gaussian-Sigmoid Niche) | Component 2 (`hsi_rf` / Rule-Based Step) | Component 3 (`hsi_glm` / Polynomial Logistic) | Sum ($\sum w_i$) | Provenance Status |
| :--- | :---: | :---: | :---: | :---: | :--- |
| **Cakalang (*Katsuwonus pelamis*)** | `0.320` | `0.355` | `0.325` | `1.000` | `PROVISIONAL_ANALYST_SELECTED_WEIGHTS` |
| **Madidihang (*Thunnus albacares*)** | `0.295` | `0.365` | `0.340` | `1.000` | `PROVISIONAL_ANALYST_SELECTED_WEIGHTS` |
| **Albakor (*Thunnus alalunga*)** | `0.000` | `0.517` | `0.483` | `1.000` | `PROVISIONAL_ANALYST_SELECTED_WEIGHTS` |

---

## 3. Synchronized Files Verified by `RT-01` & `RT-02`

1. [`models/sdm/ensemble/model_evaluations_summary.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/models/sdm/ensemble/model_evaluations_summary.json)
2. [`FINAL_SDM_VALIDATION_MATRIX.csv`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/FINAL_SDM_VALIDATION_MATRIX.csv)
3. [`PHASE_3_MODEL_METADATA.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/PHASE_3_MODEL_METADATA.json)
4. [`outputs/sdm/hsi/hsi_all_scenarios_projections.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/outputs/sdm/hsi/hsi_all_scenarios_projections.json)
5. [`outputs/sdm/hsi/rhsi_sensitivity_analysis.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/outputs/sdm/hsi/rhsi_sensitivity_analysis.json)
6. [`scratch/execute_stage_3_4_to_3_7.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/execute_stage_3_4_to_3_7.js)
7. [`html/sdm_data.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/html/sdm_data.js)
8. [`PHASE_4_1_READINESS_GATE.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/PHASE_4_1_READINESS_GATE.json) & [`reports/phase_4_1_1/PHASE_4_1_1_READINESS_GATE.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/reports/phase_4_1_1/PHASE_4_1_1_READINESS_GATE.json)
