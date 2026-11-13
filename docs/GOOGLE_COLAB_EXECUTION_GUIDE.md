# 📓 PANDUAN EKSEKUSI GOOGLE COLAB — SEACAST PRIGI v2.3.2
**Species Distribution Modeling (Ensemble SDM), Dynamic Ocean Management (DOM), & Batch Inference (1.073.028 Sel)**

Berkas notebook Google Colab **telah dibuat dan diunggah langsung ke folder Google Drive Anda** di:
📁 `Google Drive / SEACAST_PRIGI_CSV / SEACAST_PRIGI_COLAB_SDM_PIPELINE.ipynb`

---

## 🚀 1. CARA MEMBUKA NOTEBOOK DI GOOGLE COLAB

Anda dapat membuka notebook ini dengan sangat mudah:

### Opsi A (Langsung dari Google Drive — Paling Mudah):
1. Buka [Google Drive](https://drive.google.com/) di browser Anda (akun: `piesdiem@gmail.com`).
2. Masuk ke folder **`SEACAST_PRIGI_CSV`**.
3. Cari berkas: **`SEACAST_PRIGI_COLAB_SDM_PIPELINE.ipynb`**.
4. Klik kanan pada berkas tersebut $\rightarrow$ **Open with** $\rightarrow$ **Google Colaboratory**.

### Opsi B (Lewat Website Google Colab):
1. Buka [colab.research.google.com](https://colab.research.google.com/).
2. Pada jendela popup, pilih tab **Google Drive**.
3. Cari dan klik: **`SEACAST_PRIGI_COLAB_SDM_PIPELINE.ipynb`**.

---

## ⚡ 2. CARA MENJALANKAN DI GOOGLE COLAB

1. Di menu atas Google Colab, klik: **Runtime** $\rightarrow$ **Run all** (atau tekan `Ctrl + F9`).
2. Saat sel pertama berjalan, Google Colab akan meminta izin menghubungkan Google Drive (*Mount Drive*):
   * Klik **Connect to Google Drive**.
   * Pilih akun Google Anda (`piesdiem@gmail.com`) dan berikan izin akses.
3. Seluruh 132 berkas CSV bulanan (1.073.028 sel) dan 5 data referensi yang telah kita siapkan akan otomatis dibaca.
4. **Estimasi Waktu Eksekusi:** Sekitar **3 hingga 5 menit** (menggunakan CPU gratis Colab atau T4 GPU).

---

## 📦 3. STRUKTUR HASIL KELUARAN DI GOOGLE DRIVE

Setelah seluruh sel selesai berjalan (*Run All*), Colab akan secara otomatis membuat folder luaran di Google Drive Anda:

```
drive/MyDrive/SEACAST_PRIGI_CSV/OUTPUT_COLAB/
├── models/                               <-- 1. Berkas Bobot Model ML
│   ├── cakalang_ensemble_model.joblib
│   ├── madidihang_ensemble_model.joblib
│   ├── legacy_species_removed_ensemble_model.joblib
│   └── scaler_oceanographic.joblib
│
├── predictions/                          <-- 2. Master Data Prediksi (Siap PostgreSQL)
│   ├── SEACAST_PRIGI_PREDICTIONS_2015_2025_FULL.parquet  (~60 MB, 1.073.028 baris)
│   └── SEACAST_PRIGI_PREDICTIONS_2025_SAMPLE.csv         (Sampel pratinjau 2025)
│
├── geojson/                              <-- 3. Lapisan Poligon Spasial Web
│   └── canonical_grid_8129_cells.geojson (~12 MB, Poligon 8.129 sel untuk WebGL)
│
├── metrics/                              <-- 4. Metrik Validasi Ilmiah
│   └── sdm_evaluation_metrics.json       (Skor AUC-ROC > 0.85 per spesies)
│
└── figures/                              <-- 5. Grafik Ilmiah & Kurva Validasi
    └── cpue_vs_hsi_validation_plot.png   (Grafik korelasi HSI vs Pendaratan Pelabuhan)
```

---

## 🔄 4. LANGKAH BERIKUTNYA SETELAH COLAB SELESAI

Setelah selesai menjalankan *Run all* di Colab:
1. Beri tahu saya bahwa Colab telah selesai.
2. Saya akan langsung mengunduh berkas Parquet (`SEACAST_PRIGI_PREDICTIONS_2015_2025_FULL.parquet`), model `.joblib`, dan GeoJSON ke laptop ini.
3. Kita langsung memasukkannya ke basis data **PostgreSQL 18 + PostGIS** dan menghubungkannya ke **Website Modern Node.js**!

