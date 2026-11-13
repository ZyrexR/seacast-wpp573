# HUMAN VALIDATION GATE PROTOCOL
## Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi — WPPNRI 573

- **Document ID:** `STRAT-REASSESS-07`
- **Governance Principle:** **AUTOMATION-FIRST, HUMAN-VALIDATED** — Antigravity mengotomatisasi eksekusi teknis, namun peneliti memegang kewenangan tunggal untuk memberikan status `APPROVED` pada setiap gerbang keputusan ilmiah dan autentikasi.

---

## 1. Definisi Status Gate Resmi

Setiap gerbang validasi (*Validation Gate*) wajib menggunakan salah satu dari enam status standar berikut:

| Status Kode | Arti Operasional | Tindakan yang Diizinkan bagi Antigravity |
| :---: | :--- | :--- |
| `NOT STARTED` | Tahapan belum dimulai. | Menunggu penyelesaian tahapan sebelumnya. |
| `IN PROGRESS` | Antigravity sedang menyusun kode, menjalankan ekstraksi, atau menyiapkan laporan. | Menyelesaikan pekerjaan teknis di dalam cakupan phase aktif. |
| `READY FOR REVIEW` | Seluruh deliverable teknis pada phase aktif telah selesai dan diverifikasi otomatis; menunggu pemeriksaan peneliti. | **BERHENTI (STOP)** dan menyajikan ringkasan bukti kepada peneliti. Dilarang masuk ke phase berikutnya. |
| `APPROVED` | **Hanya dapat diberikan secara eksplisit oleh Peneliti** setelah memeriksa metodologi dan hasil. | Diizinkan melanjutkan ke phase berikutnya sesuai instruksi peneliti. |
| `REVISE` | Peneliti meminta perbaikan parameter, sumber data, atau metodologi pada phase aktif. | Melakukan revisi terarah sesuai catatan peneliti dan mengembalikan status ke `READY FOR REVIEW`. |
| `REJECTED` | Peneliti menolak pendekatan atau hasil pada phase tersebut. | Menghentikan jalur tersebut dan merancang ulang bersama peneliti. |

> [!WARNING]
> **Aturan Mutlak:** Antigravity **dilarang keras** menuliskan status `APPROVED` atas nama peneliti di dalam berkas gate mana pun tanpa instruksi persetujuan eksplisit dari pengguna pada percakapan.

---

## 2. Daftar Checkpoint & Human Validation Gates (`G-00` s/d `G-05`)

| Gate ID | Nama Checkpoint | Tahap Transisi | Keputusan Ilmiah & Teknis yang Menjadi Kewenangan Peneliti | Status Saat Ini |
| :---: | :--- | :---: | :--- | :---: |
| **`G-00`** | **Strategic Architecture & Baseline Freeze Gate** | Phase 4.1.1 $\rightarrow$ Phase 4.2 | 1. Menyetujui pembekuan (*freeze*) hasil Phase 4.1.1 sebagai `Baseline v1.1.1`.<br/>2. Menyetujui rancangan arsitektur GEE, Google Colab, dan pertukaran data Google Drive.<br/>3. Memberikan izin autentikasi GEE (`earthengine authenticate` + `GCP_PROJECT_ID`) dan koneksi Google Drive (Opsi A dipilih). | **`APPROVED`** |
| **`G-01`** | **GEE Satellite Extraction & Predictor Stack Gate** | Phase 4.2 $\rightarrow$ Phase 4.3 | 1. Memeriksa kualitas komposit satelit 132 bulan (`MODIS-Aqua`, `OISST v2.1`, `CFSv2`, `ETOPO1`) hasil ekspor GEE.<br/>2. Menilai proporsi tutupan awan dan menyetujui metode *cloud gap-filling*.<br/>3. Menyetujui penggunaan matriks prediktor GEE `v2.0` (`2.050` sel $\times$ `6` skenario) sebagai input pelatihan di Google Colab. | **`READY FOR REVIEW`** |
| **`G-02`** | **Google Colab AI/ML Training & Validation Gate** | Phase 4.3 $\rightarrow$ Phase 4.4 | 1. Memeriksa hasil pelatihan model nyata (`MaxEnt/MaxNet`, `Random Forest`, `GLM/GAM`, `XGBoost`) pada Track A (Temporal Empiris) dan Track B/C (Spasial).<br/>2. Menilai hasil **5-Fold Spatial Block Cross-Validation** dan kurva respons parsial/SHAP.<br/>3. Menyetujui konfigurasi bobot ensemble `v2.0` dan memverifikasi bahwa tidak ada klaim biologis palsu dari label proksi. | `NOT STARTED` |
| **`G-03`** | **Cross-Platform Artifact & Baseline Comparison Gate** | Phase 4.4 $\rightarrow$ Phase 4.5 | 1. Memeriksa perbandingan berdampingan (*side-by-side*) antara **Heuristic Baseline v1.1.1** vs **GEE+Colab Model v2.0**.<br/>2. Menyetujui versi output spasial yang akan ditampilkan pada dashboard utama (atau menyetujui fitur *toggle* pembanding `v1.1.1` vs `v2.0`). | `NOT STARTED` |
| **`G-04`** | **DOM-DSS Dashboard & Scientific Guardrail Gate** | Phase 4.5 $\rightarrow$ Phase 4.6 | 1. Memeriksa visualisasi keempat modul DOM-DSS pada `html/index.html`.<br/>2. Memastikan seluruh batasan ilmiah (label RHSI, ketidakpastian $\sigma_w$, *disclaimer* keselamatan pelayaran non-operasional, dan status CITES `NEEDS_VERIFICATION`) tampil akurat. | `NOT STARTED` |
| **`G-05`** | **Final Scientific & System Acceptance Gate** | Phase 4.6 $\rightarrow$ Rilis Akademik | 1. Menyetujui laporan evaluasi ilmiah akhir dan reproduksibilitas lintas platform untuk keperluan publikasi/disertasi/kajian akademik. | `NOT STARTED` |

---

## 3. Format Log Persetujuan Peneliti (`reports/validation_gates/gate_status_registry.json`)

Pada setiap transisi phase, status gerbang dicatat secara transparan dengan struktur berikut:

```json
{
  "gate_id": "G-00",
  "gate_name": "Strategic Architecture & Baseline Freeze Gate",
  "phase_completed": "Phase 4.1.1 + Strategic Reassessment",
  "next_phase": "Phase 4.2 — Google Earth Engine Spatial Modeling",
  "status": "READY FOR REVIEW",
  "submitted_by": "Google Antigravity",
  "submitted_at": "2026-11-02T02:55:00+07:00",
  "researcher_decision": null,
  "researcher_notes": "Awaiting explicit researcher review and approval before starting Phase 4.2."
}
```
