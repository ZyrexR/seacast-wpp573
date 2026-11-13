# 03 â€” MODEL AND SCRIPT INVENTORY & MATHEMATICAL FORMULA AUDIT
## Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi â€” WPPNRI 573

Dokumen ini menginventarisasi seluruh skrip pemrosesan, pemodelan, bundel data, dan pengujian di dalam workspace (`scratch/*.js`, `scratch/*.py`, `app.py`, `utils.py`) serta mengekstrak **rumus matematika aktual yang tertulis di dalam kode** dibandingkan dengan spesifikasi ilmiah Phase 3â€“4.

---

## 1. INVENTARISASI LENGKAP SKRIP PEMROSESAN, PEMODELAN, DAN VALIDASI

| Script | Fungsi Utama | Input Aktual | Output Aktual | Parameter Kunci | Dependencies | Execution Evidence |
|---|---|---|---|---|---|---|
| [`scratch/execute_stage_3_1_to_3_3.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/execute_stage_3_1_to_3_3.js) | Membangun stack 12 prediktor pada 6 skenario (`2.050` sel), menghitung `slope_deg` & jarak pelabuhan, membuat *Effort Bias Surface*, membangkitkan titik *Presence-Proxy* (Cakalang, Madidihang, legacy_species_removed, Bycatch), dan menyampling `5.000` titik *Target-Group Background (TGB)*. | `data/oceanographic/processed/seasonal/grid_*.json` (4 file), `seasonal_climatology.json` | `data/sdm/predictors/predictor_stack_multi_scenario.json`, `predictor_stack_metadata.json`, `data/sdm/bias/effort_bias_surface.json`, `data/sdm/presence/presence_proxy_*.geojson` (4 file), `data/sdm/background/target_group_background.geojson` | `prigiHarbor=(-8.2818, 111.7314)`, buffer `12 NM` & `60 NM`, `N_BG=5000`, `seed=42`, threshold proxy `score >= 0.75` | Node.js `fs`, `path` | `VERIFIED` â€” Seluruh 8 file output tersedia di `data/sdm/` dengan timestamp & ukuran sesuai. |
| [`scratch/execute_stage_3_4_to_3_7.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/execute_stage_3_4_to_3_7.js) | Menjalankan persamaan analitik MaxEnt, RF, dan GLM per sel, menghitung AUC & TSS terhadap 1.000 sel background, menghitung bobot ensemble, memproyeksikan `hsi_ens` dan `uncertainty` ke 6 skenario, serta mengklasifikasikan zona fungsional. | `predictor_stack_multi_scenario.json`, `presence_proxy_*.geojson` (3 spesies), `target_group_background.geojson` (dimuat di baris 17 tetapi **tidak dipakai**) | `models/sdm/ensemble/model_evaluations_summary.json`, `outputs/sdm/hsi/hsi_all_scenarios_projections.json`, `outputs/sdm/zones/functional_ecological_zones.geojson` | Koefisien hardcoded `speciesModels` (baris 56â€“125), `pfgThresh=0.65` (`0.50` untuk legacy_species_removed), `totalTrees=100`, `variance / 3` (baris 316) | Node.js `fs`, `path` | `VERIFIED` â€” Menghasilkan `hsi_all_scenarios_projections.json` (18,9 MB) dan `functional_ecological_zones.geojson` (`1.920` fitur). |
| [`scratch/update_bycatch_layer.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/update_bycatch_layer.js) | Menambahkan boolean `is_bycatch_risk` (`-1000 <= depth_m <= -200 && sst <= 26.0`) ke seluruh sel proyeksi dan mengekspor 123 poligon bycatch awal. | `outputs/sdm/hsi/hsi_all_scenarios_projections.json` | Memperbarui `hsi_all_scenarios_projections.json` dan membuat `outputs/sdm/zones/dynamic_bycatch_mitigation_zones.geojson` (123 fitur) | `depth_m in [-1000, -200]`, `sst <= 26.0`, kotak `[lonÂ±0.025, latÂ±0.025]` | Node.js `fs`, `path` | `VERIFIED` â€” `is_bycatch_risk` ada di JSON proyeksi; 123 poligon terbuat. |
| [`scratch/refine_geojson_export.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/refine_geojson_export.js) | Mengekspor GeoJSON zona fungsional per spesies (Cakalang `1.920`, Madidihang `1.858`, legacy_species_removed `513`) dan memperbarui `dynamic_bycatch_mitigation_zones.geojson`. | `outputs/sdm/hsi/hsi_all_scenarios_projections.json` | `functional_zones_cakalang.geojson`, `functional_zones_madidihang.geojson`, `functional_zones_legacy_species_removed.geojson`, `dynamic_bycatch_mitigation_zones.geojson` | Filter `zone_class !== 'General Oceanic Matrix'`, kotak `[lonÂ±0.025, latÂ±0.025]` tanpa clipping AOI | Node.js `fs`, `path` | `VERIFIED` â€” Ke-4 file GeoJSON tersedia di `outputs/sdm/zones/`. |
| [`scratch/remediate_dashboard_and_evals.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/remediate_dashboard_and_evals.js) | Mengubah `model_evaluations_summary.json` (meng-nol-kan bobot MaxEnt legacy_species_removed menjadi `0.000` dan menormalisasi ulang `rf=0.554, glm=0.446`) serta mengganti beberapa label di `html/index.html`. | `models/sdm/ensemble/model_evaluations_summary.json`, `html/index.html` | Memperbarui `model_evaluations_summary.json` dan `html/index.html` | `legacy_species_removed.ensemble.weights = {maxent: 0.000, rf: 0.554, glm: 0.446}` | Node.js `fs`, `path` | `VERIFIED` â€” `model_evaluations_summary.json` telah terupdate, tetapi `hsi_all_scenarios_projections.json` tidak dihitung ulang. |
| [`scratch/embed_five_cites_fields.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/embed_five_cites_fields.js) | Menyematkan 5 atribut taksonomi CITES ke dalam setiap fitur di `dynamic_bycatch_mitigation_zones.geojson`. | `outputs/sdm/zones/dynamic_bycatch_mitigation_zones.geojson` | Memperbarui `dynamic_bycatch_mitigation_zones.geojson` | 5 field: `identified_taxon_name`, `taxonomic_resolution_level`, `cites_status_and_appendices_date`, `identification_verification_status`, `taxonomic_uncertainty_notes` | Node.js `fs`, `path` | `VERIFIED` â€” Kelima field ada di GeoJSON, namun belum dipecah menjadi 8 field standar Phase 4. |
| [`scratch/build_sdm_web_bundle.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/build_sdm_web_bundle.js) & [`scratch/optimize_sdm_web_bundle.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/optimize_sdm_web_bundle.js) | Mengompilasi hasil proyeksi SDM, evaluasi model, titik presence, dan lokasi bycatch ke dalam bundel JS kompak `html/sdm_data.js` (`window.SDM_DATA`). | `hsi_all_scenarios_projections.json`, `model_evaluations_summary.json`, `presence_proxy_*.geojson`, `dynamic_bycatch_mitigation_zones.geojson` | `html/sdm_data.js` (2,01 MB) | Format sel kompak `[hsi_ens, uncertainty, zone_code, is_bycatch]`, mereduksi 123 poligon bycatch menjadi titik `{lat, lon, depth, sst, dist}` | Node.js `fs`, `path` | `VERIFIED` â€” `html/sdm_data.js` tersedia (2.009.194 byte). |
| [`scratch/integrate_sdm_into_index_html.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/integrate_sdm_into_index_html.js) | Menyuntikkan markup tab `#view-sdm` dan `<script id="sdm-script">` ke dalam `html/index.html`. | `html/index.html` | Memperbarui `html/index.html` | Mencari string `"if (viewId === 'view-ocean')"` (baris 50) yang **gagal cocok** dengan `"if(viewId === 'view-ocean'"` di `index.html` baris 1248 | Node.js `fs`, `path` | `VERIFIED` (dengan bug) â€” Markup `#view-sdm` masuk, tetapi kait pemanggil `initSdmMap()` di `switchView()` gagal terpasang. |
| [`scratch/generate_task1_deliverables.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/generate_task1_deliverables.js) & [`scratch/task1_reconciliation.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/task1_reconciliation.js) | Mengaudit rekonsiliasi 132 bulan antara kolom `total_pelagis_kg` dan jumlah 12 spesies, menemukan selisih +200 kg pada April 2022 (`198.446` vs `198.246`). | `data/master_prigi_all_inclusive.csv`, `data/prigi_fleet_trips_2012_2025.csv` | `FINAL_FISHERIES_RECONCILIATION.csv`, `FINAL_FISHERIES_RECONCILIATION.md`, `PHASE_3_1_FISHERIES_DATA_RECONCILIATION.md` | Deteksi selisih `discrepancy_kg = +200` pada `2022-04-01` | Node.js `fs`, `path` | `VERIFIED` â€” Menghasilkan tabel rekonsiliasi, tetapi tidak menulis balik perbaikan ke `master_prigi_all_inclusive.csv`. |
| [`scratch/execute_task2_final.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/execute_task2_final.js) & [`scratch/generate_task3_qc.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/generate_task3_qc.js) | Menghasilkan tabel kontrol kualitas (QC) bulanan 132 bulan untuk parameter oseanografi (SST, Chl-a, Ekman, Front, Gelombang). | `data/oceanographic/statistics/monthly/ocean_fisheries_master_132m.json` | `FINAL_OCEANOGRAPHIC_MONTHLY_QC.csv`, `PHASE_3_1_OCEANOGRAPHIC_QC.csv`, `PHASE_3_1_OCEANOGRAPHIC_QC.md` | Pemeriksaan rentang fisik SST ($20\text{â€“}34^\circ\text{C}$), Chl-a ($0,01\text{â€“}10\text{ mg/m}^3$), $H_s$ ($0\text{â€“}10\text{ m}$) | Node.js `fs`, `path` | `VERIFIED` â€” File CSV QC tersedia di root workspace. |
| [`scratch/test_weighted_disagreement.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/test_weighted_disagreement.py) | Unit test Python mandiri untuk memverifikasi rumus $\overline{RHSI}_w$, $\sigma_w$, dan $PFG_{\text{advisory}} = \overline{RHSI}_w - \lambda \sigma_w$ beserta clipping $[0,1]$. | Vektor uji hardcoded `rhsi=[0.8, 0.6, 0.4]`, `weights=[0.5, 0.3, 0.2]`, `lam=0.5` | Output konsol (`PASS`) | Ekspektasi: `rhsi_w=0.6600`, `sigma_w=0.156205`, `pfg_raw=0.581898` | Python `math`, `sys` | `VERIFIED` â€” Lulus uji numerik mandiri, tetapi belum diintegrasikan ke dataset proyeksi SDM. |
| [`scratch/validate_polygon_geometries.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/validate_polygon_geometries.py) & [`scratch/inspect_geojson.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/inspect_geojson.py) | Memeriksa topologi dasar, penutupan cincin koordinat, luas $>0$, duplikat centroid, dan rentang kedalaman pada 123 poligon bycatch. | `outputs/sdm/zones/dynamic_bycatch_mitigation_zones.geojson` | Output konsol validasi geometri | `123` fitur, kedalaman `-242 m` s/d `-950 m`, luas rata-rata $30,45\text{ km}^2\text{/sel}$ | Python `json`, `math`, `pathlib` | `VERIFIED` â€” Mengonfirmasi 123 poligon valid secara cincin, tetapi skrip ini hanya menguji centroid terhadap AOI dan tidak menguji simpul tepi (sehingga melewatkan overhang $0.025^\circ$). |
| [`scratch/calc_sub_aoi_area.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/calc_sub_aoi_area.py) | Menghitung luas permukaan elipsoidal WGS84 eksak untuk Sub-AOI Prigi ($[111.0^\circ, 113.0^\circ\text{ BT}] \times [-10.45^\circ, -8.00^\circ\text{ LS}]$). | Parameter elipsoid WGS84 ($a = 6378137,0\text{ m}$, $f = 1/298,257223563$) | Output konsol (`59,550.43 kmÂ²`, `4.18%` dari WPPNRI 573) | `lon1=111.0, lon2=113.0, lat1=-10.45, lat2=-8.0` | Python `math` | `VERIFIED` â€” Menghasilkan luas referensi $59.550,43\text{ km}^2$. |
| [`scratch/execute_task10_final.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/execute_task10_final.js), [`scratch/update_gate_json_protocols.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/update_gate_json_protocols.js), [`scratch/build_matrix_json.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/build_matrix_json.py) | Menghasilkan file JSON gerbang kesiapan (`PHASE_4_READINESS_GATE.json` dan `PHASE_4_FINAL_READINESS_MATRIX.json`). | String literal yang ditulis langsung di dalam skrip | `PHASE_4_READINESS_GATE.json`, `PHASE_4_FINAL_READINESS_MATRIX.json` | Status `PASS` dan skor `95.25/100` yang dikodekan secara statis | Node.js / Python | `VERIFIED` â€” Membuktikan bahwa `PHASE_4_READINESS_GATE.json` dibuat dari teks statis tanpa memverifikasi isi `html/index.html` secara terprogram. |
| [`app.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/app.py) & [`utils.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/utils.py) | Aplikasi prototipe awal berbasis Python Streamlit beserta fungsi pemuat data, regresi OLS sederhana, dan korelasi lag. | Mencari `data/master.csv` (tidak ditemukan) $\rightarrow$ jatuh ke `_make_dummy()` | Antarmuka web lokal Streamlit (7 halaman di `pages/`) | `STUDY_BOX = {"lat": (-9.5, -8.5), "lon": (111.0, 112.5)}`, `PORT = (-8.29, 111.73)` | `streamlit`, `pandas`, `numpy`, `plotly` | `VERIFIED` â€” Berjalan dalam mode fallback data dummy (`dummy = True`). |

---

## 2. AUDIT FORMULA MATEMATIKA AKTUAL DI DALAM KODE

### 2.1 Formula yang Digunakan untuk Membangkitkan Model Individu (`scratch/execute_stage_3_4_to_3_7.js`, Baris 128â€“176)

1. **Persamaan Niche MaxEnt-like (Baris 135â€“152):**
   \[
   \text{sstTerm} = \exp\left(-\frac{1}{2}\left(\frac{\text{SST} - \mu_{\text{SST}}}{\sigma_{\text{SST}}}\right)^2\right), \quad
   \text{chlTerm} = \min\left(1, \max\left(0, \frac{\text{Chl} - \text{Chl}_{\min}}{\text{Chl}_{\text{opt}} - \text{Chl}_{\min}}\right)\right)
   \]
   \[
   \text{frontTerm} = \min\left(1, \frac{\text{Front}}{\text{Front}_{\text{opt}}}\right), \quad
   \text{depthTerm} = \begin{cases} 1, & \text{Depth} \leq D_{\text{thresh}} \\ \max\left(0, 1 + \frac{\text{Depth} - D_{\text{thresh}}}{100}\right), & \text{Depth} > D_{\text{thresh}} \end{cases}
   \]
   \[
   \eta_{\text{MaxEnt}} = \beta_0 + w_{\text{SST}}(2\,\text{sstTerm}-1) + w_{\text{Chl}}(2\,\text{chlTerm}-1) + w_{\text{Front}}(2\,\text{frontTerm}-1) + w_{\text{Depth}}(2\,\text{depthTerm}-1) + w_{\text{Slope}}\min\left(1, \frac{\text{Slope}}{5}\right)
   \]
   \[
   RHSI_{\text{MaxEnt}} = \frac{1}{1 + \exp(-\eta_{\text{MaxEnt}})}
   \]

2. **Persamaan Simulasi Random Forest (Baris 154â€“164):**
   \[
   RHSI_{\text{RF}} = \frac{40 \cdot \mathbb{I}(\text{SST} \in [T_{\min}, T_{\max}] \land \text{Depth} \leq D_{\max}) + 35 \cdot \mathbb{I}(\text{Chl} \geq C_{\min} \land \text{Front} \geq F_{\min}) + 25 \cdot \mathbb{I}(d_{200\text{m}} \leq 35\text{ km} \land \text{sstTerm} > 0,5)}{100}
   \]
   *(Catatan Audit: Fungsi ini hanya dapat menghasilkan 8 kemungkinan nilai diskrit: `0.00, 0.25, 0.35, 0.40, 0.60, 0.65, 0.75, 1.00`).*

3. **Persamaan Logistik GLM (Baris 166â€“169):**
   \[
   \eta_{\text{GLM}} = \beta_0 + \beta_1\,\text{SST} + \beta_2\,\text{SST}^2 + \beta_3\log_{10}(\max(0.01, \text{Chl})) + \beta_4\,\text{Front} + \beta_5|\text{Depth}|
   \]
   \[
   RHSI_{\text{GLM}} = \frac{1}{1 + \exp(-\eta_{\text{GLM}})}
   \]

---

### 2.2 Evaluasi Khusus Tiga Formula Wajib Phase 4

Berikut adalah status verifikasi untuk ketiga formula utama yang dipersyaratkan pada Bagian 6 Master Prompt:

#### Formula 1: RHSI Weighted Ensemble ($\overline{RHSI}_w$)
\[
RHSI_w = \sum_i w_i \, RHSI_i, \qquad w_i \geq 0, \quad \sum_i w_i = 1
\]
- **Status Implementasi:** **`PARTIALLY_VERIFIED` (Sudah ditulis dan dijalankan pada data, namun terdapat ketidaksinkronan bobot legacy_species_removed pasca-remediasi)**
- **Rumus Aktual di [`scratch/execute_stage_3_4_to_3_7.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/execute_stage_3_4_to_3_7.js#L229-L238) & Baris 313:**
  ```javascript
  const wMaxEnt = Math.pow(Math.max(0, aucMaxEnt - 0.5), 2);
  const wRF = Math.pow(Math.max(0, aucRF - 0.5), 2);
  const wGLM = Math.pow(Math.max(0, aucGLM - 0.5), 2);
  const totalW = wMaxEnt + wRF + wGLM;
  // ...
  const hsiEns = parseFloat((preds.hsi_maxent * w.maxent + preds.hsi_rf * w.rf + preds.hsi_glm * w.glm).toFixed(3));
  ```
- **Bukti Pengujian:** Diuji di [`scratch/test_weighted_disagreement.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/test_weighted_disagreement.py#L14-L15) (`rhsi_w = sum(w * r for w, r in zip(weights, rhsi))` $\rightarrow 0,6600$).

#### Formula 2: Weighted Ensemble Disagreement ($\sigma_w$)
\[
\sigma_w = \sqrt{\sum_i w_i \left(RHSI_i - RHSI_w\right)^2}
\]
- **Status Implementasi:** **`CONFLICTING` (Sudah diuji di unit test mandiri Python, TETAPI BELUM dijalankan pada dataset produksi JSON/JS)**
- **Rumus Aktual yang Menghasilkan Data Produksi di [`scratch/execute_stage_3_4_to_3_7.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/execute_stage_3_4_to_3_7.js#L316-L317):**
  ```javascript
  // SALAH: Menggunakan rata-rata tak-berbobot (/ 3), BUKAN bobot w_i!
  const variance = (Math.pow(preds.hsi_maxent - hsiEns, 2) + Math.pow(preds.hsi_rf - hsiEns, 2) + Math.pow(preds.hsi_glm - hsiEns, 2)) / 3;
  const uncertainty = parseFloat(Math.sqrt(variance).toFixed(3));
  ```
- **Rumus di Unit Test Mandiri [`scratch/test_weighted_disagreement.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/test_weighted_disagreement.py#L18):**
  ```python
  # BENAR (tetapi hanya dijalankan pada vektor uji 3 angka [0.8, 0.6, 0.4]):
  sigma_w = math.sqrt(sum(w * ((r - rhsi_w) ** 2) for w, r in zip(weights, rhsi)))
  ```
- **Dampak Teknis:** Seluruh nilai `uncertainty` di [`outputs/sdm/hsi/hsi_all_scenarios_projections.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/outputs/sdm/hsi/hsi_all_scenarios_projections.json) dan [`html/sdm_data.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/html/sdm_data.js) saat ini merupakan simpangan baku tak-berbobot ($\sigma_{\text{unweighted}}$), bukan $\sigma_w$.

#### Formula 3: Uncertainty-Penalized Advisory Score ($PFG_{\text{advisory}}$)
\[
PFG_{\text{advisory}} = RHSI_w - \lambda \sigma_w
\]
- **Status Implementasi:** **`DOCUMENTED` & `UNIT_TESTED_ONLY` (Belum dihitung atau disimpan di dalam dataset proyeksi maupun kode dashboard)**
- **Bukti di Kode:**
  - Hanya ditemukan di dalam skrip unit test mandiri [`scratch/test_weighted_disagreement.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/test_weighted_disagreement.py#L21-L24):
    ```python
    pfg_raw = rhsi_w - (lam * sigma_w)
    pfg_vis = max(0.0, min(1.0, pfg_raw))
    ```
  - **Tidak ditemukan** di [`scratch/execute_stage_3_4_to_3_7.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/execute_stage_3_4_to_3_7.js), [`outputs/sdm/hsi/hsi_all_scenarios_projections.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/outputs/sdm/hsi/hsi_all_scenarios_projections.json), [`html/sdm_data.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/html/sdm_data.js), maupun [`html/index.html`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/html/index.html).
  - Analisis sensitivitas terhadap berbagai nilai $\lambda \in \{0.00, 0.25, 0.50, 0.75, 1.00\}$ dan bobot alternatif saat ini baru berupa tabel statis di dalam dokumen markdown ([`PHASE_4_PRECONDITIONS_FINAL.md`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/PHASE_4_PRECONDITIONS_FINAL.md)) dan belum memiliki skrip/modul eksekusi dinamis pada grid `2.050` sel.

