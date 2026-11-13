# 04 — GEOSPATIAL DATA VALIDATION SUMMARY
## Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi — WPPNRI 573

Dokumen ini menyajikan hasil pemeriksaan dan validasi geospasial secara terprogram (tidak hanya berdasarkan tampilan visual di Leaflet) terhadap seluruh layer vektor (`GeoJSON`) dan grid kuasi-raster (`JSON`) di dalam workspace.

---

## 1. MATRIKS VALIDASI LAYER SPASIAL UTAMA

| Layer Spasial | Path File | Format | CRS | Tipe Geometri / Grid | Jumlah Fitur / Dimensi | Ukuran Piksel / Resolusi | Bounds Aktual `[minLon, minLat, maxLon, maxLat]` | NoData / Missing | Status Ketercakupan (Containment) dalam Sub-AOI (`[111, -10.45, 113, -8.0]`) | Status Validasi |
|---|---|---|---|---|---|---|---|---|---|---|
| **Sub-AOI Prigi Configuration** | [`data/spatial_config_aoi.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/spatial_config_aoi.json) | JSON Config | `EPSG:4326` (Area: `EPSG:32749`) | Bounding Box Metadata | 1 definisi | N/A | `[111.000, -10.500, 113.000, -8.000]` | 0 | **CONFLICTING:** Menulis batas selatan `-10.5°S` dan luas `~48,000 km²`, berbeda dari grid aktual `-10.45°S` ($59.550,43\text{ km}^2$). | `CONFLICTING` |
| **123 Candidate Bathymetric Screening Polygons** | [`outputs/sdm/zones/dynamic_bycatch_mitigation_zones.geojson`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/outputs/sdm/zones/dynamic_bycatch_mitigation_zones.geojson) | GeoJSON | `EPSG:4326` | `Polygon` (5 simpul tertutup per fitur) | `123` poligon | $0.05^\circ \times 0.05^\circ$ ($\approx 5,55\text{ km}$) | `[110.975, -8.725, 113.025, -8.375]` | 0 | **OVERHANG TERDETEKSI:** Centroid 100% di dalam `[111.0, 113.0]`, tetapi **6 poligon tepi** meluber $0.025^\circ$ ($\approx 2,75\text{ km}$) hingga `110.975°E` dan `113.025°E`. | `PARTIALLY_VERIFIED` |
| **PFG & Functional Zones — Multi-Species** | [`outputs/sdm/zones/functional_ecological_zones.geojson`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/outputs/sdm/zones/functional_ecological_zones.geojson) | GeoJSON | `EPSG:4326` | `Polygon` | `1.920` poligon | $0.05^\circ \times 0.05^\circ$ | `[110.975, -10.475, 113.025, -8.325]` | 0 | **OVERHANG TERDETEKSI:** **134 poligon tepi** meluber $0.025^\circ$ keluar batas barat (`110.975°E`), timur (`113.025°E`), dan selatan (`-10.475°S`). | `PARTIALLY_VERIFIED` |
| **PFG & Functional Zones — Cakalang** | [`outputs/sdm/zones/functional_zones_cakalang.geojson`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/outputs/sdm/zones/functional_zones_cakalang.geojson) | GeoJSON | `EPSG:4326` | `Polygon` | `1.920` poligon | $0.05^\circ \times 0.05^\circ$ | `[110.975, -10.475, 113.025, -8.325]` | 0 | **OVERHANG TERDETEKSI:** **134 poligon tepi** meluber $0.025^\circ$ keluar batas Sub-AOI. | `PARTIALLY_VERIFIED` |
| **PFG & Functional Zones — Madidihang** | [`outputs/sdm/zones/functional_zones_madidihang.geojson`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/outputs/sdm/zones/functional_zones_madidihang.geojson) | GeoJSON | `EPSG:4326` | `Polygon` | `1.858` poligon | $0.05^\circ \times 0.05^\circ$ | `[110.975, -10.475, 113.025, -8.375]` | 0 | **OVERHANG TERDETEKSI:** **132 poligon tepi** meluber $0.025^\circ$ keluar batas Sub-AOI. | `PARTIALLY_VERIFIED` |
| **PFG & Functional Zones — Albakor** | [`outputs/sdm/zones/functional_zones_albakor.geojson`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/outputs/sdm/zones/functional_zones_albakor.geojson) | GeoJSON | `EPSG:4326` | `Polygon` | `513` poligon | $0.05^\circ \times 0.05^\circ$ | `[110.975, -9.875, 113.025, -8.425]` | 0 | **OVERHANG TERDETEKSI:** **48 poligon tepi** meluber $0.025^\circ$ keluar batas barat/timur Sub-AOI. | `PARTIALLY_VERIFIED` |
| **RHSI & Uncertainty Grid Projections** | [`outputs/sdm/hsi/hsi_all_scenarios_projections.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/outputs/sdm/hsi/hsi_all_scenarios_projections.json) | JSON Grid | `EPSG:4326` | Point Centroid Grid (50 baris $\times$ 41 kolom) | `2.050` sel $\times$ 6 skenario $\times$ 3 spesies | $0.05^\circ \times 0.05^\circ$ | Centroid: `[111.000, -10.450, 113.000, -8.000]` | 0 (`NoData = 0`) | **100% Centroid di dalam Sub-AOI** (`[111.0, 113.0] E`, `[-10.45, -8.0] S`). | `VERIFIED` |
| **Static Bathymetry & Slope Grid** | [`data/oceanographic/spatial/prigi_static_bathymetry_grid.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/oceanographic/spatial/prigi_static_bathymetry_grid.json) | JSON Grid | `EPSG:4326` | Point Centroid Grid (50 baris $\times$ 41 kolom) | `2.050` sel | $0.05^\circ \times 0.05^\circ$ | Centroid: `[111.000, -10.450, 113.000, -8.000]` | 0 | **100% Centroid di dalam Sub-AOI** (`depth_m` dari `-25 m` s/d `-4.650 m`). | `VERIFIED` |
| **Multi-Scenario Oceanographic Predictor Grids** | [`data/sdm/predictors/predictor_stack_multi_scenario.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/sdm/predictors/predictor_stack_multi_scenario.json) & `data/oceanographic/processed/seasonal/grid_*.json` | JSON Grid | `EPSG:4326` | Point Centroid Grid (50 baris $\times$ 41 kolom) | `2.050` sel $\times$ 6 skenario | $0.05^\circ \times 0.05^\circ$ | Centroid: `[111.000, -10.450, 113.000, -8.000]` | 0 | **100% Centroid di dalam Sub-AOI** (`slope_deg` dihitung via selisih tengah 2D di `execute_stage_3_1_to_3_3.js` baris 70–93). | `VERIFIED` |
| **200m Shelf Break Contour Line** | [`data/oceanographic/derived/bathymetry_features/prigi_shelf_break_200m.geojson`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/oceanographic/derived/bathymetry_features/prigi_shelf_break_200m.geojson) | GeoJSON | `EPSG:4326` | `LineString` (10 titik) | `1` fitur garis | Kontur vektor | `[110.800, -8.580, 113.200, -8.380]` | 0 | **OVERHANG TERDETEKSI:** Ujung barat (`110.80°E`) dan ujung timur (`113.20°E`) keluar $0.20^\circ$ dari batas bujur Sub-AOI `[111.0°E, 113.0°E]`. | `PARTIALLY_VERIFIED` |
| **Presence-Proxy Points (4 Taxa)** | `data/sdm/presence/presence_proxy_*.geojson` | GeoJSON | `EPSG:4326` | `Point` | `405` (SKJ), `387` (YFT), `189` (ALB), `105` (Bycatch) | Titik centroid $0.05^\circ$ | `[111.000, -9.250, 113.000, -8.350]` | 0 | **100% di dalam Sub-AOI Prigi.** | `VERIFIED` |
| **Target-Group Background Points** | [`data/sdm/background/target_group_background.geojson`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/sdm/background/target_group_background.geojson) | GeoJSON | `EPSG:4326` | `Point` | `5.000` titik | Titik dengan jitter $\pm 0.02^\circ$ | `[110.9802, -10.4694, 113.0195, -7.9806]` | 0 | **OVERHANG JITTER:** Akibat penambahan `jitter = (pseudoRandom() - 0.5) * 0.04` pada centroid tepi di [`execute_stage_3_1_to_3_3.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/execute_stage_3_1_to_3_3.js#L446-L451), terdapat titik background yang bergeser hingga $\pm 0.02^\circ$ keluar batas `[111.0, 113.0] x [-10.45, -8.0]`. | `PARTIALLY_VERIFIED` |
| **WPPNRI 573 Official & Web Boundaries** | `data/boundaries/wppnri_573_big.geojson` (65,0 MB) & `wppnri_573_web.geojson` (211,8 KB) | GeoJSON | `EPSG:4326` | `MultiPolygon` / `Polygon` | `1` fitur batas WPP 573 | Vektor | Melingkupi Samudra Hindia Selatan Jawa – NTT | 0 | Sub-AOI Prigi berada di bagian utara-tengah WPPNRI 573 (mencakup sebagian daratan pesisir Trenggalek/Jawa Timur pada baris lintang `-8.00°S` s/d `-8.30°S`). | `VERIFIED` |

---

## 2. PEMERIKSAAN TOPOLOGI & GEOMETRI 123 POLIGON KANDIDAT MITIGASI BYCATCH

Berdasarkan hasil eksekusi validator geometri [`scratch/validate_polygon_geometries.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/validate_polygon_geometries.py) dan pemeriksaan langsung atas [`outputs/sdm/zones/dynamic_bycatch_mitigation_zones.geojson`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/outputs/sdm/zones/dynamic_bycatch_mitigation_zones.geojson):

