# AOI SIMPLIFICATION ERROR & COASTAL TOPOLOGY AUDIT
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** AOI-SIMP-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Auditor:** Senior Marine Geospatial Scientist & GIS Data Quality Engineer  
**Date:** 2026-11-02  

---

## 1. IDENTIFIKASI GEOMETRI SIMPLIFIKASI

Dalam repository teridentifikasi file:
`data/spatial_reconstruction/aoi_qc/AOI_WPP573_SIMPLIFIED_0p01deg_FOR_GEE.geojson`

File ini dibuat dengan tujuan mereduksi jumlah simpul (*vertex reduction*) dari 625.698 titik menjadi 4.298 titik agar dapat diunggah ke Google Earth Engine tanpa melebihi batas kuota memori request API (< 10 MB).

Namun, proses simplifikasi tersebut menerapkan filter `polygon.exterior` tanpa merekonstruksi cincin interior (*interior rings*).

---

## 2. AUDIT KESALAHAN GEOMETRI & TOPOLOGI

| Parameter Audit | Nilai Master Asli | Nilai Simplifikasi | Kesalahan / Deviasi | Dampak Ilmiah |
|---|---:|---:|---:|---|
| **Keberadaan Lubang Pulau** | 1.400 lubang | **0 lubang** | **Hilang 1.400 pulau** | **KRITIS:** Daratan pulau berubah menjadi perairan laut. |
| **Luas Area (EPSG:6933)** | 933.749,49 km² | 947.214,20 km² | **+13.464,71 km² (+1,44%)** | Overestimasi luas perairan penangkapan ikan. |
| **Panjang Garis Pantai** | 25.266,49 km | 7.854,12 km | **-17.412,37 km (-68,9%)** | Seluruh teluk, selat sempit, dan estuari terdistorsi. |
| **Komponen Poligon** | 2 MultiPolygon | 1 Polygon tunggal | Hilang 1 komponen | Selat Sape / Laut Sawu terpotong secara topologi. |
| **Symmetric Difference Area**| Baseline (0 km²) | Terdistorsi | **13.892,15 km²** | Deviasi spasial nyata antara kedua batas. |

---

## 3. ATURAN PENGGUNAAN GEOMETRI SIMPLIFIKASI (STRICT GOVERNANCE RULE)

> [!CAUTION]
> **Larangan Analisis Spasial Kelautan Menggunakan Geometri Simplifikasi:**
> Geometri `AOI_WPP573_SIMPLIFIED_0p01deg_FOR_GEE.geojson` **DILARANG KERAS** digunakan untuk:
> 1. Penghitungan luas interseksi sel kisi (*grid intersection*).
> 2. Penentuan fraksi laut vs daratan (*ocean vs land fraction*).
> 3. Masking akhir produk spasial oseanografi.
> 
> **PENGGUNAAN SATU-SATUNYA YANG DIIZINKAN:**
> Geometri simplifikasi HANYA diizinkan sebagai **Spatial Query Envelope** (filter bounding spatial saat memanggil `ee.ImageCollection.filterBounds()`) untuk mempercepat transmisi query ke server Google Earth Engine.
> Seluruh proses pemotongan akhir (*raster clipping* / *zonal statistics*) wajib menggunakan masker poligon master asli `AOI_WPP573_MASTER.geojson` atau masker daratan global Copernicus / GSHHG beresolusi tinggi.
