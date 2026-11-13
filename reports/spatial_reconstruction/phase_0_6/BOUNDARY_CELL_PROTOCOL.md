# BOUNDARY CELL & COASTAL MASKING PROTOCOL
**Project:** SEACAST PRIGI — WPPNRI 573  
**Phase:** 0.6 — Boundary Protocols  
**Standardized Reference:** BIG Layer 21 Official Geometry & ETOPO1 Topography  

---

## 1. KLASIFIKASI SEL KISI (GRID CELL TYPOLOGY)

Setiap sel kisi pada WPPNRI 573 diklasifikasikan ke dalam 4 kategori hierarkis berdasarkan relasi spasial terhadap poligon master `AOI_WPP573_MASTER.geojson`:

```mermaid
flowchart TD
    Cell["Grid Cell Candidate"] --> BBoxCheck{"Intersects Bounding Box?"}
    BBoxCheck -->|No| Excluded["OUTSIDE_AOI (Excluded)"]
    BBoxCheck -->|Yes| GeomCheck{"Topological Intersection with Master AOI?"}
    
    GeomCheck -->|No| Excluded
    GeomCheck -->|Yes| InteriorCheck{"Polygon Contains Cell (100% Water)?"}
    
    InteriorCheck -->|Yes| PureOcean["PURE_OCEAN (Active, 100% Valid)"]
    InteriorCheck -->|No| BoundaryCheck{"Water Fraction >= 15%?"}
    
    BoundaryCheck -->|Yes| CoastalActive["COASTAL_BOUNDARY (Active with Land-Mask Weighting)"]
    BoundaryCheck -->|No (< 15% water)| LandExcluded["LAND_DOMINATED (Excluded from Marine Analysis)"]
```

### Kriteria Kategori:
1. **PURE_OCEAN ($W_f = 1.0$):**
   - Sel sepenuhnya berada di dalam poligon laut WPPNRI 573.
   - Jarak ke pantai $> 10\text{ km}$.
   - Semua variabel oseanografi dihitung secara murni tanpa koreksi darat.
2. **COASTAL_BOUNDARY ($0.15 \le W_f < 1.0$):**
   - Sel memotong garis pantai Jawa, Bali, Lombok, Sumbawa, Sumba, Flores, atau Timor.
   - Memiliki fraksi perairan laut minimal **15%** ($W_f \ge 0.15$).
   - Dilakukan pembobotan spasial (*area-weighted averaging*) menggunakan mask perairan murni untuk mencegah bias nilai darat (misalnya suhu darat yang lebih panas atau batimetri darat).
3. **LAND_DOMINATED ($W_f < 0.15$):**
   - Sel yang sebagian besar terdiri dari daratan pulau (>85% darat).
   - Sel ini di-*mask* (dikecualikan) dari ekstraksi fitur oseanografi agar tidak merusak komputasi statistik laut.
4. **OUTER_EEZ_BOUNDARY:**
   - Sel yang memotong batas luar ZEE Indonesia dengan Samudera Hindia dan batas perairan Australia.
   - Dipertahankan 100% sebagai sel aktif analisis.

---

## 2. PROTOKOL PENANGANAN SEL PESISIR (COASTAL RESAMPLING & ANTI-CONTAMINATION)

Untuk mencegah kontaminasi nilai daratan pada produk bersensor optik dan gelombang mikro:

1. **SST Pesisir (OISST V2.1):**
   - Sel pantai yang terkena land-mask OISST native diisi menggunakan interpolasi spasial *inverse distance weighting* (IDW) 4 titik dari sel laut terdekat, atau menggunakan band SST MODIS 1 km jika tersedia dan bebas awan.
2. **Klorofil-a Pesisir (MODIS / GCOM-C):**
   - Dilarang mengekstrapolasi klorofil-a dari darat.
   - Hanya piksel dengan kedalaman laut $> 5\text{ m}$ (ETOPO1) yang diikutsertakan untuk menghindari sedimen tersuspensi keruh (*turbid Case-2 coastal water flag*).
3. **Angin Pesisir (CFSV2):**
   - Menggunakan angin 10m dengan reduksi gesekan pantai. Diterapkan koreksi batas pesisir (*coastal roughness dampening*).
