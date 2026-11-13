<USER_REQUEST>
# SEACAST PRIGI V3
# PHASE 2 — CORE RADAR ENGINE & DYNAMIC FILTER
## Scientific Computation Integration, Spatial Intelligence & Radar Visualization

**Project:** SEACAST PRIGI V3  
**Phase:** 2 — Core Radar Engine & Dynamic Filter  
**Execution Mode:** Structured Implementation / Scientific-First Engineering  
**Priority:** High  
**Target Framework:** Laravel 13.40.0  
**Backend Runtime:** PHP 8.3.36  
**Scientific Computation:** Python  
**Spatial Database:** PostgreSQL 17.11 + PostGIS 3.6.2  
**Frontend:** Blade + Livewire 4 + Alpine.js + Tailwind CSS  
**Map Engine:** MapLibre GL JS  
**Architecture:** Modular Monolith  
**Timezone:** Asia/Jakarta (WIB)

**Authorization:** User has explicitly authorized Phase 2 implementation.

---

# 1. MANDAT UTAMA

Mulai implementasi Phase 2 SEACAST PRIGI V3 dengan fokus membangun mesin analisis spasial dan oseanografi yang menjadi inti platform.

Phase 2 bukan pekerjaan redesign homepage.

Fokus utamanya adalah memindahkan dan mengintegrasikan kemampuan analisis ilmiah ke arsitektur Laravel baru tanpa merusak metodologi, parameter, data, maupun hasil ilmiah yang sudah ditetapkan pada sistem sebelumnya.

Target utama:

1. Scientific Data Access Layer.
2. Oceanographic Data Engine.
3. Python Scientific Computation Bridge.
4. Species Habitat Prediction Engine.
5. Habitat Suitability Index (HSI).
6. Dynamic Ocean Management Engine.
7. Bycatch Risk Classification Engine.
8. Dynamic Filter System.
9. Core Radar API.
10. MapLibre Spatial Visualization.
11. Scientific Validation and Regression Testing.

**Prinsip utama:**

> Every radar result must be traceable to its source data, model version, parameters, computation timestamp, and scientific classification logic.

Jangan membuat visualisasi yang terlihat ilmiah tetapi menggunakan angka statis, data dummy, atau hasil simulasi yang tidak diberi label.

---

# 2. WAJIB: SKILL DISCOVERY DAN SKILL UTILIZATION

Sebelum menyentuh source code, lakukan pemeriksaan Skills dan kemampuan agent yang tersedia pada workspace Antigravity.

Cari dan identifikasi Skills yang relevan untuk:

- Laravel architecture and backend engineering.
- PHP application development.
- Python scientific computing.
- Geospatial analysis and PostGIS.
- GIS and spatial database engineering.
- Remote sensing and oceanographic data processing.
- Machine learning and ensemble modeling.
- Scientific software testing.
- Frontend engineering and interactive mapping.
- Security and application architecture.

Periksa lokasi Skill atau konfigurasi agent yang benar-benar tersedia pada workspace, misalnya direktori konfigurasi `.agent/`, `.agents/`, atau lokasi Skill yang ditemukan melalui dokumentasi workspace.

Ketentuan:

1. Gunakan hanya Skill yang benar-benar ditemukan dan dapat dibaca.
2. Baca instruksi Skill sebelum menggunakannya.
3. Pilih Skill sesuai pekerjaan yang sedang dikerjakan.
4. Jika Skill mendukung workflow tertentu, ikuti workflow tersebut.
5. Jangan membuat klaim telah menggunakan Skill yang tidak tersedia.
6. Jangan menginstal Skill atau dependency eksternal tanpa kebutuhan dan pemeriksaan keamanan.
7. Jika tidak tersedia Skill yang relevan, dokumentasikan sebagai `SKILL NOT AVAILABLE` dan lanjutkan menggunakan engineering best practices.

Buat inventaris:

`docs/migration/PHASE_2_SKILL_USAGE_REGISTER.md`

Format:

| Skill | Lokasi | Relevansi | Subphase Penggunaan | Status |
|---|---|---|---|---|
| Nama aktual | Path aktual | Alasan | Tahapan | Used / Unavailable |

Jika tersedia beberapa Skill, jangan memaksakan semuanya ke seluruh pekerjaan. Gunakan secara selektif berdasarkan tugas.

---

# 3. REFERENSI WAJIB DAN BASELINE

Baca seluruh dokumen berikut sebelum implementasi:

## 3.1 Phase 0

- `MIGRATION_EXECUTIVE_SUMMARY.md`
- `CURRENT_SYSTEM_INVENTORY.md`
- `API_CONTRACT_INVENTORY.md`
- `DATABASE_POSTGIS_AUDIT.md`
- `SCIENTIFIC_INTEGRITY_AUDIT.md`
- `PYTHON_ENGINE_INVENTORY.md`
- `FRONTEND_COMPONENT_INVENTORY.md`
- `LARAVEL_TARGET_ARCHITECTURE.md`
- `MIGRATION_ROADMAP.md`
- `MIGRATION_RISK_REGISTER.md`
- `PHASE_0_FINAL_RECONCILIATION.md`

## 3.2 Phase 1 dan 1.1

