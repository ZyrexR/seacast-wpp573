# DATA GAP ANALYSIS & MITIGATION PROTOCOL

## 1. Analisis Kesenjangan Temporal (2015–2025)

1. **Klorofil-a (MODIS-Aqua L3SMI):**
   - *Kondisi:* Koleksi di GEE tersedia dari 2002-07-03 hingga 2025-11-30. Terdapat 1 bulan gap pada akhir periode (Desember 2025).
   - *Mitigasi Ilmiah:* Pada Desember 2025, digunakan komposit klimatologis bulan Desember (2015-2024) atau sensor VIIRS SNPP/NOAA-20 `NOAA/VIIRS/001/VNP22Q2` dengan bendera eksplisit `qc_gap_filled = TRUE`.

2. **Tutupan Awan Musim Barat (Desember - Februari):**
   - *Kondisi:* Citra optik MODIS-Aqua mengalami tutupan awan konvektif monsunal hingga 60-80% di Samudera Hindia Selatan Jawa.
   - *Mitigasi Ilmiah:* Penggunaan jendela berjalan temporal 3-bulan (median) dan sensor fusion dengan OISST v2.1 untuk SST. Dilarang menggunakan focal mean tanpa pencatatan masker piksel asli vs estimasi.

3. **Sea Level Anomaly (SLA) & Arus Geostrofik:**
   - *Kondisi:* Dataset altimetri gridded Copernicus (CMEMS) tidak tersedia sebagai ImageCollection publik bawaan di GEE catalog.
   - *Mitigasi Ilmiah:* Ekstraksi data SLA dan EKE diorkestrasi melalui Google Colab menggunakan Copernicus Marine API resmi, lalu ditautkan ke `grid_id` WPPNRI 573.

4. **Tinggi Gelombang Signifikan (Hs):**
   - *Kondisi:* Band `swh` (significant wave height) pada ERA5 tidak terdistribusi penuh di koleksi `ERA5_LAND` GEE.
   - *Mitigasi Ilmiah:* Parameterisasi kecepatan angin permukaan atau akuisisi langsung ERA5 wave spectrum dari Copernicus CDS.
