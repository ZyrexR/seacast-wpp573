# PHASE 3.3 FLEET COMPOSITION & OPERATIONAL PROFILING REPORT
## SEACAST PRIGI V3 — Fishing Fleet Classification, GT Distribution & Spatial Ranges

**Tanggal:** 2026-11-04  
**Proyek:** SEACAST PRIGI V3  
**Sumber Data:** `prigi_fleet_by_gt` (PostgreSQL 17.11 + PostGIS 3.6.2)  
**Status:** **AUDITED, IMPLEMENTED & VERIFIED**  

---

### 1. Eksekutif Ringkasan Profil Armada PPN Prigi

Pelabuhan Perikanan Nusantara (PPN) Prigi yang beroperasi di WPPNRI 573 membawahkan armada penangkapan ikan pelagis yang secara resmi teregistrasi sebanyak **1.269 unit kapal nelayan**. Berdasarkan tabel `prigi_fleet_by_gt`, armada ini terklasifikasi ke dalam 3 kelompok tonase kotor (*Gross Tonnage* / GT) dengan kapasitas daya jangkau operasional dan keterkaitan zonasi dinamis (*Dynamic Ocean Management* / DOM) yang berbeda.

---

### 2. Distribusi Armada Berdasarkan Kelas GT

| Kelas Tonase | Jumlah Kapal (Unit) | Persentase Armada (%) | Jangkauan Operasional Aman | Rekomendasi Zonasi DOM | Karakteristik Operasional |
|---|:---:|:---:|:---:|---|---|
| **&lt; 10 GT** | **850** | **67,00%** | $\le 12$ Mil Laut (NM) | `Z1_PFG_INSHORE`, `Z4_NURSERY_GROUND` | Perahu motor tempel dan jukung tradisional. Beroperasi one-day fishing di perairan inshore Teluk Prigi. Sangat rentan terhadap gelombang tinggi. |
| **10 – 20 GT** | **165** | **13,00%** | $\le 30$ Mil Laut (NM) | `Z2_PFG_OFFSHORE`, `Z3_FEEDING_CORE` | Kapal purse seine mini dan pancing ulur motor dalam. Mampu menjangkau lereng benua (*shelf break*) dan koridor feeding ground tuna. |
| **20 – 30 GT** | **254** | **20,00%** | $\le 60$ Mil Laut (NM) | `Z2_PFG_OFFSHORE`, `Z3_FEEDING_CORE`, `Z5_HOTSPOT` | Kapal purse seine pelagis besar dan pancing gandar. Beroperasi multi-day trip hingga laut lepas Samudera Hindia WPP 573. |
| **TOTAL** | **1.269** | **100,00%** | — | — | **Total Registrasi PPN Prigi 2026** |

---

### 3. Batasan Representasi Data Agregasi

Sesuai ketentuan mandat ilmiah:
> *"Tiga baris agregasi tidak boleh dianggap sebagai 1.269 record kapal individual."*

Sistem SEACAST PRIGI V3 secara jujur memperlakukan tabel `prigi_fleet_by_gt` sebagai **tiga baris agregasi struktural** yang mencerminkan profil kelompok armada. Platform tidak mengklaim memiliki data pelacakan kapal individual perorangan (*individual vessel tracking*) karena belum adanya integrasi transponder AIS atau VMS pada seluruh perahu tradisional berukuran di bawah 10 GT.

