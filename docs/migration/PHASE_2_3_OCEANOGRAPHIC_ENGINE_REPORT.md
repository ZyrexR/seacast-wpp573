# PHASE 2.3 — OCEANOGRAPHIC DATA ENGINE REPORT
## SEACAST PRIGI V3 — Core Radar Engine & Dynamic Filter

**Tanggal:** 2026-11-04  
**Arsitektur Target:** Laravel 13.40.0 Modular Monolith  
**Namespace:** `App\Domain\Oceanography`  
**Database Sumber:** PostgreSQL 17.11 + PostGIS 3.6.2 (`seacast_prigi`)  
**Status Evaluasi:** **VERIFIED & IMPLEMENTED (100% PASS)**  

---

### 1. Eksekutif Ringkasan Engine Oseanografi

Phase 2.3 telah berhasil mengintegrasikan modul pengelolaan parameter oseanografi fisik perairan WPPNRI 573 ke dalam arsitektur Laravel. Modul ini menyediakan registri metadata ilmiah lengkap untuk 5 variabel oseanografi, validasi rentang fisik, deteksi front termal berbasis gradien SST, serta penandaan sumber data (*provenance*) secara ketat agar data simulasi lokal tidak disalahartikan sebagai observasi satelit langsung.

Struktur file yang diimplementasikan:
- **Service Domain:** [`OceanographicDataService.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/Oceanography/Services/OceanographicDataService.php)
- **DTO Kondisi Oseanografi:** [`OceanographicConditions.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/Oceanography/DTO/OceanographicConditions.php)
- **DTO Metadata Variabel:** [`VariableMetadata.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/Oceanography/DTO/VariableMetadata.php)

---

### 2. Registri Ilmiah 5 Variabel Oseanografi

| Variabel | Nama Ilmiah | Satuan | Sumber Resmi Terdaftar | Tipe Sumber | Resolusi Spasial | Rentang Valid | Keterangan Metodologis |
|---|---|:---:|---|:---:|:---:|:---:|---|
| **SST** | Sea Surface Temperature | °C | NOAA CoralTemp OISST Daily Blended | `OBSERVED` | 0.05° (~5 km) | 20.0 – 33.0 °C | Suhu permukaan laut resolusi tinggi untuk pelacakan kantong upwelling |
| **Chl-a** | Chlorophyll-a | mg/m³ | Copernicus Sentinel-3 OLCI Ocean Colour | `OBSERVED` | 0.04° (~4 km) | 0.01 – 5.00 mg/m³ | Konsentrasi fitoplankton penanda feeding core pelagis |
| **Currents** | Ocean Current Velocity | m/s | HYCOM + NCODA Global 1/12° Analysis | `MODELED` | 0.08° (~9 km) | 0.00 – 2.50 m/s | Vektor kecepatan arus laut sirkulasi monsunal |
| **Wind & Wave** | Kecepatan Angin & Gelombang | m/s & m | Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal) | `SIMULATED` | 0.10° (~11 km) | 0.5 – 25.0 m/s (Gelombang 0.5–5.0 m) | Parameter keselamatan pelayaran armada nelayan Prigi |
| **Bathymetry** | Topografi Kedalaman Laut | m | GEBCO Global Ocean Depth 2024 | `OBSERVED` | 15 arc-sec (~450 m) | 5.0 – 7.000,0 m | Delineasi paparan pesisir dan lereng benua (*shelf break*) |

---

### 3. Ambang Batas Ilmiah & Formula Delineasi

1. **Thermal Front Gradient:**
   Front termal didefinisikan apabila gradien spasial SST memenuhi:
   $$\nabla \text{SST} \ge 0.035\ ^\circ\text{C/km}$$
   Kondisi ini memicu flag `is_thermal_front = true` dan menjadi syarat penting pada zona Z5 (Bycatch Screening) dan Z3 (Feeding Core).
2. **Chlorophyll-a Bloom:**
   Konsentrasi fitoplankton tinggi untuk feeding core:
   $$\text{Chl-a} \ge 0.40\ \text{mg/m}^3$$
3. **Zona Lereng Benua (Continental Shelf Break):**
   Kedalaman batimetri lereng benua sesuai kriteria IOTC:
   $$150\ \text{m} \le \text{Depth} \le 1200\ \text{m}$$

---

### 4. Penanganan Nilai Missing & Label Disclaimer

- Nilai `null` atau `NaN` dipertahankan dan ditandai dalam `qualityFlags` tanpa konversi otomatis ke 0.0.
- Tinggi gelombang dan angin secara konsisten dilabeli sebagai:
  `"Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)"`
  Tidak ada klaim integrasi telemetry BMKG live tanpa sambungan operasional aktif.

---

### 5. Bukti Pengujian Unit

Pengujian dijalankan pada [`tests/Unit/OceanographicEngineTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Unit/OceanographicEngineTest.php):
- `test_can_retrieve_variable_metadata_registry` -> PASS
- `test_thermal_front_indicator_detection` -> PASS
- `test_variable_range_validation` -> PASS
- `test_can_retrieve_grid_oceanographic_conditions` -> PASS
- **Hasil:** 4 tests, 24 assertions, 0 failures (0,48s).

