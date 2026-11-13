# GEE PRODUCTION EXECUTION GUIDE
## Langkah Operasional Pengujian, Konfigurasi Aset, dan Pembekuan Produksi

---

## 1. KONFIGURASI PARAMETER & MODE EKSEKUSI

Dalam file `gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025.js`, seluruh kendali operasional diatur di bagian awal (`MODULE 01`):

```javascript
EXECUTION: {
  MODE: "DEVELOPMENT",               // Tetap "DEVELOPMENT" selama pengujian
  ENABLE_FULL_BATCH: false,          // Jangan diubah ke true tanpa izin tertulis
  ENABLE_PRODUCTION_EXPORT: false,   // Jangan diubah ke true tanpa izin tertulis
  ENABLE_ASSET_WRITE: false,         // Jangan diubah ke true tanpa izin tertulis
  TEST_YEAR: 2024,
  TEST_MONTH: 4                      // Bulan uji coba default (April 2024)
}
```

### Mengubah Bulan Inspeksi Uji Coba:
Untuk memeriksa bulan lain pada jendela peta interaktif:
1. Ubah nilai `TEST_YEAR` (antara 2015 hingga 2025).
2. Ubah nilai `TEST_MONTH` (1 hingga 12).
3. Klik **Run**.

---

## 2. INTEGRASI ASSET GEE (UNTUK TAHAP FULL PRODUCTION)

Untuk menjalankan agregasi skala penuh terhadap poligon Master AOI dan 8.129 sel kisi:
1. **Unggah Master AOI:**
   - File: `data/aoi/processed/AOI_WPP573_MASTER.shp` (+ dbf, shx, prj).
   - Asset Destination: `projects/seca-513505/assets/AOI_WPP573_MASTER`.
2. **Unggah Grid 8.129 Sel:**
   - File: `data/qa/phase_1_1/grid/GRID_010_REGIONAL_CELLS_QA.csv`.
   - Asset Destination: `projects/seca-513505/assets/GRID_010_REGIONAL_8129`.
3. **Unggah CMEMS NetCDF (Bulan 9–12 2024 & 2025):**
   - Unduh melalui Copernicus Marine Service CLI.
   - Konversi ke GeoTIFF dan unggah ke: `projects/seca-513505/assets/cmems_phy_anfc`.

---

## 3. PROTOKOL AKTIVASI EKSPOR FULL BATCH (PHASE 5)

Ekspor massal 132 bulan **DILARANG DIJALANKAN** sebelum Peneliti memberikan persetujuan tertulis resmi.
Setelah persetujuan diperoleh, aktivasi dilakukan secara terkontrol:
1. Ubah `ENABLE_FULL_BATCH: true`.
2. Ubah `ENABLE_PRODUCTION_EXPORT: true`.
3. Jalankan naskah dan konfirmasi *Task* ekspor pada tab **Tasks** di Code Editor.
