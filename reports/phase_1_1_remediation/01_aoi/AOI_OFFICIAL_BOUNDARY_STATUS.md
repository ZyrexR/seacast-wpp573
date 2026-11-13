# AOI OFFICIAL & REGULATORY BOUNDARY STATUS
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** AOI-REG-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Auditor:** Senior Marine Geospatial Scientist & Data Governance Auditor  
**Date:** 2026-11-02  

---

## 1. PEMISAHAN BATAS KARTOGRAFIS BIG DENGAN BATAS REGULASI KKP

Sesuai arahan mutlak Audit A Bagian 4.4, tim auditor menegaskan **pemisahan status antara geometri spasial teknis dan legitimasi regulatif**:

### A. Geometri Badan Informasi Geospasial (BIG)
- **Sumber:** BIG ArcGIS REST MapServer Layer 21 (Wilayah Pengelolaan Perikanan RI).
- **Karakteristik:** Geometri kartografis berskala nasional yang telah di-clip dengan garis pantai pulau-pulau di Indonesia.
- **Status Validasi:** **`GEOSPATIAL_SOURCE_VERIFIED: PASS`**.

### B. Batas Regulatif Kementerian Kelautan dan Perikanan (KKP)
- **Dasar Hukum:**
  1. Peraturan Menteri Kelautan dan Perikanan Nomor 18/PERMEN-KP/2014 tentang Wilayah Pengelolaan Perikanan Negara Republik Indonesia.
  2. Keputusan Menteri Kelautan dan Perikanan Nomor 1 Tahun 2021 tentang Wilayah Pengelolaan Perikanan Negara Republik Indonesia di Perairan Darat.
- **Karakteristik Dokumen:** Regulasi mendefinisikan batas WPPNRI 573 dalam bentuk narasi titik-titik koordinat perbatasan terluar maritim dan garis batas landas kontinen/ZEE dengan Australia. KKP mencantumkan luas indikatif nominal perairan sebesar **`1.423.238 km²`**.
- **Status Verifikasi:** **`PROVISIONALLY_RECONCILED_WITH_REGULATORY_TEXT`**.

> [!IMPORTANT]
> **Penegasan Auditor:**
> Karena dataset vektor batas resmi bertanda tangan digital langsung dari KKP tidak disertakan secara mandiri di workspace selain melalui katalog resmi BIG, geometri BIG Layer 21 disahkan sebagai **REPRESENTASI KARTOGRAFIS RESMI TERBAIK YANG TERSEDIA (*BEST AVAILABLE OFFICIAL CARTOGRAPHIC REPRESENTATION*)**, bukan batas yuridis final batas negara.
