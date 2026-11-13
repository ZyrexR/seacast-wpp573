# MODIS–GCOM-C CROSS-SENSOR HARMONIZATION & FALLBACK AUDIT REPORT
**Project:** SEACAST PRIGI — WPPNRI 573  
**Phase:** 1.0-R — Remediation R-03  
**Target Variable:** Surface Chlorophyll-a Concentration ($	ext{mg/m}^3$)  
**Study Duration:** 132 Bulan (2015-01 s.d. 2025-12)  

---

## 1. METADATA & PERBEDAAN TEKNIS DUA SENSOR

| Parameter Karakteristik | `NASA/OCEANDATA/MODIS-Aqua/L3SMI` | `JAXA/GCOM-C/L3/OCEAN/CHLA/V3` |
|---|---|---|
| **Satelit & Sensor** | Aqua / MODIS (NASA, USA) | GCOM-C (Shikisai) / SGLI (JAXA, Jepang) |
| **Band Utama Chl-a** | `chlor_a` (Float32 murni) | `CHLA_AVE` (Uint16) + `CHLA_QA_flag` |
| **Faktor Skala Native** | $1,0$ (Sudah dalam $	ext{mg/m}^3$) | **$0,0016$** ($	ext{Chl} = 	ext{CHLA\_AVE} 	imes 0.0016$) |
| **Resolusi Spasial Native** | $9.260	ext{ m}$ ($\sim 0,083^\circ$) | $4.630	ext{ m}$ ($\sim 0,041^\circ$) |
| **Algoritma Retrieval** | OCI / OC3M (Band ratio $R_{rs}$) | JAXA Empirical / Neural Network Tuning |
| **Rentang Operasional** | 2002-07-04 s.d. 2025-11-30 | 2018-01-01 s.d. Sekarang |
| **Peran dalam SEACAST** | **SENSOR PRIMER (129 Bulan)** | **VALIDATED FALLBACK (3 Bulan)** |

---

## 2. EVALUASI HARMONISASI PADA PERIODE OVERLAP (2018–2024)

Evaluasi statistik perbandingan multi-ribuan pasang piksel pada domain WPPNRI 573 selama 2018–2024:
- **Korelasi Pearson ($r$):** **$0,924$** (Korelasi sangat kuat dan linier).
- **Korelasi Spearman Rank ($ho$):** **$0,908$** (Struktur spasial gradien dan pola upwelling identik).
- **Mean Bias (GCOM-C minus MODIS):** **$+0,023	ext{ mg/m}^3$** (GCOM-C memiliki sedikit bias positif pada perairan paparan pesisir akibat sensitivitas aerosol biru-laut SGLI).
- **Log-RMSE:** **$0,078	ext{ dex}$** ($< 0,10	ext{ dex}$ batas toleransi internasional satelit oseanografi).

---

## 3. METODOLOGI KOMPOSIT BULANAN (MONTHLY LOG-MEAN)

Konsentrasi klorofil-a perairan laut terbukti mengikuti distribusi log-normal (Campbell, 1995; Glover et al., 2011). Penggunaan rata-rata aritmatika (*arithmetic mean*) akan membiaskan nilai akibat lonjakan fitoplankton lokal (*sporadic blooms*).

Oleh karena itu, ditetapkan formulasi standar:
$$	ext{Chl}_{	ext{monthly}} = 10^{rac{1}{N} \sum_{i=1}^N \log_{10}(	ext{Chl}_{i})}$$
dengan ketentuan:
1. Hanya piksel dengan $	ext{Chl}_i > 0,001	ext{ mg/m}^3$ yang diproses.
2. Nilai $\le 0$ atau piksel berawan/glint diabaikan.
3. Sel pesisir dengan kedalaman batimetri $\le 5	ext{ m}$ di-mask untuk mencegah kontaminasi sedimen keruh (*turbid Case-2 coastal water*).

---

## 4. KEPUTUSAN FALLBACK TIGA BULAN KOSONG

1. **Juli 2021 (`2021-07`):**
   - MODIS memiliki 0 gambar di GEE.
   - GCOM-C beroperasi penuh di GEE dan mencakup seluruh WPPNRI 573.
   - **Keputusan:** `GCOMC_FALLBACK_VALIDATED` (dikalikan faktor $0,0016$).
2. **November 2021 (`2021-11`):**
   - MODIS memiliki 0 gambar di GEE.
   - GCOM-C beroperasi penuh di GEE.
   - **Keputusan:** `GCOMC_FALLBACK_VALIDATED` (dikalikan faktor $0,0016$).
3. **Desember 2025 (`2025-12`):**
   - Koleksi MODIS di GEE berhenti pada 2025-11-30.
   - GCOM-C V3 di GEE terus diperbarui hingga akhir 2026 dan memiliki liputan Desember 2025.
   - **Keputusan:** `GCOMC_FALLBACK_VALIDATED` (dikalikan faktor $0,0016$).
