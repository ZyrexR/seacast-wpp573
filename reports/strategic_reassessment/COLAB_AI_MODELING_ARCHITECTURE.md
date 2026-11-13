# GOOGLE COLAB AI & MACHINE LEARNING ARCHITECTURE
## Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi — WPPNRI 573

- **Document ID:** `STRAT-REASSESS-03`
- **Target Execution Phase:** Phase 4.3 — Google Colab AI/ML Modeling
- **Engine Role:** Reproducible Statistical & Machine Learning Execution, Spatial Block Cross-Validation, Circularity & Leakage Auditing, Ensemble Calibration, Uncertainty Quantification, and Spatial Prediction Generation

---

## 1. Prinsip Metodologis Utama: Pencegahan Sirkularitas & Pemisahan Paradigma Pemodelan

> [!CAUTION]
> **Aturan Ketat Anti-Sirkularitas (Mandatory Scientific Guardrail):**
> Pada Phase 3 sebelumnya, titik `presence_proxy_*.geojson` (`981` titik) dibangkitkan menggunakan aturan deterministik dari variabel `SST`, `Chl-a`, `Front`, dan `Depth`, lalu dievaluasi terhadap variabel yang sama (`HIGH_CIRCULARITY`, `DIRECT_LEAKAGE`).
> Di Google Colab (Phase 4.3), **dilarang keras mengklaim AUC tinggi dari titik proksi deterministik sebagai bukti validasi biologis kehadiran ikan.**

Oleh karena itu, arsitektur AI/ML di Google Colab memisahkan pemodelan ke dalam **Tiga Jalur Eksperimen yang Jelas & Jujur**:

| Jalur Pemodelan di Colab | Variabel Target ($Y$) | Sumber Data Target | Interpretasi Ilmiah yang Sah | Status Validasi Biologis |
| :--- | :--- | :--- | :--- | :--- |
| **Track A — Temporal Fisheries Catch & CPUE Forecasting (100% Empirical Ground-Truth)** | `cakalang_kg`, `madidihang_kg`, `legacy_species_removed_kg`, `pelagic_catch_kg`, dan `CPUE` (`kg/trip`) bulanan (`N = 132` bulan, `2015–2025`) | **Data Primer Empiris PPN Prigi (`Sheet 4` & `Sheet 5`)** | Memprediksi variasi hasil tangkapan/CPUE bulanan di pelabuhan berdasarkan prediktor oseanografi GEE (`SST`, `Chl-a`, `Front`, $M_{ey}$, $\Delta T$, `IOD/ENSO`) pada lag `0..3` bulan. | **100% Empiris (Temporal Port-Level Validation via Rolling-Origin TimeSeriesSplit).** |
| **Track B — Spatial Heuristic Niche Emulator & Sensitivity Benchmark** | Skor kesesuaian habitat relatif (`RHSI`) & titik amplop lingkungan (`presence_proxy`) pada `2.050` sel grid | Aturan amplop fisiologis literatur (FAO/IOTC/FishBase) + `effort_bias_surface` | Menguji sejauh mana algoritma ML (`MaxEnt/MaxNet`, `Random Forest`, `GLM/GAM`) mereproduksi vs memperhalus permukaan *Heuristic RHSI Baseline v1.1.1* ketika diberi prediktor satelit GEE baru. | **Eksploratori / Komparasi Metodologis (Bukan Validasi Biologis Lapangan — Sirkularitas Dideklarasikan).** |
| **Track C — Independent Spatial Occurrence / Vessel Activity Screening (Opsional jika data eksternal tersedia di GEE/GBIF)** | (1) Titik kehadiran spesies independen dari **OBIS / GBIF** di WPPNRI 573 (jika jumlah sampel memadai), atau (2) Indikator lampu kapal malam **VIIRS Boat Detection (VBD)** dari GEE | Katalog eksternal OBIS/GBIF atau `NOAA/VIIRS/DNB` di GEE | Track C1 (OBIS/GBIF): Kesesuaian habitat berbasis observasi biogeografis. Track C2 (VIIRS DNB): Pemodelan **intensitas aktivitas penangkapan kapal** (*fishing effort proxy*), **bukan** bukti biologis keberadaan spesies. | **Independen dari aturan heuristik lokal (Dengan pemisahan tegas antara observasi spesies vs proksi aktivitas kapal).** |

---

## 2. Arsitektur 15 Bagian Standar Notebook Google Colab (`.ipynb`)

