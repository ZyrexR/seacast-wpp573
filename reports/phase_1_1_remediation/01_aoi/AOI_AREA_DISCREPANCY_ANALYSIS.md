# AOI AREA DISCREPANCY & GEOMETRIC RECONCILIATION REPORT
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** AOI-DISC-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Auditor:** Senior Marine Geospatial Scientist & Independent SQA Auditor  
**Date:** 2026-11-02  

---

## 1. EXECUTIVE SUMMARY & FORENSIC DISCOVERY

Laporan PHASE 1.0-R menampilkan ambiguitas angka luas master yang menjadi temuan kritis audit:
1. `933.750,90 km²` (Luas Geodesik WGS84 pada R-01).
2. `933.749,49 km²` (Luas Equal-Area EPSG:6933 pada R-01 dan baseline Phase 4.1.1).
3. `947.214,20 km²` (Luas Equal-Area EPSG:6933 pada R-05).

Audit forensik independen Phase 1.1 mengungkap **PENYEBAB PASTI KETIDAKKONSISTENAN TERSEBUT**:

### Temuan Forensik Inti:
- **`933.749,49 km²`** adalah luas proyeksi Equal-Area EPSG:6933 dari **`AOI_WPP573_MASTER.geojson`** (dan `wppnri_573_big.geojson`), yang memotong secara presisi seluruh **1.400 lubang pulau daratan** seluas **13.464,71 km²**.
- **`933.750,90 km²`** adalah luas integral geodesik elipsoid WGS84 dari file master yang sama. Deviasi antara luas geodesik dan luas proyeksi EPSG:6933 hanyalah **1,41 km² (0,00015%)**, yang membuktikan bahwa proyeksi NSIDC EASE-Grid 2.0 Global Equal-Area bekerja secara sempurna pada domain khatulistiwa/lintang selatan WPPNRI 573.
- **`947.214,20 km²`** muncul pada R-05 karena skrip `run_r05_grid_recomputation.py` **MEMUAT FILE YANG SALAH**, yaitu file simplifikasi `AOI_WPP573_SIMPLIFIED_0p01deg_FOR_GEE.geojson`. File simplifikasi tersebut **TELAH MENGHILANGKAN SELURUH 1.400 LUBANG PULAU (0 HOLES)** dan hanya menyisakan kulit luar (*outer shell*), sehingga daratan pulau Jawa dan Nusa Tenggara terhitung palsu sebagai perairan laut!

$$\text{{Luas Outer Shell (Simplified)}} = 947.214,20\text{{ km}}^2$$
$$\text{{Luas Master Bersih (1.400 Holes)}} = 933.749,49\text{{ km}}^2$$
$$\Delta \text{{Area (Daratan Pulau Terinklusi Palsu)}} = +13.464,71\text{{ km}}^2$$

---

## 2. TABEL PERBANDINGAN INDEPENDEN METRIK GEOMETRI

| Metrik Geometri | Laporan Terdahulu (R-01) | R-05 (Simplified) | Hasil Independen Master (Phase 1.1) | Selisih (Master vs R-05) | Penjelasan Saintifik |
|---|---:|---:|---:|---:|---|
| **Luas Geodesik WGS84** | $933.750,90	ext{ km}^2$ | $947.507,10	ext{ km}^2$ | **`933.750,90 km²`** | $-13.756,20	ext{ km}^2$ | Integral geodesik elipsoid WGS84 Karney (PyProj Geod). |
| **Luas Equal-Area EPSG:6933** | $933.749,49	ext{ km}^2$ | $947.214,20	ext{ km}^2$ | **`933.749,49 km²`** | **`-13.464,71 km²`** | Proyeksi planar EASE-Grid 2.0 Global Equal-Area presisi. |
| **Jumlah Lubang Interior (Pulau)** | $1.400$ lubang | $0$ lubang | **`1.400 lubang`** | **`+1.400 lubang`** | File R-05 kehilangan seluruh lubang pulau; Master mempertahankan 100%. |
| **Komponen Poligon Eksterior** | $2$ komponen | $1$ komponen | **`2 komponen`** | $+1$ komponen | Domain utama samudra dan kantung perairan Laut Sawu. |
| **Total Titik Sudut (*Vertices*)**| $625.698$ | $4.298$ | **`625.698 titik`** | $+621.400$ titik | Resolusi kartografis asli Badan Informasi Geospasial (BIG). |
| **Keliling Geodesik (*Perimeter*)**| $25.266,49	ext{ km}$ | $7.854,12	ext{ km}$ | **`25.266,49 km`** | $+17.412,37	ext{ km}$ | Penurunan akibat hilangnya garis pantai 1.400 pulau. |
| **Bounding Box Bujur** | $[102,50^\circ, 126,50^\circ]$ | $[102,50^\circ, 126,50^\circ]$ | **`[102,5000°, 126,5000°]`** | $0,00^\circ$ | Batas jangkauan longitudinal identik. |
| **Bounding Box Lintang** | $[-14,00^\circ, -6,75^\circ]$ | $[-14,00^\circ, -6,75^\circ]$ | **`[-14,0000°, -6,7500°]`** | $0,00^\circ$ | Batas jangkauan latitudinal identik. |

---

## 3. KEPUTUSAN REMEDIASI KELAYAKAN LUAS

1. **Angka Baseline Resmi Disahkan:**
   - **Luas Geodesik WGS84 Resmi:** **`933.750,90 km²`**
   - **Luas Equal-Area EPSG:6933 Resmi:** **`933.749,49 km²`**
   - Toleransi perbedaan proyeksi vs geodesik: **`1,41 km² (0,00015%)`** (Sangat presisi / *Scientifically Reconciled*).
2. **Larangan Mutlak Penggunaan Angka $947.214,20	ext{ km}^2$:**
   - Angka $947.214,20	ext{ km}^2$ dinyatakan **BATAL DAN TIDAK SAH** sebagai baseline luas perairan laut, karena mengikutsertakan daratan pulau besar (Jawa, Bali, Lombok, Sumbawa, Flores, Sumba, Timor) yang seharusnya dieksklusi dari domain perikanan laut.
