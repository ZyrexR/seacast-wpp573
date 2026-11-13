---
name: seacast-operations
description: Use when developing, operating, testing, or deploying SEACAST marine spatial intelligence pipelines, including GEE satellite extraction, live BMKG integration, ensemble ML inference, WebGIS, and CloudPanel deployment.
---

# SEACAST Operations & Spatial Intelligence Architecture

Comprehensive reference for developing, maintaining, and deploying the SEACAST oceanographic intelligence platform for WPPNRI 573.

## System Overview

SEACAST integrates real-time satellite oceanography (NOAA, Copernicus, GEBCO), national meteorological forecasts (BMKG Maritim), machine learning ensemble SDM (MaxEnt, Random Forest, GLM), and an interactive WebGIS into a unified operational platform.

```
[ GEE Orbit Data ] ────┐
                       ├─► [ Daily Extractor ] ─► [ Ensemble SDM (.joblib) ] ─► [ PostgreSQL 17 / PostGIS ]
[ Live BMKG Maritim ] ─┘                                                                  │
                                                                                          ▼
[ CloudPanel Cron (04:00 WIB) ] ──────────────────────────────────────────────► [ WebGIS Radar & Leaflet ]
```

---

## 1. Google Earth Engine Extraction Rules

### Dynamic Date & Latency Principles
- **NOAA OISST v2.1:** Standard orbit-to-catalog latency is $T - 2$ to $T - 3$ days. Never hardcode satellite dates; detect dynamically via:
  ```python
  latest_img = ee.ImageCollection("NOAA/CDR/OISST/V2_1").sort("system:time_start", False).first()
  target_date = ee.Date(latest_img.get("system:time_start")).format("YYYY-MM-dd").getInfo()
  ```
- **Chlorophyll-a (MODIS/Sentinel-3):** Never extract single-day optical pixels in tropical Indonesia due to monsoonal cloud occlusion. Always apply an **8-day rolling composite** with `ee.Reducer.median()`.
- **Spatial Grid:** 8,129 canonical cells ($0.10^\circ \times 0.10^\circ$) spanning WPPNRI 573 (`projects/seca-513505/assets/GRID_010_REGIONAL_8129`).

---

## 2. Live BMKG Maritim Integration

### Authoritative Endpoints (No API Key Required)
- Base URL: `https://maritim.bmkg.go.id/marine2026-data/`
- Ports:
  * PPN Prigi: `/pelabuhan/XO026.json`
  * PPS Cilacap: `/pelabuhan/XM001.json`
- Water Areas (WPP 573):
  * Samudra Hindia Selatan Jawa Timur: `/perairan/M.12.json`
  * Samudra Hindia Selatan Jawa Tengah: `/perairan/M.10.json`
- Caching: Laravel Cache with 60-minute TTL and graceful fallback.

---

## 3. Machine Learning Inference Engine

### Model Architecture
- **Location:** `models/`
  * `cakalang_ensemble_model.joblib` (MaxEnt: 0.320, RF: 0.355, GLM: 0.325)
  * `madidihang_ensemble_model.joblib` (MaxEnt: 0.295, RF: 0.365, GLM: 0.340)
  * `albakor_ensemble_model.joblib` (RF: 0.517, GLM: 0.483)
  * `scaler_oceanographic.joblib`
- **Execution Mode:** 100% self-contained local inference via `joblib.load()`. Zero dependency on Google Colab during daily operations.

### Output Classifications
1. **Hotspot DPI:** $\text{HSI}_{\text{composite}} \ge 0.70$ or species-specific $\text{HSI} \ge 0.70$.
2. **DOM Zones (Dynamic Ocean Management):**
   * `Z1_PFG_INSHORE`: $\le 12\text{ NM} + \text{Tuna Presence}$
   * `Z2_PFG_OFFSHORE`: $12-60\text{ NM} + \text{Tuna Presence}$
   * `Z3_FEEDING_CORE`: $\text{Chl-a} \ge 0.40\text{ mg/m}^3 + \nabla\text{SST} \ge 0.035^\circ\text{C/km}$
   * `Z4_NURSERY_GROUND`: $\text{Depth} \le 150\text{ m} + \le 15\text{ NM}$
   * `Z5_BYCATCH_SCREENING`: Shelf Break $150-1200\text{ m} + \text{SST} \le 26.5^\circ\text{C}$
   * `GENERAL_OCEANIC`: Open ocean background
3. **BMKG Safety Traffic Light:**
   * Wave $\ge 3.5\text{ m} \implies$ `BAHAYA_GELOMBANG_EKSTREM` (SPB Denied)
   * Wave $2.0 - 3.5\text{ m} \implies$ `WASPADA_GELOMBANG_TINGGI` (Restricted for $<10\text{ GT}$)
   * Wave $< 2.0\text{ m} \implies$ `AMAN_MELAUT`

---

## 4. Production Deployment: Hostinger VPS & CloudPanel

### Stack Specifications
- **OS:** Ubuntu 22.04/24.04 LTS (KVM VPS).
- **Web Server:** CloudPanel PHP 8.3-FPM + NGINX (Root: `seacast-prigi-laravel/public`).
- **Database:** PostgreSQL 16/17 + PostGIS 3.4/3.6 (`seacast_prigi`).
- **Python Runtime:** Isolated `venv` in `/home/seacast/htdocs/seacast.id/venv`.
- **GEE Auth:** `/home/seacast/.config/earthengine/credentials`.
- **Scheduler:** CloudPanel Cron running `* * * * * cd /home/seacast/htdocs/seacast.id/seacast-prigi-laravel && php artisan schedule:run`.
- **Detailed Runbook:** Reference `docs/deployment/HOSTINGER_CLOUDPANEL_DEPLOYMENT_GUIDE.md`.

---

## 5. Verification & Testing Commands

```bash
# 1. Test GEE connection & authentication
python -m gee.py.auth_check

# 2. Test ML model decision engine
python scripts/test_model_decisions.py

# 3. Test unified live sync
php artisan seacast:sync-live

# 4. Run Laravel test suite
php artisan test
```
