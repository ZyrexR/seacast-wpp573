# PHASE 4.1.1 — BACKUP MANIFEST & PROVENANCE AUDIT (`R-05`)
## Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi — WPPNRI 573

- **Audit Area:** `R-05` — Reconciliation of `total_files_backed_up: 44` vs `48` Entries in Phase 4.1 Backup Manifest
- **Canonical Baseline Manifest:** [`backups/phase_4_1_1_baseline_manifest.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/backups/phase_4_1_1_baseline_manifest.json)
- **Phase 4.1.1 Pre-Patch Snapshot Manifest:** [`backups/phase_4_1_1_pre_patch/pre_patch_manifest.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/backups/phase_4_1_1_pre_patch/pre_patch_manifest.json) (`65` files backed up prior to Phase 4.1.1 edits)
- **Audit Status:** `PASS` (`total_files_backed_up == len(files) == 48`; 100% SHA-256 verified on disk)

---

## 1. Forensic Explanation of `44` vs `48` in `backups/phase_4_1_pre_remediation/backup_manifest.json`

Direct inspection of [`backups/phase_4_1_pre_remediation/backup_manifest.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/backups/phase_4_1_pre_remediation/backup_manifest.json) revealed why line 4 reported `"total_files_backed_up": 44` while `len(files)` was `48`:

1. **Batch 1 (Stage 0 Initial Backup — Files 1 to 44):** The Stage 0 backup script copied `44` files into `backups/phase_4_1_pre_remediation/`, writing `"total_files_backed_up": 44` at line 4 and recording each entry with hash key `"sha256_before"`.
2. **Batch 2 (Stage 1 Pre-Remediation Append — Files 45 to 48):** Before executing Stage 1 data remediation, `4` additional files (`data/spatial_species_dictionary.json`, `data/oceanographic/statistics/annual/annual_climatology.json`, `data/oceanographic/statistics/seasonal/seasonal_climatology.json`, and `data/master_prigi_pelagis_2015_2022.csv`) were copied into `backups/phase_4_1_pre_remediation/` and appended to `files` (lines 226–245) using hash key `"sha256_pre_remediation"` and adding a new footer key `"files_backed_up_count": 48` at line 247 **without updating `"total_files_backed_up": 44` at line 4**.
3. **Compliance with Section 8.3:** Per Section 8.3, the historical directory `backups/phase_4_1_pre_remediation/` and its `backup_manifest.json` have **not** been modified retroactively. Instead, [`backups/phase_4_1_1_baseline_manifest.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/backups/phase_4_1_1_baseline_manifest.json) has been generated with a unified schema where `manifest["total_files_backed_up"] == len(manifest["files"]) == 48`.

---

## 2. Complete 48-File Provenance & Checksum Table (`RT-12`)

