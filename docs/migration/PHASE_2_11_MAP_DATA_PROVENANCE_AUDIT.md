# SEACAST PRIGI V3 — PHASE 2.11
# MAP VISUALIZATION & DATA PROVENANCE AUDIT REPORT

**Audit Date:** 2026-11-04  
**Audit Scope:** MapLibre GL JS, CartoDB Dark Matter, Homeport PPN Prigi, Layer Styles & Data Provenance  
**Status:** **VERIFIED PASS**  
**Audited View:** `resources/views/dashboard/dpi-radar.blade.php`  

---

## 1. GEOSPATIAL MAP ENGINE SPECIFICATIONS

| Parameter | Audited Value | Compliance Status |
|:---|:---|:---:|
| **Map Engine** | MapLibre GL JS (Client-side WebGL) | Verified |
| **Basemap Provider** | CartoDB Dark Matter (`https://basemaps.cartocdn.com/gl/dark-matter-gl-style/style.json`) | Verified |
| **Initial Center** | `[111.7275, -8.6]` (Southern Java / Prigi Offing) | Verified |
| **Initial Zoom** | `8.0` | Verified |
| **Scale Control** | Unit: Nautical Miles (`nautical`), Max Width: 100 px | Verified |
| **Navigation Controls** | Compass + Zoom Controls enabled (`top-right`) | Verified |
| **Attribution Control** | Disabled for clean scientific HUD presentation | Verified |

---

## 2. HOMEPORT PPN PRIGI ANCHOR

The homeport marker provides the spatial reference anchor for fishing vessel distance computations:
- **Identifier:** `PPN PRIGI HOMEPORT`
- **Coordinates:** Longitude `111.7275°E`, Latitude `-8.2833°S`
- **Marker Styling:** Glowing cyan dot (`bg-cyan-400`, white ring, cyan pulse animation)
- **Popup Content:**
  - PPN PRIGI HOMEPORT
  - Lat: -8.2833°S | Lon: 111.7275°E
  - Pusat Pendaratan Armada Tuna WPPNRI 573

---

## 3. FOUR MAP VISUALIZATION MODES & COLOR RAMPS

The interactive radar implements 4 dynamic visualization layers toggled via MapLibre GL expressions:

### 3.1 Mode 1: HSI Habitat Tuna (`hsi`)
Evaluates habitat suitability using continuous linear interpolation:
- `0.00` (Low Suitability): Deep Navy Slate `#0f172a`
- `0.30` (Moderate): Deep Teal `#134e4a`
- `0.55` (Potential Fishing Ground): Bright Cyan `#06b6d4`
- `0.70` (Tuna Hotspot): Amber Gold `#eab308`
- `0.90 - 1.00` (Prime Hotspot): Vivid Red `#ef4444`
- Fill Opacity: `0.75`

### 3.2 Mode 2: Zonasi DOM (`dom`)
Matches categorical DOM zones to distinct management colors:
- `Z4_NURSERY_GROUND`: Teal `#14b8a6`
- `Z5_BYCATCH_SCREENING`: Amber `#f59e0b`
- `Z3_FEEDING_CORE`: Emerald `#10b981`
- `Z1_PFG_INSHORE`: Cyan `#22d3ee`
- `Z2_PFG_OFFSHORE`: Blue `#3b82f6`
- `GENERAL_OCEANIC` (Fallback): Slate `#334155`
- Fill Opacity: `0.70`

### 3.3 Mode 3: Risiko Bycatch IOTC (`bycatch`)
Displays three-tier conservation alert levels:
- `GREEN` (Low Risk): Emerald `#34d399`
- `YELLOW` (Caution / Shelf Slope): Amber `#fbbf24`
- `RED` (High Alert / Thermal Front Hotspot): Rose `#f43f5e`
- Fill Opacity: `0.75`

### 3.4 Mode 4: Batimetri GEBCO (`bathymetry`)
Visualizes depth zones via stepped expressions:
- $\le 150\text{ m}$ (Paparan Dangkal): Light Cyan `#67e8f9`
- $150\text{ m} - 1200\text{ m}$ (Lereng Benua / Shelf Break): Ocean Blue `#0284c7`
- $> 1200\text{ m}$ (Palung Oseanik): Deep Navy `#1e3a8a`
- Fill Opacity: `0.65`

---

## 4. DATA PROVENANCE TRANSPARENCY

To maintain strict scientific credibility, all non-satellite/non-live inputs are clearly disclosed:
1. **Header Disclaimer Badge:**  
   Persistent badge in the top navigation bar reads:  
   `Gelombang/Angin: Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)`
2. **Snapshot Timestamp:**  
   Displays active database prediction snapshot date (`2026-11-03`).
3. **Sub-header Metadata:**  
   Displays: `8.129 Poligon PostGIS • Batimetri GEBCO • Ensemble SDM Cakalang, Madidihang & Albakor`.
4. **Interactive Cell Inspector:**  
   Clicking any grid polygon immediately extracts raw oceanographic properties into a dedicated HUD panel (Depth, Distance to Port, SST, Chl-a, Wave Height, Bycatch Alert, Species HSI Breakdown, and DOM Zone).

---
*Report verified by SEACAST PRIGI V3 Migration Audit Directorate.*

