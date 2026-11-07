# -*- coding: utf-8 -*-
"""
Canonical Configuration for SEACAST PRIGI Phase 4.2 Google Earth Engine Pipeline.
"""
import os

WORKSPACE_ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", ".."))
GEE_EXPORT_DIR = os.path.join(WORKSPACE_ROOT, "data", "gee", "exports")
DRIVE_EXPORT_FOLDER = "SEACAST_PRIGI_573"
DEFAULT_GCP_PROJECT_ID = "seca-513505"

# Sub-AOI Prigi Canonical Spatial Specification (Matches Phase 4.1.1 Baseline)
SUB_AOI = {
    "west": 111.00,
    "east": 113.00,
    "south": -10.45,
    "north": -8.00,
    "step_deg": 0.05,
    "n_rows": 50,
    "n_cols": 41,
    "n_cells": 2050,
    "harbor_name": "PPN Prigi",
    "harbor_lon": 111.7314,
    "harbor_lat": -8.2818,
    "crs_geographic": "EPSG:4326",
    "crs_equal_area": "EPSG:6933",
    "crs_utm": "EPSG:32749"
}

# GEE Official Public Dataset Catalog IDs
GEE_DATASETS = {
    "bathymetry_etopo1": "NOAA/NGDC/ETOPO1",
    "sst_oisst_v21": "NOAA/CDR/OISST/V2_1",
    "modis_aqua_l3smi": "NASA/OCEANDATA/MODIS-Aqua/L3SMI",
    "wind_cfsv2_6h": "NOAA/CFSV2/FOR6H_HARMONIZED",
    "hycom_temp_salinity": "HYCOM/sea_temp_salinity",
    "hycom_velocity": "HYCOM/sea_water_velocity",
    "viirs_dnb_monthly": "NOAA/VIIRS/DNB/MONTHLY_V1/VCMSLCFG"
}

# 6 Canonical Spatial Scenarios + April 2022 Reference
SCENARIOS = {
    "april_2022_reference": {"years": [2022], "months": [4], "label": "April 2022 Transition 1 Reference"},
    "east_monsoon_jja": {"years": list(range(2015, 2025)), "months": [6, 7, 8], "label": "East Monsoon (JJA Climatology)"},
    "west_monsoon_djf": {"years": list(range(2015, 2025)), "months": [12, 1, 2], "label": "West Monsoon (DJF Climatology)"},
    "transition_1_mam": {"years": list(range(2015, 2025)), "months": [3, 4, 5], "label": "Transition 1 (MAM Climatology)"},
    "transition_2_son": {"years": list(range(2015, 2025)), "months": [9, 10, 11], "label": "Transition 2 (SON Climatology)"},
    "super_positive_iod_2019": {"years": [2019], "months": [7, 8, 9, 10], "label": "Super Positive IOD Upwelling Peak (Jul-Oct 2019)"},
    "negative_iod_2016": {"years": [2016], "months": [7, 8, 9, 10], "label": "Negative IOD Warm Anomaly (Jul-Oct 2016)"}
}
