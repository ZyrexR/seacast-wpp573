# SEACAST PRIGI V3 — PHASE 4
# API CONTRACT VERIFICATION REPORT (`/api/timemachine`, `/api/simulate`)

**Document ID:** SEACAST-DOC-P4-API-01  
**Phase:** 4 — Operational Dashboard & Scientific Analytics Suite  
**Release Target:** v3.0-rc1  
**Timestamp:** 2026-11-04T20:25:20+07:00  
**Author:** Senior Laravel Architect & Scientific Software Auditor  
**Classification:** API Specification & Interoperability Report  

---

## 1. ENDPOINT SPECIFICATIONS & VERIFICATION

### 1.1 Endpoint: `GET /api/timemachine`
- **Controller:** `App\Http\Controllers\Api\TimeMachineApiController@index`
- **Rate Limit:** 60 requests / minute
- **Cache-Control:** `public, max-age=86400`
- **Query Parameters:**
  - `summary=true`: Returns 12-month aggregated summaries across all 97,548 records in `grid_monthly_predictions`.
  - `cell_id={id}`: Returns 12-month temporal trajectory for a specific cell.
  - `ym={YYYY_MM}`: Returns spatial snapshot for that month.
- **Verification Result:** PASS (`TimeMachineApiTest`).

### 1.2 Endpoint: `POST /api/simulate`
- **Controller:** `App\Http\Controllers\Api\SimulationApiController@simulate`
- **Rate Limit:** 60 requests / minute
- **Request Body (Climate Perturbation):**
  ```json
  {
    "sstDelta": 1.5,
    "chlaMultiplier": 1.2,
    "limit": 200
  }
  ```
- **Response Structure:**
  - `scenario`: SST delta, Chl-a multiplier, baseline date.
  - `summary`: Total cells, baseline hotspots, simulated hotspots, percentage delta, DOM zone counts.
  - `cells`: Array of simulated cell predictions.
- **Request Body (Vessel Economics):**
  ```json
  {
    "vesselClass": "< 10 GT",
    "targetDistanceNm": 10.0,
    "targetSpecies": "cakalang",
    "solarPrice": 6800
  }
  ```
- **Response Structure:**
  - Round trip NM, travel hours, fuel liters, fuel cost, gross revenue, net profit, out-of-range safety status.
- **Verification Result:** PASS (`SimulationApiTest`).