- `PHASE_1_IMPLEMENTATION_REPORT.md`
- `PHASE_1_CHANGELOG.md`
- `PHASE_1_TEST_REPORT.md`
- `PHASE_1_ENVIRONMENT_SETUP.md`
- `PHASE_1_KNOWN_LIMITATIONS.md`
- `PHASE_1_1_REMEDIATION_REPORT.md`
- `PHASE_1_1_SECURITY_AUDIT.md`
- `PHASE_1_1_DATABASE_READONLY_VERIFICATION.md`
- `PHASE_1_1_SCIENTIFIC_BASELINE_RECONCILIATION.md`
- `PHASE_1_1_LEGACY_SERVER_SHUTDOWN_REPORT.md`
- `PHASE_1_1_REGRESSION_TEST_REPORT.md`
- `PHASE_1_1_FINAL_READINESS.md`

Jika ada dokumen yang belum ditemukan, jangan mengarang isinya. Catat dokumen yang tidak tersedia dan gunakan sumber aktual yang masih ada.

## 3.3 Scientific baseline terkunci

Baseline berikut harus dipertahankan:

**Database:**

- PostgreSQL 17.11.
- PostGIS 3.6.2.
- Canonical grids: 8,129 polygon.
- EPSG:4326.
- Grid resolution: 0.10°.
- Spatial GiST index: `idx_canonical_grids_geom`.
- Bounding box: Lon 102.50–126.50°E; Lat -14.00 hingga -6.80°.

**Data aktual yang telah diaudit:**

| Dataset | Baseline |
|---|---:|
| `canonical_grids` | 8,129 rows |
| `daily_predictions` | 8,129 rows untuk 2026-11-03 |
| `grid_monthly_predictions` | 97,548 rows |
| `prigi_port_landings` | 132 monthly records |
| `prigi_fleet_by_gt` | 3 aggregated rows |
| Total armada terwakili | 1,269 vessels |

Jangan menyamakan 3 baris agregasi armada dengan 3 kapal.

**Species:**

- Cakalang.
- Madidihang.
- Legacy species removed from TCT scope.

**Model baseline:**

| Species | MaxEnt | Random Forest | GLM | AUC | TSS |
|---|---:|---:|---:|---:|---:|
| Cakalang | 0.320 | 0.355 | 0.325 | 0.795 | 0.630 |
| Madidihang | 0.295 | 0.365 | 0.340 | 0.784 | 0.606 |
| Legacy species removed from TCT scope | 0.000 | 0.517 | 0.483 | 0.860 | 0.743 |

Legacy species removed from TCT scope MaxEnt weight 0.000 harus dipertahankan berdasarkan evaluasi proxy dengan AUC 0.507.

Jangan mengganti GLM dengan GAM atau mengubah bobot ensemble tanpa bukti metodologis dan persetujuan eksplisit.

**Oceanographic limitations:**

- Data spasial bulanan mencakup 12 bulan tahun 2025.
- Data pendaratan bulanan mencakup Januari 2015–Desember 2025.
- Jangan mengklaim seluruh 132 bulan memiliki spatial predictions.
- Fallback oseanografi lokal harus tetap diberi label simulasi terkalibrasi.
- Jangan mengklaim live BMKG synchronization tanpa bukti koneksi resmi.
- Particle flow yang tidak berasal dari observasi aktual harus diberi label simulasi.

---

# 4. ATURAN KESELAMATAN DAN ARSITEKTUR

Aturan berikut tidak boleh dilanggar:

1. Jangan menghapus direktori `web/`.
2. Jangan menghidupkan kembali server Next.js lama.
3. Jangan menjalankan backend aplikasi berbasis Node.js.
4. Jangan menghapus instalasi Node.js yang mungkin dibutuhkan untuk build aset.
5. Jangan mengubah struktur tabel ilmiah yang sudah ada.
6. Jangan menjalankan `migrate:fresh`.
7. Jangan menjalankan `db:wipe`.
8. Jangan menggunakan `DROP TABLE`, `TRUNCATE`, atau mass DELETE.
9. Jangan memberikan hak tulis kepada `seacast_readonly`.
10. Jangan mengganti data sumber dengan dummy.
11. Jangan mengubah baseline ilmiah secara diam-diam.
12. Jangan mengklaim model berhasil hanya berdasarkan HTTP 200.
13. Jangan membuat hasil prediksi palsu jika Python engine gagal.
14. Jangan menjalankan scheduler inferensi otomatis tanpa pengujian dan persetujuan terpisah.
15. Jangan melakukan deployment produksi.

## 4.1 Pemisahan database access

Pertahankan koneksi:

`seacast_readonly`

Koneksi tersebut khusus untuk membaca tabel ilmiah sumber.

Jika Phase 2 membutuhkan penyimpanan hasil komputasi, lakukan desain terpisah untuk data turunan yang dimiliki aplikasi.

Target konseptual:

- `public` atau schema sumber yang sudah ada: tetap tidak dapat dimodifikasi oleh aplikasi.
- `seacast_runtime`: schema terpisah untuk metadata eksekusi, job, dan hasil turunan apabila memang diperlukan.

Jangan langsung membuat schema baru sebelum memeriksa struktur database, hak akses, dan kebutuhan penyimpanan aktual.

Jika dibutuhkan akun penulis khusus, buat dengan privilege minimum dan batasi pada schema runtime yang disetujui.

Tidak boleh ada privilege write terhadap tabel ilmiah sumber.

Semua perubahan schema aplikasi harus melalui migration Laravel yang terkontrol dan reversible.

---

# 5. TARGET ARCHITECTURE

Pertahankan pembagian tanggung jawab:

## Laravel

Bertanggung jawab untuk:

- Application orchestration.
- Authentication dan authorization.
- Request validation.
- API.
- Scientific data access.
- Job management.
- Result persistence.
- Dynamic filter state.
- Map data delivery.
- Logging dan audit trail.

