# PHASE 2.8 — DYNAMIC FILTER SYSTEM REPORT
## SEACAST PRIGI V3 — Core Radar Engine & Dynamic Filter

**Tanggal:** 2026-11-04  
**Arsitektur Target:** Laravel 13.40.0 Modular Monolith + Alpine.js State Engine  
**Modul:** Dynamic Oceanographic & Spatial Filter System  
**Status Evaluasi:** **VERIFIED & IMPLEMENTED (100% PASS)**  

---

### 1. Eksekutif Ringkasan Sistem Filter Dinamis

Phase 2.8 telah berhasil mengintegrasikan sistem filter dinamis yang menghubungkan interaksi antarmuka pengguna secara real-time ke query server-side PostGIS dan engine SDM. Berbeda dengan pendekatan dekoratif sebelumnya yang hanya menyembunyikan elemen visual di sisi browser, sistem filter baru ini mengirimkan parameter analitis terstruktur ke endpoint `/api/predictions` untuk menghasilkan subset data spasial kanonik yang akurat secara matematis.

Struktur file implementasi:
- **Interface Pengendali Filter:** [`dpi-radar.blade.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/resources/views/dashboard/dpi-radar.blade.php)
- **Controller Backend Penangan Query:** [`PredictionApiController.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Http/Controllers/Api/PredictionApiController.php)
- **Repository Penegak Query Spasial:** [`PostGisScientificDataRepository.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/ScientificData/Repositories/PostGisScientificDataRepository.php)

---

### 2. Matriks Parameter Filter & Perilaku Analitis

| Kategori Filter | Parameter Input | Nilai / Opsi yang Didukung | Perilaku Backend & Dampak Spasial |
|---|---|---|---|
| **A. Spesies Target** | `species` | `all`, `cakalang`, `madidihang`, `albakor` | Memilih kolom kesesuaian habitat target (`hsi_cakalang`, `hsi_madidihang`, `hsi_albakor`) atau komposit rata-rata. |
| **B. Ambang Batas HSI** | `min_hsi` | Slider kontinu $0.00 - 0.85$ (step 0.05) | Menyaring sel poligon secara server-side: `WHERE dp.hsi_{species} >= ?`. Mendukung debounce 300ms untuk kelancaran interaksi. |
| **C. Zonasi DOM** | `dom_zone` | `Z1_PFG_INSHORE`, `Z2_PFG_OFFSHORE`, `Z3_FEEDING_CORE`, `Z4_NURSERY_GROUND`, `Z5_BYCATCH_SCREENING`, `GENERAL_OCEANIC` | Memfilter hasil berdasarkan matriks tata ruang bergerak 5-zona plus kelas umum. |
| **D. Risiko Bycatch** | `bycatch` | `ALL`, `GREEN`, `YELLOW`, `RED` | Memfilter sel poligon berdasarkan kriteria batimetri lereng benua dan front termal IOTC. |
| **E. Sektor Spasial** | `sector` | `ALL`, `JATIM`, `JABAR_JATENG`, `BALI_NUSA_TENGGARA` | Memotong query per sektor geospasial WPPNRI 573 dengan indeks spasial PostGIS. |
| **F. Hotspot Saja** | `hotspot_only` | Boolean toggle (`true`/`false`) | Menampilkan hanya sel dengan $\text{HSI} \ge 0.70$ (agregasi tuna intensif). |
| **G. Bounding Box** | `bbox` | `minLon,minLat,maxLon,maxLat` | Membatasi hasil pada jendela koordinat viewport peta (`ST_MakeEnvelope`). |

---

### 3. Pembaruan HUD Counter Real-Time

Setiap perubahan kombinasi filter memperbarui 4 metrik HUD secara dinamis:
1. **Sel Terfilter:** Jumlah sel poligon yang memenuhi seluruh kombinasi syarat (AND logic).
2. **Hotspot Tuna:** Jumlah sel aktif dengan kriteria hotspot.
3. **Rata-rata HSI:** Mean nilai kesesuaian habitat pada subset sel terfilter.
4. **Persentase Aman Melaut:** Proporsi sel terfilter dengan status keselamatan BMKG `AMAN_MELAUT`.

---

### 4. Bukti Pengujian Feature & Interaktivitas

Pengujian dijalankan pada [`tests/Feature/CoreRadarApiTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Feature/CoreRadarApiTest.php) dan [`tests/Feature/DpiRadarViewTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Feature/DpiRadarViewTest.php):
- Filter multi-kriteria (Sektor JATIM + DOM Z4 Nursery) -> PASS (tepat menyaring poligon sesuai syarat kedalaman <=150m dan jarak <=15 NM).
- Filter Bycatch Light -> PASS.
- Tombol Reset Filter -> Mengembalikan state Alpine.js dan memuat ulang baseline lengkap 8.129 sel.
- **Hasil:** 100% PASS.

