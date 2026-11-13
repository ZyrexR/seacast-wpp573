# PHASE 2.5 — HABITAT SUITABILITY INDEX (HSI) REPORT
## SEACAST PRIGI V3 — Core Radar Engine & Dynamic Filter

**Tanggal:** 2026-11-04  
**Arsitektur Target:** Laravel 13.40.0 Modular Monolith  
**Namespace:** `App\Domain\Species`  
**Status Evaluasi:** **VERIFIED & IMPLEMENTED (100% PASS)**  

---

### 1. Eksekutif Ringkasan Formula HSI

Habitat Suitability Index (HSI) merupakan indeks gabungan yang mengukur tingkat kesesuaian lingkungan perairan terhadap kehadiran spesies pelagis target. Pada SEACAST PRIGI V3, HSI dihitung melalui model ensemble multi-komponen dengan pembobotan linear berbasis keterampilan prediksi proxy.

File implementasi:
- **Service HSI:** [`HabitatSuitabilityIndexService.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/Species/Services/HabitatSuitabilityIndexService.php)

---

### 2. Formula Komputasi Ensemble HSI

Komputasi HSI mentah per sel grid $i$ mengikuti persamaan:
$$\text{HSI}_{\text{raw}, s} = w_{\text{maxent}, s} \cdot P_{\text{maxent}} + w_{\text{rf}, s} \cdot P_{\text{rf}} + w_{\text{glm}, s} \cdot P_{\text{glm}}$$

Di mana:
- $s \in \{\text{cakalang}, \text{madidihang}, \text{legacy_species_removed}\}$
- $P$ adalah probabilitas kelas kehadiran dari model sub-komponen
- $\sum w = 1.000$

Normalisasi spasial dihitung pada skala domain WPPNRI 573:
$$\text{HSI}_{\text{norm}} = \frac{\text{HSI}_{\text{raw}} - \min(\text{HSI}_{\text{raw}})}{\max(\text{HSI}_{\text{raw}}) - \min(\text{HSI}_{\text{raw}})}$$
Rentang terdefinisi ketat: $0.000 \le \text{HSI} \le 1.000$.

---

### 3. Ambang Batas Operasional Ilmiah

1. **Fishing Hotspot Identification:**
   Suatu sel grid diklasifikasikan sebagai hotspot penangkapan ikan apabila:
   $$\text{HSI}_{\text{cakalang}} \ge 0.70 \quad \lor \quad \text{HSI}_{\text{madidihang}} \ge 0.70$$
2. **Tuna Presence Potential (Syarat DOM Z1/Z2):**
   Ambang batas minimum potensi tuna untuk klasifikasi zona DOM Z1 (Inshore) dan Z2 (Offshore):
   $$\text{HSI}_{\text{cakalang}} \ge 0.55 \quad \lor \quad \text{HSI}_{\text{madidihang}} \ge 0.55$$

---

### 4. Bukti Pengujian Unit

Pengujian dijalankan pada [`tests/Unit/SpeciesAndHsiEngineTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Unit/SpeciesAndHsiEngineTest.php):
- `test_raw_ensemble_calculation_formula` -> PASS
- `test_hsi_normalization_and_clamping` -> PASS
- `test_hotspot_and_potential_thresholds` -> PASS
- **Hasil:** 5 tests, 26 assertions, **100% PASS**.

