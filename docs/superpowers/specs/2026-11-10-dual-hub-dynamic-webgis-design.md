# Design Document: Dual-Hub Dynamic WebGIS & Spatial Engine (SEACAST WPPNRI 573)

**Date:** 2026-11-10  
**Status:** Draft for Approval  
**Target Milestone:** Interactive Demo & Defense Readiness (18 November 2026)  
**Author / Collaborators:** Muhammad Habibi & Antigravity  

---

## 1. Intent and The Why

Pengguna mengalami kelelahan mental (*burnout*) dan kecemasan karena merasa sistem saat ini masih terasa kaku (*statis*), model hanya mengandalkan data PPN Prigi, dan Google Earth Engine pipeline belum berjalan mandiri, sementara data riil yang dimiliki mencakup dua pelabuhan utama di WPPNRI 573 (PPN Prigi di Jawa Timur dan PPS Cilacap di Jawa Tengah).

Tujuan desain ini adalah mengubah platform SEACAST dari sistem yang kaku menjadi **WebGIS interaktif, hidup, dan dinamis**, sekaligus menyelaraskan pemanfaatan data PPN Prigi dan PPS Cilacap sebagai pembuktian ilmiah transferabilitas spasial (*Dual-Hub Spatial Transferability*) di WPPNRI 573 tanpa membebani pengguna dengan perombakan arsitektur yang berisiko tinggi.

---

## 2. Goals, Non-Goals, and Anti-Goals

### Goals
1. **Peta WebGIS 'Hidup' (Dynamic Ocean Currents):** Mengintegrasikan layer partikel animasi arus laut (*streamlines / moving particles*) di atas peta Leaflet berbasis komponen vektor kecepatan laut ($u, v$), sehingga peta tidak lagi berupa poligon mati.
2. **Interactive Seasonal Time-Slider:** Menyediakan slider kontrol waktu dengan tombol *Play/Pause/Scrub* untuk memvisualisasikan dinamika musiman (Musim Timur upwelling vs Musim Barat) dan pergeseran Habitat Suitability Index (HSI).
3. **Dual-Hub Corridor Toggle:** Menyediakan tombol alih cepat di peta antara **Koridor PPN Prigi** (Jawa Timur) dan **Koridor PPS Cilacap** (Jawa Tengah), lengkap dengan kamera otomatis (*fly-to*), lingkaran jangkauan mil laut (12 NM, 30 NM, 60 NM), dan penyesuaian titik pangkalan nelayan.
4. **Dual-Hub Spatial Analytics Integration:** Menghubungkan dataset empiris 10 tahun (2016–2025) PPS Cilacap berdampingan dengan PPN Prigi di dasbor analitik armada untuk menunjukkan komparasi tangkapan dan validasi model spasial di WPPNRI 573.
5. **Zero-Stress Developer Experience:** Seluruh proses implementasi teknis dieksekusi secara otomatis oleh asisten coding tanpa menuntut pengguna melakukan *coding* manual.

### Non-Goals
1. **Tidak merombak model Machine Learning dari nol:** Tidak melakukan retraining ulang arsitektur model `.joblib` dalam waktu 8 hari ini; menggunakan bobot model yang sudah terbukti stabil.
2. **Tidak memaksakan cloud-native cron job live 24/7 sebelum pendanaan:** Automasi GEE cron job berbasis Google Cloud Service Account di server produksi tetap dialokasikan untuk fase pelaksanaan hibah PKM (Desember 2026 – April 2027).
3. **Tidak mengubah naskah proposal PKM-RE:** Naskah proposal tetap utuh sesuai arahan pengguna.

### Anti-Goals
1. **Anti-Lag:** Menghindari rendering berat yang membuat browser *freeze* atau *stuttering* saat memuat 8.129 sel poligon PostGIS. Animasi partikel arus harus berjalan ringan menggunakan HTML5 Canvas 60 FPS.
2. **Anti-Complexity:** Antarmuka tidak boleh membingungkan nelayan atau reviewer; kontrol harus intuitif, bersih, dan langsung terlihat fungsinya.

---

