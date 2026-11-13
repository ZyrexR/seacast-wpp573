# PPN PRIGI LANDING DATA USAGE POLICY & SCIENTIFIC BOUNDARIES
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** POL-LAND-01  
**Phase:** 1.0-R — Remediation R-07  
**Date:** 2026-11-02  

---

## 1. NATURE & PROVENANCE OF PPN PRIGI LANDING DATA

Data pendaratan perikanan PPN Prigi berasal dari pencatatan statistik pelabuhan perikanan harian/bulanan yang mencatat:
- Volume pendaratan total ikan per kelompok/spesies (kg atau ton).
- Jumlah trip kapal yang mendarat berdasarkan jenis alat tangkap (*Purse Seine*, *Gill Net*, dsb.).
- Nilai produksi perikanan (Rupiah).

> [!CAUTION]
> **Batasan Spasial Kritis:**
> Data pendaratan pelabuhan dicatat di dermaga pendaratan (*port dock*). Data ini **TIDAK MEMILIKI ATRIBUT KOORDINAT LOKASI PENANGKAPAN (FISHING GROUND)**, lintang/bujur setting alat tangkap, atau rekaman *Vessel Monitoring System* (VMS) berbasis koordinat GPS per penarikan jaring.

---

## 2. PENGGUNAAN YANG DIIZINKAN (PERMITTED USE CASES)

Data pendaratan bulanan PPN Prigi **SAH SECARA ILMIAH DAN DIIZINKAN** untuk:
1. **Analisis Tren Temporal Regional:**
   - Menggambarkan fluktuasi musiman volume pendaratan Cakalang, Madidihang, dan Albakor (siklus musiman barat vs musiman timur).
   - Analisis tren produksi multi-tahun (2015–2025).
2. **Karakterisasi Komposisi Spesies:**
   - Menghitung persentase kontribusi masing-masing spesies pelagis terhadap total hasil tangkapan tahunan.
3. **Korelasi Makro Temporal Oseanografi:**
   - Melakukan analisis korelasi silang temporal (time-series cross-correlation) antara indeks oseanografi rata-rata regional WPPNRI 573 (misalnya IOD/DMI, ENSO/ONI, rata-rata upwelling index) dengan volume pendaratan bulanan di tingkat pelabuhan.
4. **Konteks Sosial-Ekonomi & Kebijakan:**
   - Memberikan gambaran dampak fluktuasi iklim terhadap ketahanan pangan dan ekonomi nelayan Prigi.

---

## 3. PENGGUNAAN YANG DILARANG KERAS (PROHIBITED USES)

Sesuai kaidah integritas riset geospasial dan ekologi spasial perikanan:

1. **DILARANG MENGGUNAKAN SEBAGAI LABEL SPASIAL PRESENSI SPESIES:**
   - Dilarang merekayasa titik koordinat acak (*random pseudo-coordinates*) di sekitar Prigi atau di laut selatan Jawa lalu memberinya label keberadaan ikan berdasarkan data pendaratan pelabuhan.
2. **DILARANG MELATIH MODEL DISTRIBUSI SPESIES (SDM / HABITAT SUITABILITY MODEL):**
   - Melatih algoritma *Maximum Entropy* (MaxEnt), *Random Forest*, atau *Gradient Boosting* untuk memprediksi keberadaan ikan (*species occurrence*) menggunakan data pendaratan pelabuhan tanpa koordinat riil adalah **TIDAK SAH SECARA METODOLOGI (*SCIENTIFICALLY INVALID*)**.
3. **DILARANG MENGKLAIM ZONA POTENSIAL PENANGKAPAN IKAN (ZPPI) SEBAGAI ZONA AKTUAL:**
   - Zona oseanografi potensial (berdasarkan pertemuan upwelling, thermal front, dan klorofil-a) hanya boleh dinamai **"Zona Oseanografi Potensial"** (*Oceanographic Potential Zones*), BUKAN *"Fishing Ground Aktual Nelayan"*.

---

## 4. PERSYARATAN MINIMUM UNTUK MEMBUKA MODEL HABITAT SPESIFIK SPESIES

Model kesesuaian habitat spesifik spesies (*Species-Specific Habitat Suitability Model*) hanya dapat diaktifkan jika data kejadian independen memenuhi kriteria:
1. Memiliki koordinat GPS ($x, y$) penarikan alat tangkap dengan ketidakpastian spasial $< 10	ext{ km}$.
2. Memiliki tanggal dan jam penangkapan ($t$) yang sinkron dengan data satelit harian/mingguan.
3. Identifikasi taksonomi spesies terverifikasi di atas kapal (*on-board observer / scientific logbook*).
4. Catatan upaya penangkapan (*Catch per Unit Effort / CPUE*) yang terstandardisasi.
