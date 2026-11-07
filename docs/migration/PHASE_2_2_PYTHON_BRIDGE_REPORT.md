# PHASE 2.2 — PYTHON SCIENTIFIC COMPUTATION BRIDGE REPORT
## SEACAST PRIGI V3 — Core Radar Engine & Dynamic Filter

**Tanggal:** 2026-11-04  
**Arsitektur Target:** Laravel 13.40.0 Modular Monolith  
**Namespace:** `App\Infrastructure\Python`  
**Host Python Runtime:** Python 3.14.7 (`C:\Users\ThinkPad\AppData\Local\Python\pythoncore-3.14-64\python.exe`)  
**CLI Runner:** `scripts/bridge_inference_runner.py`  
**Status Evaluasi:** **VERIFIED & IMPLEMENTED (100% PASS)**  

---

### 1. Eksekutif Ringkasan Jembatan Komputasi Ilmiah

Phase 2.2 telah berhasil membangun jembatan komputasi ilmiah antara Laravel dan Python engine secara modular, terisolasi, dan aman. Algoritma ilmiah habitat, transformasi scaler, inferensi probabilitas model (MaxEnt, Random Forest, GLM), dan klasifikasi DOM/Bycatch dieksekusi secara native oleh Python, sementara orkestrasi, sanitasi input, validasi parameter, penegakan timeout, dan verifikasi schema JSON dikelola oleh Laravel.

Arsitektur Komponen:
- **Interface Kontrak:** [`PythonComputationBridgeInterface.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Infrastructure/Python/Contracts/PythonComputationBridgeInterface.php)
- **Implementasi Bridge:** [`PythonComputationBridge.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Infrastructure/Python/PythonComputationBridge.php)
- **DTO Hasil Komputasi:** [`PythonComputationResult.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Infrastructure/Python/DTO/PythonComputationResult.php)
- **Custom Exceptions:** `PythonBridgeException`, `PythonSecurityException`, `PythonTimeoutException`
- **CLI Runner Terstandarisasi:** [`bridge_inference_runner.py`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scripts/bridge_inference_runner.py)

---

### 2. Standar Keamanan & Mitigasi Kerentanan

1. **Zero Shell Command Interpolation:**
   Eksekusi menggunakan `Symfony\Component\Process\Process` dengan array argument:
   `[$pythonBinary, $scriptFullPath, ...$sanitizedArgs]`. Tidak ada penggunaan `shell_exec`, `exec`, `system`, atau interpolasi string mentah.
2. **Strict Script Whitelisting:**
   Hanya skrip yang terdaftar secara eksplisit (`daily_inference`, `audit_monthly`, `raw_daily_inference`) yang diizinkan untuk dieksekusi. Percobaan mengeksekusi path arbitrary atau traversal (`../../../cmd.exe`) langsung memicu `PythonSecurityException`.
3. **Penyaringan Karakter Berbahaya:**
   Argument yang mengandung karakter command injection (`;`, `|`, `&`, `$`, `` ` ``, `<`, `>`, newline) langsung diblokir sebelum proses dibuat.
4. **Timeout Enforcement:**
   Timeout eksekusi default 60 detik (dapat disesuaikan hingga 120 detik untuk inferensi spasial masif 8.129 sel). Jika waktu terlampaui, proses dimatikan secara terkontrol dan memicu `PythonTimeoutException`.

---

### 3. Penegakan Bobot Ensemble Ilmiah Terkunci (Weight Provenance)

Sebelumnya, file bundle `.joblib` memiliki bobot default `{'maxent': 0.4, 'rf': 0.35, 'glm': 0.25}`. `bridge_inference_runner.py` secara ketat memaksakan (*enforces*) bobot ilmiah yang telah diaudit pada Phase 0:

| Spesies | MaxEnt Enforced | Random Forest Enforced | GLM Enforced | Catatan Ilmiah |
|---|---:|---:|---:|---|
| **Cakalang** | **0.320** | **0.355** | **0.325** | AUC Heuristik: 0.795, TSS: 0.630 |
| **Madidihang** | **0.295** | **0.365** | **0.340** | AUC Heuristik: 0.784, TSS: 0.606 |
| **Albakor** | **0.000** | **0.517** | **0.483** | **MaxEnt dinolkan mutlak (AUC proxy acak 0.507)** |

Output provenance yang dihasilkan menyertakan seluruh bobot yang diterapkan, koordinat homeport Prigi, dan label sumber fallback BMKG.

---

### 4. Kontrak Schema JSON Hasil Komputasi

Setiap keluaran dari `bridge_inference_runner.py` mematuhi struktur standar:
```json
{
  "status": "SUCCESS",
  "engine": "SEACAST_PRIGI_SDM_ENGINE",
  "model_version": "2.3.2",
  "species": ["cakalang", "madidihang", "albakor"],
  "generated_at": "2026-11-04T17:25:00.000000",
  "parameters": {
    "prediction_date": "2026-11-03",
    "target_species": "all",
    "limit": 0
  },
  "total_cells": 8129,
  "hotspots_count": 6,
  "data": {
    "summary": {
      "mean_hsi_cakalang": 0.209,
      "mean_hsi_madidihang": 0.276,
      "mean_hsi_albakor": 0.448,
      "safe_cells_pct": 0.0,
      "red_bycatch_cells": 0,
      "yellow_bycatch_cells": 1116,
      "green_bycatch_cells": 7013
    },
    "records": [...]
  },
  "warnings": [],
  "provenance": {
    "python_runtime": "3.14.7",
    "applied_weights": { ... },
    "homeport_coordinates": {"lat": -8.2833, "lon": 111.7275},
    "sources": {
      "wave": "Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)",
      "sdm": "Ensemble SDM (MaxEnt, Random Forest, GLM)",
      "bycatch": "Screening Lereng Batimetri 150-1200m Standar IOTC"
    }
  }
}
```

---

### 5. Bukti Pengujian Unit & Security Feature

1. [`tests/Unit/PythonBridgeTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Unit/PythonBridgeTest.php):
   - `test_bridge_detects_valid_python_binary` -> PASS
   - `test_blocks_arbitrary_unlisted_scripts` -> PASS
   - `test_prevents_command_injection_via_arguments` -> PASS
   - `test_can_execute_whitelisted_script_and_receive_valid_json` -> PASS
   - **Hasil:** 4 tests, 10 assertions, 0 failures (17,13s).

2. [`tests/Feature/PythonBridgeSecurityTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Feature/PythonBridgeSecurityTest.php):
   - `test_blocks_arbitrary_path_traversal` -> PASS
   - `test_blocks_pipe_command_injection` -> PASS
   - `test_blocks_ampersand_command_injection` -> PASS
   - `test_blocks_backtick_command_injection` -> PASS
   - `test_provenance_and_locked_weights_are_strictly_present` -> PASS
   - **Hasil:** 5 tests, 15 assertions, 0 failures (9,86s).

**Total Keseluruhan Pengujian Phase 2.2:** 9 tests, 25 assertions, **100% PASS**.

