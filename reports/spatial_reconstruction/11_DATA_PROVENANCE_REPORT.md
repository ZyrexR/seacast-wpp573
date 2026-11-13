# 11 — DATA PROVENANCE & LINEAGE REPORT
## SEACAST PRIGI — Wilayah Pengelolaan Perikanan Negara Republik Indonesia (WPPNRI) 573

- **Document ID:** `PROV-REP-11`

---

## 1. Rantai Silsilah Data (Data Lineage)

1. **Batas Wilayah Studi:**
   - Sumber: Badan Informasi Geospasial (BIG) — `MapServer Layer 21`.
   - Akuisisi: `2026-11-02` via HTTPS REST API GeoJSON (`outSR=4326`).
   - Checksum RAW: `33a35a58572d4cfbc8221c97a5c88ca4d41dbbf60c91ba059955fa989cb8e96a`.
2. **Data Oseanografi Satelit (GEE):**
   - NOAA CDR OISST v2.1: `NOAA/CDR/OISST/V2_1`
   - NASA MODIS-Aqua L3SMI: `NASA/OCEANDATA/MODIS-Aqua/L3SMI`
   - NOAA CFSv2 Wind: `NOAA/CFSV2/FOR6H_HARMONIZED`
   - HYCOM Sea Water Velocity: `HYCOM/sea_water_velocity`
   - NOAA ETOPO1 Bathymetry: `NOAA/NGDC/ETOPO1`
