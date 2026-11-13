# SEACAST PRIGI V3 — PHASE 2.11
# PYTHON MODEL & INFERENCE BRIDGE VERIFICATION REPORT

**Audit Date:** 2026-11-04  
**Audit Scope:** Species Distribution Modeling (SDM), Python Subprocess Bridge & scikit-learn Bundles  
**Status:** **VERIFIED PASS**  
**Audit Environment:** Python 3.14.7 (64-bit), Windows x64  

---

## 1. MODEL ARTIFACTS & INTEGRITY AUDIT

All machine learning model artifacts stored in `models/` were audited for file presence, byte count, and SHA256 cryptographic hashes.

### Artifact Registry & SHA256 Checksums

| Artifact File | Size (Bytes) | SHA256 Checksum | Target Purpose |
|:---|:---:|:---|:---|
| `models/scaler_oceanographic.joblib` | 855 | `09904bae3be8b4e7232fe09d57a4192b109e3ae30e79391fe7842e1434c4f3d4` | StandardScaler for 9 oceanographic features |
| `models/cakalang_ensemble_model.joblib` | 3,785,645 | `6226b147345b5c9287c126d24660cb10505183dbfa03e8787fba4db92a06140d` | Cakalang (*Katsuwonus pelamis*) SDM bundle |
| `models/madidihang_ensemble_model.joblib` | 3,453,645 | `5e14553ef512140a3225270f80bc9b39860aa018e61e05d21baef48e24c56fdc` | Madidihang (*Thunnus albacares*) SDM bundle |
| `models/legacy_species_removed_ensemble_model.joblib` | 2,552,045 | `0e0682ab584cf052ceca1e988cf05b1c95353ee5c59ca4628e9d300eb0ecadfe` | Legacy species removed from TCT scope (*Current TCT scope exclusion*) SDM bundle |
| `models/sdm/ensemble/model_evaluations_summary.json` | 7,057 | `f2d36118bfae83fcf63116dfbe1beabafc3e7b165b451ca5df716d10c2826cfd` | Baseline evaluation metrics (AUC / TSS) |

*Audit note: All files match the repository baseline bit-for-bit with zero drift or corruption.*

---

## 2. FEATURE ORDERING VERIFICATION

The standard oceanographic scaler and ensemble models accept an exact 9-dimensional input vector. Any disruption to the feature ordering invalidates the model predictions.

The audited inference runner (`bridge_inference_runner.py`) and Laravel service (`SpeciesPredictionService.php`) strictly maintain the following ordering:

1. `depth` (meters, negative or bathymetric depth)
2. `dist_to_port` (nautical miles from PPN Prigi)
3. `dist_to_coast` (nautical miles from coastline)
4. `dist_to_shelf` (nautical miles from 200m isobath)
5. `slope` (degrees, bathymetric gradient)
6. `sst` (sea surface temperature in °C)
7. `chla` (chlorophyll-a concentration in mg/m³)
8. `sst_gradient` (°C/km or thermal front intensity)
9. `eke` (eddy kinetic energy in cm²/s²)

---

## 3. ENSEMBLE WEIGHT AUDIT & LEGACY SPECIES REMOVED FROM TCT SCOPE ZERO-WEIGHT ENFORCEMENT

The audited models combine three base learners: MaxEnt, Random Forest (RF), and Generalized Linear Model (GLM).

### Weight Matrix Verification

| Species | MaxEnt Weight | Random Forest Weight | GLM Weight | Sum | MaxEnt Status |
|:---|:---:|:---:|:---:|:---:|:---:|
| **Cakalang** (*K. pelamis*) | 0.320 | 0.355 | 0.325 | 1.000 | Active |
| **Madidihang** (*T. albacares*) | 0.295 | 0.365 | 0.340 | 1.000 | Active |
| **Legacy species removed from TCT scope** (*T. alalunga*) | **0.000** | **0.517** | **0.483** | **1.000** | **ZERO-WEIGHT STRICTLY ENFORCED** |

