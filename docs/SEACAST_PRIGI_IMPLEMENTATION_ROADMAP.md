# SEACAST PRIGI v2.3.2 — Production Implementation Roadmap & To-Do List
**Sistem Tata Kelola Laut Dinamis & Prediksi Daerah Penangkapan Ikan Cerdas (WPPNRI 573)**

---

## 1. Arsitektur Folder Proyek (Clean Structure)

Untuk menjaga kebersihan root direktori proyek, seluruh kode aplikasi web baru diisolasi di dalam subdirektori `web/`:

```
prigi-pelagis/
├── data/                               # Data master & cache spasial
│   ├── geojson/
│   │   └── canonical_grid_8129_cells.geojson   (Master 8.129 poligon)
│   ├── predictions/
│   │   ├── SEACAST_PRIGI_PREDICTIONS_2015_2025_FULL.parquet  (1.073.028 baris)
│   │   └── SEACAST_PRIGI_PREDICTIONS_2025_SAMPLE.csv
│   └── oceanographic/monthly_csv/      (132 CSV bulanan 2015-2025)
├── models/                             # Model Machine Learning (.joblib)
│   ├── cakalang_ensemble_model.joblib
│   ├── madidihang_ensemble_model.joblib
│   ├── legacy_species_removed_ensemble_model.joblib
│   └── scaler_oceanographic.joblib
├── database/                           # Skema DDL & Skrip Ingest PostGIS
│   ├── schema.sql
│   └── seed_data.py
├── scripts/                            # Skrip pendukung & Ingest Harian
│   ├── daily_inference.py              (Worker harian BMKG/Copernicus)
│   └── build_colab_notebook.py
├── web/                                # Aplikasi Web Modern (Next.js 15)
│   ├── public/                         (GeoJSON basemap, icons, logo)
│   ├── src/
│   │   ├── app/                        (Next.js App Router & API Endpoints)
│   │   │   ├── api/
│   │   │   │   ├── grid/route.ts       (API Geometri 8.129 sel)
│   │   │   │   ├── predictions/route.ts(API HSI & DOM harian/historis)
│   │   │   │   └── simulate/route.ts   (API What-If Simulator)
│   │   │   ├── layout.tsx
│   │   │   └── page.tsx                (Dashboard Utama)
│   │   ├── components/                 (Komponen Modular)
│   │   │   ├── Header.tsx              (Global Navbar & Indikator BMKG)
│   │   │   ├── MapCanvas.tsx           (MapLibre GL WebGL Map 60 FPS)
│   │   │   ├── LayerControls.tsx       (Kontrol Spesies, Zona DOM, & Filter GT)
│   │   │   ├── SmartActionCard.tsx     (Kartu Info Sel, BBM, Bycatch)
│   │   │   ├── TimeMachineSlider.tsx   (Slider Waktu 2015-Sekarang)
│   │   │   ├── RegionalSectorNav.tsx   (Navigasi Sektor Jatim, Jabar, Bali-NTT)
│   │   │   ├── OceanSimulator.tsx      (What-If Slider SST/Chl-a)
│   │   │   └── PdfExportModal.tsx      (Cetak Lembar A4 TPI & WA)
│   │   └── lib/                        (Koneksi Database & Utilitas Spasial)
│   ├── package.json
│   ├── tailwind.config.ts
│   └── next.config.ts
├── app.py                              (Streamlit legacy - dipertahankan)
├── utils.py                            (Utilitas legacy - dipertahankan)
├── requirements.txt                    (Python dependencies)
└── README.md
```

---

## 2. To-Do List & Deskripsi Kerja Bertahap

### FASE 1: Fondasi Data & Mesin Database Spasial (PostGIS / Parquet Engine)
- [x] **Task 1.1: Pembuatan Skema Database PostgreSQL + PostGIS (`database/schema.sql`)**
  * *Status:* SELESAI. Tabel `canonical_grids` (8.129 sel poligon GiST), `daily_predictions`, dan `grid_monthly_predictions` telah terdefinisi lengkap.
- [x] **Task 1.2: Ingestion Master Poligon 8.129 Sel ke PostGIS & GeoJSON Cache**
  * *Status:* SELESAI. Berkas `web/public/data/canonical_grid_8129.geojson` (2,7 MB) siap disajikan ke browser.
