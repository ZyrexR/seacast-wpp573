# PRD — SEACAST PRIGI v2.3.2
**Sistem Tata Kelola Laut Dinamis & Prediksi Daerah Penangkapan Ikan Cerdas (WPPNRI 573)**

| Atribut | Nilai |
| --- | --- |
| Versi dokumen | 2.0 (Resmi / Finalized) |
| Tanggal | 3 November 2026 |
| Status produk | Seluruh task Fase 1–6 Selesai; Server Web Aktif di `http://localhost:3000` |
| Cakupan wilayah | WPPNRI 573 (102.50°E – 126.40°E, -6.90°S – -14.00°S, Selat Sunda hingga NTT) |
| Basis kode | Next.js 15.5 + React 19 (`web/`), PostGIS, MapLibre GL JS, Python worker, model `.joblib` |

---

## 1. Ringkasan Eksekutif

SEACAST PRIGI adalah platform web operasional untuk memprediksi **Daerah Penangkapan Ikan (DPI)** tuna pelagis (**Cakalang, Madidihang, Albakor**) di WPPNRI 573 berbasis integrasi multi-satelit oseanografi (SST, klorofil-a, arus, angin) dan model *Ensemble Machine Learning* (**MaxEnt + Random Forest + GLM**).

Prediksi disajikan sebagai peta WebGL interaktif (MapLibre GL 60 FPS) dengan **Habitat Suitability Index (HSI)**, klasifikasi **5 Zona Tata Kelola Laut Dinamis (Dynamic Ocean Management / DOM Z1–Z5)**, sistem lampu lalu lintas **risiko bycatch (Hijau/Kuning/Merah)**, dan **integrasi status keselamatan gelombang BMKG**, sehingga nelayan tradisional, armada kapal motor, syahbandar, dan pengelola TPI dapat memutuskan waktu dan lokasi melaut secara efisien, aman, dan berkelanjutan.

**Nilai Utama:**
- **Efisiensi Bahan Bakar:** Menurunkan *search time* dan konsumsi BBM nelayan PPN Prigi dengan estimasi kebutuhan solar (PP) per sel tangkapan.
- **Keselamatan Jiwa Melaut:** Integrasi peringatan tinggi gelombang BMKG INA-OFS dengan filter spesifik kapasitas kapal (<10 GT dilarang melaut jika gelombang $\ge 2.0\text{ m}$).
- **Konservasi & Sertifikasi Ekspor:** Perlindungan habitat asuhan anakan (*Z4 Nursery Ground*) dan sistem mitigasi tangkapan sampingan hiu/penyu (*Z5 Bycatch Traffic Light*) sesuai standar internasional IOTC.
- **Eksplorasi Makro Regional:** Cakupan penuh 8.129 sel poligon WPPNRI 573 dari Selat Sunda hingga Laut Sawu/NTT dengan time-machine iklim 11 tahun (2015–2025).

---

## 2. Latar Belakang & Masalah

| # | Masalah | Dampak Lapangan | Solusi SEACAST PRIGI |
| --- | --- | --- | --- |
| **P1** | Nelayan Prigi menentukan lokasi melaut secara spekulatif/informal | Waktu pencarian lama, biaya BBM solar bengkak | Prediksi koordinat GPS hotspot HSI terkalibrasi AI |
| **P2** | Data satelit oseanografi (Copernicus, BMKG) mentah & terpisah | Tidak dapat dimanfaatkan langsung oleh nelayan/TPI | Pipeline otomatisasi ekstraksi ke 8.129 sel WPPNRI 573 |
| **P3** | Rekomendasi penangkapan tidak membedakan armada | Perahu kecil melaut terlalu jauh, membahayakan nyawa | Filter armada bertingkat (<10 GT $\le 12$ NM vs 20–30 GT $\le 60$ NM) |
| **P4** | Rekomendasi ikan sering mengabaikan risiko cuaca ekstrem | Kecelakaan laut akibat gelombang tinggi | Integrasi filter keselamatan gelombang BMKG |
| **P5** | Nelayan tradisional tidak membawa laptop ke laut | Informasi digital tidak tersampaikan di lapangan | Fitur 1-klik cetak Lembar Rekomendasi A4 untuk papan TPI & WhatsApp |
| **P6** | Peneliti sulit melacak pergeseran iklim jangka panjang | Analisis dampak El Niño / Dipole Mode (+IOD) lambat | Time-Machine 132 bulan (2015–2025) & What-If Simulator |

---

## 3. Tujuan & Metrik Keberhasilan (KPI)

