# GRID CELL CLASSIFICATION & TOPOLOGICAL RULES
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** GRID-RULE-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Date:** 2026-11-02  

---

## 1. ATURAN MATEMATIS PEMBAGIAN ATRIBUT WAJIB

Setiap sel kisi memiliki atribut geometris terpisah secara tegas:
1. `cell_area_km2`: Luas planar poligon sel kisi pada proyeksi Equal-Area EPSG:6933.
2. `aoi_intersection_area_km2`: Luas perpotongan poligon sel dengan poligon batas WPPNRI 573 bersih.
3. `aoi_intersection_fraction`: Rasio $\frac{\text{aoi\_intersection\_area\_km2}}{\text{cell\_area\_km2}} \in [0,0, 1,0]$.
4. `ocean_fraction`: Rasio permukaan laut murni di dalam perpotongan sel dengan AOI.
5. `land_fraction`: Rasio permukaan pulau/daratan di dalam sel ($1,0 - \text{ocean\_fraction}$).

---

## 2. DEFINISI RESMI 4 KLASIFIKASI SEL (SECTION 8.4)

1. **`PURE_OCEAN`:**
   - Sel terletak sepenuhnya di dalam perairan laut WPPNRI 573 tanpa menyentuh batas luar ZEE atau garis pantai pulau manapun (`aoi_intersection_fraction == 1.0` dan `ocean_fraction == 1.0`).
2. **`COASTAL_BOUNDARY`:**
   - Sel berpotongan dengan garis pantai pulau (Jawa, Madura, Bali, Lombok, Sumbawa, Flores, Sumba, Timor, Rote, dll.) (`0.0 < ocean_fraction < 1.0` dan `lat >= -9.0°`). Nilai oseanografi wajib memperhitungkan efek kontaminasi daratan (*land contamination mask*).
3. **`OUTER_AOI_BOUNDARY`:**
   - Sel berpotongan dengan batas maritim terluar ZEE Indonesia dengan Australia atau batas barat/timur WPPNRI 573. Nilai laut adalah murni ($100\%$ laut), namun fraksi luas interseksi $< 1,0$ terhadap amplop WPPNRI 573.
4. **`LAND_EXCLUDED`:**
   - Sel terletak di atas daratan pulau utama dan memiliki luas perairan laut $< 5\%$ dari luas sel. Dikeluarkan sepenuhnya dari pemodelan oseanografi.
