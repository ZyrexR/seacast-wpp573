# AOI REVALIDATION REPORT — INDEPENDENT TRIPLE-METHOD AUDIT
## SEACAST PRIGI — Wilayah Pengelolaan Perikanan Negara Republik Indonesia (WPPNRI) 573

- **Document ID:** `AOI-REVAL-01`
- **Phase:** Phase 0.3 — Independent AOI Geometry Revalidation
- **Evaluation Date:** 2026-11-02
- **Audit Mandate:** Independent verification across three separate computational libraries/methods (Pure Python, Shapely 2.2 / GEOS 3.14, pyogrio 0.13 / GDAL 3.12.4).
- **Gate A Recommendation:** **`PASS`** (All discrepancies fully investigated and resolved).

---

## 1. Hasil Audit Geometri & Metrik Spasial Rekalkulasi

Pemeriksaan independen dijalankan terhadap file kanonis:
* File Sumber: [`data/aoi/raw/WPP573_BIG_ORIGINAL.geojson`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/aoi/raw/WPP573_BIG_ORIGINAL.geojson)
* SHA-256: `4e2b16197b0d48f350a488ad0a9c076691cf757e78ae855ae117d63d0e53c669`
* Master File: [`data/aoi/processed/AOI_WPP573_MASTER.geojson`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/aoi/processed/AOI_WPP573_MASTER.geojson)

| Parameter Geometri | Nilai Hasil Rekalkulasi | Metode Verifikasi | Status |
| :--- | :---: | :---: | :---: |
| **Driver / Format** | GeoJSON (RFC 7946) | pyogrio 0.13.0 / GDAL 3.12.4 | `VERIFIED` |
| **CRS OGR Driver** | `EPSG:4326` (WGS 84) | GDAL SpatialReference | `VERIFIED` |
| **Feature Count** | `1` Fitur Tunggal | Pure-Python, Shapely, GDAL | `VERIFIED` |
| **Tipe Geometri** | `MultiPolygon` | Pure-Python, Shapely, GDAL | `VERIFIED` |
| **Komponen Poligon** | `2` Bagian Utama | Shapely (`len(geoms)`) | `VERIFIED` |
| **Interior Rings (Enclaves/Islands)** | `1.400` Cincin Dalam | Pure-Python parsing & Shapely | `VERIFIED` |
| **Jumlah Simpul Vertices** | `625.698` Simpul Koordinat | Shapely `get_num_coordinates` | `VERIFIED` |
| **Topological Validity (`is_valid`)** | `True` (`Valid Geometry`) | GEOS / Shapely `explain_validity` | `PASS` |
| **Ring Closure** | `True` (0 Open Rings) | Pure-Python first-last check | `PASS` |
| **Self-Intersection** | `0` (Tidak Ada) | GEOS Topology Engine | `PASS` |
| **Degenerate Rings (< 4 koordinat)** | `0` pada File Master/Raw | Pure-Python length check | `PASS` |
| **Need Geometry Repair?** | **`False` (Tidak Perlu Koreksi)** | Original Raw Geometry Valid | `PASS` |

---

## 2. Investigasi Mendalam Perbedaan Koordinat (Discrepancy Investigation)

### A. Tabel Perbandingan Nilai Koordinat

| Parameter Batas Spasial | Nilai Geometri Aktual (M1, M2, M3) | Angka Discrepancy yang Dikutip di SPAT-DES-05 | Selisih Absolut | Sumber Akar Masalah |
| :--- | :---: | :---: | :---: | :--- |
| **Batas Barat (Min Lon)** | **`102,577830° BT`** | `105,132141° BT` | `2,554311°` | Template Literal Hardcoded di `scratch/run_aoi_validation.py:284` |
| **Batas Timur (Max Lon)** | **`126,499579° BT`** | `129,544498° BT` | `3,044919°` | Template Literal Hardcoded di `scratch/run_aoi_validation.py:284` |
| **Batas Selatan (Min Lat)** | **`-13,938231° LS`** | `-13,784534° LS` | `0,153697°` | Template Literal Hardcoded di `scratch/run_aoi_validation.py:284` |
| **Batas Utara (Max Lat)** | **`-6,801796° LS`** | `-7,300000° LS` | `0,498204°` | Template Literal Hardcoded di `scratch/run_aoi_validation.py:284` |

### B. Temuan Forensik Sumber Kesalahan
1. **Bukan Perubahan Geometri:** Geometri asli dari BIG MapServer Layer 21 sejak awal memiliki bounding box `[102.577830, -13.938231, 126.499579, -6.801796]`. Ketiga metode independen (M1, M2, M3) mengonfirmasi nilai yang persis sama hingga 6 angka di belakang koma.
2. **Asal Usul Angka Discrepancy:**
   Angka `[105.132141, -13.784534, 129.544498, -7.300000]` terlacak berasal dari teks statis pada skrip generator lama (`scratch/run_aoi_validation.py` baris 284) di mana penulis skrip sebelumnya memasukkan angka perkiraan koordinat manual ke dalam kolom keterangan format tabel markdown, yang kemudian tersalin ke laporan ringkasan.
3. **Resolusi Kebenaran Komputasional:**
   Sumber kebenaran komputasional tunggal yang sah dan terverifikasi untuk WPPNRI 573 adalah hasil kalkulasi langsung dari file geometri:
   $$\text{BBox WPPNRI 573} = [102,577830^\circ\text{BT},\; -13,938231^\circ\text{LS},\; 126,499579^\circ\text{BT},\; -6,801796^\circ\text{LS}]$$

---

## 3. Rekalkulasi Luas & Perimeter Geodesik

| Parameter Luas / Ukuran | Nilai Rekalkulasi | Metode & CRS |
| :--- | :---: | :--- |
| **Luas Geodesik WGS 84** | **`933.750,90 km²`** | `pyproj.Geod(ellps='WGS84')` (Algoritma Karney 2013) |
| **Luas Equal-Area EPSG:6933** | **`933.749,49 km²`** | EASE-Grid 2.0 Global Equal Area (Planar Shapely) |
| **Selisih Relatif Antar-Metode** | **`-0,000151%`** | Konsistensi matematis luar biasa (< 0,001%) |
| **Keliling Perimeter Seluruh Ring** | **`12.633,74 km`** | Geodesik WGS 84 (Mencakup 1.400 pulau/enclave) |
| **Luas Total Cincin Dalam (Holes)** | **`13.988,28 km²`** | Luas daratan kepulauan yang dikeluarkan dari poligon |

---

## 4. Evaluasi File Turunan & Web Preview
* **`data/boundaries/wppnri_573_big.geojson`:** Identik `100%` secara geometris (`equals_exact = True`, selisih luas `0,000000%`).
* **`data/boundaries/wppnri_573_web.geojson`:** Memiliki 1.363 cincin degenerasi akibat generalisasi kartografis web yang terlampau agresif. Telah diisolasi salinan perbaikannya di [`data/spatial_reconstruction/aoi_qc/wppnri_573_web_DEGENERATE_RINGS_DROPPED.geojson`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/spatial_reconstruction/aoi_qc/wppnri_573_web_DEGENERATE_RINGS_DROPPED.geojson) khusus untuk preview antarmuka tanpa merusak file master.
* **`AOI_WPP573_SIMPLIFIED_0p01deg_FOR_GEE.geojson`:** Poligon outer shell teroptimasi untuk query GEE server-side (`809` simpul) tanpa beban komputasi 1.400 lubang interior.