## 3. Constraints & Target Infrastructure
* **Local Development & Demo Environment:** Windows 11 (ThinkPad), PHP 8.3, Python 3.14, PostgreSQL 17 / PostGIS 3.6 (dengan fail-safe cached fallback).
* **Production Deployment Infrastructure (Locked):**
  * **Provider:** Hostinger VPS (KVM, Ubuntu 22.04/24.04 LTS, Dedicated IP, Full Root SSH).
  * **Control Panel:** CloudPanel 2.x (NGINX + PHP 8.3-FPM + Let's Encrypt SSL).
  * **Database:** Native Linux PostgreSQL 16/17 + PostGIS 3.4/3.6 (seacast_prigi).
  * **Python Environment:** Isolated Linux `venv` (`joblib`, `scikit-learn`, `earthengine-api`, `psycopg2-binary`).
  * **GEE Server Credential:** Stored at `/home/seacast/.config/earthengine/credentials` (Project `seca-513505`).
  * **Automated Cron Scheduler:** CloudPanel Cron Service running `php artisan schedule:run` every minute (Automated Daily 04:00 WIB Sync).
  * **Panduan Deployment:** Tersimpan lengkap di `docs/deployment/HOSTINGER_CLOUDPANEL_DEPLOYMENT_GUIDE.md`.
* **Waktu:** Seluruh pembaruan harus siap diuji coba dan didemokan secara lokal sebelum 18 November 2026.
* **Performa Browser:** Animasi arus dan partikel harus berjalan lancar pada perangkat laptop standar tanpa memerlukan GPU diskrit kelas tinggi.

---

## 4. Approaches Considered

| Pendekatan | Deskripsi | Kelebihan | Kekurangan | Status |
| :--- | :--- | :--- | :--- | :---: |
| **Pendekatan 1: High-Impact Integrated WebGIS & Dual-Hub Spatial Engine** | Menambahkan layer partikel arus HTML5 canvas, time-slider musiman, dual-hub toggle Prigi-Cilacap, dan analitik komparatif pada sistem yang ada. | Dampak visual dan interaktivitas luar biasa, data Cilacap termanfaatkan maksimal, aman tanpa risiko bug arsitektur. | Memerlukan integrasi library partikel canvas yang ringan di Leaflet. | **DIPILIH** |
| **Pendekatan 2: Full Model Retraining & Vector Tile Engine** | Melatih ulang ketiga model SDM dari awal dengan data gabungan Prigi-Cilacap dan migrasi Leaflet ke MapLibre GL 3D. | Sangat komprehensif dari sisi model. | Risiko tinggi komputasi gagal/overfitting, waktu tidak cukup sebelum 18 Nov. | Ditolak |
| **Pendekatan 3: Frontend Skinning Ringan Saja** | Hanya merapikan CSS tombol dan warna poligon tanpa animasi arus atau time-slider. | Sangat cepat selesai. | Gagal mengatasi masalah utama; peta tetap terasa statis, kaku, dan membosankan. | Ditolak |

---

## 5. Architectural & Technical Decisions

### 5.1. Komponen WebGIS Interaktif (`dpi-radar.blade.php`)
1. **Canvas Ocean Velocity Particle Layer:**
   * Memanfaatkan HTML5 Canvas transparan di atas peta Leaflet.
   * Menghasilkan partikel yang bergerak mengikuti magnitudo dan arah arus permukaan laut ($u = \text{arus zonal}$, $v = \text{arus meridional}$) di perairan selatan Jawa (WPPNRI 573).
   * Dilengkapi switch *toggle*: "Tampilkan Aliran Arus (ON/OFF)" dan slider intensitas kecepatan.
2. **Seasonal Time-Slider Playback (Data-Driven Pre-Aggregated):**
   * Menggunakan dataset klimatologi empiris yang sudah dihitung di repositori:
     * `data/oceanographic/processed/seasonal/grid_musim_barat_climatology.json` (Musim Barat / DJF)
     * `data/oceanographic/processed/seasonal/grid_musim_timur_climatology.json` (Musim Timur / JJA Upwelling Peak)
     * `data/oceanographic/processed/seasonal/grid_extreme_2019_super_iod.json` (Anomali Upwelling Ekstrem IOD+ 2019)
     * `data/oceanographic/processed/seasonal/grid_extreme_2016_negative_iod.json` (Anomali Hangat IOD- 2016)
   * Menyediakan slider 12 bulan + skenario ekstrem dengan pre-load lookup table JSON (~680 KB) sehingga transisi antar-langkah waktu berjalan instan (<50ms) dan bebas lag tanpa mengunduh ulang GeoJSON 8.129 sel.
   * Tombol *Play*: Secara otomatis memutar pergeseran bulan demi bulan dengan interval transisi 1,5 detik.
3. **Dual-Hub Corridor Switcher & Dynamic Distance Recalculation:**
   * Di panel navigasi atas peta radar, terdapat selektor pangkalan:
     * `[ PPN Prigi (Jatim) ]` $\to$ Peta bergerak (*flyTo*) ke koordinat 111.73°E, -8.28°S, memunculkan cincin jangkauan 12 & 30 NM dari Prigi.
     * `[ PPS Cilacap (Jateng) ]` $\to$ Peta bergerak (*flyTo*) ke koordinat 109.01°E, -7.75°S, memunculkan cincin jangkauan 12, 30, & 60 NM dari Cilacap.
   * **Dynamic Port Distance Binding (Client-Side Haversine):** Saat beralih pangkalan, jarak sel ke pelabuhan (`dist_port_nm`) dihitung ulang secara dinamis di browser terhadap pelabuhan yang sedang aktif. Nelayan/penguji yang mengklik sel di depan perairan Cilacap akan melihat jarak realistis dari Cilacap (misal 15 NM), bukan jarak 250 NM dari Prigi, sehingga kredibilitas ilmiah terjaga penuh. Model bobot inferensi ML tetap terjaga utuh.

### 5.2. Komparasi Data & Validasi Spasial (`fleet-analytics.blade.php`)
* Dasbor analitik memuat tab komparasi langsung:
  * **PPN Prigi:** Profil pendaratan 132 bulan (didominasi armada purse seine, cakalang, layang, tongkol).
  * **PPS Cilacap:** Profil pendaratan 120 bulan (didominasi armada pancing tonda, rawai tuna/longline, madidihang, cakalang).
* Memberikan bukti empiris bahwa model WPPNRI 573 mampu menangkap pola agregasi pelagis baik di wilayah timur (upwelling pesisir Prigi) maupun wilayah barat (jalur jelajah tuna lepas pantai Cilacap).

### 5.3. Live BMKG Maritim Integration (`https://maritim.bmkg.go.id/marine2026-data/`)
* **Live Public Endpoints Terverifikasi:**
  * Pelabuhan PPN Prigi: `/pelabuhan/XO026.json` (Wave: ~1.0m, Wind, Current, Tides)
  * Pelabuhan PPS Cilacap: `/pelabuhan/XM001.json` (Wave, Wind, Current, Tides)
  * Perairan Samudra Hindia Selatan Jatim (WPP 573 Timur): `/perairan/M.12.json`
  * Perairan Samudra Hindia Selatan Jateng (WPP 573 Barat): `/perairan/M.10.json`
* **Arsitektur Service (`BmkgMaritimService.php`):**
  * HTTP Client mengambil prakiraan 3 harian per jam dari endpoint di atas.
  * Dilengkapi mekanisme *Laravel Cache* (TTL 60 menit) dan *graceful fallback* agar aplikasi tetap berjalan lancar jika jaringan terputus.
* **Integrasi Tampilan UI:**
  * **Radar Live Pill:** Badge dinamis di header peta radar menampilkan cuaca maritim live BMKG (Tinggi Gelombang, Kecepatan Angin, Status Aman/Waspada).
  * **Dynamic Context Switch:** Ketika pengguna memilih pangkalan Prigi atau Cilacap, data cuaca maritim BMKG otomatis menyesuaikan ke pelabuhan dan perairan yang bersangkutan.

### 5.4. Unified Live Pipeline Orchestrator (`seacast:sync-live`)
* **Pilar Utama (Otomatis, Maksimal, Realtime, Zero-Dummy):**
  1. **Deteksi Tanggal Satelit Dinamis (Tanpa Hardcode):** Skrip Python GEE secara otomatis memeriksa tanggal citra NOAA OISST paling mutakhir di orbit pada saat dijalankan (`oisst.sort('system:time_start', False).first()`).
  2. **Komposit Klorofil-a Bebas Awan:** Mengambil jendela bergerak 8 hari terakhir dari tanggal target menggunakan median reducer untuk mengatasi tutupan awan monsun di selatan Jawa.
  3. **Zero-Dummy Ingestion:** 100% parameter oseanografi berasal dari ekstraksi citra satelit asli (NOAA, Copernicus, GEBCO, CFSv2) untuk 8.129 sel WPP 573.
  4. **Live Hourly BMKG Ingestion:** Mengambil prakiraan per jam real-time dari stasiun maritim BMKG (Prigi, Cilacap, Samudra Hindia) dan mengaitkannya ke dasbor.
  5. **Perintah Tunggal (One-Command Sync):**
     ```bash
     php artisan seacast:sync-live
     ```
     Menjalankan ekstraksi satelit terbaru $\to$ mengalirkan data lingkungan asli ke model ensemble (MaxEnt, RF, GLM) $\to$ meng-upsert hasil ke PostgreSQL $\to$ memperbarui cache WebGIS secara otomatis tanpa campur tangan manual.

---

## 6. What's Left to The Builder (Rencana Eksekusi)

1. **Step 1:** Menambahkan generator atribut vektor arus ($u, v$) pada pipeline inferensi harian dan GeoJSON response.
2. **Step 2:** Membangun *Canvas Flow Streamline Layer* di `dpi-radar.blade.php` (animasi arus bergerak 60 FPS).
3. **Step 3:** Membangun *Interactive Time-Slider Playback* di antarmuka peta radar.
4. **Step 4:** Menambahkan *Dual-Hub Quick Toggle & Range Rings* untuk PPN Prigi dan PPS Cilacap.
5. **Step 5:** Pengujian lokal end-to-end, memastikan performa ringan, mulus, dan tidak terjadi kendala saat presentasi.