| # | Relative Path | Backup Size (Bytes) | Backup SHA-256 (Prefix) | Phase 4.1 Hash Key | Modified in Phase 4.1 | Modified in Phase 4.1.1 |
| :---: | :--- | ---: | :--- | :--- | :---: | :---: |
| 1 | `data/master_prigi_all_inclusive.csv` | `17,228` | `004d07982f1bf86e...` | `sha256_before` | YES | YES |
| 2 | `data/oceanographic/statistics/monthly/monthly_stats_prigi.csv` | `16,469` | `3ac4b82fb8a0f605...` | `sha256_before` | YES | YES |
| 3 | `data/oceanographic/statistics/monthly/monthly_stats_wpp573.csv` | `17,293` | `984a95c4fd6a67c4...` | `sha256_before` | NO | NO |
| 4 | `data/oceanographic/statistics/monthly/ocean_fisheries_master_132m.json` | `163,538` | `2a2bea39938b1264...` | `sha256_before` | YES | YES |
| 5 | `PHASE_3_1_OCEANOGRAPHIC_QC.csv` | `84,080` | `97a411b318881759...` | `sha256_before` | YES | NO |
| 6 | `FINAL_OCEANOGRAPHIC_MONTHLY_QC.csv` | `25,988` | `5b690f6fc8b405e7...` | `sha256_before` | YES | NO |
| 7 | `data/spatial_config_aoi.json` | `5,161` | `e21f8c5865f1f7c5...` | `sha256_before` | YES | YES |
| 8 | `data/spatial_readiness_report.json` | `4,234` | `b7ee5bd904f1e0cc...` | `sha256_before` | YES | NO |
| 9 | `data/manifests/processing_manifest.json` | `1,934` | `291f19df6e2a480c...` | `sha256_before` | YES | NO |
| 10 | `data/oceanographic/metadata/data_sources.json` | `5,769` | `c5d3c8a46f0d6857...` | `sha256_before` | YES | NO |
| 11 | `data/oceanographic/metadata/quality_control_report.json` | `2,594` | `2f17a2c4a48944ed...` | `sha256_before` | YES | NO |
| 12 | `data/oceanographic/metadata/pilot_verification_report.json` | `2,097` | `38349e80eb124810...` | `sha256_before` | YES | NO |
| 13 | `data/oceanographic/metadata/variable_dictionary.json` | `4,035` | `2b398935b743c3ac...` | `sha256_before` | YES | NO |
| 14 | `data/oceanographic/processed/monthly/pilot_ocean_2024_01.json` | `556` | `c1db1d8afa3542fb...` | `sha256_before` | YES | NO |
| 15 | `data/oceanographic/processed/monthly/pilot_ocean_2024_07.json` | `547` | `414fe6694e318540...` | `sha256_before` | YES | NO |
| 16 | `data/oceanographic/processed/seasonal/grid_musim_timur_climatology.json` | `471,236` | `a31442db5435fddf...` | `sha256_before` | YES | NO |
| 17 | `data/oceanographic/processed/seasonal/grid_musim_barat_climatology.json` | `472,443` | `3989da119a74aa33...` | `sha256_before` | YES | NO |
| 18 | `data/oceanographic/processed/seasonal/grid_extreme_2019_super_iod.json` | `469,689` | `1527a5a79a4bdd01...` | `sha256_before` | YES | NO |
| 19 | `data/oceanographic/processed/seasonal/grid_extreme_2016_negative_iod.json` | `472,073` | `4ec53b0c07086bd5...` | `sha256_before` | YES | NO |
| 20 | `data/oceanographic/derived/bathymetry_features/prigi_shelf_break_200m.geojson` | `1,320` | `ce1a79c9747356ce...` | `sha256_before` | YES | NO |
| 21 | `data/oceanographic/derived/upwelling/ocean_fisheries_correlation.json` | `16,160` | `e5fac753a61ba5e3...` | `sha256_before` | YES | YES |
| 22 | `data/sdm/background/target_group_background.geojson` | `2,018,987` | `2a7199516f4fdd3c...` | `sha256_before` | YES | YES |
| 23 | `data/sdm/predictors/predictor_stack_metadata.json` | `2,512` | `648e16f71d9d7a21...` | `sha256_before` | YES | NO |
| 24 | `data/sdm/predictors/predictor_stack_multi_scenario.json` | `6,776,253` | `11b7ca54b1f8f3c1...` | `sha256_before` | YES | NO |
| 25 | `PHASE_3_MODEL_METADATA.json` | `6,979` | `8456a4bcdbeeb9ac...` | `sha256_before` | YES | YES |
| 26 | `models/sdm/ensemble/model_evaluations_summary.json` | `2,856` | `433c1943e12fa3f3...` | `sha256_before` | YES | YES |
| 27 | `outputs/sdm/hsi/hsi_all_scenarios_projections.json` | `18,909,282` | `b81340d30380a967...` | `sha256_before` | YES | YES |
| 28 | `outputs/sdm/zones/dynamic_bycatch_mitigation_zones.geojson` | `274,128` | `fd45bf0d334789fe...` | `sha256_before` | YES | NO |
| 29 | `outputs/sdm/zones/functional_ecological_zones.geojson` | `1,767,591` | `c094876373c60645...` | `sha256_before` | YES | NO |
| 30 | `outputs/sdm/zones/functional_zones_cakalang.geojson` | `1,787,186` | `13ac24e918ed6982...` | `sha256_before` | YES | NO |
| 31 | `outputs/sdm/zones/functional_zones_madidihang.geojson` | `1,724,404` | `b84700bfd9a443d6...` | `sha256_before` | YES | NO |
| 32 | `outputs/sdm/zones/functional_zones_legacy_species_removed.geojson` | `471,635` | `1d7af819138f3e56...` | `sha256_before` | YES | NO |
| 33 | `html/index.html` | `327,030` | `750eded735df1379...` | `sha256_before` | YES | NO |
| 34 | `html/oceanographic_data.js` | `346,421` | `b6cabc969db160ae...` | `sha256_before` | YES | YES |
| 35 | `html/sdm_data.js` | `2,009,194` | `722fca24dfd8fba1...` | `sha256_before` | YES | YES |
| 36 | `app.py` | `2,083` | `48a50c28bf837c13...` | `sha256_before` | NO | NO |
| 37 | `utils.py` | `8,139` | `e118c5cd92516b15...` | `sha256_before` | YES | NO |
| 38 | `pages/2_Iklim_dan_Laut.py` | `3,809` | `29643694b78f69d8...` | `sha256_before` | YES | NO |
| 39 | `pages/5_Spesies_dan_Pengelolaan.py` | `2,937` | `98e0cf65417aec7f...` | `sha256_before` | YES | NO |
| 40 | `pages/6_Data_dan_Keterbatasan.py` | `2,580` | `b6d3426fa81c2357...` | `sha256_before` | NO | NO |
| 41 | `scratch/execute_stage_3_1_to_3_3.js` | `20,484` | `172add79780afd6b...` | `sha256_before` | YES | NO |
| 42 | `scratch/execute_stage_3_4_to_3_7.js` | `17,010` | `d39b9db96a905b7b...` | `sha256_before` | YES | YES |
| 43 | `scratch/optimize_sdm_web_bundle.js` | `5,551` | `6fc497687721a5a2...` | `sha256_before` | YES | NO |
| 44 | `scratch/validate_polygon_geometries.py` | `3,401` | `c73f002cc19ecd6c...` | `sha256_before` | YES | YES |
| 45 | `data/spatial_species_dictionary.json` | `15,040` | `b3c0f0bbd3fd108b...` | `sha256_pre_remediation` | YES | YES |
| 46 | `data/oceanographic/statistics/annual/annual_climatology.json` | `5,638` | `c8e5719b399787d9...` | `sha256_pre_remediation` | YES | NO |
| 47 | `data/oceanographic/statistics/seasonal/seasonal_climatology.json` | `2,612` | `0357075724e355f7...` | `sha256_pre_remediation` | YES | NO |
| 48 | `data/master_prigi_pelagis_2015_2022.csv` | `7,466` | `6884f063e829daeb...` | `sha256_pre_remediation` | NO | NO |
