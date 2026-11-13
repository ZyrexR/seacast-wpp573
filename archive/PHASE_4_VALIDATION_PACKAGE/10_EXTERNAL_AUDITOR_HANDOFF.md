# 10 â€” EXTERNAL AUDITOR HANDOFF GUIDE
## Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi â€” WPPNRI 573
### Independent Audit Briefing & Verification Playbook

- **Tanggal Handoff:** 1 November 2026
- **Lokasi Workspace:** `C:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis`
- **Lokasi Paket Validasi:** `C:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\PHASE_4_VALIDATION_PACKAGE\`

---

## 1. GAMBARAN PROYEK & STATUS AKTUAL

Proyek **Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi â€” WPPNRI 573** bertujuan mengintegrasikan data historis pendaratan perikanan pelabuhan (2012â€“2025), indikator oseanografi satelit/reanalisis (2015â€“2025), pemodelan kesesuaian habitat relatif (*Relative Habitat Suitability Index* / `RHSI`), informasi risiko maritim, dan skrining mitigasi tangkapan sampingan (*bycatch*) hiu ke dalam satu **Dynamic Ocean Management & Decision Support System (DOM-DSS)**.

### Status Aktual Berdasarkan Pemeriksaan Forensik (Read-Only)
1. **Fondasi Data & Dokumen Spesifikasi (`VERIFIED`):**
   - Dataset CSV/JSON perikanan (132 bulan, 2015â€“2025), armada (14 tahun, 2012â€“2025), deret waktu oseanografi (132 bulan), grid batimetri & oseanografi Sub-AOI Prigi (`2.050` sel pada resolusi $0.05^\circ$), proyeksi SDM 6 skenario, dan 5 layer GeoJSON tersedia secara fisik dan dapat dibaca.
   - Dokumen spesifikasi ilmiah dan hukum di [`PHASE_4_PRECONDITIONS_FINAL.md`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/PHASE_4_PRECONDITIONS_FINAL.md) sangat komprehensif dan telah mendefinisikan 4 *Blocking Constraints* serta 5 *Scientific Protocols* dengan tepat.
2. **Kesenjangan antara Laporan Audit Terdahulu vs Kode & Data Aktual (`CONFLICTING`):**
   - File [`PHASE_4_READINESS_GATE.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/PHASE_4_READINESS_GATE.json) dan [`PHASE_4_FINAL_READINESS_MATRIX.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/PHASE_4_FINAL_READINESS_MATRIX.json) dihasilkan oleh skrip statis (`scratch/execute_task10_final.js` & `scratch/build_matrix_json.py`) yang mendeklarasikan status `PASS` untuk `PG-01` s/d `PG-12`, padahal sebagian persyaratan tersebut baru diselesaikan di tingkat dokumen `.md` atau skrip unit test terpisah dan **belum diterapkan ke dalam dataset utama maupun kode aplikasi (`html/index.html` dan `app.py`)**.

---

## 2. DAFTAR FILE PRIORITAS YANG HARUS DIPERIKSA PERTAMA OLEH AUDITOR EKSTERNAL

Auditor eksternal dapat memverifikasi seluruh temuan dalam paket ini hanya dengan memeriksa 10 file kunci berikut secara berurutan:

| Urutan | Path File | Baris Kunci | Apa yang Harus Diperiksa oleh Auditor |
|---:|---|---|---|
| **1** | [`scratch/execute_stage_3_1_to_3_3.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/execute_stage_3_1_to_3_3.js) | L294â€“L400 | Membuktikan bahwa titik *presence* Cakalang (`405`), Madidihang (`387`), legacy_species_removed (`189`), dan Bycatch (`105`) adalah **proxy heuristik** yang difilter langsung dari variabel prediktor Musim Timur (SST, Chl-a, Front, Depth, Harbor Distance). |
| **2** | [`scratch/execute_stage_3_4_to_3_7.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/execute_stage_3_4_to_3_7.js) | L56â€“L275 & L313â€“L378 | Membuktikan bahwa: (a) MaxEnt, RF, dan GLM adalah persamaan analitik berparameter tetap (*hardcoded*) tanpa *spatial cross-validation*; (b) Evaluasi background menggunakan 1.000 sel pertama (bukan 5.000 titik TGB); (c) Baris 316 menghitung `uncertainty` menggunakan **varians tak-berbobot (`/ 3`)**, bukan $\sigma_w$ berbobot; (d) `raw_advisory_score` dan `visualization_score` belum dihitung. |
| **3** | [`html/index.html`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/html/index.html) | L437, L660â€“L685, L1236â€“L1254, L1450â€“L1550, L1781 | Membuktikan bahwa: (a) Baris 437 masih menampilkan `18.405.461 kg`; (b) Baris 660â€“685 masih memuat `Coastal Upwelling Index (CUI)` & `EMPIRICAL PROOF`; (c) Baris 1236â€“1254 `switchView()` **tidak memanggil `initSdmMap()`** untuk `view-sdm`; (d) Baris 1450â€“1550 adalah peta oseanografi (bukan matriks risiko maritim 4-tier seperti yang diklaim `PG-06`); (e) Tidak ada footer sitasi hukum (`PG-10`). |
| **4** | [`data/master_prigi_all_inclusive.csv`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/master_prigi_all_inclusive.csv) & [`FINAL_FISHERIES_RECONCILIATION.csv`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/FINAL_FISHERIES_RECONCILIATION.csv) | L88 (`2022-04-01`) | Membandingkan `total_pelagis_kg = 198446` di CSV master dengan jumlah 12 spesies `198246` di CSV rekonsiliasi (+200 kg typo). |
| **5** | [`outputs/sdm/zones/dynamic_bycatch_mitigation_zones.geojson`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/outputs/sdm/zones/dynamic_bycatch_mitigation_zones.geojson) | Seluruh 123 fitur | Memverifikasi 123 poligon valid (`-242 m` s/d `-950 m`), adanya 6 poligon tepi yang meluber $0.025^\circ$ hingga `110.975Â°E` dan `113.025Â°E`, serta adanya 5 field CITES (dibandingkan 8 field di `data/prigi_elasmobranch_bycatch_records.csv`). |
| **6** | [`models/sdm/ensemble/model_evaluations_summary.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/models/sdm/ensemble/model_evaluations_summary.json) | L1â€“L127 | Memverifikasi bobot ensemble ternormalisasi ($\sum w_i = 1,000$) dan nilai AUC/TSS per spesies. |
| **7** | [`scratch/test_weighted_disagreement.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/test_weighted_disagreement.py) & [`scratch/calc_sub_aoi_area.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/calc_sub_aoi_area.py) | Seluruh baris | Memverifikasi kebenaran unit test mandiri untuk $\overline{RHSI}_w, \sigma_w, PFG_{\text{advisory}}$ serta perhitungan luas elipsoidal Sub-AOI Prigi ($59.550,43\text{ km}^2$). |
| **8** | [`data/spatial_config_aoi.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/spatial_config_aoi.json) & [`data/oceanographic/metadata/data_sources.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/oceanographic/metadata/data_sources.json) | L25â€“L42 & L1â€“L145 | Memeriksa ketidaksinkronan batas selatan `-10.5` vs `-10.45` dan kelengkapan field provenance (baru 12 field dari 21 field wajib). |
| **9** | [`utils.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/utils.py) & [`app.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/app.py) | L43â€“L145 | Memverifikasi bahwa aplikasi Streamlit gagal menemukan `data/master.csv` dan beralih ke `_make_dummy()`. |
| **10** | [`PHASE_4_READINESS_GATE.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/PHASE_4_READINESS_GATE.json) & [`scratch/execute_task10_final.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/execute_task10_final.js) | L1â€“L290 | Membuktikan bagaimana artefak readiness gate dibuat secara statis dari skrip tanpa pengujian otomatis terhadap DOM `html/index.html`. |

