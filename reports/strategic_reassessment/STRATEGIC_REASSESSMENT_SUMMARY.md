# STRATEGIC REASSESSMENT SUMMARY & EXECUTIVE DECISION BRIEF
## Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi — WPPNRI 573

- **Document ID:** `STRAT-REASSESS-08`
- **Date:** `2026-11-02`
- **Current Gate Status:** **`G-00: READY FOR REVIEW`** (Menunggu persetujuan peneliti sebelum memasuki Phase 4.2)

---

## 1. Ringkasan Eksekutif Kondisi Proyek Saat Ini

1. **Apa yang Sudah Terbukti & Valid (`Baseline v1.1.1`):**
   - Data pendaratan perikanan PPN Prigi (`2015–2025`, `132` bulan, `12` kategori = `18.405.261 kg`, `10` taksa pelagis sejati = `18.070.653 kg`), profil armada per kelas GT (`2025`: `296` `<10 GT`, `53` `10–<20 GT`, `91` `20–<30 GT`, `3` `>30 GT`, `Total = 443 unit`; puncak `2016 = 794 unit`), batas resmi WPPNRI 573 dari BIG (`933.749,49 km²` poligon BIG; `1.423.238 km²` rujukan nominal KKP), dan geometri Sub-AOI Prigi (`[111.00, 113.00]° BT × [-10.45, -8.00]° LS`, `50 × 41 = 2.050` sel, `59.550,43 km²`) telah terverifikasi 100% dari sumber primer dan lulus seluruh 15 pengujian regresi (`RT-01` s/d `RT-15`).
2. **Apa yang Belum Direproduksi di Cloud Ilmiah (`GEE` & `Google Colab`):**
   - Grid oseanografi Phase 2 dan skor kesesuaian habitat relatif (`RHSI`) Phase 3 saat ini masih berasal dari **rekonstruksi analitik dan persamaan heuristik lokal** di dalam skrip Node.js/Python (`0` skrip GEE yang dieksekusi di server Earth Engine, `0` GeoTIFF satelit, dan `0` notebook `.ipynb` Google Colab yang melatih model ML).
3. **Transformasi Strategis yang Dirancang:**
   - Mempertahankan seluruh hasil Phase 4.1.1 sebagai **Frozen Heuristic Baseline (`v1.1.1`)**, lalu membangun dan menjalankan pipeline ilmiah **`v2.0`** yang sepenuhnya dapat direproduksi melalui **Google Earth Engine (Phase 4.2)** $\rightarrow$ **Google Drive** $\rightarrow$ **Google Colab (Phase 4.3)** $\rightarrow$ **Antigravity Integration & DOM-DSS Dashboard (Phase 4.4–4.6)**.

---

## 2. Ringkasan Keputusan Arsitektur Lintas Platform

| Komponen Arsitektur | Keputusan Teknis Utama | Alasan Ilmiah & Reproduksibilitas |
| :--- | :--- | :--- |
| **Mesin Spasial (`Phase 4.2`)** | **Google Earth Engine** menggunakan kombinasi **Earth Engine Python API (`ee.batch`)** untuk eksekusi otomatis dan **GEE JavaScript (`gee/js/`)** untuk inspeksi visual di Code Editor. | Menghilangkan ketergantungan pada klik manual di tab *Tasks* Code Editor sekaligus menjamin ekstraksi piksel riil dari `MODIS-Aqua`, `NOAA OISST v2.1`, `ERA5`, dan `ETOPO1`/`GEBCO`. |
| **Mesin AI/ML (`Phase 4.3`)** | **Google Colab (`.ipynb` 15 Bagian)** dengan pemisahan tegas antara **Track A** (Prediksi Tangkapan/CPUE Bulanan Empiris 132 Bulan), **Track B** (Emulator & Komparasi Spasial RHSI), dan **Track C** (Data Kehadiran/Aktivitas Kapal Independen). | Mencegah klaim validasi biologis semu akibat sirkularitas titik proksi (`HIGH_CIRCULARITY`) serta mengeksekusi pelatihan kode nyata (`MaxEnt/MaxNet`, `RF`, `GLM`, `XGBoost`) dengan **5-Fold Spatial Block Cross-Validation**. |
| **Bus Pertukaran Data** | **Google Drive (`SEACAST_PRIGI_WPP573/`)** dengan manifest JSON ber-checksum `SHA-256` dan pemisahan namespace `v1.1.1` (Frozen Baseline) vs `v2.0` (GEE+Colab). | Menjamin keterlacakan (*provenance*) setiap file dari ID tugas GEE hingga prediksi Colab tanpa menimpa baseline lama. |
| **Orkestrator & UI (`Phase 4.4–4.6`)** | **Google Antigravity** mengelola pembuatan kode, validasi geometri/statistik, pengujian regresi, dan fitur *Model Version Toggle* (`v1.1.1` vs `v2.0`) di dashboard WebGIS [`html/index.html`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/html/index.html). | Memungkinkan evaluasi ilmiah berdampingan antara model heuristik awal dan model berbasis satelit GEE + ML Colab. |

