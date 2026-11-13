# AOI EVIDENCE REPRODUCTION REPORT
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** AOI-EV-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Status Bukti:** `INDEPENDENTLY_REPRODUCED`  
**Date:** 2026-11-02  

---

## 1. REPRODUKSI BUKTI INTEGRITAS SUMBER ASLI

Audit independen memverifikasi secara langsung integritas sumber data mentah Badan Informasi Geospasial (BIG):
- **File Sumber Mentah:** `data/aoi/raw/WPP573_BIG_ORIGINAL.geojson`
- **Ukuran File:** `25.554.709 bytes`
- **SHA-256 Terhitung:** **`4e2b16197b0d48f350a488ad0a9c076691cf757e78ae855ae117d63d0e53c669`**
- **Status Verifikasi Hash:** **100% MATCH** terhadap spesifikasi Master Prompt.

File master terproses `data/aoi/processed/AOI_WPP573_MASTER.geojson`:
- **Ukuran File:** `67.525.956 bytes`
- **SHA-256 Terhitung:** **`fad138ee3dab333d93ac94a416bccd7b73c308313b328e8f0b4f743cd17e7545`**
- **Status:** Konsisten penuh dengan file `data/boundaries/wppnri_573_big.geojson` pada baseline historis Phase 4.1.1.

---

## 2. HASIL VERIFIKASI TOPOLOGI & INTEGRITAS

Pengujian topologi independen menggunakan Shapely 2.x GEOS C API:
- `is_valid`: **True** (Geometri valid, tidak ada simpul yang saling berpotongan / self-intersection).
- `degenerate_rings`: **0** (Tidak ada cincin dengan simpul $< 4$).
- `ring_closure`: **100% Closed** (Titik awal sama dengan titik akhir pada seluruh $1.402$ cincin).
- `crs`: WGS 84 (EPSG:4326), koordinat terurut bujur kemudian lintang (`always_xy=True`).