## Python

Bertanggung jawab untuk:

- Scientific preprocessing.
- Species prediction.
- Model inference.
- Ensemble calculation.
- HSI calculation.
- Spatial analytical operations yang sudah tersedia.
- Scientific validation.
- Output terstruktur.

## PostgreSQL/PostGIS

Bertanggung jawab untuk:

- Canonical spatial grid.
- Spatial queries.
- Oceanographic data access.
- Spatial indexing.
- Runtime-derived result storage apabila dibutuhkan.

## MapLibre

Bertanggung jawab untuk:

- Spatial visualization.
- Layer management.
- Grid interaction.
- Radar visualization.
- Dynamic filter rendering.
- Legend dan tooltip.

Laravel adalah satu-satunya framework aplikasi backend.

Python tetap menjadi mesin komputasi ilmiah, bukan framework aplikasi.

---

# 6. IMPLEMENTATION ROADMAP

Laksanakan Phase 2 secara berurutan.

Jangan mengerjakan seluruh modul sekaligus tanpa pengujian setiap tahapan.

## PHASE 2.0 — FORENSIC SOURCE AUDIT & ENGINE DISCOVERY

Sebelum implementasi:

1. Periksa seluruh source code Laravel.
2. Periksa seluruh script Python yang tersedia.
3. Periksa file model dan konfigurasi.
4. Periksa dataset dan metadata.
5. Periksa dependency Python dan lockfile.
6. Periksa API lama melalui dokumentasi kontrak.
7. Periksa komponen MapLibre yang sudah tersedia.
8. Identifikasi fungsi yang sudah aktif, parsial, placeholder, atau belum tersedia.

Buat peta hubungan:

`Laravel Request → Service → Python Engine → PostGIS → Result → API → MapLibre`

Identifikasi semua bagian yang dapat dipakai ulang.

Jangan menulis ulang script Python jika implementasi aktual sudah tersedia dan dapat divalidasi.

Periksa kompatibilitas Python secara aktual. Jangan mengasumsikan seluruh dependency mendukung Python 3.14.7.

Jika ada dependency yang tidak kompatibel, dokumentasikan versi yang dibutuhkan. Jangan melakukan upgrade atau downgrade global tanpa rencana dan persetujuan.

**Output:**

`docs/migration/PHASE_2_0_ENGINE_DISCOVERY.md`

Acceptance criteria:

- Inventaris Python selesai.
- Semua input dan output engine teridentifikasi.
- Model dan dependency teridentifikasi.
- Data source dan batasan data terdokumentasi.
- Tidak ada perubahan database.
- Tidak ada perubahan baseline.

## PHASE 2.1 — SCIENTIFIC DATA ACCESS LAYER

Bangun lapisan akses data ilmiah Laravel.

Struktur modular yang direkomendasikan:

`app/Domain/ScientificData/`

`app/Domain/Oceanography/`

`app/Domain/Species/`

`app/Domain/Radar/`

`app/Domain/DynamicOceanManagement/`

`app/Domain/Bycatch/`

`app/Infrastructure/Python/`

`app/Infrastructure/Geospatial/`

Gunakan repository dan service hanya ketika memiliki tanggung jawab yang jelas. Hindari abstraksi berlebihan.

Buat data access layer untuk:

- Canonical grids.
- Daily predictions.
- Monthly predictions.
- SST.
- Chlorophyll-a.
- Ocean currents.
- Wind.
- Bathymetry.
- Landings.
- Fleet aggregation.

Nama tabel dan kolom wajib diambil dari database aktual.

Jangan mengasumsikan struktur kolom berdasarkan nama dataset.

Gunakan:

- Eloquent untuk relasi dan entitas standar.
- Parameterized SQL untuk operasi PostGIS.
- Pagination atau bounded queries.
- Spatial index.
- Query profiling untuk operasi berat.

Gunakan operasi PostGIS yang sesuai, seperti:

- `ST_Intersects`
- `ST_DWithin`
- `ST_Distance`
- `ST_MakeEnvelope`
- `ST_AsGeoJSON`
- `ST_IsValid`
- `ST_Extent`

Pastikan perhitungan jarak NM menggunakan geodesic/geography atau metode yang tervalidasi.

Jangan mengonversi derajat menjadi nautical miles menggunakan asumsi yang tidak memperhitungkan lokasi dan geometri.

**Output:**

`docs/migration/PHASE_2_1_DATA_ACCESS_REPORT.md`

Acceptance criteria:

- Query data sumber berhasil.
- Hasil query konsisten dengan baseline.
- Read-only privilege tetap berlaku.
- Tidak ada full-table scan yang tidak diperlukan.
- Query spasial menggunakan indeks ketika relevan.

## PHASE 2.2 — PYTHON SCIENTIFIC COMPUTATION BRIDGE

Bangun integrasi Laravel dengan Python.

Python tetap menjadi mesin komputasi utama.

Jangan memindahkan algoritma ilmiah ke PHP hanya demi menyederhanakan integrasi.

Buat adapter atau service yang bertanggung jawab menjalankan script Python yang sudah diaudit.

Target alur:

`Laravel Request → Validation → Job/Service → Python Process → JSON Result → Validation → Persistence/Response`

### Persyaratan teknis

