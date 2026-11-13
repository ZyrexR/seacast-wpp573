# SEACAST PRIGI V3 — API CONTRACT INVENTORY
**Audit Date:** 4 November 2026  
**Target:** 100% Contract Parity on Laravel (`routes/api.php`)  
**Strict Rule:** No breaking schema changes during migration  

---

## 1. ENDPOINT 1: `/api/predictions`

- **Metode:** `GET`
- **Controller Target:** `App\Http\Controllers\Api\PredictionApiController@index`
- **Parameter Query:**
  - `date` *(opsional, string YYYY-MM-DD, default: tanggal terbaru di database)*
  - `format` *(opsional, enum: `geojson` | `json` | `summary`, default: `geojson`)*
  - `summary_only` *(opsional, boolean string `true` | `false`, default: `false`)*

### 1.1. Response Schema: Summary Mode (`summary_only=true` atau `format=summary`)
```json
{
  "date": "2026-11-03",
  "summary": {
    "total_cells": 8129,
    "hotspots_count": 6,
    "safe_pct": 0,
    "mean_sst": 29.2,
    "mean_chla": 0.125,
    "mean_wave_m": 3.36,
    "species": {
      "cakalang_mean_hsi": 0.209,
      "madidihang_mean_hsi": 0.276,
      "legacy_species_removed_mean_hsi": 0.448,
      "highest_species": "Legacy species removed from TCT scope",
      "highest_score": 0.448
    },
    "bycatch": {
      "green_count": 7013,
      "green_pct": 86.3,
      "yellow_count": 1116,
      "yellow_pct": 13.7,
      "red_count": 0,
      "red_pct": 0
    },
    "sources": {
      "sdm": "Ensemble SDM (MaxEnt, Random Forest, GLM)",
      "ocean": "NOAA CoralTemp OISST & Sentinel-3 Chl-a",
      "wave": "Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)",
      "bycatch": "Screening Lereng Batimetri 150-1200m Standar IOTC"
    }
  }
}
```

### 1.2. Response Schema: GeoJSON Mode (`format=geojson`)
- **Tipe:** `FeatureCollection` (8.129 features)
- **Feature Properties:** `cell_id`, `cell_class`, `center_lon`, `center_lat`, `depth_m`, `dist_port_km`, `dist_port_nm`, `is_shelf_break`, `is_prigi_inshore`, `is_prigi_offshore`, `sector`, `hsi_cakalang`, `hsi_madidihang`, `hsi_legacy_removed`, `is_hotspot`, `dom_zone`, `bycatch_traffic_light`, `sst_c`, `chla_mg_m3`, `wave_height_m`, `safety_advisory`.
- **Geometry:** Polygon koordinat SRID 4326.
- **HTTP Caching Header:** `Cache-Control: public, max-age=300, stale-while-revalidate=1800`.

---

## 2. ENDPOINT 2: `/api/grid`

- **Metode:** `GET`
- **Controller Target:** `App\Http\Controllers\Api\GridApiController@index`
- **Parameter Query:**
  - `sector` *(opsional, enum: `ALL` | `JATIM` | `JABAR_JATENG` | `BALI_NUSA_TENGGARA`)*
  - `bbox` *(opsional, format comma-separated: `minLon,minLat,maxLon,maxLat`)*
  - `limit` *(opsional, integer)*
- **Response:** `FeatureCollection` PostGIS kanonik.
- **HTTP Caching Header:** `Cache-Control: public, max-age=3600, stale-while-revalidate=86400`.

---

## 3. ENDPOINT 3: `/api/landings`

- **Metode:** `GET`
- **Controller Target:** `App\Http\Controllers\Api\LandingApiController@index`
- **Parameter Query:**
  - `year` *(opsional, integer 2015 – 2025)*
