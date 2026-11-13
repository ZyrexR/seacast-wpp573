# CFSv2 OUTLIER FORENSIC INVESTIGATION, TEMPORAL COMPLETENESS & REMEDIATION REPORT
**Project:** SEACAST PRIGI — WPPNRI 573  
**Phase:** 1.0-R — Remediation R-02  
**Dataset Under Audit:** `NOAA/CFSV2/FOR6H_HARMONIZED`  
**Study Period:** 132 Bulan (2015-01-01 s.d. 2025-12-31 | 4.018 Hari Kalender)  

---

## 1. INVESTIGASI FORENSIK OUTLIER EKSTREM (>400 m/s)

### 1.1 Temuan Audit Awal
Pada audit Phase 0.4, pengecekan nilai maksimum global atas AOI WPPNRI 573 mendeteksi nilai ekstrim tak masuk akal:
- $U_{max} = 465.26	ext{ m/s}$ (~1.675 km/jam)
- $V_{max} = 458.27	ext{ m/s}$ (~1.650 km/jam)

### 1.2 Lokalisasi Waktu & Ruang Outlier
Pengujian tahunan server-side pada Google Earth Engine membuktikan fakta ilmiah penting:
1. **Tahun 2015 s.d. 2024 (10 Tahun Penuh / 120 Bulan):**
   - Nilai $U_{max}$ tahunan berada pada rentang **$15.36	ext{ m/s}$ s.d. $24.72	ext{ m/s}$**.
   - Nilai $V_{max}$ tahunan berada pada rentang **$10.06	ext{ m/s}$ s.d. $21.13	ext{ m/s}$**.
   - **TIDAK DITEMUKAN SATU PUN OUTLIER DI ATAS 30 m/s** selama 10 tahun tersebut. Seluruh data 2015–2024 valid secara meteorologi.
2. **Tahun 2025 (Bulan Oktober s.d. Desember 2025):**
   - Outlier ekstrem terisolasi secara eksklusif pada **21 frame di akhir tahun 2025** (Oktober 1 frame, November 10 frame, Desember 10 frame).
   - Sebanyak 9 bulan pertama tahun 2025 (Januari s.d. September 2025) memiliki nilai normal ($U_{max} \le 19.35	ext{ m/s}$, $V_{max} \le 19.44	ext{ m/s}$).

### 1.3 Pembuktian Fisik Ketidakabsahan Nilai (Physical Disproof)
Pemeriksaan runtun waktu pada titik koordinat outlier `[115.396° BT, -10.212° LS]` membuktikan ketidakabsahan fisis:
- `2025-11-03 18:00 UTC`: $U = -2.22	ext{ m/s}$, $V = +0.45	ext{ m/s}$ (Angin tenang normal)
- `2025-11-04 00:00 UTC` (+6 jam): **$U = +294.54	ext{ m/s}$**, $V = +0.10	ext{ m/s}$ (Lonjakan impulsif tak fisik ~1.060 km/jam pada komponen U saja)
- `2025-11-04 06:00 UTC` (+6 jam berikutnya): $U = +3.57	ext{ m/s}$, $V = +1.35	ext{ m/s}$ (Kembali normal)

**Kesimpulan Ilmiah:** Nilai ~300–450 m/s adalah **`INVALID_ARTIFACT`** murni akibat instabilitas numerik lokal / divergensi batas model operasional NCEP pada saat asimilasi ingest GEE di akhir 2025. Nilai ini **BUKAN fenomena badai/siklon tropis**, karena siklon tropis riil memiliki pusaran sirkuler simetris pada kedua komponen $U$ dan $V$ secara simultan dan melintas secara spasial berkelanjutan.

---

## 2. KELENGKAPAN TEMPORAL & 11 FRAME YANG HILANG

Dalam periode 2015-01-01 s.d. 2025-12-31 (4.018 hari):
- **Ekspektasi Frame (6-jam):** $4.018 	imes 4 = \mathbf{16.072	ext{ frame}}$.
- **Frame Aktual di GEE:** $\mathbf{16.061	ext{ frame}}$.
- **Jumlah Frame Hilang:** Tepat **11 frame** (tingkat kelengkapan temporal global = **99,93%**).

### Daftar 11 Timestamp Frame yang Hilang:
1. `2024-08-12 12:00 UTC`
2. `2024-12-16 00:00 UTC`
3. `2024-12-20 12:00 UTC`
4. `2025-01-21 00:00 UTC`
5. `2025-04-09 12:00 UTC`
6. `2025-04-09 18:00 UTC`
7. `2025-04-10 18:00 UTC`
8. `2025-04-11 18:00 UTC`
9. `2025-04-15 06:00 UTC`
10. `2025-04-15 18:00 UTC`
11. `2025-11-28 12:00 UTC`

---

## 3. ATURAN REMEDIASI & QUALITY ASSURANCE FINAL

Dilarang memotong (*clamp*) nilai secara serampangan menjadi $\pm 35	ext{ m/s}$. Diterapkan arsitektur klasifikasi 3 tingkat:

1. **`VALID_PHYSICAL` ($|u| \le 30	ext{ m/s} \land |v| \le 30	ext{ m/s}$):**
   - Diterima langsung tanpa modifikasi (mencakup 99,87% seluruh data 132 bulan).
2. **`SUSPECT_REVIEW` ($30 < |u, v| \le 35	ext{ m/s}$):**
   - Ditandai flag peninjauan siklon tropis. Dipertahankan jika ada konfirmasi lintasan siklon tropis BMKG / BoM TCWC.
3. **`INVALID_ARTIFACT` ($|u| > 35	ext{ m/s} \lor |v| > 35	ext{ m/s} \lor |\Delta U / 6	ext{h}| > 50	ext{ m/s}$):**
   - **Wajib di-mask menjadi `NaN`**.
   - Dilarang dimasukkan dalam perhitungan tegangan angin (*wind stress*) $	au$ dan pemompaan Ekman $W_E$.
   - Diberikan flag `INVALID_QA_EXCLUDED`.
   - Untuk sel tunggal terisolasi, diisi menggunakan interpolasi median spasial lokal $3 	imes 3$ sel tetangga yang valid dan diberi flag `INTERPOLATED_SPATIAL_LOCAL`.
