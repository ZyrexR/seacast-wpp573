# SEACAST PRIGI — GEE PRODUCTION SCRIPT (2015–2025)
## Panduan Teknis & Arsitektur Perangkat Lunak Earth Engine

- **File Utama:** [`gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025.js)
- **Target Platform:** Google Earth Engine JavaScript API (Code Editor)
- **GCP Project:** `seca-513505`
- **Study Area:** Seluruh WPPNRI 573 ($933.749,49	ext{ km}^2$)
- **Study Period:** 132 Bulan Kalender (Januari 2015 – Desember 2025)
- **Target Grid:** `WPP573_R010` ($8.129$ sel laut aktif)

---

## 1. STRUKTUR MODULAR KODE

Naskah kode dibangun secara modular mengikuti kaidah arsitektur sistem informasi geospasial kelautan modern:
- **`MODULE 01 — PROJECT CONFIGURATION`:** Parameter terpusat, kontrol batas eksekusi (`EXECUTION_MODE = "DEVELOPMENT"`, `ENABLE_FULL_BATCH = false`), konstanta fisik, dan palet warna visualisasi.
- **`MODULE 02 — AOI AND GRID INITIALIZATION`:** Pemuatan Master AOI WPPNRI 573 dan grid 8.129 sel secara terverifikasi.
- **`MODULE 03 — TEMPORAL DATA PROCESSING`:** Generator rentang waktu 132 bulan kalender dan penanganan transisi kalender.
- **`MODULE 04 — OCEANOGRAPHIC PARAMETERS`:** Mesin komputasi fisik: SST (OISST v2.1), Thermal Front Sobel (°C/km), Klorofil-a (MODIS Aqua + GCOM-C), Angin & Ekman (CFSv2 Harmonized), Arus Permukaan (HYCOM/CMEMS), dan Batimetri (ETOPO1).
- **`MODULE 05 — SPATIAL GRID AGGREGATION`:** Pereduksi spasial `reduceRegions` untuk mengekstraksi statistik per-sel grid 0.10°.
- **`MODULE 06 — QUALITY CONTROL & MISSING DATA`:** Klasifikasi status QA dan penanganan transparan data hilang.
- **`MODULE 07 — INTERACTIVE VISUALIZATION`:** Panel kontrol interaktif UI, visualisasi layer dinamis, legenda, dan penanda Pelabuhan Perikanan Nusantara (PPN) Prigi.
- **`MODULE 08 — EXPORT PREPARATION`:** Konfigurasi task `Export.table.toDrive` yang terkunci di belakang sakelar keamanan produksi.

---

## 2. CARA MEMBUKA DAN MENJALANKAN KODE DI GEE CODE EDITOR

1. Salin seluruh isi file [`gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025.js).
2. Buka peramban dan akses Google Earth Engine Code Editor: `https://code.earthengine.google.com/`.
3. Pastikan proyek Cloud aktif adalah **`seca-513505`** (dapat diatur dari pemilih proyek di pojok kanan atas).
4. Tempelkan kode ke dalam jendela editor naskah (*Script Editor*).
5. Klik tombol **Run**.
6. Secara default, kode akan berjalan dalam mode `DEVELOPMENT` untuk bulan inspeksi contoh (April 2024). Peta akan berpusat otomatis pada WPPNRI 573 dan menampilkan layer SST, Klorofil-a, Angin, Front Termal, dan Batimetri disertai panel informasi interaktif.
