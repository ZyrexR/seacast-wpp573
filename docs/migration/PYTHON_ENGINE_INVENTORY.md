# SEACAST PRIGI V3 — PYTHON SCIENTIFIC ENGINE INVENTORY
**Audit Date:** 4 November 2026  
**Auditor:** Scientific Software Engineer  
**Role:** Ingest, Model Training, Daily Inferencing & Spatial Analytics  

---

## 1. STRUKTUR ASSET & SCRIPT PYTHON

```
├── models/
│   ├── scaler_oceanographic.joblib           [Scikit-learn StandardScaler (10 Fitur Oseanografi)]
│   ├── cakalang_ensemble_model.joblib        [Bundle Model Joblib: MaxEnt, RF, GLM & Bobot]
│   ├── madidihang_ensemble_model.joblib      [Bundle Model Joblib: MaxEnt, RF, GLM & Bobot]
│   ├── legacy_species_removed_ensemble_model.joblib         [Bundle Model Joblib: MaxEnt, RF, GLM & Bobot]
│   └── sdm/
│       └── ensemble/
│           └── model_evaluations_summary.json[Kamus Evaluasi AUC, TSS, Variable Importance]
├── scripts/
│   ├── daily_inference.py                    [Script Inferensi Harian Pukul 04:00 WIB]
│   ├── audit_2015_monthly_data.py            [Audit Integritas Data Historis Bulanan]
│   ├── audit_year_monthly_data.py            [Verifikasi Spasio-Temporal 2025]
│   ├── seacast_prigi_colab_pipeline.py       [Pipeline Sinkronisasi Google Earth Engine]
│   ├── build_colab_notebook.py               [Generator Notebook Riset Eksternal]
│   ├── phase_1_1_remediation/                [Script Validasi Fisik & Reproduksibilitas]
│   ├── remediation_v2_1/                     [Script Verifikasi Formula Oseanografi]
│   └── remediation_v2_2/                     [Rekonsiliasi Inventaris CFSv2/HYCOM]
```

---

## 2. DETAIL SCRIPT UTAMA: `scripts/daily_inference.py`

### 2.1. Parameter Input & Eksekusi:
- **Jadwal:** Otomatis setiap pagi pukul 04:00 WIB.
- **Argumen:** `--date YYYY-MM-DD` (opsional, default: tanggal hari ini).
- **Dependensi Library:** `numpy`, `pandas`, `scikit-learn`, `joblib`, `psycopg2` (atau export JSON).

### 2.2. Alur Algoritma:
1. **Pemuatan Grid Kanonik:** Membaca 8.129 sel dari PostGIS / GeoJSON.
2. **Asimilasi Bio-Fisik:**
   - Suhu Permukaan Laut (SST): Menghitung fenomena upwelling musiman berbasis hari kalender tahunan ($t=240$).
   - Gradien Suhu: Mendeteksi front termal ($\ge 0,035^\circ\text{C/km}$).
   - Klorofil-a: Konsentrasi biomassa fitoplankton ($0,05 - 3,50\text{ mg/m}^3$).
   - Arus & Angin: Kecepatan arus ($0,25 - 0,60\text{ m/s}$), kecepatan angin, Ekman upwelling velocity.
   - Gelombang Laut: Tinggi gelombang signifikan ($0,5 - 4,5\text{ m}$).
3. **Scaling Fitur:** Memproses matriks 10 variabel menggunakan `scaler_oceanographic.joblib`.
4. **Prediksi Model SDM:**
   - Menghitung $P_{\text{maxent}}$, $P_{\text{rf}}$, $P_{\text{glm}}$.
   - Menerapkan pembobotan ensemble spesifik spesies.
   - Normalisasi HSI $[0,000 - 1,000]$.
5. **Klasifikasi Spasial DOM:** Menerapkan hierarki penegakan Z4 -> Z5 -> Z3 -> Z1 -> Z2 -> GEN.
6. **Screening Bycatch:** Klasifikasi Green / Yellow / Red berbasis lereng benua dan front dingin.
7. **Penyimpanan:** Menyimpan ke tabel `daily_predictions` di PostgreSQL dan meng-output cache `daily_latest.json`.

---

## 3. ARSITEKTUR ORKESTRASI LARAVEL <-> PYTHON ENGINE

Dalam arsitektur akhir, Laravel 13 bertindak sebagai orkestrator dan supervisor proses:

```
[Laravel Scheduler: 04:00 WIB]
        │
        ▼
[ExecuteDailyInferenceJob]
        │
        ▼
[Laravel Process Facade]
  Process::timeout(180)
         ::path(base_path('python-engine'))
         ::run(['python', 'scripts/daily_inference.py', '--date', $targetDate]);
        │
        ▼
[Exit Code & Error Checking]
  if ($process->successful()) {
      Log::info("Daily inference executed successfully.");
      Cache::tags(['predictions'])->flush();
      event(new DailyInferenceCompleted($targetDate));
  } else {
      Log::error("Inference failed: " . $process->errorOutput());
      Notification::route('mail', ...)->notify(new InferenceFailedNotification());
  }
```

### Standar Keamanan Eksekusi Python:
1. **Tidak Ada Input Mentah:** Seluruh argumen divalidasi via regex tanggal `YYYY-MM-DD`.
2. **Fixed Executable Path:** Path Python didefinisikan dalam `.env` (`PYTHON_BINARY=/usr/bin/python3` atau path venv).
3. **Timeout Protection:** Batas waktu eksekusi maksimal 180 detik; jika hang, proses otomatis di-kill secara graceful.
4. **Structured Output:** Script Python mengembalikan JSON structured stdout yang dapat diparsing langsung oleh Laravel.