---

## 3. HAL YANG PERLU DIVERIFIKASI LEBIH LANJUT & PERTANYAAN TERBUKA (OPEN QUESTIONS)

1. **Keputusan Arsitektur Antarmuka Phase 4 (Dual Frontend vs Unified Frontend):**
   - Apakah aplikasi Python Streamlit (`app.py` + `pages/1..7_*.py`) tetap akan dipertahankan dan diperbaiki sebagai pendamping analitik Python, atau apakah seluruh fokus antarmuka Phase 4 DOM-DSS dipusatkan pada Web GIS Standalone (`html/index.html`) yang didukung oleh layanan data Python? *(Rencana di `PHASE_4_IMPLEMENTATION_PLAN.md` memperbaiki keduanya agar konsisten 100%).*
2. **Perlakuan Metodologi SDM pada Phase 4:**
   - Mengingat `execute_stage_3_4_to_3_7.js` menggunakan fungsi respons analitik deterministik pada label *presence-proxy* heuristik, apakah Phase 4 tetap menggunakan fungsi respons analitik tersebut (dengan perbaikan wajib pada rumus $\sigma_w$ berbobot, `raw_advisory_score`, `visualization_score`, dan analisis sensitivitas $\lambda$/bobot/threshold, serta pelabelan ketat sebagai *Heuristic Multi-Model Envelope Screening*), atau apakah diperlukan pelatihan ulang model statistik di Python?