- **Response Schema:**
```json
{
  "meta": {
    "port": "PPN Prigi",
    "wppnri": "573",
    "period": "2015 - 2025 (132 Months)",
    "total_records": 132,
    "annual_totals": {
      "total_pelagic_tons": 18405.3,
      "cakalang_tons": 8412.5,
      "madidihang_tons": 5340.2,
      "tongkol_tons": 4652.6,
      "trips_count": 167220,
      "cpue_proxy_avg": 110.06
    }
  },
  "yearly_summary": [
    { "year": 2025, "total_tons": 1750.2, "trips_count": 14200, "cpue": 123.25 }
  ],
  "landings": [
    {
      "year_month": "2025_12",
      "year": 2025,
      "month": 12,
      "cakalang_tons": 78.4,
      "madidihang_tons": 45.1,
      "tongkol_tons": 32.0,
      "total_pelagic_tons": 155.5,
      "trips_count": 1250,
      "cpue_proxy": 124.4,
      "mean_sst": 28.5,
      "mean_chla": 0.22
    }
  ]
}
```

---

## 4. ENDPOINT 4: `/api/timemachine`

- **Metode:** `GET`
- **Controller Target:** `App\Http\Controllers\Api\TimeMachineApiController@index`
- **Parameter Query:**
  - `summary` *(opsional, boolean string `true` | `false`)*
  - `cell_id` *(opsional, string, misal `CELL_0412`)*
  - `ym` *(opsional, format `YYYY_MM`, misal `2025_08`)*
- **Response (summary=true):** 12 array ringkasan bulanan tahun 2025 (`year_month`, `avg_hsi_cakalang`, `avg_sst`, `avg_chla`, `hotspot_cells`).

---

## 5. ENDPOINT 5: `/api/fleet`

- **Metode:** `GET`
- **Controller Target:** `App\Http\Controllers\Api\FleetApiController@index`
- **Parameter Query:** Tidak ada.
- **Response Schema:**
```json
{
  "port": "PPN Prigi",
  "total_vessels": 1269,
  "fleet_profile": [
    {
      "year": 2026,
      "gt_class": "< 10 GT",
      "vessel_count": 850,
      "pct_fleet": 67.0,
      "max_range_nm": 12.0,
      "operational_profile": "Perahu Motor Tempel & Kapal Sekoci Tradisional (Operasi One-Day Trip Pesisir <= 12 NM)",
      "recommended_dom_zones": ["Z1"]
    },
    {
      "year": 2026,
      "gt_class": "10 - 20 GT",
      "vessel_count": 165,
      "pct_fleet": 13.0,
      "max_range_nm": 30.0,
      "operational_profile": "Kapal Kayu Purse Seine & Gillnet Menengah (Operasi 3-7 Hari Paparan Kontinen <= 30 NM)",
      "recommended_dom_zones": ["Z1", "Z2", "Z3"]
    },
    {
      "year": 2026,
      "gt_class": "20 - 30 GT",
      "vessel_count": 254,
      "pct_fleet": 20.0,
      "max_range_nm": 60.0,
      "operational_profile": "Kapal Baja/Fiber Purse Seine Modern & Longline Samudra (Operasi >10 Hari ZEEI <= 60 NM)",
      "recommended_dom_zones": ["Z1", "Z2", "Z3", "Z5"]
    }
  ]
}
```

---

## 6. ENDPOINT 6: `/api/simulate`

- **Metode:** `POST`
- **Controller Target:** `App\Http\Controllers\Api\SimulationApiController@simulate`
- **Request Body (JSON):**
```json
{
  "sstDelta": 0.5,
  "chlaMultiplier": 1.2,
  "date": "2026-11-03"
}
```
- **Response Schema:**
```json
{
  "meta": {
    "date": "2026-11-03",
    "sst_delta": 0.5,
    "chla_multiplier": 1.2,
    "total_cells": 8129,
    "baseline_hotspots": 6,
    "simulated_hotspots": 8
  },
  "zonation_shifts": {
    "Z1": 2,
    "Z2": 29,
    "Z3": 215,
    "Z4": 3,
    "Z5": 0
  },
  "cells": [ ... ]
}
```

---

## 7. RENCANA IMPLEMENTASI LARAVEL

Seluruh kontrak di atas akan diimplementasikan menggunakan:
1. Form Requests (`App\Http\Requests\PredictionQueryRequest`, dll.) untuk validasi input.
2. API Resources (`App\Http\Resources\PredictionSummaryResource`, dll.) untuk menjamin integritas struktur JSON tanpa deviasi 1 byte pun.
3. Automated Feature Tests (`tests/Feature/ApiParityTest.php`) yang membandingkan response Next.js dengan response Laravel menggunakan snapshot JSON.

