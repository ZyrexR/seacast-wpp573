# SEACAST Multi-Agent Engineering Handbook (`AGENTS.md`)

Panduan koordinasi bersama untuk **Antigravity** dan **Codex** pada repositori `prigi-pelagis`. Tujuannya agar kedua agen bekerja paralel **tanpa saling menimpa file, tanpa mengubah kontrak API yang sudah teruji, dan selalu merujuk satu sumber kebenaran yang sama**.

---

## 1. Pembagian Kepemilikan (File Ownership Split)

| Agen | Fokus Pekerjaan | Direktori / Area Utama | Dilarang Diubah Tanpa Koordinasi |
|---|---|---|---|
| **Antigravity** | UI/UX, Blade templates, narasi ilmiah di web, tampilan peta/chart, desain ekspor PDF, sinkronisasi status & dokumentasi proyek | `seacast-prigi-laravel/resources/views/**`, `README.md`, `AGENTS.md`, `docs/project/SEACAST_IMPLEMENTATION_STATUS.md`, presentasi di `DashboardController.php` | Skrip Python GEE/modeling (`scripts/*.py`), migrasi/skema PostgreSQL, logika kontrak JSON backend |
| **Codex** | Pipeline Python GEE & inference, writer `ingestion_manifest.json` -> PostGIS, kontrak API/service backend, unit & feature tests | `scripts/**`, `tests/**` (Python), `seacast-prigi-laravel/app/Services/**`, `seacast-prigi-laravel/app/Domain/**`, `seacast-prigi-laravel/tests/**` | File `.blade.php`, teks pemasaran/narasi di halaman publik, aset frontend CSS/JS UI |

> **Aturan Emas:** Sebelum memodifikasi file di luar area masing-masing, cek apakah perubahan tersebut cukup dilakukan melalui kontrak JSON/DTO agar tidak terjadi konflik edit antar-agen.

---

## 2. Single Source of Truth (Wajib Dipatuhi Kedua Agen)

1. **Komoditas Publik:** Hanya **TCT** (`Tuna Sirip Kuning`, `Cakalang`, `Tongkol Krai`). Jangan memunculkan `Albakor` sebagai komoditas operasional publik. Alias kolom legacy (`hsi_madidihang`, `hsi_tongkol`) hanya untuk kompatibilitas API/DB internal.
2. **Two-Output Architecture:**
   - **Output 1 — Spatial HSI/DOM (`PROXY_HSI`):** Kesesuaian habitat relatif berbasis oseanografi & skoring heuristik (`model_evaluations_summary.json`). Bukan klaim titik koordinat ikan riil.
   - **Output 2 — Trip Planner (`FORMULA_SCENARIO`):** Rekomendasi operasional berbasis `cell_id`, port origin (`PPN Prigi` / `PPS Cilacap`), biaya BBM, GT kapal, dan cuaca. Hanya boleh memakai label `TEMPORAL_CPUE_CALIBRATED` jika faktor koreksi numerik tervalidasi benar-benar diterapkan.
3. **Metrik Evaluasi Model Proxy WebGIS (`models/sdm/ensemble/model_evaluations_summary.json`):**
   - `cakalang`: Ensemble Proxy AUC `0.795`, TSS `0.630`, threshold hotspot `0.70`
   - `madidihang` (Tuna Sirip Kuning): Ensemble Proxy AUC `0.784`, TSS `0.606`, threshold hotspot `0.70`
   - `tongkol_krai`: Ensemble Proxy AUC `0.892`, TSS `0.752`, bobot provisional `0.332 / 0.335 / 0.333` (`models/tongkol_krai_ensemble_model.joblib`, dilatih dari sampel proxy neritik 132 bulan WPPNRI 573 via `scripts/train_tongkol_krai_sdm.py`)
   - Jangan menulis ulang metrik Cakalang/YFT lama (`0.874`, `0.908`, threshold hotspot `0.75`) di kode, view, atau test baru.
4. **Gate Ilmiah Temporal (`outputs/tct_hybrid_2015_2025/reports/tct_scientific_gate.json`):**
   - `status`: `HOLD_FOR_SCIENTIFIC_REVIEW` (`web_integration_allowed: false`)
   - Ketiga komoditas berstatus `temporal_status: REJECT` dan `web_status: HOLD`.
   - `beats_seasonal_baseline` untuk Tuna Sirip Kuning memang `true`, tetapi `model_r2_2025` masih `-0.2818` (di bawah `0.50`), sehingga tidak boleh diklaim siap produksi.
5. **Provenance Ingest (`seacast-prigi-laravel/storage/app/gee_ingestion/ingestion_manifest.json`):**
   - Jika manifest tidak ada, service wajib jujur mengembalikan `UNVERIFIED` / `DATABASE_SNAPSHOT_SOURCE_UNVERIFIED`, bukan menandai data sebagai live satelit.

---

## 3. Guardrails Anti-Regresi (Jangan Diubah!)

Beberapa string dan pola di bawah ini diuji langsung oleh PHPUnit test suite agar kompatibilitas kontrak tetap terjaga:

1. **String fallback internal di backend services** (`OceanographicDataService.php`, `PostGisScientificDataRepository.php`, `SystemMonitoringService.php`, `scripts/bridge_inference_runner.py`):
   - Pertahankan literal `'Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)'` di layer internal agar `CoreRadarApiTest`, `MonitoringApiTest`, `OceanographicEngineTest`, dan `Wpp573GeneralizationTest` tetap hijau. Label provenance publik di-overlay melalui `evidence_level` & `source_mode`.
2. **Halaman publik `/` (`seacast-prigi-laravel/resources/views/public/home.blade.php`):**
   - Pertahankan teks `PPN Palabuhanratu` (wajib ada untuk `Wpp573GeneralizationTest`).
3. **Halaman `/science/methodology` (`seacast-prigi-laravel/resources/views/public/science/methodology.blade.php`):**
   - Pertahankan frasa `'10-Fold Spatial Cross-Validation'` pada judul/konteks kerangka konseptual (wajib ada untuk `PublicScienceKnowledgePagesTest`), dengan penjelasan eksplisit bahwa validasi blok-spasial pada data koordinat tangkapan belum dieksekusi di kode.
4. **Database test Laravel:**
   - `seacast-prigi-laravel/config/database.php` membaca `DB_SQLITE_DATABASE`, bukan `DB_DATABASE`. Jangan mengasumsikan `phpunit` menulis ke `DB_DATABASE` jika memakai SQLite.
5. **File untracked penelitian lokal:**
   - Pertahankan dan jangan hapus atau commit paksa `data/modeling/`, `scripts/build_tct_occurrences.py`, serta `docs/superpowers/`.

---

## 4. Konteks Tambahan Laravel

Lihat juga `seacast-prigi-laravel/AGENTS.md` untuk konvensi Laravel 11, PHPUnit, dan Boost MCP.

---

## 5. Checklist Verifikasi Sebelum Menutup Tugas

- [ ] `php -l` bersih untuk semua file PHP yang diubah.
- [ ] PHPUnit terkait (atau `php artisan test`) lulus penuh tanpa ada assertion yang dilonggarkan atau di-skip.
- [ ] `.venv-tct\Scripts\python.exe -m unittest discover -s tests -t . -v` lulus penuh bila skrip/kontrak Python ikut disentuh.
- [ ] Tidak ada angka metrik kontradiktif yang tertinggal di view, controller, atau dokumen status.
