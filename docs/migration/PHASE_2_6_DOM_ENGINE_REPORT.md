# PHASE 2.6 — DYNAMIC OCEAN MANAGEMENT (DOM) ENGINE REPORT
## SEACAST PRIGI V3 — Core Radar Engine & Dynamic Filter

**Tanggal:** 2026-11-04  
**Arsitektur Target:** Laravel 13.40.0 Modular Monolith  
**Namespace:** `App\Domain\DynamicOceanManagement`  
**Homeport Acuan:** PPN Prigi (`111.7275°E, -8.2833°S`)  
**Status Evaluasi:** **VERIFIED & IMPLEMENTED (100% PASS)**  

---

### 1. Eksekutif Ringkasan Engine DOM

Phase 2.6 telah berhasil mengimplementasikan mesin klasifikasi Dynamic Ocean Management (DOM) yang mendelineasi perairan operasional WPPNRI 573 ke dalam 5 zona fungsional plus kelas umum (*General Oceanic*). Logika delineasi dan penegakan urutan prioritas konflik telah diuji secara komprehensif tanpa perubahan ambang batas dari PRD SEACAST PRIGI V2.3.2.

Struktur file implementasi:
- **Service Domain:** [`DomClassificationService.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/DynamicOceanManagement/Services/DomClassificationService.php)
- **DTO Hasil Delineasi:** [`DomZoneClassification.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/DynamicOceanManagement/DTO/DomZoneClassification.php)

---

### 2. Kriteria Ilmiah 6 Kelas DOM

| Kode Zona | Nama Zona | Batas Kedalaman | Jarak Homeport | Parameter Oseanografi / HSI | Sasaran Armada / Regulasi |
|---|---|:---:|:---:|---|---|
| **Z4** | Paparan Dangkal & Daerah Asuhan (*Nursery Ground*) | $\le 150\text{ m}$ | $\le 15\text{ NM}$ | Independen terhadap HSI | Perlindungan bibit juvenil pelagis & biota pesisir |
| **Z5** | Front Termal Lereng Benua (*Bycatch Screening*) | $150 - 1200\text{ m}$ | Bebas | $\text{SST} \le 26.5^\circ\text{C}$ DAN $\nabla \text{SST} \ge 0.035^\circ\text{C/km}$ | Skrining ketat alat tangkap longline/purse seine |
| **Z3** | Inti Produktivitas Klorofil (*Feeding Core*) | Bebas | Bebas | $\text{Chl-a} \ge 0.40\text{ mg/m}^3$ DAN $\nabla \text{SST} \ge 0.035^\circ\text{C/km}$ | Zona agregasi pakan alami tuna |
| **Z1** | Potensi Pesisir (*Inshore PFG*) | Bebas | $\le 12\text{ NM}$ | $\text{HSI}_{\text{cakalang}} \ge 0.55$ ATAU $\text{HSI}_{\text{madidihang}} \ge 0.55$ | Armada perahu motor tempel / sekoci $< 10\text{ GT}$ |
| **Z2** | Potensi Lepas Pantai (*Offshore PFG*) | Bebas | $12 - 60\text{ NM}$ | $\text{HSI}_{\text{cakalang}} \ge 0.55$ ATAU $\text{HSI}_{\text{madidihang}} \ge 0.55$ | Armada kapal motor $10 - 30\text{ GT}$ (operasi multi-day) |
| **GEN** | Samudera Terbuka (*General Oceanic*) | Bebas | Bebas | Tidak memenuhi kriteria Z1 s.d. Z5 | Navigasi umum & perikanan pelagis non-spesifik |

---

### 3. Penegakan Urutan Prioritas Mutlak

Apabila satu sel grid memenuhi lebih dari satu kriteria zona secara bersamaan, sistem menerapkan urutan prioritas mutlak berikut:

$$\mathbf{Z4} \longrightarrow \mathbf{Z5} \longrightarrow \mathbf{Z3} \longrightarrow \mathbf{Z1} \longrightarrow \mathbf{Z2} \longrightarrow \mathbf{GEN}$$

Contoh Kasus Konflik yang Berhasil Diverifikasi:
1. **Konflik Z4 vs Z1:** Sel di perairan kedalaman 80 m, jarak 6 NM dari pelabuhan, dengan HSI tuna 0.70. Meskipun memenuhi syarat Z1, sel tersebut **wajib** diklasifikasikan sebagai **Z4_NURSERY_GROUND** untuk memprioritaskan konservasi daerah asuhan di atas potensi penangkapan.
2. **Konflik Z5 vs Z3:** Sel pada lereng benua (500 m) dengan kantong dingin (SST 25.0°C, gradien 0.040) dan klorofil-a tinggi (0.60 mg/m³). Sel tersebut **wajib** diklasifikasikan sebagai **Z5_BYCATCH_SCREENING** untuk memprioritaskan keselamatan bycatch di atas indikator feeding core.

---

### 4. Bukti Pengujian Unit

Pengujian dijalankan pada [`tests/Unit/DomAndBycatchEngineTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Unit/DomAndBycatchEngineTest.php):
- `test_classifies_z4_nursery_ground` -> PASS
- `test_classifies_z5_bycatch_thermal_front` -> PASS
- `test_classifies_z3_feeding_core` -> PASS
- `test_classifies_z1_and_z2_pfg_by_distance` -> PASS
- `test_classifies_general_oceanic_fallback` -> PASS
- `test_strict_dom_priority_order_enforcement` -> PASS
- **Hasil:** 6 tests, 10 assertions, **100% PASS**.

