# SENSOR CONTINUITY & HARMONIZATION DECISION
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** CHL-DEC-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Auditor:** Remote Sensing Specialist & Independent SQA Auditor  
**Date:** 2026-11-02  

---

## 1. EVALUASI OPSI KEPUTUSAN KONTINUITAS SENSOR

Sesuai arahan Bagian 6.4 Master Prompt, dilakukan evaluasi kritis terhadap 4 opsi:
- **Opsi A (Penggantian Tanpa Syarat):** DITOLAK. Mengganti MODIS dengan GCOM-C seolah-olah sensor yang sama menyamarkan perbedaan respons radiometrik dan pita spektral.
- **Opsi B (Pemanfaatan Terkendali Sebagai Variabel Terpisah / SENSOR_FALLBACK Eksplisit):** **DISETUJUI DENGAN SYARAT (APPROVED WITH GOVERNANCE)**.
- **Opsi C (Penolakan Penuh GCOM-C):** DITOLAK. Ketiadaan data 3 bulan pada Juli 2021, November 2021, dan Desember 2025 dapat dipulihkan secara sah karena validasi tumpang tindih membuktikan korelasi kuat ($r = 0,924$, log-RMSE $0,078\text{ dex}$).
- **Opsi D (Membiarkan Kosong):** Menjadi baseline komparatif bila pengguna menginginkan data murni satu sensor.

---

## 2. KEPUTUSAN FINAL: OPSIONAL TERPILIH (OPTION B)

1. **Kebijakan Penyimpanan Nilai:**
   - Rekam data untuk 129 bulan MODIS Aqua tetap mempertahankan provenans asli `TERRA_AQUA_MODIS`.
   - Untuk 3 bulan gap (`2021-07`, `2021-11`, `2025-12`), nilai diambil dari komposit GCOM-C SGLI dengan faktor skala $0,0016$ dan dikoreksi regresi:
     $$\log_{10}(\text{Chl}_{\text{Harmonized}}) = 0,982 \cdot \log_{10}(\text{Chl}_{\text{GCOMC}}) + 0,014$$
   - Atribut status wajib diset secara eksplisit ke:
     `missing_state: "SENSOR_FALLBACK"`
     `sensor_identifier: "SGLI_GCOMC"`
   - **DILARANG MENYAMAKAN DATA FALLBACK INI SEBAGAI OBSERVASI MODIS LANGSUNG**.
