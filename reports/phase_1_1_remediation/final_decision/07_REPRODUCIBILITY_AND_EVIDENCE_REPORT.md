# REPRODUCIBILITY AND EVIDENCE AUDIT REPORT
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** REPRO-FINAL-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Date:** 2026-11-02  

---

## 1. DOKUMENTASI LENGKAP BUKTI REPRODUSIBILITAS

1. **Penerapan Taksonomi Bukti 4-Status:**
   - Seluruh 11 klaim inti Phase 1.0-R telah diperiksa dan diverifikasi ulang dengan status **`INDEPENDENTLY_REPRODUCED`**.
   - Matriks keterlacakan tersimpan pada `CLAIM_EVIDENCE_MATRIX.csv`.
2. **Integritas Uji Regresi Baseline Historis:**
   - Script `scratch/run_phase_4_1_1_regression_tests.py` dieksekusi ulang dan menghasilkan **15 / 15 PASS (100%)**.
   - Terbukti tidak ada mutasi atau perusakan terhadap baseline historis Phase 1–4.1.1.
3. **Validasi Schema JSON:**
   - `GRID_CELL_SCHEMA.json` dan `FINAL_DATA_QA_SCHEMA.json` lolos validasi sintaks dan integritas primary key `grid_id + year + month`.
4. **Manifest SHA-256:**
   - Sebanyak 35 file deliverable Phase 1.1 tercatat hash kriptografisnya pada `EVIDENCE_MANIFEST_SHA256.csv`.