---

## 3. Matriks Risiko & Mitigasi

| ID | Risiko Teknis / Metodologis | Dampak | Strategi Mitigasi Terukur |
| :---: | :--- | :---: | :--- |
| **RSK-01** | Tutupan awan tinggi pada citra optik `MODIS-Aqua` (`chlor_a` & `sst`) di Selatan Jawa selama Musim Barat (DJF). | Kesenjangan piksel (`NaN`) pada komposit bulanan. | Menggunakan *sensor fusion* dengan `NOAA OISST v2.1` (bebas awan untuk SST) dan komposit median bulanan + interpolasi klimatologi berbobot dengan bendera `qc_gap_filled_flag` untuk `chlor_a`. |
| **RSK-02** | Sirkularitas evaluasi apabila model ML di Colab dilatih dan diuji pada titik `presence_proxy` yang dibuat dari aturan suhu/klorofil yang sama. | Inflasi artifisial nilai AUC spasial. | Memisahkan **Track A** (target pendaratan empiris PPN Prigi 132 bulan), menerapkan **5-Fold Spatial Block CV**, serta memberi label wajib `EXPLORATORY_NICHE_EMULATOR` pada **Track B**. |
| **RSK-03** | Keterbatasan Google Colab versi gratis yang tidak memiliki *headless REST API* publik dan memiliki batas waktu *idle*. | Kegagalan jika mengandalkan *browser scraping* murni. | Menyinkronkan notebook `.ipynb` mandiri ke Google Drive sehingga dapat dijalankan dengan satu klik `Run All` di Colab (atau dieksekusi melalui `nbclient` pada kernel yang terautentikasi) dengan penyimpanan checkpoint otomatis ke Drive. |

---

## 4. Daftar Kebutuhan Akses & Persetujuan Pengguna untuk Memulai Phase 4.2

Sebelum Antigravity dapat mengeksekusi **Phase 4.2 (Google Earth Engine Spatial Modeling)**, diperlukan tiga tindakan/persetujuan dari Peneliti pada **Checkpoint Gate `G-00`**:

1. **Persetujuan Dokumen Reassessment (`Gate G-00 = APPROVED`):**
   - Menyetujui ke-8 dokumen arsitektur di `reports/strategic_reassessment/` dan pembekuan baseline Phase 4.1.1 (`v1.1.1`).
2. **Autentikasi Google Earth Engine & Google Cloud Project ID:**
   - Mengizinkan instalasi paket `earthengine-api` (`pip install earthengine-api`) di lingkungan Python lokal.
   - Menjalankan autentikasi satu kali (`earthengine authenticate`) melalui browser ke akun Google peneliti yang terdaftar di Google Earth Engine, serta memberikan **`GCP_PROJECT_ID`** yang aktif untuk inisialisasi `ee.Initialize(project='...')`.
3. **Koneksi Google Drive untuk Pertukaran Data (`GEE ↔ Drive ↔ Colab ↔ Antigravity`):**
   - Mengaktifkan plugin resmi **Google Drive (`antigravity-plugins-official/plugin/gdrive`)** dari kartu plugin di bawah, **dan/atau** menyiapkan folder sinkronisasi Google Drive lokal untuk menampung ekspor GEE dan notebook Colab.

---

## 5. Pernyataan Kesiapan Perencanaan Phase 4.2 & Kondisi Berhenti (Stop Condition)

- **Status Kesiapan Perencanaan Phase 4.2:** **`READY FOR PHASE 4.2 EXECUTION PLANNING UPON RESEARCHER APPROVAL OF GATE G-00`**.
- **Kepatuhan Batas Eksekusi:** Sesuai instruksi pada **Section 11 & Section 12**, tidak ada kode Phase 4.2 yang dijalankan, tidak ada pelatihan AI/ML baru yang dieksekusi, dan tidak ada satu pun berkas baseline Phase 1–3 maupun Phase 4.1.1 yang diubah selama penyusunan reassessment ini.