Seluruh notebook di `colab/notebooks/` wajib mengikuti struktur 15 bagian modular yang berurutan, deterministik (`RANDOM_SEED = 42`), dan dapat dijalankan ulang (*Run All* tanpa intervensi manual setelah autentikasi Drive):

| Bagian | Judul Seksi Notebook | Isi Teknis, Pustaka, & Output Artefak |
| :---: | :--- | :--- |
| **1** | **Environment Setup & Reproducibility Lock** | Pencatatan versi Python, `scikit-learn`, `scipy`, `statsmodels`, `xgboost`, `shap`, `elapid`/`maxnet`; penguncian `PYTHONHASHSEED=42`, `np.random.seed(42)`; pemasangan Google Drive (`drive.mount('/content/drive')`). |
| **2** | **Data Acquisition & Checksum Verification** | Memuat dataset dari folder Google Drive `SEACAST_PRIGI_DATA_EXCHANGE/01_gee_exports/` dan `00_canonical_baseline/`; memverifikasi kecocokan `SHA-256` terhadap `dataset_manifest.json` sebelum eksekusi. |
| **3** | **Data Quality Assessment (DQA)** | Pemeriksaan nilai hilang (`NaN`), proporsi piksel satelit valid per bulan (`valid_pixel_fraction`), deteksi *outlier* fisik, dan pemeriksaan konsistensi dimensi grid (`50 × 41 = 2.050` sel; `132` bulan). |
| **4** | **Exploratory Data Analysis (EDA) & Collinearity Audit** | Matriks korelasi Pearson $r$ & Spearman $\rho$, perhitungan **Variance Inflation Factor (VIF)** antar prediktor GEE (`SST`, `Chl-a`, `Log10_Chl-a`, `Front_Sobel`, $\Delta T$, $M_{ey}$, `Depth`, `Slope`, `Dist_200m`), serta distribusi musiman. |
| **5** | **Feature Engineering & Ecological Transformations** | Pembuatan fitur lag temporal (`t-1`, `t-2`, `t-3`), anomali terdeseasonalisasi, interaksi termal-klorofil ($\text{SST} \times \log_{10}\text{Chl-a}$), suku kuadratik termal ($\text{SST}^2$ untuk respon unimodal Gaussian), dan permukaan pembobotan upaya tangkap $B(d) = \exp(-\lambda_d d)$. |
| **6** | **Dataset Preparation & Target-Source Labeling** | Pemisahan tegas matriks data untuk **Track A** (132 bulan empiris PPN Prigi), **Track B** (2.050 sel $\times$ 6 skenario RHSI/proksi dengan label `circularity_flag = TRUE`), dan **Track C** (GBIF/OBIS/VIIRS jika tersedia). |
| **7** | **Train-Test & Cross-Validation Strategy** | - **Spasial (Track B/C):** **Spatial Block Cross-Validation (5-Fold Checkerboard / Longitudinal-Latitudinal Blocks berukuran `0.40° × 0.50°`)** untuk mencegah kebocoran autokorelasi spasial antar sel bertetangga.<br/>- **Temporal (Track A):** **Expanding-Window / Rolling-Origin TimeSeriesSplit** (Train: `2015–2022` [96 bln], Validation/Test: `2023–2025` [36 bln]). |
| **8** | **Model Training (Multi-Algorithm Suite)** | Pelatihan kode nyata (*actual code execution*) untuk:<br/>1. **MaxEnt / MaxNet** (Regresi Poisson terregularisasi $L_1/L_2$ dengan fitur *linear, quadratic, hinge, product* terhadap `5.000` titik *target-group background*).<br/>2. **Random Forest** (`RandomForestClassifier` / `RandomForestRegressor` dengan pembobotan sampel *effort bias*).<br/>3. **Generalized Linear / Additive Model (`GLM / GAM`)** (`statsmodels.api.GLM` keluarga Binomial/Poisson/Gamma dengan polinomial derajat-2 pada SST dan $\log_{10}\text{Chl-a}$).<br/>4. **Model Pembanding Tambahan:** `Histogram-Based Gradient Boosting` / `XGBoost` dan `Ridge/ElasticNet` (untuk Track A temporal). |
| **9** | **Hyperparameter Tuning** | Pencarian grid/acak terkendali (*nested spatial/temporal CV*): regularisasi $\beta \in \{0.5, 1.0, 2.0, 5.0\}$ untuk MaxEnt; `max_depth` $\in \{4, 6, 8\}$, `min_samples_leaf` $\in \{10, 20, 50\}$ untuk RF (dibatasi agar tidak *overfit* pada proksi spasial); derajat penalti spline/L2 untuk GLM. |
| **10** | **Spatial & Temporal Cross-Validation Execution** | Eksekusi nyata 5-Fold Spatial Block CV (menyimpan metrik per blok spasial `Fold 1..5`) dan Rolling-Origin Temporal CV (menyimpan prediksi *out-of-sample* `2023–2025`). |
| **11** | **Model Evaluation & Circularity-Aware Metrics** | - **Metrik Spasial:** Area Under ROC Curve ($\text{AUC}_{\text{spatial-CV}}$), Precision-Recall AUC ($\text{PR-AUC}$), True Skill Statistic ($\text{TSS}$), Boyce Index (Continuous Boyce Index untuk data *presence-background*), dan Brier Score.<br/>- **Metrik Temporal (Track A):** RMSE, MAE, MAPE, $R^2$, dan korelasi arah perubahan (*Directional Symmetry*).<br/>- **Pernyataan Wajib:** Mencetak peringatan eksplisit apabila evaluasi dilakukan terhadap label proksi (Track B). |
| **12** | **Ensemble Modeling & Weight Calibration** | Menggabungkan prediksi komponen (`MaxEnt`, `RF`, `GLM`) menggunakan:<br/>1. Bobot berbasis kinerja *out-of-block Spatial CV* ($\text{TSS}$ / Boyce / $\text{AUC}_{\text{spatial}}$).<br/>2. Komparasi langsung terhadap bobot kanonis Phase 4.1.1 (`PROVISIONAL_ANALYST_SELECTED_WEIGHTS`: `0.320/0.355/0.325`, `0.295/0.365/0.340`, `0.000/0.517/0.483`).<br/>3. Perhitungan ketidakpastian terboboti $\sigma_w = \sqrt{\sum_i w_i (\text{RHSI}_i - \overline{\text{RHSI}}_w)^2}$ dan skor advisory $\overline{\text{RHSI}}_w - \lambda \sigma_w$. |
| **13** | **Feature Importance, Response Curves & SHAP Interpretation** | Perhitungan *Permutation Importance* pada blok spasial *out-of-fold*, kurva respons parsial (*Partial Dependence Plots* / PDP untuk `SST`, `Chl-a`, `Front`, `Depth`), dan nilai **SHAP (`TreeExplainer` / `LinearExplainer`)**. |
| **14** | **Spatial Prediction Generation & Export** | Menghasilkan proyeksi pada seluruh `2.050` sel $\times$ `6` skenario $\times$ `3` spesies, melakukan *clipping* geometri ketat ke `[111.00, 113.00]° BT × [-10.45, -8.00]° LS`, dan mengekspor JSON, CSV, GeoJSON, serta bobot model ke Google Drive `02_colab_outputs/` tanpa menimpa baseline lama. |
| **15** | **Automated Reproducibility & Provenance Report** | Menghasilkan `colab_execution_manifest.json` yang mencatat waktu mulai/selesai, *hash* SHA-256 seluruh file input dan output, parameter model, serta tabel perbandingan antara **Baseline Heuristik v1.1.1** vs **Colab Trained Models v2.0**. |

---

## 3. Daftar Notebook Modular yang Akan Dibangun (`colab/notebooks/`)

1. `01_GEE_Data_Ingestion_and_EDA.ipynb` — Verifikasi ekspor GEE, analisis kolinearitas (VIF), dan validasi statistik spasiotemporal (`N = 132` bulan, `2.050` sel).
2. `02_Temporal_Fisheries_Catch_and_CPUE_ML.ipynb` — **Track A (100% Empiris):** Pemodelan deret waktu hasil tangkapan & CPUE bulanan PPN Prigi (`Ridge`, `GLM`, `RandomForestRegressor`, `XGBoost`) dengan *Rolling-Origin TimeSeriesSplit*.
3. `03_Spatial_RHSI_MaxEnt_RF_GLM_and_BlockCV.ipynb` — **Track B & Track C:** Pelatihan `MaxEnt/MaxNet`, `Random Forest`, dan `GLM` spasial, eksekusi *5-Fold Spatial Block Cross-Validation*, analisis sensitivitas $\lambda \in [0, 1]$, dan komparasi head-to-head terhadap *Phase 4.1.1 Heuristic Baseline*.
