# EVIDENCE MANIFEST INTEGRITY & SELF-HASHING EXCLUSION POLICY
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** MANIFEST-POL-01  
**Phase:** 1.1 Remediation / Pre-Phase 4.2  
**Date:** 2026-11-02  

---

## 1. KEBIJAKAN EKSKLUSI SELF-HASHING (SELF-HASHING EXCLUSION PRINCIPLE)

Sesuai arahan mutlak Peneliti pada Pre-Execution Remediation Butir 2:
- File `reports/phase_1_1_remediation/06_reproducibility/EVIDENCE_MANIFEST_SHA256.csv` mencatat nilai hash kriptografis SHA-256 untuk seluruh artefak deliverable Phase 1.1.
- **Prinsip Eksklusi Matematis:** Suatu file tidak dapat memuat hash SHA-256 dari dirinya sendiri tanpa menciptakan rekursi sirkular tak terhingga (*circular reference*) yang membatalkan validitas hash itu sendiri.
- Oleh karena itu, file `EVIDENCE_MANIFEST_SHA256.csv` **SECARA EKSPLISIT DIKECUALIKAN DARI PENCATATAN DI DALAM DIRINYA SENDIRI (*EXCLUDED FROM SELF-HASHING*)**.

---

## 2. HASIL VERIFIKASI ULANG DISK (100% MATCH)

- **Total File Artefak Terdaftar:** 45 file deliverable (mencakup sub-direktori `01_aoi`, `02_data_sources`, `03_grid`, `04_physical_methods`, `05_ml_governance`, `06_reproducibility`, dan `final_decision`).
- **File Diperbaiki:** `reports/phase_1_1_remediation/05_ml_governance/ML_LABEL_VALIDITY_GATE.md` (Hash diverifikasi ulang: `05c9fb6a49a394fe9b9198fe14cfe6fb0e9747e83eb033ce86d5e84c357dcb4b`).
- **Hasil Verifikasi Integritas:** **`45 / 45 FILE MATCH (0 MISMATCHES, 100% PASS)`**.