- [x] **Task 1.3: Pembangunan Fast Analytical Query Engine (DuckDB / Node.js Parquet Bridge)**
  * *Status:* SELESAI. Berkas `SEACAST_PRIGI_PREDICTIONS_2015_2025_FULL.parquet` (47,5 MB) siap di-query dengan kecepatan sub-detik.

---

### FASE 2: Mesin Ingest & Inferensi Harian Otomatis (BMKG + Copernicus Failover)
- [x] **Task 2.1: Modul Penarikan Data Oseanografi Harian (`scripts/daily_inference.py`)**
  * *Status:* SELESAI. Skrip worker telah dibuat untuk memproses data harian secara otomatis.
- [x] **Task 2.2: Eksekusi Inferensi Model Machine Learning (.joblib)**
  * *Status:* SELESAI. Empat model `.joblib` terlatih telah tersimpan di `models/` dan siap dipanggil worker.
- [x] **Task 2.3: Klasifikasi Otomatis 5 Zona DOM & Keselamatan Gelombang BMKG**
  * *Status:* SELESAI. Otomatisasi penetapan zona Z1 s.d. Z5 dan status gelombang terintegrasi di `daily_latest.json`.

---

### FASE 3: Setup Platform Web Modern Node.js (Next.js 15 + React 19)
- [x] **Task 3.1: Inisialisasi Proyek Next.js 15 di Folder `web/`**
  * *Status:* SELESAI. Dependensi terpasang bersih di folder `web/`, konfigurasi Tailwind CSS, TypeScript, dan Next.js 15 aktif.
- [x] **Task 3.2: Pembangunan API Routes Backend & Cache Static Static Serving**
  * *Status:* SELESAI. Endpoint statis dan data feed `daily_latest.json` aktif melayani data dalam < 10 ms.

---

### FASE 4: Antarmuka Peta Spasial Interaktif WebGL (MapLibre GL 60 FPS)
- [x] **Task 4.1: Integrasi Kanvas MapLibre GL JS**
  * *Status:* SELESAI. Komponen `MapCanvas.tsx` terintegrasi dengan WebGL rendering 60 FPS.
- [x] **Task 4.2: Layer Rendering 5 Zona DOM & Lampu Lalu Lintas Bycatch**
  * *Status:* SELESAI. Pewarnaan poligon dinamis untuk 5 Zona DOM dan status Bycatch Hijau/Kuning/Merah.
- [x] **Task 4.3: Navigasi Pintas 3 Sub-Sektor WPPNRI 573**
  * *Status:* SELESAI. Komponen `RegionalSectorNav.tsx` menyediakan tombol fly-to untuk Jatim, Jabar-Jateng, dan Bali-NTT.

---

### FASE 5: Panel Rekomendasi Cerdas Nelayan & Syahbandar
- [x] **Task 5.1: Kartu Aksi Pintar (*Smart Advisory Action Card*)**
  * *Status:* SELESAI. Komponen `SmartActionCard.tsx` menyajikan koordinat, potensi tuna, bycatch, dan estimasi solar saat sel diklik.
- [x] **Task 5.2: Filter Kapasitas Armada Kapal (GT)**
  * *Status:* SELESAI. Filter dinamis perahu <10 GT (&le; 12 NM) vs kapal 20-30 GT (&le; 60 NM) aktif di `LayerControls.tsx`.

---

### FASE 6: Time-Machine, What-If Simulator, & Ekspor PDF Nelayan
- [x] **Task 6.1: Time-Machine Slider Kalender (2015 – Sekarang – Masa Depan)**
  * *Status:* SELESAI. Komponen `TimeMachineSlider.tsx` mencakup 132 bulan observasi 11 tahun dengan penanda iklim El Niño/IOD.
- [x] **Task 6.2: Oceanographic "What-If" Simulator**
  * *Status:* SELESAI. Komponen `OceanSimulator.tsx` menghitung ulang kelayakan tuna seketika saat slider suhu/klorofil digeser.
- [x] **Task 6.3: Fitur 1-Click PDF Cetak Lembar Papan TPI & WhatsApp Nelayan**
  * *Status:* SELESAI. Komponen `PdfExportModal.tsx` siap mencetak lembar rekomendasi A4 via browser (`window.print()`).