1. **Jumlah Fitur & Tipe Geometri (`VERIFIED`):**
   - Tepat **123 fitur**, seluruhnya bertipe `Polygon` tunggal dengan 1 cincin luar (*exterior ring*) yang terdiri dari 5 pasang koordinat `[lon, lat]`.
2. **Validitas Cincin, Self-Intersection, dan Duplikasi (`VERIFIED`):**
   - **Geometri Kosong / Null:** `0`
   - **Penutupan Cincin (*Ring Closure* $v_0 = v_4$):** `123/123` tertutup sempurna.
   - **Self-Intersection / Bowtie Polygons:** `0` (setiap poligon adalah persegi panjang ortogonal murni $[lon \pm 0.025^\circ, lat \pm 0.025^\circ]$).
   - **Duplikat `cell_id` atau Centroid:** `0` (`123` `cell_id` unik).
   - **Overlap Antar-Poligon:** `0` interior overlap (poligon yang bersebelahan hanya berbagi garis tepi/simpul pada kelipatan $0.025^\circ$).
3. **Rentang Kedalaman & Parameter Lingkungan (`VERIFIED`):**
   - Kedalaman (`depth_m`): **`-242 m` hingga `-950 m`** (100% berada di dalam pita skrining `-150 m` s/d `-1.200 m`, dan secara spesifik dibangun dari filter `-200 m` s/d `-1.000 m`).
   - Suhu Permukaan Laut (`sst`): **`25,22 °C` hingga `25,68 °C`** ($\leq 26,0^\circ\text{C}$ pada Musim Timur).
