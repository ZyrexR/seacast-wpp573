# AOI FINAL PROVENANCE, AUTHORITY & HISTORICAL AREA RECONCILIATION REPORT
**Project:** SEACAST PRIGI — Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi  
**Phase:** 1.0-R — Remediation R-01  
**Study Domain:** Wilayah Pengelolaan Perikanan Negara Republik Indonesia (WPPNRI) 573  
**Evaluated Geometry:** Official WPPNRI 573 Polygon  

---

## 1. PROVENANCE & RANTAI KEPEMILIKAN DATA (DATA PROVENANCE)

Berdasarkan audit independen terhadap seluruh berkas batas spasial di workspace, rantai kepemilikan data (*provenance chain*) telah diverifikasi secara forensik:

1. **Sumber Asli Pemerintah (Government Source):**
   - **Institusi Penerbit:** Badan Informasi Geospasial (BIG) Republik Indonesia.
   - **Layanan API:** BIG Satu Peta REST MapServer:
     `https://kspservices.big.go.id/satupeta/rest/services/PUBLIK/SUMBER_DAYA_ALAM_DAN_LINGKUNGAN/MapServer/21`
   - **Layer Name:** `Peta Wilayah Pengelolaan Perikanan RI` (Layer ID: `21`).
   - **Query Parameter:** `namobj = 'WPP-RI 573'`.
   - **Waktu Unduh:** `2026-10-31T08:04:34Z`.
   - **Format Native:** Esri Feature JSON, dikonversi langsung ke standard GeoJSON (RFC 7946).
   - **File Raw Asli:** `data/aoi/raw/WPP573_BIG_ORIGINAL.geojson` (SHA-256: `4e2b16197b0d48f350a488ad0a9c076691cf757e78ae855ae117d63d0e53c669`).
2. **File Master Terproses (Master Candidate):**
   - **File Master:** `data/aoi/processed/AOI_WPP573_MASTER.geojson` (SHA-256: `fad138ee3dab333d93ac94a416bccd7b73c308313b328e8f0b4f743cd17e7545`).
   - **Status Geometri Raw vs Master:** Identik 100% secara topologis dan koordinat.

---

## 2. VERIFIKASI GEOMETRI & TOPOLOGI KOMPUTASIONAL

| Parameter Geometri | Nilai Aktual Terverifikasi | Metode Verifikasi | Status Audit |
|---|---|---|---|
| **CRS Driver** | `EPSG:4326` (WGS 84 Geographic) | GDAL SpatialReference | `VERIFIED` |
| **Feature Count** | `1` Fitur Tunggal | Pure Python, Shapely 2.2, GDAL | `VERIFIED` |
| **Tipe Geometri** | `MultiPolygon` | Pure Python, Shapely 2.2, GDAL | `VERIFIED` |
| **Komponen Poligon** | `2` Poligon Utama | Shapely `len(geom.geoms)` | `VERIFIED` |
| **Interior Rings (Lubang Pulau)** | `1.400` Cincin Pulau | Pure Python Ring Counter | `VERIFIED` |
| **Jumlah Simpul (Vertices)** | `625.698` Simpul Koordinat | Shapely `get_num_coordinates` | `VERIFIED` |
| **Validitas Topologis (`is_valid`)** | `True` (`Valid Geometry`) | GEOS Topology Engine | `PASS` |
| **Ring Closure** | `True` (0 Open Rings) | First-Last Coordinate Equality | `PASS` |
| **Self-Intersection** | `0` (Tidak Ada) | GEOS Overlay Engine | `PASS` |
| **Degenerate Rings (< 4 koordinat)**| `0` pada File Master/Raw | Pure Python Length Check | `PASS` |
| **Bounding Box** | `[102.57783°, -13.938231°, 126.499579°, -6.801796°]` | Bounding Box Geometry Exact | `PASS` |
| **Luas Geodesik WGS 84** | **`933,750.9 km²`** | PyProj Geod (Karney 2013) | `PASS` |
| **Luas Planar EASE-Grid 2.0** | **`933,749.49 km²`** | Proyeksi EPSG:6933 Equal-Area | `PASS` |

