# PHASE 2.7 — BYCATCH RISK CLASSIFICATION ENGINE REPORT
## SEACAST PRIGI V3 — Core Radar Engine & Dynamic Filter

**Tanggal:** 2026-11-04  
**Arsitektur Target:** Laravel 13.40.0 Modular Monolith  
**Namespace:** `App\Domain\Bycatch`  
**Standar Konservasi:** IOTC Resolution 19/05 (Bycatch Mitigation & Monitoring)  
**Status Evaluasi:** **VERIFIED & IMPLEMENTED (100% PASS)**  

---

### 1. Eksekutif Ringkasan Klasifikasi Risiko Bycatch

Phase 2.7 telah memverifikasi dan mengimplementasikan mesin klasifikasi risiko tangkapan sampingan (*bycatch*) berbasis tiga status lampu lalu lintas (*Traffic Light System*): **GREEN**, **YELLOW**, dan **RED**. Aturan ini didasarkan pada audit forensik `scripts/daily_inference.py` dan standar pelaporan batimetri lereng benua IOTC.

Struktur file implementasi:
- **Service Domain:** [`BycatchRiskService.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/Bycatch/Services/BycatchRiskService.php)
- **DTO Hasil Skrining:** [`BycatchClassification.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/Bycatch/DTO/BycatchClassification.php)

---

### 2. Aturan Klasifikasi Tiga Tingkat

```
[Sel Grid Masuk]
       │
       ▼
Apakah kedalaman di luar 150 - 1200 m?
       ├── YA  ──► GREEN (Risiko Rendah)
       │
       └── TIDAK (Lereng Benua 150-1200 m)
             │
             ▼
       Apakah SST <= 26.5°C DAN gradien SST >= 0.035°C/km
       DAN HSI Cakalang >= 0.70?
             ├── YA  ──► RED (Risiko Tinggi / Eskalasi Kritis)
             └── TIDAK ──► YELLOW (Kewaspadaan / Monitoring Standar)
```

#### Detail Definisi Tingkat Risiko:
1. **GREEN (Risiko Rendah / Low Risk):**
   - Seluruh sel yang berada di luar batas lereng benua (*continental shelf break*), yaitu kedalaman perairan $< 150\text{ m}$ (paparan pesisir dangkal) atau perairan oseanik dalam $> 1200\text{ m}$.
2. **YELLOW (Kewaspadaan / Precautionary Monitoring):**
   - Seluruh sel pada lereng benua ($150 - 1200\text{ m}$) yang tidak memicu seluruh kondisi eskalasi merah secara bersamaan. Zona ini mewajibkan penggunaan kail lingkaran (*circle hooks*) dan *tori lines* untuk armada rawai tuna.
3. **RED (Risiko Tinggi / High Risk):**
   - Seluruh sel pada lereng benua ($150 - 1200\text{ m}$) yang berada tepat pada front termal dingin ($\text{SST} \le 26.5^\circ\text{C}$ dan $\nabla \text{SST} \ge 0.035^\circ\text{C/km}$) DAN memiliki konsentrasi Cakalang sangat tinggi ($\text{HSI}_{\text{cakalang}} \ge 0.70$). Pertemuan faktor-faktor ini meningkatkan risiko interaksi penyu, hiu oseanik, dan juvenil madidihang secara eksponensial.

---

### 3. Bukti Pengujian Unit

Pengujian dijalankan pada [`tests/Unit/DomAndBycatchEngineTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Unit/DomAndBycatchEngineTest.php):
- `test_bycatch_traffic_light_classification` menguji seluruh 5 variasi input:
  - Paparan dangkal (100 m) -> **GREEN** (PASS)
  - Palung dalam (2500 m) -> **GREEN** (PASS)
  - Lereng benua hangat tanpa front (500 m) -> **YELLOW** (PASS)
  - Lereng benua front dingin dengan HSI sedang (0.65) -> **YELLOW** (PASS)
  - Lereng benua front dingin dengan HSI Cakalang >= 0.70 -> **RED** (PASS)
- **Hasil:** 100% PASS tanpa ambang batas rekaan.