4. **Masalah Ketercakupan Spasial (Overhang $0.025^\circ$) (`PARTIALLY_VERIFIED`):**
   - Terdapat **6 poligon** yang berpusat pada `lon = 111.00°E` (3 poligon: `cell_id` pada lintang `-8.45, -8.50, -8.55`) dan `lon = 113.00°E` (3 poligon) yang memiliki sisi barat di `110.975°E` atau sisi timur di `113.025°E`.
   - Agar 100% patuh terhadap aturan *strict spatial containment* di dalam Sub-AOI Prigi (`[111.00°E, 113.00°E]`), keenam poligon tepi tersebut harus di-*clip* menjadi setengah sel ($0.025^\circ \times 0.05^\circ$).

---

## 3. METODE PERHITUNGAN LUAS (AREA CALCULATION AUDIT)

1. **Perhitungan Luas Sub-AOI Prigi (`VERIFIED`):**
   - Skrip [`scratch/calc_sub_aoi_area.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/calc_sub_aoi_area.py#L1-L54) menghitung luas zona permukaan elipsoid WGS84 ($a = 6.378.137,0\text{ m}$, $e^2 = 2f - f^2$) menggunakan integral analitik lintang geodetik antara $\phi_1 = -10,45^\circ$ dan $\phi_2 = -8,00^\circ$ serta $\Delta\lambda = 2,00^\circ$:
     \[
     A_{\text{Sub-AOI}} = \mathbf{59.550,43\text{ km}^2} \quad (4,18\% \text{ dari } 1.423.238\text{ km}^2 \text{ WPPNRI 573})
     \]
2. **Perhitungan Luas 123 Poligon Bycatch (`PARTIALLY_VERIFIED`):**
   - Skrip [`scratch/validate_polygon_geometries.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/validate_polygon_geometries.py#L64-L68) menghitung luas per poligon $0.05^\circ \times 0.05^\circ$ dengan koreksi kosinus lintang ($\Delta x = 0.05 \times 111,3195 \cos\phi$, $\Delta y = 0.05 \times 110,574$), menghasilkan luas rata-rata **$30,45\text{ km}^2\text{/poligon}$** dan total luas 123 poligon sebesar **$3.745,76\text{ km}^2$** (sebelum pemotongan overhang 6 sel tepi; setelah 6 sel tepi dipotong setengah, luas di dalam Sub-AOI adalah $\approx 3.654,4\text{ km}^2$).
   - Di dalam file GeoJSON [`dynamic_bycatch_mitigation_zones.geojson`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/outputs/sdm/zones/dynamic_bycatch_mitigation_zones.geojson) sendiri, atribut `area_km2` per poligon **belum disimpan** di dalam `properties`.

