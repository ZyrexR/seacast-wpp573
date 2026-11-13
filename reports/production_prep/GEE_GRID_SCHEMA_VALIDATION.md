# GEE PRODUCTION GRID SCHEMA & TOPOLOGY SPECIFICATION
**Project:** SEACAST PRIGI — Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi  
**Domain:** WPPNRI 573 | Grid Resolution: 0.10° (WPP573_R010)  
**Document ID:** GEE-GRID-SPEC-01  
**Status:** VALIDATED BASELINE COMPLIANT  

---

## 1. STRUKTUR GRID KANONIS (WPP573_R010)

- **Sistem Grid Analisis:** 0.10° × 0.10° reguler, berpusat pada koordinat bujur dan lintang genap kelipatan 0.05°.
- **CRS Geografis:** `EPSG:4326` (WGS84).
- **CRS Proyeksi Luas:** `EPSG:6933` (Equal-Area Cylindrical).
- **Dimensi Bounding Box:**
  - Bujur Barat ($X_{min}$): $102.50^\circ	ext{E}$
  - Bujur Timur ($X_{max}$): $126.50^\circ	ext{E}$ ($\Delta X = 24.00^\circ ightarrow 240	ext{ kolom}$)
  - Lintang Selatan ($Y_{min}$): $-14.00^\circ	ext{S}$
  - Lintang Utara ($Y_{max}$): $-6.80^\circ	ext{S}$ ($\Delta Y = 7.20^\circ ightarrow 72	ext{ baris}$)
- **Total Sel Bounding Box (Kandidat):** $240 	imes 72 = \mathbf{17.280	ext{ sel}}$.
- **Total Sel Aktif Analisis Kelautan:** **`8,129` sel** (100% konsisten dengan baseline Phase 1.1 dan Phase 4.2).

### Taksonomi Sel Grid Resmi
1. **`PURE_OCEAN` (7,166 sel):** Sel laut interior yang seluruh areanya berada di dalam batas perairan WPPNRI 573 tanpa menyentuh garis pantai atau batas terluar.
2. **`COASTAL_BOUNDARY` (501 sel):** Sel batas pesisir yang beririsan dengan daratan Pulau Jawa, Bali, Lombok, Sumbawa, Sumba, Flores, Timor, atau 1.400 pulau kecil interior WPP 573.
3. **`OUTER_AOI_BOUNDARY` (462 sel):** Sel batas traktat terluar yang beririsan dengan batas ZEE / yurisdiksi internasional WPPNRI 573.
4. **`OUTSIDE_AOI` (9,151 sel):** Sel daratan interior atau perairan luar WPP yang dieksklusikan dari agregasi produksi.

---

## 2. SKEMA ATRIBUT SPASIAL PER SEL (JSON SCHEMA COMPLIANT)

Setiap sel grid dalam pipeline GEE menyimpan atribut kanonis:
- `cell_id`: Pengenal unik dengan pola `WPP573_R010_C{col:03d}_R{row:03d}` (contoh: `WPP573_R010_C175_R001`).
- `lon_center`: Bujur titik tengah sel (derajat desimal).
- `lat_center`: Lintang titik tengah sel (derajat desimal).
- `cell_area_km2`: Luas metrik sel terhitung pada EPSG:6933.
- `aoi_intersection_area_km2`: Luas irisan dengan Master AOI WPPNRI 573.
- `ocean_area_km2`: Luas permukaan laut bersih.
- `land_area_km2`: Luas daratan dalam sel.
- `ocean_fraction`: Fraksi laut ($0.0 \le f_{ocean} \le 1.0$).
- `cell_classification`: `PURE_OCEAN` | `COASTAL_BOUNDARY` | `OUTER_AOI_BOUNDARY`.
- `dist_prigi_harbor_km`: Jarak geodesik ke Pelabuhan Perikanan Nusantara (PPN) Prigi ($111.7275^\circ	ext{E}, -8.2833^\circ	ext{S}$).
