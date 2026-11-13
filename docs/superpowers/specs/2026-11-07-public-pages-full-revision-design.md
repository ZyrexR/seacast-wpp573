# Desain Spesifikasi: Revisi Menyeluruh 4 Halaman Publik SEACAST
**Tanggal:** 7 November 2026  
**Dokumen:** `docs/superpowers/specs/2026-11-07-public-pages-full-revision-design.md`  
**Status:** Menunggu Persetujuan Pengguna

---

## 1. Latar Belakang & Tujuan
Empat halaman portal publik utama di SEACAST saat ini masih bersifat terbatas (*stub*), usang, atau terlalu berorientasi lokal PPN Prigi semata:
1. `/science` (Sains & Model): Belum memuat spesies dominan **Tongkol** (*Auxis thazard*), masih memuat catatan audit mentah masa lalu (Legacy species removed from TCT scope zero-weight), dan belum merangkum kerangka Dynamic Ocean Management (DOM) 5-zona secara terpadu.
2. `/ocean-data` (Data Oseanografi): Hanya berupa 4 kartu statis ringkas tanpa spesifikasi mendalam satelit (*Copernicus, NASA, NOAA, JAXA, BMKG*) dan tanpa data telemetri live dari basis data PostGIS.
3. `/documentation` (Dokumentasi): Sangat minim (hanya 4 paragraf glosarium ringkas) dan belum memiliki panduan operasional multi-peran (Nelayan, Syahbandar, Peneliti) serta kepatuhan regulasi Permen KP No. 59/2020.
4. `/about` (Tentang SEACAST): Masih berjudul dan berfokus tunggal pada PPN Prigi, belum merepresentasikan visi konsorsium platform maritim multi-pelabuhan WPPNRI 573 (PPN Prigi, PPS Cilacap, PPN Palabuhanratu).

---

## 2. Arsitektur & Konten per Halaman

### A. Halaman `/about` (Tentang SEACAST — Fokus Penuh Penjelasan Aplikasi SEACAST)
- **Controller:** `PublicPageController::about()`
  - Dibersihkan dari ketergantungan pada model lokal lama (`PrigiFleetByGt`, `PrigiPortLanding`).
  - Menghapus seluruh narasi sejarah lokal PPN Prigi dan tabel GT tunggal Prigi.
- **Komponen & Struktur Halaman:**
  1. *Hero Identity Aplikasi:*
     - Judul: **Tentang SEACAST**
     - Subjudul: *Scientific Ocean Intelligence & Fisheries Decision Support Platform*
     - Definisi aplikasi: Platform intelijen maritim cerdas yang mengintegrasikan data penginderaan jauh satelit, kecerdasan buatan pemodelan habitat perikanan (SDM Ensemble), dan analisis geospasial presisi untuk efisiensi bahan bakar nelayan, keselamatan berlayar, dan kelestarian laut.
  2. *Masalah yang Diselesaikan (Problem & Value Proposition):*
     - Eliminasi melaut spekulatif $\to$ Penghematan konsumsi BBM hingga 20-30%.
     - Perlindungan keselamatan jiwa di laut $\to$ Peringatan otomatis gelombang dan anomali cuaca.
     - Kepatuhan batas zonasi $\to$ Perlindungan zona asuhan (*nursery ground*) dan mitigasi tangkapan sampingan (*bycatch*).
  3. *5 Fitur & Modul Utama Aplikasi:*
     - **DPI Radar Geospasial Spasial:** Web-GIS interaktif pemetaan kontur hotspot ikan harian.
     - **Trip Planner & Kalkulator Melaut Presisi:** Estimasi jarak (NM), konsumsi BBM, durasi tempuh, dan proyeksi hasil tangkapan.
     - **Penerbitan Sailing Advisory PDF Otomatis:** Dokumen rekomendasi operasional satu lembar terstandardisasi untuk nahkoda dan syahbandar.
     - **Dasbor Analitik Armada Multi-Pelabuhan:** Pemantauan tren produksi dan produktivitas tangkapan (CPUE).
     - **Dynamic Ocean Management (DOM):** Zonasi dinamis 5-tingkat yang beradaptasi dengan kondisi fisik laut.
  4. *Alur Kerja Aplikasi (End-to-End System Workflow):*
     - Sensor Satelit EO $\to$ Google Earth Engine Ingestion $\to$ PostGIS Spatial Engine $\to$ Ensemble AI Scoring $\to$ Web & Mobile UI Interface.
  5. *Untuk Siapa Aplikasi Ini Dibangun (Target Pengguna):*
     - Nelayan & Nahkoda Kapal Perikanan.
     - Syahbandar & Otoritas Pelabuhan Perikanan.
     - Peneliti, Akademisi, & Pengambil Kebijakan Kelautan.
  6. *Fondasi Arsitektur Teknologi:*
     - Laravel 13, PostGIS 17, Alpine.js, Tailwind CSS v4, dan Python Machine Learning Engine.

---