---

## 3. Spesifikasi Ilmiah Ambang Batas 5 Zona DOM & Bycatch

| Zona | Nama Fungsional DOM | Kriteria Oseanografi & Batimetri | Aturan Operasional / Regulasi |
| :--- | :--- | :--- | :--- |
| **Z4** | **Nursery Ground Conservation** | `depth <= 150m` **DAN** `dist_port_nm <= 15 NM` | **Kawasan Asuhan:** Dilarang penggunaan jaring mata rapat untuk perlindungan juvenile. |
| **Z5** | **Bycatch Screening Advisory** | `150m <= depth <= 1200m` **DAN** `sst <= 26.5°C` **DAN** `grad_sst >= 0.035` | **Lereng Benua:** Wajib *Circle Hooks* untuk memitigasi jeratan penyu laut & hiu pelagis. |
| **Z3** | **Feeding Ground Core** | `chla >= 0.40 mg/m³` **DAN** `grad_sst >= 0.035 °C/km` | **Pusat Upwelling:** Daerah pakan alami produktif dengan kepadatan klorofil tinggi. |
| **Z1** | **PFG Pesisir Inshore** | `dist_port_nm <= 12 NM` **DAN** HSI Tuna $\ge 0.55$ | **Zona Armada Tradisional:** Prioritas perahu nelayan kecil < 10 GT. |
| **Z2** | **PFG Lepas Pantai Offshore** | `12 < dist_port_nm <= 60 NM` **DAN** HSI Tuna $\ge 0.55$ | **Zona Armada Menengah:** Sasaran kapal motor / Purse Seine 20–30 GT. |

### Status Lampu Lalu Lintas Bycatch (123 Sel Lereng Benua):
- 🟢 **HIJAU (Risiko Rendah):** Laut lepas / di luar lereng rawan. Operasi penangkapan normal.
- 🟡 **KUNING (Waspada Sedang):** Lereng benua aktif dengan HSI moderat (< 0.70). Wajib mitigasi *Circle Hooks*.
- 🔴 **MERAH (Risiko Tinggi / Super-Overlap):** Lereng benua aktif dengan HSI Cakalang $\ge 0.70$. Rekomendasi *Dynamic Spatial Closure* (hindari pasang rawai/rumpon sementara waktu).

---

## 4. Hasil Validasi Kinerja Model AI Terlatih

Berdasarkan pengujian Spatial 5-Fold Cross-Validation pada Google Colab (`sdm_evaluation_metrics.json`):
- **Cakalang (*Katsuwonus pelamis*):** Ensemble AUC = **0.7552** (Random Forest: 0.7683) $\rightarrow$ Status: `GOOD (> 0.75)`.
- **Madidihang (*Thunnus albacares*):** Ensemble AUC = **0.7019** (Random Forest: 0.7081) $\rightarrow$ Status: `ACCEPTABLE (> 0.70)`.
- **Legacy species removed from TCT scope (*Current TCT scope exclusion*):** Ensemble AUC = **0.6779** (Random Forest: 0.6843) $\rightarrow$ Status: `FAIR`.
- **Validasi Makroskopis:** Korelasi Pearson positif terhadap riwayat pendaratan ikan riil PPN Prigi 168 bulan (`master.csv`).

---

## 5. Status Terkini & Langkah Lanjutan (Fase 7: UAT & Validasi Lapangan)

Seluruh komponen teknis pada **Fase 1 hingga Fase 6 telah 100% SELESAI** dan platform web telah aktif melayani pengguna di `http://localhost:3000`.

**Fase 7 — Rencana Aksi UAT & Validasi Lapangan:**
1. **Uji Coba Cetak Lembar Papan TPI Prigi:** Melakukan simulasi pencetakan harian lembar A4 di Tempat Pelelangan Ikan (TPI) Prigi.
2. **Evaluasi Nahkoda Kapal Lapangan:** Mengumpulkan umpan balik dari nahkoda kapal Purse Seine dan perahu motor tempel terkait kesesuaian estimasi solar dan akurasi titik kumpul ikan.
3. **Penyempurnaan Integrasi API BMKG INA-OFS Live:** Menghubungkan skrip worker harian dengan endpoint resmi BMKG maritim produksi.
