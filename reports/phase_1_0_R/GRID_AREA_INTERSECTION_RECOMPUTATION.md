# GRID AREA INTERSECTION RECOMPUTATION, CLASSIFICATION & HARMONIZATION REPORT
**Project:** SEACAST PRIGI — WPPNRI 573  
**Phase:** 1.0-R — Remediation R-05  
**Master AOI Baseline Area:** 933,750.9 km² (Geodesik) / 947,214.20 km² (Equal-Area EPSG:6933)  
**Projection Engine:** NSIDC EASE-Grid 2.0 Global Equal-Area (EPSG:6933)  

---

## 1. REKONSILIASI MATEMATIS LUAS INTEGRASI GRID (ZERO-RESIDUAL RECONCILIATION)

Pada audit terdahulu (Phase 0.5), penghitungan jumlah total sel menggunakan pendekatan poligon bounding box sederhana menghasilkan estimasi luas kasar yang tampak memiliki deviasi terhadap luas AOI master.

Pada Phase 1.0-R, **seluruh sel kisi dipotong (*intersected*) secara geometris menggunakan poligon presisi tinggi pada sistem proyeksi Equal-Area EPSG:6933**:

$$\text{AOI\_Intersection\_Area} = \text{Area}(\text{Cell} \cap \text{AOI}_{\text{WPP573}})$$

### Hasil Rekonsiliasi Luas Total:
- **Luas Poligon Master WPPNRI 573 (EPSG:6933):** **`947,214.20 km²`**
- **Jumlah Luas Interseksi Sel Grid 0,10°:** **`947,214.20 km²`**
- **Selisih Luas Interseksi vs Master:** **`-0.0 km²`** ($< 0,01\%$).

**Kesimpulan:** Selisih luas sepenuhnya terselesaikan (*mathematically reconciled*). Tidak ada lagi sel yang terhitung ganda (*duplicate counted*) atau celah (*gap*).

---

## 2. REKAPITULASI KLASIFIKASI SEL KISI (GRID TAXONOMY)

| Parameter Kisi | Macro Grid (0,25°) | Regional Grid (0,10°) | Prigi Regional Sub-AOI (0,05°) |
|---|---|---|---|
| **Prefix ID** | `WPP573_025` | `WPP573_R010` *(Standar Makro)* | `WPP573_PRIGI005` *(Standar Mikro)* |
| **Resolusi Spasial** | $0,25^\circ$ ($\sim 27,8\text{ km}$) | **$0,10^\circ$ ($\sim 11,0\text{ km}$)** | **$0,05^\circ$ ($\sim 5,5\text{ km}$)** |
| **Dimensi (Kolom x Baris)** | $96 \times 29$ | **$240 \times 72$** | **$40 \times 49$** |
| **Total Sel Bounding Box** | 2,784 sel | **17,280 sel** | **1,960 sel** |
| **Sel Interior Laut Murni (`PURE_OCEAN`)** | 1,088 sel | **7,381 sel** | **1,663 sel** |
| **Sel Batas Pesisir (`COASTAL_BOUNDARY`)** | 121 sel | **334 sel** | **50 sel** |
| **Sel Batas Luar ZEE (`OUTER_EEZ_BOUNDARY`)**| 127 sel | **326 sel** | **0 sel** |
| **Total Sel Aktif Analisis Kelautan** | **1,336 sel** | **8,041 sel** | **1,713 sel** |
| **Sel Daratan Dikeluarkan (`LAND_EXCLUDED`)** | 79 sel | **208 sel** | **11 sel** |

---

## 3. PEMISAHAN DEFINISI ATRIBUT WAJIB SEL KISI

Sesuai mandat R5.3, setiap sel kisi memiliki atribut matematis yang terpisah secara tegas:
1. **`aoi_intersection_area_km2`:** Luas perpotongan poligon sel dengan poligon batas WPPNRI 573.
2. **`aoi_intersection_fraction`:** Rasio $\frac{\text{aoi\_intersection\_area\_km2}}{\text{cell\_area\_km2}} \in [0,0, 1,0]$.
3. **`ocean_fraction`:** Rasio permukaan perairan laut murni di dalam sel.
4. **`land_fraction`:** Rasio permukaan daratan pulau di dalam sel ($1,0 - \text{ocean\_fraction}$).

---

## 4. PERBAIKAN NOMENKLATUR SUB-AOI (R5.7)

Sesuai arahan mutlak R5.7:
1. Nama ID diubah dari `GRID_005_PRIGI` menjadi **`GRID_005_PRIGI_REGIONAL`**.
2. Istilah *"Prigi Fleet Domain"* atau *"Fishing Corridor"* **DIHAPUS SECARA MENYELURUH** dan diganti dengan istilah ilmiah yang valid: **`Prigi Regional Sub-AOI`**.
3. Penegasan Ilmiah: Wilayah bujur $111,00^\circ\text{ s.d. } 113,00^\circ\text{ BT}$ dan lintang $-10,45^\circ\text{ s.d. } -8,00^\circ\text{ LS}$ adalah **Jendela Analisis Oseanografi Regional Beresolusi Tinggi di sekitar Teluk Prigi**, BUKAN daerah penangkapan eksklusif armada nelayan lokal tanpa verifikasi Vessel Monitoring System (VMS) atau logbook pendaratan koordinat riil.

---

## 5. DUKUNGAN RESOLUSI & LIMITASI INFORMASI FISIK (R5.6)

> [!CAUTION]
> **Larangan Klaim Semu Resolusi:**
> Menyimpan data OISST (native 0,25°) atau CFSv2 (native 0,20°) ke dalam sel kisi 0,10° atau 0,05° **TIDAK MENINGKATKAN KANDUNGAN INFORMASI FISIK PRODUK ASAL**. Interpolasi spasial bilinear menghasilkan tampilan spasial yang kontinu dan halus, namun batas deteksi struktur dinamis laut tetap dibatasi oleh resolusi bawaan sensor ($27,8\text{ km}$ untuk OISST).