### 3.1 Tujuan Produk
1. Menyajikan prediksi DPI harian dan 5 Zona DOM untuk seluruh **8.129 sel grid kanonik** di WPPNRI 573.
2. Mengubah data numerik oseanografi menjadi kartu aksi nelayan praktis (koordinat, potensi %, izin GT, status bycatch, estimasi solar).
3. Menyediakan navigasi cepat 3 sub-sektor (Jawa Timur, Jabar–Jateng, Bali–NTT) dan eksplorasi historis 11 tahun (1.073.028 baris).
4. Menyalurkan rekomendasi harian sebelum pukul 04:30 WIB setiap pagi melalui portal web, papan TPI Prigi, dan WhatsApp.

### 3.2 Metrik Kinerja Sistem
| Kategori | Parameter | Target | Realisasi v2.3.2 |
| :--- | :--- | :--- | :--- |
| **Performa Web** | Latensi Feed Harian (`daily_latest.json`) | $< 50\text{ ms}$ | **$8\text{ ms}$** (Static Serving) |
| **Performa Peta** | Frame Rate Map Canvas (WebGL) | $60\text{ FPS}$ | **$60\text{ FPS}$** (MapLibre GL) |
| **Ukuran Data** | Master Grid Poligon (GeoJSON) | $< 3.0\text{ MB}$ | **$2.7\text{ MB}$** (8.129 poligon) |
| **Model Cakalang** | AUC-ROC Ensemble SDM | $> 0.75\text{ (Good)}$ | **$0.7552$** (RF: 0.7683) |
| **Model Madidihang** | AUC-ROC Ensemble SDM | $> 0.70\text{ (Fair)}$ | **$0.7019$** (RF: 0.7081) |
| **Model Albakor** | AUC-ROC Ensemble SDM | $> 0.65\text{ (Fair)}$ | **$0.6779$** (RF: 0.6843) |
| **Keandalan Ingest** | Uptime Pipeline Harian 04:00 WIB | $\ge 98\%$ | Multi-Source Auto-Failover (BMKG ↔ CMEMS) |

---

## 4. Persona Pengguna

1. **Nelayan Perahu Tradisional (< 10 GT / 67% Armada Prigi):**
   - *Kebutuhan:* Titik tangkap dekat pantai ($\le 12\text{ NM}$), aman dari ombak besar, hemat BBM solar.
   - *Fitur Utama:* Filter Armada < 10 GT, Smart Action Card, Lembar Cetak Papan TPI & WA.
2. **Nahkoda Kapal Motor / Purse Seine (20–30 GT / 20% Armada Prigi):**
   - *Kebutuhan:* Hotspot tuna lepas pantai ($12 - 60\text{ NM}$), estimasi waktu tempuh, dan kebutuhan solar besar.
   - *Fitur Utama:* Filter Armada Offshore, Layer HSI Heatmap, Kalkulator BBM Solar.
3. **Syahbandar & Petugas PPN Prigi:**
   - *Kebutuhan:* Memantau keselamatan kapal melaut, validasi logbook, kepatuhan konservasi.
   - *Fitur Utama:* Status Gelombang BMKG, Export Lembar A4 Harian, Lampu Bycatch Merah.
4. **Peneliti Kelautan & Akademisi:**
   - *Kebutuhan:* Analisis serial waktu 11 tahun, uji sensitivitas upwelling, respons tuna terhadap suhu.
   - *Fitur Utama:* Time-Machine Slider (2015–2025), Oceanographic What-If Simulator, Parquet Data Lake.

---

## 5. Ruang Lingkup Sistem

### 5.1 In-Scope (v2.3.2 Selesai & Berjalan)
- **Tiga Spesies Target:** Cakalang (*Katsuwonus pelamis*), Madidihang (*Thunnus albacares*), Albakor (*Thunnus alalunga*).
- **Cakupan Spasial Penuh:** 8.129 sel poligon WPPNRI 573 ($102.50^\circ\text{ E} - 126.40^\circ\text{ E}$, $-6.90^\circ\text{ S} - -14.00^\circ\text{ S}$).
- **Basis Data Historis 11 Tahun:** 132 bulan observasi (1.073.028 baris data kanonik) dari Januari 2015 s.d. Desember 2025.
- **Pipeline Inferensi Harian Otomatis:** Worker Python (`scripts/daily_inference.py`) pukul 04:00 WIB.
- **5 Zona Tata Kelola Laut Dinamis (DOM):** Z1 Inshore, Z2 Offshore, Z3 Feeding Core, Z4 Nursery Ground, Z5 Bycatch Screening.
- **Sistem Lampu Lalu Lintas Bycatch:** 123 poligon lereng benua (150–1.200 m) berstatus Hijau, Kuning, atau Merah.
- **Antarmuka Web Modern:** Next.js 15.5 + React 19 + MapLibre GL JS + Tailwind CSS berjalan di `http://localhost:3000`.
- **Fitur Interaktif:** Navigasi 3 Sektor Regional, Smart Action Card, Time-Machine 11 Tahun, What-If Simulator, dan 1-Click PDF Export.