3. **Sumber Umpan Cuaca Maritim pada Mode Operasional vs Simulasi Akademik:**
   - Karena workspace berjalan secara lokal tanpa kredensial API waktu-nyata ke server BMKG/Copernicus, bagaimana perilaku default tab *Maritime Risk Assessment* saat dibuka secara offline? *(Sesuai Protocol 2, sistem harus menampilkan banner `DATA UNAVAILABLE / STALE DATA â€” ACADEMIC SCENARIO SIMULATION MODE` secara default ketika umpan waktu-nyata tidak terhubung, sambil tetap mengizinkan simulasi skenario gelombang/angin dan evaluasi profil kapal multivariat).*

---

## 4. REKOMENDASI TINDAK LANJUT (RECOMMENDED NEXT ACTION)

Berdasarkan bukti forensik di atas, rekomendasi auditor adalah:

> **RESOLVE CRITICAL ISSUES FIRST / PROCEED CONDITIONALLY TO PHASE 4.1 REMEDIATION**
>
> Proyek **TIDAK BOLEH** langsung mengklaim bahwa fondasi Phase 1â€“3 dan Readiness Gate `PG-01` s/d `PG-12` telah berstatus `PASS` tanpa cacat. Sebaliknya, eksekusi implementasi Phase 4 harus dimulai dari **PHASE 4.1 (Remediasi Baseline & Integrasi Provenance)** untuk menyelesaikan 6 perbaikan teknikal wajib berikut sebelum membangun modul lanjutan Phase 4.2â€“4.7:
> 1. Perbaiki `total_pelagis_kg` bulan April 2022 (`198.446` $\rightarrow$ `198.246 kg`, kumulatif `18.405.261 kg`) di `data/master_prigi_all_inclusive.csv`, `ocean_fisheries_master_132m.json`, `html/oceanographic_data.js`, dan `html/index.html`.
> 2. Hitung ulang seluruh proyeksi di `outputs/sdm/hsi/hsi_all_scenarios_projections.json` dan `html/sdm_data.js` menggunakan rumus **Weighted Ensemble Disagreement ($\sigma_w$)** yang benar, serta simpan `hsi_rf`, `hsi_glm`, `sigma_w`, `raw_advisory_score`, dan `visualization_score`.
> 3. Lakukan *exact geometric clipping* terhadap batas Sub-AOI Prigi ($[111.00^\circ, 113.00^\circ\text{ BT}] \times [-10.45^\circ, -8.00^\circ\text{ LS}]$) pada kelima file GeoJSON di `outputs/sdm/zones/` dan `prigi_shelf_break_200m.geojson`, serta seragamkan batas `-10.45Â°S` dan jumlah sel `2.050` di seluruh file konfigurasi/manifest.
> 4. Sematkan ke-8 atribut taksonomi CITES (`cites_reference_date = "2026-03-05"`, `identification_verification_status = "NEEDS_VERIFICATION"`) ke dalam `outputs/sdm/zones/dynamic_bycatch_mitigation_zones.geojson` dan render sebagai poligon di peta.
> 5. Perbaiki bug `switchView('view-sdm')` di `html/index.html` baris 1248 dan hapus seluruh label terlarang (`Coastal Upwelling Index (CUI)`, `EMPIRICAL PROOF`).
> 6. Hubungkan `utils.py` ke `data/master_prigi_all_inclusive.csv` agar aplikasi Streamlit tidak lagi menggunakan data acak `_make_dummy()`.

