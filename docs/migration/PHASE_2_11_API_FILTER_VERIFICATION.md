# SEACAST PRIGI V3 — PHASE 2.11
# RADAR API & DYNAMIC FILTER PERFORMANCE BENCHMARK REPORT

**Audit Date:** 2026-11-04  
**Audit Scope:** Endpoints `/api/predictions` and `/api/grid`, query filters, SQL injection safety, payload benchmarks  
**Status:** **VERIFIED PASS**  
**Audit Tool:** `scratch/audit_api_benchmark.php` via Laravel HTTP Kernel  

---

## 1. API BENCHMARK MATRIX (11 QUERY COMBINATIONS)

All benchmarks were captured directly from the running Laravel 13 kernel with read-only PostgreSQL 17 database queries.

| No | Scenario / Query Combination | HTTP Status | Feature Count | Payload Size | Size (Human) | Kernel Latency | Cache-Control Header |
|:---:|:---|:---:|:---:|:---:|:---:|:---:|:---|
| 1 | **Full GeoJSON Baseline (All 8,129 cells)**<br>`/api/predictions?format=geojson` | 200 OK | 8,129 | 6,277,474 B | 6,130.3 KB | 3,088.07 ms | `max-age=300, public, stale-while-revalidate=1800` |
| 2 | **Summary Mode (HUD Stats Only)**<br>`/api/predictions?summary_only=true` | 200 OK | N/A | 637 B | 0.6 KB | 110.49 ms | `max-age=300, public, stale-while-revalidate=1800` |
| 3 | **Geographic Sector (JATIM)**<br>`/api/predictions?format=geojson&sector=JATIM` | 200 OK | 1,447 | 1,095,465 B | 1,069.8 KB | 605.43 ms | `max-age=300, public, stale-while-revalidate=1800` |
| 4 | **DOM Zone Filter (Z4 Nursery)**<br>`/api/predictions?format=geojson&dom_zone=Z4_NURSERY_GROUND` | 200 OK | 3 | 2,357 B | 2.3 KB | 69.76 ms | `max-age=300, public, stale-while-revalidate=1800` |
| 5 | **Bycatch Filter (GREEN)**<br>`/api/predictions?format=geojson&bycatch=GREEN` | 200 OK | 7,013 | 5,415,500 B | 5,288.6 KB | 634.89 ms | `max-age=300, public, stale-while-revalidate=1800` |
| 6 | **Bycatch Filter (YELLOW)**<br>`/api/predictions?format=geojson&bycatch=YELLOW` | 200 OK | 1,116 | 862,091 B | 841.9 KB | 477.46 ms | `max-age=300, public, stale-while-revalidate=1800` |
| 7 | **Bycatch Filter (RED)**<br>`/api/predictions?format=geojson&bycatch=RED` | 200 OK | 0 | 118 B | 0.1 KB | 220.98 ms | `max-age=300, public, stale-while-revalidate=1800` |
| 8 | **Hotspot Only Filter**<br>`/api/predictions?format=geojson&hotspot_only=true` | 200 OK | 6 | 4,782 B | 4.7 KB | 236.13 ms | `max-age=300, public, stale-while-revalidate=1800` |
| 9 | **Species HSI Threshold (Cakalang $\ge 0.70$)**<br>`/api/predictions?format=geojson&species=cakalang&min_hsi=0.70` | 200 OK | 6 | 4,782 B | 4.7 KB | 352.05 ms | `max-age=300, public, stale-while-revalidate=1800` |
| 10 | **Bounding Box (Prigi Bay)**<br>`/api/predictions?format=geojson&bbox=111.0,-9.0,112.5,-8.0` | 200 OK | 146 | 108,266 B | 105.7 KB | 70.70 ms | `max-age=300, public, stale-while-revalidate=1800` |
| 11 | **Canonical Grid API (JATIM)**<br>`/api/grid?sector=JATIM` | 200 OK | 1,447 | 645,389 B | 630.3 KB | 177.24 ms | `max-age=3600, public, stale-while-revalidate=86400` |

---

## 2. KEY PERFORMANCE & PAYLOAD OBSERVATIONS

1. **Summary Mode Efficiency:** When client requests `summary_only=true`, payload shrinks from 6.13 MB to **637 bytes** (a 9,850x reduction) and latency drops from 3.08s to **110 ms**.
2. **Selective Filtering Latency:** Bounding box spatial queries execute via PostGIS spatial index `ST_Intersects` in only **70.7 ms** for 146 cells.
3. **HTTP Caching Headers:**
   - Dynamic prediction endpoints emit `Cache-Control: max-age=300, public, stale-while-revalidate=1800`.
   - Static canonical grid endpoints emit `Cache-Control: max-age=3600, public, stale-while-revalidate=86400`.
4. **GeoJSON Spec Compliance:** Output adheres strictly to RFC 7946:
   - Root object type is `FeatureCollection`.
   - Each item is a `Feature` with `type: "Polygon"` geometry in EPSG:4326 `[longitude, latitude]`.
   - Properties contain typed numeric scalars: `hsi_cakalang`, `hsi_madidihang`, `hsi_albakor`, `depth_m`, `dist_port_nm`, `sst_c`, `chla_mg_m3`, `wave_height_m`, `wind_speed_kts`, `bycatch_traffic_light`, `dom_zone`, `safety_advisory`, `is_hotspot`.

---

## 3. QUERY SAFETY & SQL INJECTION AUDIT

All database queries in `PredictionController.php` and `GridController.php` were audited for injection vulnerabilities:
- Parameter binding uses PDO prepared statements (`$query->where(...)`).
- Sector filtering validates against an explicit enum whitelist (`['JATIM', 'BALI_NTB', 'NTT', 'SAMUDERA_SELATAN']`).
- DOM zone filtering validates against allowed constants (`Z1_PFG_INSHORE`, `Z2_PFG_OFFSHORE`, `Z3_FEEDING_CORE`, `Z4_NURSERY_GROUND`, `Z5_BYCATCH_SCREENING`, `GENERAL_OCEANIC`).
- Bounding box coordinates are cast via `array_map('floatval', explode(',', $bbox))` and passed as numeric parameters to PostGIS:
  ```php
  ST_MakeEnvelope(?, ?, ?, ?, 4326)
  ```
- Connection operates under database user `seacast_readonly`, with zero `INSERT`, `UPDATE`, `DELETE`, or `DROP` permissions granted.

---
*Report verified by SEACAST PRIGI V3 Migration Audit Directorate.*