---

## 6. Spesifikasi Kebutuhan Fungsional & Aturan Bisnis

### FR-01: Definisi & Kriteria Pasti 5 Zona DOM (Z1–Z5)
Penetapan status sel dilakukan secara hierarkis dengan aturan kuantitatif berikut:

| Kode Zona | Nama Zona Fungsional DOM | Kriteria Oseanografi & Batimetri | Aturan Regulasi / Operasional |
| :--- | :--- | :--- | :--- |
| **Z4** | **Nursery Ground Conservation** | `depth <= 150.0 m` **DAN** `dist_port_nm <= 15.0 NM` | **Kawasan Konservasi Asuhan:** Dilarang keras penggunaan alat tangkap jaring mata rapat / berkantong. |
| **Z5** | **Bycatch Screening Advisory** | `depth >= 150 m` **DAN** `depth <= 1200 m` **DAN** `sst <= 26.5 °C` **DAN** `grad_sst >= 0.035 °C/km` | **Koridor Lereng Batimetri:** Wajib menggunakan *Circle Hooks* pada rawai tuna untuk mencegah kematian penyu/hiu. |
| **Z3** | **Feeding Ground Core** | `chla >= 0.40 mg/m³` **DAN** `grad_sst >= 0.035 °C/km` | **Pusat Daerah Pakan:** Konsentrasi upwelling aktif dengan kelimpahan pakan alami tertinggi. |
| **Z1** | **PFG Pesisir Inshore** | `dist_port_nm <= 12.0 NM` **DAN** (`hsi_cakalang >= 0.55` atau `hsi_madidihang >= 0.55`) | **Zona Khusus Armada Tradisional:** Prioritas perahu motor tempel < 10 GT. |
| **Z2** | **PFG Lepas Pantai Offshore** | `12.0 < dist_port_nm <= 60.0 NM` **DAN** (`hsi_cakalang >= 0.55` atau `hsi_madidihang >= 0.55`) | **Zona Armada Menengah:** Sasaran kapal Purse Seine 20–30 GT. |
| **GEN** | **General Oceanic** | Sel di luar ambang batas di atas | Perairan umum laut lepas. |

*Urutan Prioritas Evaluasi:* `Z4 (Konservasi)` $\rightarrow$ `Z5 (Bycatch)` $\rightarrow$ `Z3 (Feeding)` $\rightarrow$ `Z1 (Inshore)` $\rightarrow$ `Z2 (Offshore)` $\rightarrow$ `General Oceanic`.

---

### FR-02: Perhitungan Status Lampu Bycatch (Hijau / Kuning / Merah)
Dihitung dari batimetri lereng benua (150–1.200 m) dan nilai HSI Cakalang:
1. 🟢 **HIJAU (Risiko Rendah):** Sel di luar lereng benua (`depth < 150 m` atau `depth > 1200 m`) atau lereng benua dengan front termal tenang. Operasi penangkapan normal.
2. 🟡 **KUNING (Waspada Sedang):** Sel berada di lereng benua dengan front termal aktif (`grad_sst >= 0.035`), namun HSI tuna moderat (< 0.70). Syarat: Wajib *Circle Hooks*, hindari jaring malam.
3. 🔴 **MERAH (Risiko Tinggi / Super-Overlap):** Sel berada di lereng benua bersuhu dingin ($\le 26.5^\circ\text{C}$) dengan front tajam SEKALIGUS potensi tuna sangat tinggi (`hsi_cakalang >= 0.70`). Rekomendasi: *Dynamic Spatial Closure* (hindari penebaran rawai/rumpon sementara waktu demi kepatuhan IOTC).

---