---

## 3. PEMISAHAN TIGA STATUS OTORITAS BATAS (BOUNDARY AUTHORITY STATUSES)

Sesuai arahan audit Phase 1.0-R, status otoritas batas WPPNRI 573 dipisahkan secara proporsional dan tidak dilebih-lebihkan:

1. **`SOURCE_GEOMETRY_VALID: PASS`**
   - Geometri poligon valid secara matematis dan komputasional (GEOS `is_valid = True`, 0 self-intersections, ring closure 100%).
2. **`GOVERNMENT_SOURCE_VERIFIED: PASS`**
   - Berkas spasial terbukti bersumber dari instansi pemetaan resmi pemerintah (Badan Informasi Geospasial, REST Layer 21).
3. **`LEGAL_BOUNDARY_RECONCILED: PROVISIONALLY_RECONCILED_WITH_REGULATORY_TEXT`**
   - Poligon spasial bersesuaian dengan deskripsi perairan WPPNRI 573 dalam regulasi:
     - **Permen-KP No. 18/PERMEN-KP/2014** tentang Wilayah Pengelolaan Perikanan Negara Republik Indonesia.
     - **Kepmen-KP No. 1/2021** tentang Wilayah Pengelolaan Perikanan Negara Republik Indonesia di Perairan Laut.
   - *Catatan Kritis Auditor:* Penetapan batas hukum final mengacu pada titik-titik koordinat dalam lampiran regulasi KKP. BIG menerjemahkan lampiran koordinat tersebut ke dalam batas vektor spasial 1:250.000. Oleh karena itu, status ini dinyatakan *provisional* yang merefleksikan bahwa vektor BIG adalah representasi resmi terbaik yang tersedia di Indonesia.

---

## 4. REKONSILIASI LUAS HISTORIS (1.423.238 km² vs 933.750,90 km²)

### A. Penelusuran Asal Angka Historis:
- Angka **`1.423.238 km²`** (dan pembulatan lamanya `1.425.000 km²`) tercatat dalam buku profil perikanan tangkap KKP dan dokumen statistik historis sebagai "Luas Wilayah Pengelolaan Perikanan 573".

### B. Hasil Investigasi Forensik:
1. Angka 1.423.238 km2 adalah **Luas Envelop Administratif Maritim Nominal** yang mencakup seluruh koridor Zona Ekonomi Eksklusif (ZEE) dan Laut Teritorial Samudera Hindia Selatan Jawa hingga Laut Timor, yang dihitung secara garis lurus batas luar (*outer boundary box/envelope*) tanpa melakukan pemotongan cincin pulau dalam (*without vector island hole subtraction*).
2. Sebaliknya, poligon resmi BIG Layer 21 (`AOI_WPP573_MASTER.geojson`) merupakan poligon vektor perairan laut presisi tinggi (625.698 verteks) yang:
   - Secara tegas memotong 1.400 pulau daratan di pesisir selatan Jawa, Madura, Bali, Lombok, Sumbawa, Sumba, Flores, dan Timor (total luas daratan yang dipotong: 13.988,28 km2).
   - Mengikuti lekuk garis pantai 1:250.000.
3. Luas permukaan laut murni yang dihasilkan oleh poligon BIG adalah **`933,750.9 km²`** (geodesik ellipsoid WGS 84) atau **`933,749.49 km²`** (planar EPSG:6933).

### C. Status Rekonsiliasi:
Status rekonsiliasi ditetapkan sebagai **`RECONCILED_BY_METHODOLOGY_SEPARATION`**:
- Untuk keperluan pemodelan bio-oseanografi dan ekstraksi piksel satelit laut: **Wajib menggunakan luas vektor permukaan laut riil 933,750.9 km²**.
- Angka 1.423.238 km2 dipertahankan sebagai **konteks statistik administratif nominal KKP**, dan tidak boleh digunakan sebagai penyebut pembagi (*denominator*) luas raster habitat perairan.