### Code Verification in `bridge_inference_runner.py`:
```python
CALIBRATED_WEIGHTS = {
    'cakalang':   {'maxent': 0.320, 'rf': 0.355, 'glm': 0.325},
    'madidihang': {'maxent': 0.295, 'rf': 0.365, 'glm': 0.340},
    'legacy_species_removed':    {'maxent': 0.000, 'rf': 0.517, 'glm': 0.483},
}
```

---

## 4. CONTROLLED MULTI-SPECIES INFERENCE TEST

A controlled CLI test script (`scratch/audit_inference_test.py`) was executed against the active Python 3.14.7 runtime with 3 synthetic oceanographic test vectors:
- **Vector 1 (Coastal Zone):** Depth 65m, Dist 8 NM, SST 28.5°C, Chl-a 0.45 mg/m³
- **Vector 2 (Shelf Break Upwelling Front):** Depth 350m, Dist 25 NM, SST 25.2°C, Chl-a 0.65 mg/m³
- **Vector 3 (Deep Oceanic Basin):** Depth 3200m, Dist 85 NM, SST 29.8°C, Chl-a 0.12 mg/m³

### Inference Execution Results:

```json
{
  "cakalang": {
    "raw_hsi": [0.5065, 0.8411, 0.1474],
    "submodel_breakdown": [
      {"maxent": 0.4812, "rf": 0.5210, "glm": 0.5156},
      {"maxent": 0.8250, "rf": 0.8540, "glm": 0.8425},
      {"maxent": 0.1240, "rf": 0.1580, "glm": 0.1590}
    ],
    "weights_used": {"maxent": 0.32, "rf": 0.355, "glm": 0.325}
  },
  "madidihang": {
    "raw_hsi": [0.3027, 0.4919, 0.1675],
    "submodel_breakdown": [
      {"maxent": 0.2850, "rf": 0.3120, "glm": 0.3080},
      {"maxent": 0.4720, "rf": 0.5110, "glm": 0.4890},
      {"maxent": 0.1510, "rf": 0.1820, "glm": 0.1670}
    ],
    "weights_used": {"maxent": 0.295, "rf": 0.365, "glm": 0.34}
  },
  "legacy_species_removed": {
    "raw_hsi": [0.0783, 0.1494, 0.3440],
    "submodel_breakdown": [
      {"maxent": 0.0000, "rf": 0.0810, "glm": 0.0754},
      {"maxent": 0.0000, "rf": 0.1540, "glm": 0.1445},
      {"maxent": 0.0000, "rf": 0.3560, "glm": 0.3312}
    ],
    "weights_used": {"maxent": 0.0, "rf": 0.517, "glm": 0.483}
  }
}
```

### Key Analytical Confirmations:
1. In all test points for Legacy species removed from TCT scope, `submodel_breakdown.maxent` contributed exactly `0.0000`.
2. Vector 2 (Shelf Break Upwelling Front) correctly produced a high Cakalang HSI of **0.8411**, qualifying as a Tuna Hotspot ($HSI \ge 0.70$).
3. Vector 3 (Deep Oceanic Basin) yielded low Cakalang HSI (**0.1474**) but higher Legacy species removed from TCT scope suitability (**0.3440**), matching known pelagic deep-water biology.

---

## 5. SCIENTIFIC INTEGRITY & METHODOLOGICAL CAVEAT

> [!NOTE]
> **Heuristic Evaluation Methodology Notice:**
> In accordance with scientific auditing ethics, it must be documented that the model evaluation metrics reported in `model_evaluations_summary.json` (Cakalang AUC 0.88, Madidihang AUC 0.84, Legacy species removed from TCT scope AUC 0.81) were evaluated against heuristic proxy environmental pseudo-absences (`EXPLORATORY_HEURISTIC_SCREENING`), rather than independent vessel logbook catch-effort coordinates with spatial cross-validation.
> 
> While the numerical models and inference bridge function flawlessly from a software engineering standpoint, future biological recalibration with actual port landing logbooks is strongly recommended in Phase 4.

---
*Report verified by SEACAST PRIGI V3 Migration Audit Directorate.*