1. Gunakan process execution yang aman.
2. Gunakan argument array, bukan interpolasi shell command.
3. Jangan menerima path script bebas dari request pengguna.
4. Gunakan allowlist script yang sudah diperiksa.
5. Validasi semua parameter sebelum eksekusi.
6. Terapkan timeout.
7. Batasi ukuran input dan output.
8. Validasi struktur JSON hasil Python.
9. Tangani exit code dan stderr.
10. Catat durasi eksekusi dan versi model.
11. Jangan mencatat secret atau kredensial.
12. Cegah path traversal.
13. Jangan mengeksekusi file yang diunggah pengguna sebagai script.

Gunakan Laravel Queue apabila diperlukan untuk pekerjaan komputasi berat.

Jika queue memerlukan tabel atau penyimpanan tambahan, gunakan storage aplikasi yang terisolasi dari tabel ilmiah sumber.

Jangan mengasumsikan bahwa queue sudah berjalan hanya karena konfigurasi tersedia.

### Kontrak output

Setiap hasil Python harus memiliki struktur yang tervalidasi, minimal mencakup:

- `status`
- `engine`
- `model_version`
- `species`
- `generated_at`
- `input_metadata`
- `parameters`
- `results`
- `warnings`
- `provenance`

Struktur final disesuaikan dengan engine yang benar-benar tersedia.

Jika Python gagal:

- Jangan menghasilkan prediksi pengganti palsu.
- Kembalikan error yang dapat ditelusuri.
- Pertahankan hasil sebelumnya jika kebijakan aplikasi mengizinkan.
- Tandai hasil lama sebagai stale bila relevan.

Jangan menjalankan inferensi otomatis harian pada tahap ini.

**Output:**

`docs/migration/PHASE_2_2_PYTHON_BRIDGE_REPORT.md`

Acceptance criteria:

- Python dapat dipanggil dari Laravel.
- Script yang digunakan berasal dari inventaris aktual.
- Input tervalidasi.
- Output JSON tervalidasi.
- Timeout dan error handling bekerja.
- Tidak ada shell injection.
- Hasil dapat direproduksi dengan input dan versi model yang sama.

## PHASE 2.3 — OCEANOGRAPHIC DATA ENGINE

Bangun service untuk mengelola variabel oseanografi yang tersedia.

Variabel target:

1. Sea Surface Temperature (SST).
2. Chlorophyll-a.
3. Ocean current.
4. Wind.
5. Bathymetry.

Untuk setiap variabel, identifikasi:

- Source.
- Dataset/table.
- Unit.
- Spatial resolution.
- Temporal resolution.
- Timestamp.
- Missing-value convention.
- Validity range.
- Processing method.
- Quality flag.

Jangan mengasumsikan semua variabel memiliki sumber, resolusi, dan periode yang sama.

### Validasi ilmiah

SST:

- Pastikan satuan Celsius.
- Validasi nilai dan timestamp.
- Pastikan gradient dihitung dalam °C/km.
- Jangan mengubah threshold tanpa dokumentasi.

Chlorophyll-a:

- Pastikan satuan mg/m³.
- Periksa nilai nol, missing, dan outlier.
- Jangan menganggap missing sebagai nol.

Current:

- Periksa komponen U/V jika tersedia.
- Pastikan satuan dan orientasi vektor.
- Bedakan current observation dengan simulated particle flow.

Wind:

- Periksa satuan dan arah.
- Bedakan data aktual dengan data fallback.

Bathymetry:

- Verifikasi sumber data aktual.
- Periksa satuan kedalaman dan konvensi tanda.
- Jangan menggunakan dekorasi kontur sebagai pengganti bathymetry ilmiah.

Jangan menampilkan variabel yang tidak tersedia sebagai data aktual.

**Output:**

`docs/migration/PHASE_2_3_OCEANOGRAPHIC_ENGINE_REPORT.md`

Acceptance criteria:

- Data source setiap variabel terdokumentasi.
- Unit konsisten.
- Timestamp tervalidasi.
- Missing data ditangani dengan benar.
- Tidak ada data sintetis yang diklaim sebagai observasi.

## PHASE 2.4 — SPECIES HABITAT PREDICTION ENGINE

Integrasikan prediksi habitat tiga spesies:

- Cakalang.
- Madidihang.
- Legacy species removed from TCT scope.

Gunakan model aktual dari inventaris Python.

Model baseline:

- MaxEnt.
- Random Forest.
- GLM.

Pertahankan bobot ensemble:

| Species | MaxEnt | RF | GLM |
|---|---:|---:|---:|
| Cakalang | 0.320 | 0.355 | 0.325 |
| Madidihang | 0.295 | 0.365 | 0.340 |
| Legacy species removed from TCT scope | 0.000 | 0.517 | 0.483 |

Jangan mengubah bobot karena optimasi kode.

### Persyaratan implementasi

- Input features harus mengikuti model training.
- Feature ordering harus konsisten.
- Missing values harus mengikuti metode preprocessing model.
- Output model harus memiliki validasi rentang.
- Ensemble harus mengikuti formula yang terdokumentasi.
- Setiap hasil memiliki model version dan timestamp.
- Jangan mencampur output antarspesies.

Pastikan AUC dan TSS tidak digunakan sebagai nilai prediksi HSI.

AUC dan TSS merupakan metrik evaluasi model, bukan nilai kesesuaian habitat.

Jika validasi independen belum tersedia, tampilkan keterbatasannya secara eksplisit.

Jangan mengklaim performa generalisasi spasial tanpa pengujian spatial cross-validation atau validasi independen yang sesuai.

**Output:**

`docs/migration/PHASE_2_4_SPECIES_ENGINE_REPORT.md`

Acceptance criteria:

