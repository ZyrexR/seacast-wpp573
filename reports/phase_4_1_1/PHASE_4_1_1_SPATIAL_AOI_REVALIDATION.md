# PHASE 4.1.1 — SPATIAL AOI, GEOMETRY & BACKGROUND POINTS REVALIDATION (`R-04`)
## Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi — WPPNRI 573

- **Audit Area:** `R-04` — Polygon Geometry (`within` / `difference`), Within-Layer vs Cross-Layer Overlap, WPPNRI 573 Area Denominator, & Background Points Clamping Remediation
- **Validation Script:** [`scratch/validate_polygon_geometries.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/validate_polygon_geometries.py)
- **Validation Results Artifact:** [`scratch/polygon_geometry_validation_results.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/polygon_geometry_validation_results.json)
- **Audit Status:** `PASS`

---

## 1. WPPNRI 573 Area Denominator Reconciliation (`1,423,238 km²` vs `1,425,000 km²` vs `933,749.49 km²`)

| Spatial Domain / Reference Denominator | Exact Surface Area (`km²`) | Sub-AOI Prigi Share (`%`) | Provenance & Reconciliation Status |
| :--- | ---: | ---: | :--- |
| **Sub-AOI Prigi `[111.00, 113.00]° BT × [-10.45, -8.00]° LS`** | **`59,550.43 km²`** | — | Exact WGS84 authalic/equal-area (`EPSG:6933`) integral across `50 × 41 = 2,050` clipped cells (`56,365.85 km²` active marine habitat cells). |
| **WPPNRI 573 Nominal Administrative Reference (KKP)** | **`1,423,238.00 km²`** | **`4.1842%` (`4.18%`)** | Canonical administrative reference denominator synchronized across [`data/spatial_config_aoi.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/spatial_config_aoi.json), [`PHASE_4_EXISTING_ASSET_INVENTORY.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/PHASE_4_EXISTING_ASSET_INVENTORY.json), and [`PHASE_4_1_READINESS_GATE.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/PHASE_4_1_READINESS_GATE.json) (replacing legacy rounded `1,425,000 km²`). |
| **WPPNRI 573 Official BIG Polygon Geometry (`wppnri_573_big.geojson`)** | **`933,749.49 km²`** | **`6.3776%` (`6.38%`)** | Direct WGS84 equal-area (`EPSG:6933`) integral of `objectid: 5` (`MultiPolygon`, 2 polygons, 625,698 vertices) in [`data/boundaries/wppnri_573_big.geojson`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/boundaries/wppnri_573_big.geojson). Both ratios (`4.1842%` nominal and `6.3776%` BIG polygon) are explicitly documented in `data/spatial_config_aoi.json`. |

---

## 2. Full 2D Polygon Geometry & Within-Layer Topology Validation (`RT-08` & `RT-09`)

| GeoJSON Layer (`outputs/sdm/zones/`) | Features | Valid Polygons | Self-Intersections | Unclosed Rings | Duplicate Geometries | Within-Layer Interior Overlaps | Polygons Outside AOI | `difference(AOI)` Area (`km²`) | Boundary-Clipped Polygons | Total Area (`EPSG:6933 km²`) | Status |
| :--- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | :---: |
| `functional_ecological_zones.geojson` | `1,923` | `1,923` | `0` | `0` | `0` | `0` | `0` | `0.000000` | `134` | `56,365.85` | `PASS` |
| `functional_zones_cakalang.geojson` | `1,923` | `1,923` | `0` | `0` | `0` | `0` | `0` | `0.000000` | `134` | `56,365.85` | `PASS` |
| `functional_zones_madidihang.geojson` | `1,858` | `1,858` | `0` | `0` | `0` | `0` | `0` | `0.000000` | `131` | `54,431.39` | `PASS` |
| `functional_zones_albakor.geojson` | `513` | `513` | `0` | `0` | `0` | `0` | `0` | `0.000000` | `28` | `15,190.87` | `PASS` |
| `dynamic_bycatch_mitigation_zones.geojson` | `123` | `123` | `0` | `0` | `0` | `0` | `0` | `0.000000` | `6` | `3,653.23` | `PASS` |

---

## 3. Cross-Layer Overlap Matrix (`RT-09`)

Unlike within-layer interior overlaps (which are strictly `0` across all 5 layers), multi-species and bathymetric screening layers share grid cells across thematic layers by design:

| Layer A | Layer B | Shared Grid Cells | Shared Equal-Area (`km²`) | Ecological / Technical Interpretation |
| :--- | :--- | ---: | ---: | :--- |
| `functional_ecological_zones.geojson` | `functional_zones_cakalang.geojson` | `1,923` | `56,365.85` | Identical alias layer (`functional_ecological_zones` mirrors flagship Cakalang layer) |
| `functional_zones_cakalang.geojson` | `functional_zones_madidihang.geojson` | `1,858` | `54,431.39` | Multi-species pelagic habitat co-occurrence across Sub-AOI |
| `functional_zones_cakalang.geojson` | `functional_zones_albakor.geojson` | `513` | `15,190.87` | Offshore deep-water overlap between Cakalang and Albakor screening zones |
| `dynamic_bycatch_mitigation_zones.geojson` | `functional_zones_cakalang.geojson` | `123` | `3,653.23` | All 123 continental slope (`-1000 m` to `-200 m`, `SST <= 26.0 °C`) bycatch candidate cells lie within the active marine domain |
| `dynamic_bycatch_mitigation_zones.geojson` | `functional_zones_albakor.geojson` | `123` | `3,653.23` | 100% of the 123 slope bycatch cells co-occur with Albakor deep-water screening cells |

---

## 4. Background Points Count (`5,000`) & Boundary Clamping Bias Remediation (`RT-10`)

- **Canonical Count (`5,000` points):** [`data/sdm/background/target_group_background.geojson`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/sdm/background/target_group_background.geojson) contains exactly **`5,000` points** (`1,000` points per fold reference in `FINAL_SDM_VALIDATION_MATRIX.csv`). The stale `2,000` override in `scratch/test_phase_4_1_integrity.py` and `PHASE_3_MODEL_METADATA.json` has been corrected to `5,000`.
- **Boundary Clamping Bias Remediation (`93` → `0` points on boundary):** In Phase 4.1, clamping `93` out-of-bounds jittered points onto `[111.00, 113.00] × [-10.45, -8.00]` caused `93` points (`1.86%`) to sit directly on the boundary lines. In Phase 4.1.1, those `93` points were deterministically re-sampled (seed `42`) within their respective clipped grid cell interiors `[111.0005, 112.9995]° BT × [-10.4495, -8.0005]° LS`, resulting in **`0` points outside AOI** and **`0` points piled on the AOI boundary**.
