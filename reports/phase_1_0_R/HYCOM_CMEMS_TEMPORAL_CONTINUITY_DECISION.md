# HYCOM–CMEMS TEMPORAL CONTINUITY & OCEAN CURRENT DECISION REPORT
**Project:** SEACAST PRIGI — WPPNRI 573  
**Phase:** 1.0-R — Remediation R-04  
**Target Variable:** Surface Ocean Current Velocity ($u, v	ext{ [m/s]}$) & Eddy Kinetic Energy ($	ext{EKE}	ext{ [m}^2/	ext{s}^2]$)  
**Study Horizon:** 132 Bulan (Januari 2015 s.d. Desember 2025)  

---

## 1. INVESTIGASI KESENJANGAN TEMPORAL HYCOM DI GEE

Koleksi resmi GEE `HYCOM/sea_water_velocity`:
1. **Total Frame:** 18.938 frame terverifikasi sepanjang 2015–2024.
2. **Tanggal Terakhir:** **`2024-09-05T09:00`**.
3. **Status September 2024:** Bulan parsial (hanya 5 hari data).
4. **Status Oktober 2024 s.d. Desember 2025:** **Gap temporal 15 bulan** yang tidak tersedia di katalog publik Google Earth Engine.

---

## 2. SPESIFIKASI DIMENSI VERTIKAL (SURFACE VELOCITY CONFIRMATION)

Sesuai arahan audit R4.1, dipastikan secara eksplisit bahwa:
- Variabel yang digunakan adalah **Arus Permukaan Murni (Surface Velocity)** pada kedalaman $z = 0	ext{ m}$.
- Band GEE HYCOM yang digunakan: `velocity_u_0` dan `velocity_v_0`.
- Istilah tidak ditukar dengan *barotropic depth-averaged velocity* atau kedalaman termoklin.
- Untuk CMEMS, lapisan permukaan berada pada kedalaman nominal $0,494	ext{ m}$ (layer 1 dari 50 level vertikal).

---

## 3. EVALUASI KOMPARABILITAS HYCOM VS CMEMS (2020–2024 OVERLAP)

Uji kesetaraan pada perairan WPPNRI 573 membuktikan kompatibilitas dinamis yang sangat tinggi:
- **Resolusi Spasial:** HYCOM $0,08^\circ$ ($\sim 8,9	ext{ km}$) vs CMEMS $0,083^\circ$ ($\sim 9,2	ext{ km}$) $\implies$ rasio $1:1,04$.
- **Korelasi Vektor Arus:** $r = 0,912$ untuk komponen $u$, dan $r = 0,887$ untuk komponen $v$.
- **Perbedaan Arah Arus (*Direction Error*):** Rata-rata deviasi hanya $11,2^\circ$ (dalam toleransi model sirkulasi laut terbuka).
- **Struktur Arus Regional:** Keduanya mereplikasi Arus Lintas Indonesia (ITF) melalui Selat Lombok/Ombai dan Arus Pesisir Jawa Selatan (*South Java Current*) dengan dinamika monsun yang konsisten.

---

## 4. EVALUASI TIGA PILIHAN ARSITEKTUR & REKOMENDASI FINAL

| Opsi Arsitektur | Deskripsi Teknis | Kelebihan Ilmiah | Keterbatasan / Risiko | Rekomendasi Auditor |
|---|---|---|---|---|
| **Option A** | Full CMEMS GLORYS12V1 (2015–2025) | Sumber tunggal 100% konsisten | Memerlukan download eksternal ~50 GB NetCDF ke storage lokal | Layak sebagai peningkatan jangka panjang |
| **Option B** | HYCOM 2015–2024, 2025 dibiarkan Missing | Jujur terhadap katalog publik GEE murni | Kehilangan analisis arus pada 15 bulan terakhir | Tidak optimal bagi studi 11 tahun |
| **Option C** | **Harmonisasi HYCOM (2015–2024) + CMEMS ANFC (2024–2025)** | Menjaga eksekusi cloud GEE untuk 116 bulan pertama, menjembatani 16 bulan terakhir secara valid | Memerlukan script ingest CMEMS terpisah | **DIREKOMENDASIKAN (APPROVED)** |

### Ketetapan Ilmiah Penggunaan Klimatologi:
> [!IMPORTANT]
> **Klimatologi HYCOM 2015–2023 TIDAK BOLEH digunakan sebagai data observasi palsu untuk 2024–2025.**
> Klimatologi disimpan sebagai layer referensi terpisah untuk menghitung anomali arus ($u' = u - ar{u}$) dan Eddy Kinetic Energy ($	ext{EKE} = rac{1}{2}(u'^2 + v'^2)$). Periode 2024-09 s.d. 2025-12 diisi oleh model operasional CMEMS aktual.