- Ketiga spesies dapat diproses jika input dan model tersedia.
- Model yang digunakan dapat ditelusuri.
- Bobot ensemble sesuai baseline.
- Hasil dapat dibandingkan dengan output Python baseline.
- Tidak ada perubahan model training tanpa persetujuan.

## PHASE 2.5 — HABITAT SUITABILITY INDEX

Implementasikan HSI berdasarkan metodologi aktual yang tersedia.

Periksa apakah HSI pada sistem lama:

- Menggunakan output ensemble secara langsung.
- Menggunakan transformasi atau normalisasi.
- Menggunakan environmental suitability.
- Menggunakan pembobotan tambahan.

Jangan mengasumsikan HSI identik dengan ensemble probability.

Jika formula aktual belum dapat ditemukan, tandai sebagai unresolved dan jangan membuat formula baru berdasarkan tebakan.

Setiap nilai HSI harus:

- Memiliki rentang yang jelas.
- Memiliki species identifier.
- Memiliki model provenance.
- Memiliki input timestamp.
- Memiliki formula atau method version.

Tampilkan perbedaan antara HSI dan metrik evaluasi model.

**Output:**

`docs/migration/PHASE_2_5_HSI_REPORT.md`

Acceptance criteria:

- Formula dapat ditelusuri.
- Hasil dapat direproduksi.
- Rentang nilai valid.
- Tidak ada klaim kesesuaian habitat tanpa dasar metodologis.

## PHASE 2.6 — DYNAMIC OCEAN MANAGEMENT ENGINE

Implementasikan klasifikasi DOM menggunakan baseline terkunci.

Zona:

- Z1.
- Z2.
- Z3.
- Z4.
- Z5.
- GEN sebagai general class.

### Kriteria klasifikasi

**Z4 — Shallow Coastal Zone**

- Depth <=150 m.
- Distance to port <=15 NM.

**Z5 — Thermal Front Zone**

- Depth 150–1200 m.
- SST <=26.5°C.
- SST gradient >=0.035°C/km.

**Z3 — Chlorophyll Productivity Zone**

- Chlorophyll-a >=0.40 mg/m³.
- SST gradient >=0.035°C/km.

**Z1 — Nearshore Fishing Potential**

- Distance <=12 NM.
- HSI Cakalang atau Madidihang >=0.55.

**Z2 — Offshore Fishing Potential**

- Distance 12–60 NM.
- HSI Cakalang atau Madidihang >=0.55.

**GEN — General Classification**

- Grid yang tidak memenuhi kriteria Z1–Z5.

### Priority

Pertahankan urutan:

`Z4 → Z5 → Z3 → Z1 → Z2 → GEN`

Jika satu grid memenuhi lebih dari satu kriteria, gunakan urutan prioritas tersebut.

Jangan mengubah prioritas untuk menghasilkan distribusi zona yang terlihat lebih menarik.

### Persyaratan implementasi

- Implementasikan sebagai service terpisah.
- Parameter threshold terpusat.
- Semua parameter memiliki dokumentasi.
- Classification dapat diuji secara unit.
- Simpan atau tampilkan reason code untuk setiap klasifikasi.
- Bedakan missing input dengan nilai yang gagal memenuhi threshold.
- Jangan mengklasifikasikan grid dengan input kritis yang hilang sebagai GEN secara otomatis tanpa aturan eksplisit.

Distance to port harus dihitung secara spasial menggunakan koordinat homeport:

`111.7275°E, -8.2833°S`

Pastikan satuan nautical miles benar.

**Output:**

`docs/migration/PHASE_2_6_DOM_ENGINE_REPORT.md`

Acceptance criteria:

- Keenam kelas tersedia.
- Prioritas klasifikasi sesuai baseline.
- Hasil klasifikasi dapat ditelusuri.
- Threshold tidak berubah.
- Missing-data behavior terdokumentasi.

## PHASE 2.7 — BYCATCH RISK CLASSIFICATION ENGINE

Implementasikan tiga kelas:

- Green.
- Yellow.
- Red.

Baseline Red:

- Continental slope.
- Cold SST/front.
- Cakalang HSI >=0.70.

Periksa source code lama untuk menentukan apakah seluruh kondisi bersifat AND, OR, atau kombinasi tertentu.

Jangan menebak logika operator.

Untuk Green dan Yellow, temukan kriteria aktual dari implementasi atau dokumentasi lama.

Jika tidak ditemukan:

- Jangan mengarang threshold.
- Tandai kriteria sebagai unresolved.
- Jangan menampilkan klasifikasi yang belum tervalidasi sebagai klasifikasi final.

Setiap hasil klasifikasi harus memiliki:

- Risk class.
- Input variables.
- Triggered rules.
- Reason code.
- Classification timestamp.
- Rule version.

Pastikan label risiko tidak diklaim sebagai prediksi probabilitas bycatch jika tidak ada model probabilistik yang tervalidasi.

**Output:**

`docs/migration/PHASE_2_7_BYCATCH_ENGINE_REPORT.md`

Acceptance criteria:

- Kriteria Red sesuai baseline.
- Green dan Yellow sesuai source aktual.
- Trigger dapat ditelusuri.
- Tidak ada threshold rekaan.

## PHASE 2.8 — DYNAMIC FILTER SYSTEM

Bangun filter interaktif yang terhubung langsung dengan data dan engine.

Target filter:

### A. Species

- Cakalang.
- Madidihang.
- Legacy species removed from TCT scope.

### B. Time

- Daily prediction.
- Monthly prediction.
- Tahun.
- Bulan.
- Timestamp aktual.