### FR-03: Formula Estimasi Jarak & Konsumsi BBM Nelayan
- **Titik Referensi Titik Tolak (Homeport):** Muara PPN Prigi ($111.7275^\circ\text{ E}, -8.2833^\circ\text{ S}$).
- **Formula Jarak:** Vektor bola bumi *Haversine formula* dikonversi ke Mil Laut: $\text{Jarak (NM)} = \text{Haversine}(\text{lon}_1, \text{lat}_1, \text{lon}_2, \text{lat}_2) / 1.852$.
- **Kecepatan Jelajah Kapal:** Rata-rata 7.5 knot ($\text{Waktu Tempuh} = \text{Jarak} / 7.5\text{ Jam}$).
- **Konsumsi Solar Kapal Kecil (< 10 GT):** $3.5\text{ Liter/NM}$ (Pulang-Pergi). Formula: $\text{BBM}_{\text{kecil}} = \text{Jarak} \times 2 \times 3.5\text{ Liter}$.
- **Konsumsi Solar Purse Seine (20–30 GT):** $7.5\text{ Liter/NM}$ (Pulang-Pergi). Formula: $\text{BBM}_{\text{besar}} = \text{Jarak} \times 2 \times 7.5\text{ Liter}$.

---

## 7. Model Data Inti

### Tabel 1: `canonical_grids` (PostgreSQL 18 + PostGIS)
Menyimpan 8.129 poligon master kanonikal satu kali saja (indeks spasial GiST):
- `cell_id` (PK), `cell_class`, `center_lon`, `center_lat`, `depth_m`, `dist_port_km`, `dist_port_nm`, `is_shelf_break`, `sector`, `geom` (Polygon, SRID 4326).

### Tabel 2: `daily_predictions` (Partisi Harian)
- `prediction_date` (PK), `cell_id` (PK, FK), `hsi_cakalang`, `hsi_madidihang`, `hsi_albakor`, `is_hotspot`, `dom_zone`, `bycatch_traffic_light`, `sst_c`, `chla_mg_m3`, `wave_height_m`, `safety_advisory`.

### Berkas Feed Kinerja Tinggi: `daily_latest.json`
Snapshot siap saji berukuran 3.3 MB yang memuat 8.129 sel data harian aktif untuk rendering seketika di frontend MapLibre GL.

---

## 8. Jawaban Resmi atas Pertanyaan Terbuka PRD

1. **Ambang Batas HSI & Kriteria DOM:** Telah didefinisikan secara eksak pada Bagian 6 (FR-01).
2. **Kalkulasi Bycatch:** Dihitung dari perpotongan lereng batimetri 150–1.200 m, front termal, dan ambang batas `HSI >= 0.70` (Bagian 6, FR-02).
3. **Resolusi Grid & Waktu:** Grid kanonik $0.10^\circ \times 0.10^\circ$ ($\approx 11.1\text{ km}$); data historis bulanan 11 tahun (132 bulan); data operasional harian (pukul 04:00 WIB).
4. **Perhitungan BBM:** Menggunakan formula konsumsi kapal Prigi pulang-pergi (3.5 L/NM untuk <10 GT; 7.5 L/NM untuk 20–30 GT) dari titik muara Teluk Prigi.
5. **Titik Pangkalan Resmi:** PPN Prigi pada koordinat $111.7275^\circ\text{ E}, -8.2833^\circ\text{ S}$.
6. **Metrik Validasi Model:** Cakalang AUC 0.7552 (Good), Madidihang AUC 0.7019 (Fair), Albakor AUC 0.6779 (Fair); tervalidasi dengan korelasi pendaratan riil pelabuhan (168 bulan).
7. **Hosting & Jadwal Worker:** Worker harian dijalankan via cron sistem operasi pukul 04:00 WIB; server web Next.js 15 berjalan di port 3000.
8. **Lokasi Pilot & Stakeholder:** PPN Prigi (Trenggalek) bersama Syahbandar, TPI Prigi, Paguyuban Rukun Nelayan, HNSI, dan DKP Jatim.
9. **Lisensi & Atribusi Data:** Data CMEMS berlisensi Open Access EU Copernicus; data BMKG adalah layanan publik maritim RI; atribusi dicantumkan lengkap pada antarmuka web dan lembar cetak A4.

---

## 9. Rencana Kerja Lanjutan (Fase 7 — Validasi Lapangan & UAT)

Dengan selesainya seluruh Fase 1 hingga Fase 6 (aplikasi web sudah aktif dan siap digunakan), sistem memasuki **Fase 7**:
1. **Uji Validasi Peta Harian di PPN Prigi:** Menguji coba pencetakan lembar A4 harian di TPI Prigi selama 14 hari berturut-turut.
2. **Umpan Balik Nelayan Lapangan:** Wawancara nahkoda kapal Purse Seine dan perahu motor tempel terkait akurasi lokasi tuna dan kesesuaian estimasi solar.
3. **Penyempurnaan Integrasi API BMKG INA-OFS Live:** Memastikan sambungan API produksi BMKG stabil dengan token resmi institusi.

