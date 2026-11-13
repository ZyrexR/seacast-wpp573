# SEACAST PRIGI — KNOWN SCIENTIFIC & TECHNICAL LIMITATIONS
## PHASE 4.2 GEE PRODUCTION SCRIPT v2.1
### Methodological Boundaries, Spatial-Temporal Constraints, and Ecological Scope

**Document ID:** `SEACAST-PHASE-4.2-v2.1-LIMITATIONS`  
**Classification:** `SCIENTIFIC_GOVERNANCE`  
**Target Script:** [`gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025_v2_1.js`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025_v2_1.js)  
**Study Domain:** WPPNRI 573 ($105.0^\circ\text{E} - 117.0^\circ\text{E}$, $6.8^\circ\text{S} - 14.0^\circ\text{S}$)  
**Date of Assessment:** 2026-11-02  

---

## 1. SCOPE BOUNDARIES & PROHIBITION OF SPECIES MODELING

`SEACAST_PRIGI_GEE_PRODUCTION_2015_2025_v2_1.js` is strictly designed as an **Oceanographic and Geophysical Spatial Feature Engineering Pipeline**.

**NON-NEGOTIABLE SCIENTIFIC BOUNDARY:**  
- The script **does not execute species distribution modeling (SDM)**, habitat suitability indexing (HSI), or machine learning classification.
- The script **strictly prohibits synthetic occurrence generation** or pseudo-presence sampling.
- All biological and fisheries modeling functions are isolated into downstream Machine Learning phases and are gated behind `ENABLE_SPECIES_ML = false`.

---

## 2. SPATIAL RESOLUTION & SCALE MISMATCH

1. **Analysis Grid Resolution ($0.10^\circ \approx 11.1\text{ km}$):**  
   The canonical grid provides excellent regional mesoscale coverage ($8,129$ cells across $933,749.49\text{ km}^2$), but inherently averages sub-mesoscale features.
2. **Sensor Footprint Differences:**  
   - OISST ($0.25^\circ \approx 27.8\text{ km}$) and CFSv2 ($0.205^\circ \approx 22.8\text{ km}$) have native footprints larger than the analysis grid cells. While spatial interpolation and zonal averaging are smooth, gradients near sharp frontal boundaries reflect the resolution limits of the input sensors.
   - MODIS/GCOM-C ($4.6\text{ km}$) and ETOPO1 ($1.85\text{ km}$) have native resolutions finer than the grid cells and are downscaled via spatial aggregation.
3. **Sub-Mesoscale Dynamics:**  
   Internal solitary waves generated in the Lombok and Ombai Straits, coastal headland eddies near Prigi Bay, and small-scale reef lagoons cannot be resolved at $0.10^\circ$ scale.

---

## 3. COASTAL ZONE & NEAR-SHORE UNCERTAINTIES

The $501$ cells classified as `COASTAL_BOUNDARY` require cautious interpretation:
1. **Mixed Land-Sea Pixels:** Coarser radiometers (such as microwave sensors contributing to OISST) suffer from land-contamination within $10 - 25\text{ km}$ of steep coastlines (e.g., South Java cliffs).
2. **Case-2 Waters & Turbidity:** Optical chlorophyll-a algorithms (OCx) can misinterpret suspended inorganic sediments, colored dissolved organic matter (CDOM), and shallow bottom reflectance near river mouths as biogenic phytoplankton chlorophyll.
3. **Wind Boundary Layer:** CFSv2 winds do not account for fine-scale coastal orographic wind channeling through mountain passes along southern Java.

---

## 4. TEMPORAL RESOLUTION & HIGH-FREQUENCY DYNAMICS

1. **Monthly Integration Window:**  
   The primary analysis period aggregates environmental drivers into 132 monthly timesteps (2015–2025). While this captures seasonal monsoon cycles and interannual climate modes (IOD, ENSO), it does not resolve:
   - Diurnal land-sea breeze cycles.
   - 3-to-7-day synoptic wind bursts during the peak Southeast Monsoon.
   - Transient passages of tropical depressions or equatorial Kelvin waves.
2. **Instantaneous Stress vs Monthly Means:**  
   While `v2.1` solves the non-linear wind stress underestimation by computing stress at 6-hour CFSv2 intervals prior to monthly averaging, the resulting monthly curl and Ekman pumping remain mean estimates of a dynamic, pulsing field.

---

## 5. OCEAN CURRENT ASSET HIATUS (2024-09 TO 2025-12)

1. **Hiatus Duration:** Exactly $16$ calendar months.
2. **Operational Reason:** Cessation of HYCOM operational model output in September 2024, combined with the lack of native public CMEMS daily current rasters in the standard GEE Earth Engine catalog.
3. **Scientific Safeguard:** In adherence to the zero-fabrication protocol, velocity components for these 16 months remain null with flag `SENSOR_FALLBACK_MISSING_ASSET`.
4. **Resolution Path:** Ingestion of Copernicus Marine NetCDF products into project `seca-513505` user assets (`projects/seca-513505/assets/cmems_velocity_monthly`).

---

## 6. SCRIPT CLASSIFICATION & PERMITTED RUNTIME MODES

1. **Candidate Status:** `REMEDIATED_DEVELOPMENT_CANDIDATE`.
2. **Runtime Locking:** The script cannot be executed as a full batch pipeline or submit bulk export tasks while configured in `MODE = "DEVELOPMENT"`.
3. **Transition Requirement:** Advancing this candidate to production execution requires formal researcher sign-off following the completion of the Phase 4.2 review.