Jangan menampilkan tanggal atau periode yang tidak tersedia.

### C. Oceanographic variables

- SST.
- Chlorophyll-a.
- Current.
- Wind.
- Bathymetry.

### D. DOM

- Z1.
- Z2.
- Z3.
- Z4.
- Z5.
- GEN.

### E. Bycatch

- Green.
- Yellow.
- Red.

### F. Spatial extent

- Grid selection.
- Bounding box.
- Area of interest.
- Distance to port.

### G. HSI

Gunakan rentang berdasarkan nilai HSI aktual yang tersedia.

Jangan mengasumsikan filter HSI dapat diterapkan jika data HSI belum tersedia.

### Ketentuan teknis

1. Filter harus memengaruhi query atau hasil engine secara aktual.
2. Jangan hanya menyembunyikan elemen visual.
3. Gunakan server-side filtering untuk dataset besar.
4. Gunakan debounce untuk input interaktif yang sesuai.
5. Gunakan pagination atau bounded spatial responses.
6. Hindari mengambil seluruh 8,129 grid setiap kali filter berubah jika tidak diperlukan.
7. Pertahankan state filter secara konsisten.
8. Berikan tombol reset.
9. Tampilkan jumlah hasil aktual.
10. Tampilkan kondisi loading, empty, error, dan stale data.
11. Tampilkan metadata waktu dan sumber data.

Filter kombinasi harus memiliki perilaku AND/OR yang terdokumentasi dan konsisten.

Jangan mengubah kriteria ilmiah ketika pengguna mengubah filter tampilan.

**Output:**

`docs/migration/PHASE_2_8_DYNAMIC_FILTER_REPORT.md`

Acceptance criteria:

- Setiap filter benar-benar memengaruhi hasil.
- Kombinasi filter tervalidasi.
- Hasil dan jumlah grid konsisten.
- Tidak ada hardcoded data.
- Respons tetap efisien.

## PHASE 2.9 — CORE RADAR API

Audit kontrak API lama dan implementasikan endpoint Laravel yang diperlukan.

Enam endpoint lama:

- `/api/predictions`
- `/api/grid`
- `/api/landings`
- `/api/timemachine`
- `/api/fleet`
- `/api/simulate`

Jangan menganggap semuanya harus dibangun ulang dalam Phase 2.

Prioritaskan API yang dibutuhkan Core Radar Engine.

Endpoint `/api/monitoring` merupakan fitur Laravel baru, bukan endpoint parity dari aplikasi lama.

### Ketentuan API

- Request validation.
- Consistent JSON schema.
- Proper HTTP status codes.
- Authentication dan authorization sesuai kebutuhan.
- Rate limiting.
- Pagination.
- Spatial query bounds.
- Error handling.
- Model and data provenance.
- API contract tests.

Jangan mengembalikan HTTP 200 ketika engine gagal menghasilkan data valid.

Jangan memindahkan endpoint lama tanpa menguji kesetaraan payload dan semantiknya.

**Output:**

`docs/migration/PHASE_2_9_RADAR_API_REPORT.md`

Acceptance criteria:

- API memberikan hasil aktual.
- Kontrak JSON terdokumentasi.
- Error response konsisten.
- Tidak ada data sensitif yang terekspos.
- API test lulus.

## PHASE 2.10 — MAPLIBRE RADAR VISUALIZATION

Hubungkan hasil API dengan MapLibre.

Bangun visualisasi:

1. Canonical grid layer.
2. Species habitat layer.
3. HSI layer.
4. DOM zone layer.
5. Bycatch risk layer.
6. Oceanographic layers yang benar-benar tersedia.
7. Selected grid interaction.
8. Tooltip.
9. Legend.
10. Dynamic filter integration.

### Visual design direction

Pertahankan identitas:

**Scientific Ocean Intelligence Platform**

Karakter visual:

- Professional scientific dashboard.
- Dark ocean interface.
- Clear hierarchy.
- Data-dense but readable.
- Restrained visual effects.
- High-quality map interaction.
- Scientific legends.
- Consistent unit labels.
- Clear distinction between observed, modeled, and simulated data.

Jangan membuat tampilan generik bergaya AI dashboard.

Jangan menggunakan decorative contours sebagai bathymetry aktual.

Jangan menampilkan particle animation sebagai current observation jika hanya simulasi.

Jangan menampilkan angka statis yang menyerupai live metrics.

### Performance

- Gunakan GeoJSON hanya untuk dataset berukuran sesuai.
- Evaluasi vector tiles jika dibutuhkan.
- Gunakan viewport-based loading.
- Gunakan clustering atau simplification jika relevan.
- Hindari rendering ribuan elemen DOM.
- Pertahankan interaksi peta tetap responsif.

**Output:**

`docs/migration/PHASE_2_10_MAP_VISUALIZATION_REPORT.md`

Acceptance criteria:

- Layer berasal dari API aktual.
- Legend sesuai klasifikasi.
- Filter mengubah layer.
- Tooltip menunjukkan data grid aktual.
- Loading dan error states tersedia.
- Tidak ada data palsu.

---

# 7. SCIENTIFIC VALIDATION FRAMEWORK

Ini merupakan bagian wajib Phase 2.

Jangan hanya menguji apakah kode berjalan. Uji apakah hasil ilmiahnya benar.

Buat dataset pengujian yang berasal dari data aktual atau fixture yang memiliki nilai referensi dan provenance jelas.

### Validasi minimum

**A. Grid validation**

