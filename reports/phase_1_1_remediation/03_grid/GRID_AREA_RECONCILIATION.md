# GRID AREA INDEPENDENT RECONCILIATION REPORT
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** GRID-REC-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Auditor:** Senior Marine Geospatial Scientist & GIS Data Quality Engineer  
**Date:** 2026-11-02  

---

## 1. REKONSILIASI MATEMATIS LUAS SEL KISI DENGAN POLIGON MASTER ASLI

Pada Phase 1.0-R terjadi anomali pemuatan poligon simplifikasi tanpa pulau ($947.214,20\text{ km}^2$).
Pada Phase 1.1, **seluruh sel kisi direkonstruksi dan diinterseksikan secara independen terhadap POLIGON MASTER RESMI YANG ASLI (`AOI_WPP573_MASTER.geojson`)** pada sistem proyeksi Equal-Area EPSG:6933:

$$\text{AOI\_Intersection\_Area} = \text{Area}(\text{Cell} \cap \text{AOI}_{\text{WPP573\_MASTER\_TRUE}})$$

### Hasil Rekonsiliasi Luas Total:
- **Luas Poligon Master WPPNRI 573 Bersih (EPSG:6933):** **`933,749.49 km²`**
- **Luas Geodesik WGS84 Master:** **`933,750.90 km²`**
- **Jumlah Luas Interseksi Sel Grid 0,10° (R010):** **`933,749.49 km²`**
- **Selisih Luas Interseksi vs Poligon Master:** **`0.0 km²`** ($< 0,001\%$ residu numerik batas batas piksel).

---

## 2. REKAPITULASI TAKSONOMI SEL KISI INDEPENDEN

| Parameter Kisi | Macro Grid (0,25°) | Regional Grid (0,10°) | Prigi Local Sub-AOI (0,05°) |
|---|---|---|---|
| **Prefix ID Resmi** | `WPP573_025` | `WPP573_R010` *(Standar Makro)* | `WPP573_PRIGI005` *(Standar Mikro)* |
| **Resolusi Spasial** | $0,25^\circ$ ($\sim 27,8\text{ km}$) | **$0,10^\circ$ ($\sim 11,0\text{ km}$)** | **$0,05^\circ$ ($\sim 5,5\text{ km}$)** |
| **Dimensi (Kolom x Baris)** | $96 \times 29$ | **$240 \times 72$** | **$40 \times 49$** |
| **Total Sel Bounding Box** | 2,784 sel | **17,280 sel** | **1,960 sel** |
| **Sel Interior Laut Murni (`PURE_OCEAN`)** | 1,039 sel | **7,166 sel** | **1,654 sel** |
| **Sel Batas Pesisir Pulau (`COASTAL_BOUNDARY`)** | 173 sel | **501 sel** | **72 sel** |
| **Sel Batas Luar ZEE (`OUTER_AOI_BOUNDARY`)** | 175 sel | **462 sel** | **0 sel** |
| **Total Sel Aktif Analisis Kelautan** | **1,387 sel** | **8,129 sel** | **1,726 sel** |
| **Sel Daratan Dikeluarkan (`LAND_EXCLUDED`)** | 0 sel | **0 sel** | **0 sel** |
| **Luas Interseksi Terhitung** | 933,749.49 km² | **933,749.49 km²** | **51,510.49 km²** |
