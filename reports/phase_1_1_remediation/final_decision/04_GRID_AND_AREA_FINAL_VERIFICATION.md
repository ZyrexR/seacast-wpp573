# GRID AND AREA FINAL VERIFICATION REPORT
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** GRID-FINAL-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Date:** 2026-11-02  

---

## 1. REPRODUKSI INDEPENDEN TIGA TINGKAT KISI SPASIAL

Rekonstruksi kisi pada proyeksi Equal-Area EPSG:6933 terhadap poligon master asli menghasilkan:

1. **Kisi Regional Standar Makro (0,10° ~ 11,0 km):**
   - Prefix ID: `WPP573_R010`
   - Dimensi Bounding Box: $240 	imes 72 = 17.280	ext{ sel}$.
   - Sel Laut Aktif: **`8.129 sel`** ($7.166$ sel laut murni + $501$ sel pesisir pulau + $462$ sel batas ZEE).
   - Total Luas Interseksi: **`933.749,49 km²`**.
   - Residu terhadap Master: **`0,00 km² (Zero Residual Reconciled)`**.
2. **Kisi Makro (0,25° ~ 27,8 km):**
   - Prefix ID: `WPP573_025`
   - Sel Laut Aktif: **`1.387 sel`**. Luas: **`933.749,49 km²`** (Residu $0,00	ext{ km}^2$).
3. **Prigi Local Sub-AOI (0,05° ~ 5,5 km):**
   - Nomenklatur Resmi: **`PRIGI_LOCAL_SUBAOI`** (Grid ID: `WPP573_PRIGI005`).
   - Bounding Box: $[111,00^\circ	ext{BT s.d. } 113,00^\circ	ext{BT}] 	imes [-10,45^\circ	ext{LS s.d. } -8,00^\circ	ext{LS}]$.
   - Dimensi: $40 	imes 49 = 1.960	ext{ sel}$.
   - Sel Laut Aktif: **`1.726 sel`** ($1.654$ pure ocean + $72$ coastal boundary).
   - Luas Interseksi Bersih: **`51.510,49 km²`**.