- Jumlah grid.
- CRS.
- Geometry validity.
- Spatial extent.
- Cell identifier uniqueness.

**B. Oceanographic validation**

- Unit.
- Timestamp.
- Missing values.
- Range validation.
- Spatial coverage.

**C. Species validation**

- Model version.
- Feature order.
- Ensemble weights.
- Prediction range.
- Reproducibility.

**D. HSI validation**

- Formula.
- Normalization.
- Output range.
- Species separation.

**E. DOM validation**

Buat test cases untuk setiap zona.

Uji juga grid yang memenuhi beberapa kriteria sekaligus untuk memastikan priority order benar.

**F. Bycatch validation**

Buat test cases berdasarkan rule aktual.

Uji setiap trigger dan kombinasi kondisi.

**G. Spatial validation**

- Distance to port.
- Nautical miles conversion.
- Spatial intersections.
- Boundary behavior.
- Polygon classification.

### Independent validation

Jika data validasi independen belum tersedia, dokumentasikan sebagai keterbatasan.

Jangan menyebut hasil validasi internal sebagai independent scientific validation.

---

# 8. PERFORMANCE AND SECURITY REQUIREMENTS

Periksa:

- Query execution time.
- Spatial index utilization.
- Python process duration.
- Memory usage.
- API response size.
- Map rendering time.
- Queue status jika digunakan.

Keamanan:

- Validasi seluruh request.
- Batasi parameter yang dapat dikirim ke Python.
- Cegah command injection.
- Cegah path traversal.
- Gunakan authorization untuk operasi administratif.
- Jangan mengekspos filesystem paths.
- Jangan mengekspos model internals yang sensitif.
- Jangan memberikan akses tulis ke database ilmiah.
- Gunakan logging tanpa secret.

Jangan mengoptimasi dengan mengubah algoritma atau threshold ilmiah.

Optimasi harus mempertahankan kesetaraan hasil.

---

# 9. REQUIRED TESTING

Buat unit, feature, integration, dan regression tests.

Minimum:

### Data layer

- Scientific table access.
- Grid count.
- Spatial extent.
- Query correctness.
- Read-only enforcement.

### Python bridge

- Valid execution.
- Invalid input.
- Timeout.
- Process failure.
- Invalid JSON.
- Missing dependency.
- Path restriction.
- Command injection prevention.

### Species engine

- Model loading.
- Input feature validation.
- Ensemble weights.
- Prediction range.
- Reproducibility.

### DOM engine

- Z1 threshold.
- Z2 threshold.
- Z3 threshold.
- Z4 threshold.
- Z5 threshold.
- GEN fallback.
- Priority conflict.
- Missing data.

### Bycatch

- Red criteria.
- Green criteria.
- Yellow criteria.
- Unresolved rules handling.

### Dynamic filters

- Single filter.
- Multiple filters.
- Empty result.
- Invalid parameter.
- Date limitation.
- Spatial extent.

### API and frontend

- API schema.
- Authorization.
- Rate limiting.
- Map layer data.
- Legend consistency.
- Tooltip.
- Loading/error states.

Jangan menghapus atau melemahkan tes Phase 1 dan Phase 1.1.

Jalankan regression suite lama setelah perubahan.

---

# 10. PHASE 2 DELIVERABLES

Simpan seluruh laporan dalam `docs/migration/`.

Dokumen wajib:

1. `PHASE_2_SKILL_USAGE_REGISTER.md`
2. `PHASE_2_0_ENGINE_DISCOVERY.md`
3. `PHASE_2_1_DATA_ACCESS_REPORT.md`
4. `PHASE_2_2_PYTHON_BRIDGE_REPORT.md`
5. `PHASE_2_3_OCEANOGRAPHIC_ENGINE_REPORT.md`
6. `PHASE_2_4_SPECIES_ENGINE_REPORT.md`
7. `PHASE_2_5_HSI_REPORT.md`
8. `PHASE_2_6_DOM_ENGINE_REPORT.md`
9. `PHASE_2_7_BYCATCH_ENGINE_REPORT.md`
10. `PHASE_2_8_DYNAMIC_FILTER_REPORT.md`
11. `PHASE_2_9_RADAR_API_REPORT.md`
12. `PHASE_2_10_MAP_VISUALIZATION_REPORT.md`
13. `PHASE_2_SCIENTIFIC_VALIDATION_REPORT.md`
14. `PHASE_2_PERFORMANCE_REPORT.md`
15. `PHASE_2_CHANGELOG.md`
16. `PHASE_2_TEST_REPORT.md`
17. `PHASE_2_FINAL_READINESS.md`

Setiap laporan wajib membedakan:

- VERIFIED.
- IMPLEMENTED.
- TESTED.
- NOT VERIFIED.
- BLOCKED.
- NOT APPLICABLE.

Sertakan bukti aktual:

- File yang dibuat dan diubah.
- Source code relevan.
- Command yang dijalankan.
- Output pengujian.
- Query SQL.
- Model metadata.
- Data provenance.
- Parameter ilmiah.
- Performance metrics.

Jangan menyertakan credential, token, atau secret.

---

# 11. PHASE 2 ACCEPTANCE CRITERIA

Phase 2 baru dapat dinyatakan selesai apabila:

