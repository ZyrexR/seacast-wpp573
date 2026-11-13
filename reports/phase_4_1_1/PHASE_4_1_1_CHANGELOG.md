# PHASE 4.1.1 — TARGETED RECONCILIATION CHANGELOG
## Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi — WPPNRI 573

- **Pre-Patch Snapshot Directory:** `backups/phase_4_1_1_pre_patch/` (`65` files backed up before any Phase 4.1.1 edits)
- **Canonical Baseline Manifest:** [`backups/phase_4_1_1_baseline_manifest.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/backups/phase_4_1_1_baseline_manifest.json)

---

## 1. Files Modified During Phase 4.1.1 (With Pre-Patch vs. Post-Patch SHA-256 Checksums)

| Relative Path | Size Before (B) | Size After (B) | SHA-256 Pre-Patch 4.1.1 | SHA-256 Post-Patch 4.1.1 | Status |
| :--- | ---: | ---: | :--- | :--- | :---: |
| `FINAL_SDM_VALIDATION_MATRIX.csv` | `6,184` | `6,767` | `13981c6cf664c9e4...` | `8cd15cd4d82fd148...` | **MODIFIED IN 4.1.1** |
| `PHASE_3_MODEL_METADATA.json` | `8,723` | `10,996` | `1e0984d2f547a3b9...` | `3b8ebebef058e8f8...` | **MODIFIED IN 4.1.1** |
| `PHASE_4_1_DATA_INTEGRITY_REPORT.md` | `5,280` | `6,401` | `5b2b0285d19937f7...` | `76777f6d3e9158bc...` | **MODIFIED IN 4.1.1** |
| `PHASE_4_1_READINESS_GATE.json` | `50,140` | `65,161` | `9afbfb1d78950bfa...` | `6e326a4e03268a72...` | **MODIFIED IN 4.1.1** |
| `PHASE_4_EXISTING_ASSET_INVENTORY.json` | `67,439` | `67,507` | `ab69dc51ce183179...` | `43ebd7fa5c5299c5...` | **MODIFIED IN 4.1.1** |
| `data/master.csv` | `20,785` | `24,775` | `09fdea081a29f2b1...` | `98494b0148905d38...` | **MODIFIED IN 4.1.1** |
| `data/master_prigi_all_inclusive.csv` | `17,716` | `21,706` | `2e09566a77985c43...` | `06948dc6a1835f55...` | **MODIFIED IN 4.1.1** |
| `data/oceanographic/derived/upwelling/ocean_fisheries_correlation.json` | `17,607` | `54,398` | `852543712e1c0274...` | `1e75eb646cae6eed...` | **MODIFIED IN 4.1.1** |
| `data/oceanographic/statistics/monthly/monthly_stats_prigi.csv` | `16,339` | `20,329` | `d3b207f5112c1431...` | `65c386411e300af9...` | **MODIFIED IN 4.1.1** |
| `data/oceanographic/statistics/monthly/ocean_fisheries_master_132m.json` | `169,967` | `199,818` | `6a8864d2f1b6a088...` | `904c845478737792...` | **MODIFIED IN 4.1.1** |
| `data/sdm/background/target_group_background.geojson` | `2,108,714` | `2,113,752` | `544f80998553ace7...` | `018c03d44935fc07...` | **MODIFIED IN 4.1.1** |
| `data/sdm/bias/effort_bias_surface.json` | `338,432` | `338,873` | `1eda62442ecc45fd...` | `38a8f3b492be0ffe...` | **MODIFIED IN 4.1.1** |
| `data/spatial_config_aoi.json` | `5,656` | `6,503` | `ef3fdaee68240a57...` | `cc6d7fdc4735d659...` | **MODIFIED IN 4.1.1** |
| `data/spatial_species_dictionary.json` | `16,613` | `17,508` | `219cd977c32a24df...` | `b816b1d5a44984ed...` | **MODIFIED IN 4.1.1** |
| `data/species_traits.csv` | `1,426` | `3,511` | `8abbba8f80f48479...` | `7febf66976a7ec22...` | **MODIFIED IN 4.1.1** |
| `html/oceanographic_data.js` | `1,045,664` | `1,098,577` | `3f8842223082bf22...` | `251c3f55cda5c9ea...` | **MODIFIED IN 4.1.1** |
| `html/sdm_data.js` | `3,119,017` | `3,140,978` | `fe9d7a148935123c...` | `4eb7b66df944f65f...` | **MODIFIED IN 4.1.1** |
| `models/sdm/ensemble/model_evaluations_summary.json` | `5,217` | `7,057` | `c8015a2f443a380d...` | `f2d36118674d0109...` | **MODIFIED IN 4.1.1** |
| `outputs/sdm/hsi/hsi_all_scenarios_projections.json` | `30,087,017` | `30,090,521` | `71c7a92614fad0d9...` | `c41f8e0189298234...` | **MODIFIED IN 4.1.1** |
| `outputs/sdm/hsi/rhsi_sensitivity_analysis.json` | `29,881` | `68,809` | `f7c5a382d457fdd6...` | `a3bda7492a181b94...` | **MODIFIED IN 4.1.1** |
| `scratch/execute_stage_3_4_to_3_7.js` | `17,879` | `18,317` | `cfc79875e99eb5b3...` | `4969edb7d5d4fd5a...` | **MODIFIED IN 4.1.1** |
| `scratch/test_phase_4_1_integrity.py` | `46,808` | `46,656` | `d493793c7e0556bf...` | `74a2d8443f02d5d4...` | **MODIFIED IN 4.1.1** |
| `scratch/validate_polygon_geometries.py` | `3,360` | `9,772` | `432185865b9572f1...` | `01d8b45d262b0ef1...` | **MODIFIED IN 4.1.1** |

---

## 2. New Canonical Data, Manifest, Test & Gate Files Created in Phase 4.1.1

| Relative Path | Size (Bytes) | SHA-256 Checksum (Prefix) | Status |
| :--- | ---: | :--- | :---: |
| `data/prigi_fleet_by_gt_2015_2025.csv` | `1,665` | `7e3f9e0b028e38f7...` | **CREATED IN 4.1.1** |
| `backups/phase_4_1_1_baseline_manifest.json` | `37,782` | `a2956a346562def3...` | **CREATED IN 4.1.1** |
| `backups/phase_4_1_1_pre_patch/pre_patch_manifest.json` | `20,082` | `0dd71bcbc570b1c8...` | **CREATED IN 4.1.1** |
| `scratch/polygon_geometry_validation_results.json` | `8,526` | `bf10abd82687332a...` | **CREATED IN 4.1.1** |
| `scratch/run_phase_4_1_1_regression_tests.py` | `34,910` | `bf89a2ce8e9bb73a...` | **CREATED IN 4.1.1** |
| `scratch/phase_4_1_1_regression_test_results.json` | `17,836` | `822935ceb799d500...` | **CREATED IN 4.1.1** |
| `reports/phase_4_1_1/PHASE_4_1_1_READINESS_GATE.json` | `63,938` | `a64a296f3995be1d...` | **CREATED IN 4.1.1** |
