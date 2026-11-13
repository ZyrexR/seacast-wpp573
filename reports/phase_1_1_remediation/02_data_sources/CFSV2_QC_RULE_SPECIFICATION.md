# CFSV2 QUALITY CONTROL SPECIFICATION & PHILOSOPHY
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** CFSV2-RULE-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Auditor:** Physical Oceanographer & Independent SQA Auditor  
**Date:** 2026-11-02  

---

## 1. FILOSOFI PENANGANAN KUALITAS ANGIN (CORRECT QC PHILOSOPHY)

Sesuai arahan mutlak Bagian 5.3 Master Prompt:
- **DILARANG CLAMPING OTOMATIS:** Mengubah nilai $450\text{ m/s}$ menjadi $35\text{ m/s}$ adalah **PENIPUAN SAINTIFIK (*SCIENTIFIC FABRICATION*)**, karena mengubah nilai rusak buatan model menjadi nilai seolah-olah terjadi badai kencang.
- **DILARANG MENGUBAH DATA MENTAH:** Seluruh arsip mentah (*raw data*) disimpan tanpa dimodifikasi.
- **SKEMA KLASIFIKASI 5-KATEGORI RESMI:**
  1. `VALID`: Lolos seluruh ambang fisik dan koheren secara spasial/temporal.
  2. `SUSPECT`: Nilai berada pada rentang $[30, 40]\text{ m/s}$ atau lonjakan temporal $>20\text{ m/s}$ yang bertepatan dengan rekam jejak siklon tropis resmi (BOM/JTWC). Dipertahankan dengan bendera peringatan.
  3. `INVALID_SOURCE_FLAG`: Nilai $>40\text{ m/s}$ atau lonjakan 6-jam $>25\text{ m/s}$ yang terbukti diskontinu terhadap tetangga spasial ($|U - \text{median}_{\text{spatial}}| > 25\text{ m/s}$). Dimask menjadi `NaN`.
  4. `MISSING`: Data tidak tersedia di repositori sumber (11 frame).
  5. `IMPUTED`: Nilai hasil imputasi temporal/klimatologis, ditandai transparan dengan `interpolation_flag: true`.

---

## 2. ATURAN PENYARINGAN LONJAKAN 6-JAM (*SIX-HOUR JUMP TEST*)

Uji lonjakan 6-jam menggabungkan dimensi temporal dan koherensi spasial:

$$\Delta U_{6h} = |U(t) - U(t - 6h)|$$
$$\Delta U_{\text{spatial}} = |U(x, y, t) - \text{Median}_{3 \times 3}(U(t))|$$

```text
IF ( |U| > 40.0 m/s ) THEN
    CLASSIFICATION = "INVALID_SOURCE_FLAG"
    ACTION = MASK_AS_NAN
ELSE IF ( Delta_U_6h > 25.0 m/s AND Delta_U_spatial > 20.0 m/s ) THEN
    CLASSIFICATION = "INVALID_SOURCE_FLAG"
    ACTION = MASK_AS_NAN
ELSE IF ( |U| > 30.0 m/s OR Delta_U_6h > 20.0 m/s ) THEN
    IF ( Check_Tropical_Cyclone_Catalog(t, x, y) == TRUE ) THEN
        CLASSIFICATION = "VALID"
        ACTION = RETAIN_WITH_FLAG
    ELSE
        CLASSIFICATION = "SUSPECT"
        ACTION = RETAIN_WITH_FLAG
    END IF
ELSE
    CLASSIFICATION = "VALID"
    ACTION = RETAIN
END IF
```