- [ ] Skill discovery selesai dan didokumentasikan.
- [ ] Source code Python telah diaudit.
- [ ] Scientific Data Access Layer berjalan.
- [ ] Read-only privilege tetap terlindungi.
- [ ] Python Bridge berjalan dengan aman.
- [ ] Oceanographic Data Engine menggunakan sumber yang terverifikasi.
- [ ] Species Prediction Engine menghasilkan output aktual.
- [ ] Ensemble weights sesuai baseline.
- [ ] HSI menggunakan formula yang dapat ditelusuri.
- [ ] DOM Z1–Z5 dan GEN berjalan sesuai baseline.
- [ ] Priority order benar.
- [ ] Bycatch rules sesuai implementasi ilmiah yang tervalidasi.
- [ ] Dynamic filters terhubung ke data aktual.
- [ ] Radar API memiliki kontrak yang jelas.
- [ ] MapLibre menampilkan hasil aktual.
- [ ] Scientific validation tests lulus.
- [ ] Regression tests Phase 1 dan 1.1 tetap lulus.
- [ ] Tidak ada perubahan data sumber yang tidak diizinkan.
- [ ] Tidak ada klaim live data tanpa bukti.
- [ ] Seluruh dokumentasi tersedia.

Jika ada komponen yang tidak dapat divalidasi, tandai sebagai BLOCKED atau NOT VERIFIED.

Jangan mengklaim Phase 2 100% PASS jika ada acceptance criteria kritis yang belum terpenuhi.

---

# 12. PROTOKOL EKSEKUSI

Kerjakan secara bertahap:

1. Phase 2.0 — Audit dan discovery.
2. Phase 2.1 — Data access.
3. Phase 2.2 — Python bridge.
4. Phase 2.3 — Oceanographic engine.
5. Phase 2.4 — Species engine.
6. Phase 2.5 — HSI.
7. Phase 2.6 — DOM.
8. Phase 2.7 — Bycatch.
9. Phase 2.8 — Dynamic filter.
10. Phase 2.9 — Radar API.
11. Phase 2.10 — MapLibre visualization.
12. Scientific validation dan regression testing.

Setelah setiap subphase:

- Jalankan tes yang relevan.
- Buat laporan.
- Periksa regresi.
- Perbaiki kegagalan sebelum melanjutkan.

Jika ditemukan ketidakcocokan metodologi, data, atau model yang berpotensi mengubah hasil ilmiah, hentikan modul terkait dan dokumentasikan masalahnya. Jangan membuat keputusan ilmiah sendiri.

Jangan membiarkan satu kegagalan Python membuat sistem menampilkan data palsu sebagai pengganti.

---

# 13. BATAS AKHIR PHASE 2

Phase 2 berfokus pada Core Radar Engine dan Dynamic Filter.

Jangan memperluas ruang lingkup ke:

- Automated daily inference scheduler.
- Produksi pipeline BMKG live.
- Integrasi data satelit baru tanpa audit.
- Fleet optimization lanjutan.
- Cost optimization.
- Automated fishing route recommendation.
- Deployment produksi.
- Penghapusan aplikasi lama.
- Pengembangan modul Phase 3 dan seterusnya.

Fitur-fitur tersebut dapat direncanakan pada fase berikutnya.

Setelah seluruh pekerjaan Phase 2 selesai:

1. Jalankan regression tests.
2. Buat laporan final readiness.
3. Sajikan ringkasan modul yang benar-benar aktif.
4. Sajikan modul yang masih parsial atau blocked.
5. Sajikan perbandingan hasil dengan baseline lama.
6. Berikan daftar risiko tersisa.
7. Hentikan implementasi.
8. Tunggu persetujuan eksplisit pengguna sebelum memulai Phase 3.

---

# 14. INSTRUKSI FINAL KEPADA ANTIGRAVITY

Mulai dari Phase 2.0.

Jangan langsung membuat semua modul sebelum memahami source code dan scientific engine yang tersedia.

Prioritaskan penggunaan kembali kode ilmiah yang sudah benar.

Jangan mengubah algoritma, bobot ensemble, threshold DOM, maupun aturan klasifikasi bycatch hanya untuk mempermudah implementasi.

Jangan membuat angka dummy, data palsu, atau klaim live yang tidak dapat dibuktikan.

Jaga pemisahan Laravel, Python, dan PostGIS sesuai tanggung jawab masing-masing.

Gunakan Skills yang tersedia secara nyata dan dokumentasikan penggunaannya.

**Hasil akhir yang dituju: SEACAST PRIGI V3 menjadi Scientific Ocean Intelligence Platform yang memiliki mesin radar berbasis data aktual, komputasi ilmiah yang dapat direproduksi, klasifikasi spasial yang konsisten, serta visualisasi interaktif yang dapat dipertanggungjawabkan secara akademik.**

**EXECUTE PHASE 2.0 NOW.**
</USER_REQUEST>
<ADDITIONAL_METADATA>
The current local time is: 2026-11-04T17:09:11+07:00.

The user's current state is as follows:
Active Document: c:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\docs\migration\PHASE_1_1_FINAL_READINESS.md (LANGUAGE_UNSPECIFIED)
Cursor is on line: 1
Other open documents:
- c:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\seacast-prigi-laravel\routes\web.php (LANGUAGE_UNSPECIFIED)
- c:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\seacast-prigi-laravel\config\database.php (LANGUAGE_UNSPECIFIED)
- c:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\seacast-prigi-laravel\tests\Feature\SecurityRemediationTest.php (LANGUAGE_UNSPECIFIED)
- c:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\seacast-prigi-laravel\bootstrap\app.php (LANGUAGE_UNSPECIFIED)
- c:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\seacast-prigi-laravel\database\seeders\DatabaseSeeder.php (LANGUAGE_UNSPECIFIED)
</ADDITIONAL_METADATA>