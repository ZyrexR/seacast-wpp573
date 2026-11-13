# GEE PIPELINE KNOWN LIMITATIONS & ECOLOGICAL BOUNDARIES
**Project:** SEACAST PRIGI — Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi  
**Document ID:** GEE-LIMIT-01  
**Status:** MANDATORY GOVERNANCE RESTRICTIONS  

---

## 1. KETERBATASAN DATA SATELLITE & SENSOR TRANSITION

1. **Terminasi HYCOM & Ketiadaan Ingesti CMEMS:**
   - Model global HYCOM menghentikan layanan operasional per 5 September 2024.
   - Variabel arus laut untuk September 2024 s/d Desember 2025 bergantung pada ingesti data Copernicus Marine Service (CMEMS).
   - Selama aset CMEMS belum diunggah ke private asset GEE proyek `seca-513505`, maka seluruh sel arus laut pada bulan-bulan tersebut disajikan sebagai `NaN` (masked) dengan flag `SENSOR_FALLBACK_MISSING_ASSET`.
2. **Disparitas Resolusi Spasial Sensor Asli:**
   - ETOPO1: 0.0167° (~1.85 km).
   - MODIS Aqua / GCOM-C: 0.0417° (~4.63 km).
   - HYCOM / CMEMS: 0.08° – 0.083° (~9.2 km).
   - CFSv2 Harmonized: 0.205° (~22.8 km).
   - OISST v2.1: 0.25° (~27.8 km).
   - **Kaidah Analisis:** Pengambilan sampel (*sampling*) ke dalam grid target 0.10° (~11 km) merupakan representasi spasial multiskala. Hal ini tidak menambah informasi resolusi efektif dari sensor yang lebih kasar (OISST dan CFSv2).

---

## 2. BATASAN ILMIAH & LARANGAN KLAIM EKOLOGIS SPESIES

1. **Status Model Spesies:** Seluruh model habitat spesies (Cakalang, Madidihang, Legacy species removed from TCT scope) tetap berstatus **`SPECIES_MODEL_NOT_READY`**.
2. **Ketiadaan Klaim Probabilitas Keberadaan Ikan:**
   - Parameter oseanografi yang dihasilkan (SST, front termal, klorofil-a, kecepatan pemompaan Ekman, arus) murni mencerminkan kondisi fisik oseanografi dan indikator produktivitas primer.
   - **Dilarang keras menyimpulkan keberadaan ikan hanya dari parameter oseanografi**.
   - Front termal atau upwelling bukan bukti langsung adanya gerombolan ikan pelagis tanpa adanya data kejadian (*occurrence records*) terverifikasi secara independen.
3. **Data Pelabuhan Bukan Koordinat Penangkapan:**
   - Data produksi pendaratan ikan PPN Prigi murni mencerminkan volume bongkar pelabuhan, bukan lokasi tangkapan spasial di laut.
   - Deteksi lampu kapal VIIRS (VBD) bukan bukti identifikasi taksonomi spesies.
