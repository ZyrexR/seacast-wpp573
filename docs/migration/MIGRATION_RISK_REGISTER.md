# SEACAST PRIGI V3 — MIGRATION RISK REGISTER
**Audit Date:** 4 November 2026  
**Auditor:** QA & Software Reliability Engineer  

---

## 1. MATRIKS RISIKO & MITIGASI MIGRASI

| ID | Kategori Risiko | Deskripsi Risiko | Dampak | Probabilitas | Tingkat Risiko | Strategi Mitigasi Proaktif |
| :---: | :--- | :--- | :---: | :---: | :---: | :--- |
| **R-01** | **Data Integrity** | Perintah migrasi Laravel yang destruktif (`migrate:fresh`, `db:wipe`) menghapus 8.129 sel PostGIS atau arsip 132 bulan pendaratan. | **FATAL** | Rendah | **KRITIS** | Koneksi database dikonfigurasi **read-only** pada awal fase. Database diexport/dump ke file backup `.sql` terkompresi sebelum migrasi. Larangan keras perintah destruktif di environment. |
| **R-02** | **Scientific Integrity** | Perbedaan pembulatan floating point antara JavaScript dan PHP menyebabkan pergeseran klasifikasi zona DOM atau threshold hotspot HSI $\ge 0,70$. | **TINGGI** | Sedang | **TINGGI** | Menulis unit test dengan toleransi numerik ketat ($10^{-5}$) berbasis snapshot JSON input-output yang sama antara Python, Node.js, dan PHP. |
| **R-03** | **Spatial Integrity** | Serialisasi GeoJSON di PHP merusak format multipoligon atau urutan koordinat poligon kanonik (SRID 4326). | **TINGGI** | Rendah | **SEDANG** | Memanfaatkan fungsi PostGIS langsung (`ST_AsGeoJSON`) via PostgreSQL engine daripada parsing poligon manual di PHP. |
| **R-04** | **Performance** | Serialisasi GeoJSON 8.129 sel (~5.8 MB) di PHP menyebabkan memory exhaustion atau latensi respons $>1.000\text{ ms}$. | **SEDANG** | Sedang | **SEDANG** | Menerapkan parameter `summary_only=true` untuk dashboard (mereduksi payload ke ~1 KB), mengaktifkan HTTP Caching Header (`max-age=300`), dan menggunakan gzip/brotli compression pada web server. |
| **R-05** | **Security** | Mock autentikasi lama berbasis `localStorage` dipindahkan apa adanya sehingga route dashboard tidak terlindungi secara aman. | **TINGGI** | Sedang | **TINGGI** | Refaktorisasi total pada Phase 7: Menggunakan Laravel native session authentication, CSRF tokens, dan role-based authorization middleware (`EnsureRole`). |
| **R-06** | **Subprocess Execution** | Eksekusi `scripts/daily_inference.py` via Laravel Process hang atau gagal karena dependensi library Python hilang. | **TINGGI** | Rendah | **SEDANG** | Mendefinisikan fixed timeout (180 detik), exit-code verification, isolasi venv Python terdaftar di `.env`, dan fallback automated alerts via email/log. |
| **R-07** | **Frontend Parity** | Interaktivitas peta WebGL MapLibre GL JS terganggu saat dipindahkan dari React hooks (`useEffect`) ke Alpine.js. | **SEDANG** | Sedang | **SEDANG** | Membuat arsitektur Alpine.js wrapper yang teruji (`x-data="dpiRadarMap"`) dengan siklus hidup `x-init` dan `destroy` yang bersih. |
| **R-08** | **Environment Readiness** | PHP 8.3+ dan Composer belum terdaftar pada system PATH Windows host. | **TINGGI** | Tinggi | **TINGGI** | Memanfaatkan manajer paket Windows yang terbukti tersedia (`winget` atau `choco`) untuk menginstal PHP 8.3 dan Composer secara otomatis pada Phase 1. |

---

## 2. PROSEDUR ROLLBACK & EMERGENCY PROTOCOL

Jika terjadi kendala fatal selama masa transisi migrasi:
1. **Traffic Reversion:** Traffic Nginx / reverse proxy dialihkan kembali secara instan ke port Next.js (port 3000) yang tetap berjalan sebagai backup standby.
2. **Database Protection:** Karena database PostGIS tidak pernah di-wipe atau dimodifikasi skemanya, integritas data tetap 100% utuh tanpa perlu restore berulang.
3. **Audit Trail:** Seluruh pesan error dicatat pada `storage/logs/laravel.log` untuk diagnosis forensik.