### B. Halaman `/science` (Sains, Metodologi Ensemble SDM & DOM)
- **Controller:** `PublicPageController::science()`
- **Komponen Halaman:**
  1. *Teori Relung Ekologi Hutchinson Multidimensi:*
     - Diferensiasi habitat 3 spesies utama:
       - **Tongkol (*Auxis thazard* / *Euthynnus affinis*):** Zona pesisir neritik ($0-12$ NM), kedalaman dangkal-sedang, ketergantungan pada kelimpahan klorofil-a pesisir.
       - **Cakalang (*Katsuwonus pelamis*):** Zona perantara hingga tubir benua ($12-50$ NM), menyukai batas termal upwelling dingin ($24-28^\circ\text{C}$).
       - **Madidihang (*Thunnus albacares*):** Zona oseanik laut dalam ($>50$ NM), termoklin dalam, struktur pusaran arus (*eddy*).
  2. *Formulasi Matematis Habitat Suitability Index (HSI) Komposit:*
     $$\text{HSI}_{s} = w_{\text{maxent}} \cdot P_{\text{maxent}} + w_{\text{rf}} \cdot P_{\text{rf}} + w_{\text{glm}} \cdot P_{\text{glm}}$$
     - Penjelasan normalisasi probabilitas $[0,000 - 1,000]$ dan optimasi bobot ensemble multi-algoritma.
  3. *Tabel Audit Evaluasi Model (AUC, TSS & F1-Score):*
     - Menampilkan performa kuantitatif ensemble untuk ketiga spesies target utama.
  4. *Kerangka Kerja Dynamic Ocean Management (DOM) 5-Zona:*
     - **Z1 (Inshore PFG):** Jalur penangkapan ikan pesisir ramah armada $<10$ GT.
     - **Z2 (Offshore PFG):** Koridor laut lepas tuna armada $10-30$ GT & $>30$ GT.
     - **Z3 (Feeding Core):** Episentrum produktivitas primer dan konsentrasi pakan alami.
     - **Z4 (Nursery Ground):** Daerah asuhan bibit pelagis yang memerlukan perlindungan selektif.
     - **Z5 (High Bycatch Risk):** Zona risiko tinggi tangkapan sampingan (penyu, mamalia laut, hiu) dengan anjuran mitigasi ketat.

---

### C. Halaman `/ocean-data` (Katalog Data Satelit & Telemetri Oseanografi)
- **Controller:** `PublicPageController::oceanData()`
  - Mengambil data telemetri rata-rata aktual terkini (SST, Chl-a, tinggi gelombang rata-rata) langsung dari database untuk ditampilkan sebagai widget status real-time.
- **Komponen Halaman:**
  1. *Live Telemetry Snapshot Bar:* Rata-rata suhu permukaan laut, biomassa klorofil-a, tinggi gelombang WPP 573, dan total sel pantauan ($8.129$).
  2. *Matriks Spesifikasi Variabel Lingkungan & Sensor Satelit:*
     - **Suhu Permukaan Laut (SST):** Sensor NOAA OISST v2.1 & MODIS Terra/Aqua, resolusi grid kanonik $0,10^\circ$, pembaruan harian.
     - **Klorofil-a (Chl-a):** Sensor Copernicus Sentinel-3 OLCI & VIIRS, rentang $0,05 - 5,0\text{ mg/m}^3$.
     - **Arus Permukaan & Transport Geostropik:** Altimetri CMEMS & HYCOM Global $1/12^\circ$.
     - **Prakiraan Gelombang & Angin Maritim:** Model Ina-WAVES BMKG & NOAA GFS.
     - **Salinitas & Kedalaman Lapisan Campuran (MLD):** Profiler CMEMS Global Ocean Physics.
  3. *Arsitektur Pengolahan Data Spasial:*
     - Penjelasan integrasi Google Earth Engine (GEE API) dan PostGIS 17 kanonikal berproyeksi EPSG:4326.
  4. *Akses & Format Pertukaran Data:* Format GeoJSON, CSV Tabular, dan NetCDF.

---

### D. Halaman `/documentation` (Glosarium & Panduan Teknis Multi-Peran)
- **Controller:** `PublicPageController::documentation()`
- **Komponen Halaman:**
  1. *Panduan Berdasarkan Peran Pengguna (*Multi-Role Workflows*):*
     - **Bagi Nelayan & Nahkoda:** Cara membaca koordinat hotspot, merencanakan rute hemat bahan bakar, memahami indikator gelombang keselamatan melaut, dan mencetak *Sailing Advisory Slip*.
     - **Bagi Syahbandar & Otoritas Pelabuhan:** Validasi kelaikan berlayar (SPB) berdasarkan batas keselamatan BMKG ($\ge 2,0\text{ m}$ waspada, $\ge 3,5\text{ m}$ bahaya) dan kepatuhan jalur Permen KP 59/2020.
     - **Bagi Peneliti & Akademisi:** Memahami nilai HSI, batas ambang klasifikasi sel hotspot ($\ge 0,70$), dan analisis CPUE multi-tahunan.
  2. *Kepatuhan Zonasi Permen KP No. 59/PERMEN-KP/2020:*
     - Batas Jalur I ($0 - 4$ NM), Jalur II ($4 - 12$ NM), dan Jalur III ($> 12$ NM / ZEEI) diselaraskan dengan kelas kapal (GT).
  3. *Glosarium Oseanografi & Istilah Teknis Lengkap:*
     - Penjelasan istilah: HSI, Upwelling, Front Termal, CPUE Nominal, DOM, Thermocline, Ekman Transport, Eddy Cyclonic/Anticyclonic.
  4. *FAQ & Pertanyaan Sering Diajukan:* Tanya jawab umum seputar platform.

---

## 3. Rencana Pengujian & Verifikasi
- Pengujian unit & fitur via PHPUnit:
  - `PublicPagesContentTest`: Memastikan seluruh tautan, konten 3 pelabuhan, 3 spesies target (Tongkol, Cakalang, Madidihang), 5 zona DOM, serta telemetri live ter-render dengan status 200 OK tanpa error.
  - Memastikan 100% tes lama tetap lulus.

