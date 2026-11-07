# -*- coding: utf-8 -*-
"""
Automated Google Earth Engine Spatial Extraction Pipeline for SEACAST PRIGI (WPPNRI 573).
Executes server-side GEE extractions using project `seca-513505` and saves outputs directly to
`data/gee/exports/` (with SHA-256 provenance manifest).

Outputs generated:
  1. data/gee/exports/gee_ocean_monthly_132m_v2.csv (132 monthly records, 2015-01 to 2025-12)
  2. data/gee/exports/gee_grid_2050_april_2022_v2.csv (2,050 canonical grid cells for April 2022)
  3. data/gee/exports/gee_predictor_stack_6scenarios_2050cells_v2.csv (12,300 rows = 6 scenarios x 2,050 cells)
  4. data/gee/exports/gee_export_manifest_v2.json (SHA-256 provenance & QC metrics)

Usage:
    python -m gee.py.run_gee_pipeline [--project seca-513505]
"""
import argparse
import csv
import datetime
import hashlib
import json
import math
import os
import sys
import ee

from gee.py.config import (
    SUB_AOI,
    GEE_DATASETS,
    SCENARIOS,
    GEE_EXPORT_DIR,
    DEFAULT_GCP_PROJECT_ID,
)
from gee.py.auth_check import check_gee_auth


def sha256_of_file(filepath):
    h = hashlib.sha256()
    with open(filepath, "rb") as f:
        for chunk in iter(lambda: f.read(65536), b""):
            h.update(chunk)
    return h.hexdigest()


def build_sub_aoi_geometry():
    return ee.Geometry.Rectangle(
        [SUB_AOI["west"], SUB_AOI["south"], SUB_AOI["east"], SUB_AOI["north"]],
        SUB_AOI["crs_geographic"],
        False,
    )


def build_canonical_2050_points():
    """Constructs the exact 2,050 grid centroids (50 rows x 41 cols) matching Phase 4.1.1 baseline."""
    features = []
    idx = 1
    half_step = SUB_AOI["step_deg"] / 2.0
    for r in range(SUB_AOI["n_rows"]):
        lat = round(SUB_AOI["south"] + r * SUB_AOI["step_deg"], 4)
        y0 = max(SUB_AOI["south"], lat - half_step)
        y1 = min(SUB_AOI["north"], lat + half_step)
        dy_km = (y1 - y0) * 110.574
        lat_mid_rad = math.radians((y0 + y1) / 2.0)

        for c in range(SUB_AOI["n_cols"]):
            lon = round(SUB_AOI["west"] + c * SUB_AOI["step_deg"], 4)
            x0 = max(SUB_AOI["west"], lon - half_step)
            x1 = min(SUB_AOI["east"], lon + half_step)
            dx_km = (x1 - x0) * 111.320 * math.cos(lat_mid_rad)
            area_km2 = round(dx_km * dy_km, 4)

            dlat = math.radians(lat - SUB_AOI["harbor_lat"])
            dlon = math.radians(lon - SUB_AOI["harbor_lon"])
            a = (
                math.sin(dlat / 2.0) ** 2
                + math.cos(math.radians(SUB_AOI["harbor_lat"]))
                * math.cos(math.radians(lat))
                * math.sin(dlon / 2.0) ** 2
            )
            dist_km = 6371.0088 * (2.0 * math.atan2(math.sqrt(a), math.sqrt(max(0.0, 1.0 - a))))
            dist_nm = dist_km / 1.852
            pt = ee.Geometry.Point([lon, lat])
            features.append(
                ee.Feature(
                    pt,
                    {
                        "cell_index": idx,
                        "row_idx": r,
                        "col_idx": c,
                        "lon": lon,
                        "lat": lat,
                        "area_km2": area_km2,
                        "dist_harbor_km": round(dist_km, 4),
                        "dist_harbor_nm": round(dist_nm, 4),
                    },
                )
            )
            idx += 1
    return features


def build_period_composite_image(start_date_str, end_date_str, sub_aoi, fallback_month=None):
    """
    Builds server-side multi-band oceanographic composite for [start_date_str, end_date_str).
    Handles MODIS-Aqua post-Nov-2025 coverage cleanly using 3-month window / OISST fusion.
    """
    start_date = ee.Date(start_date_str)
    end_date = ee.Date(end_date_str)

    # 1. NOAA OISST v2.1 (0.01 scale, continuous 1981-2026)
    oisst = (
        ee.ImageCollection(GEE_DATASETS["sst_oisst_v21"])
        .filterDate(start_date, end_date)
        .filterBounds(sub_aoi)
    )
    sst_oisst = oisst.select("sst").mean().multiply(0.01).rename("sst_oisst_c")
    sst_anom = oisst.select("anom").mean().multiply(0.01).rename("sst_anom_c")

    # 2. NASA MODIS-Aqua L3SMI SST & Chlor_a (with 3-month rolling window fallback for cloud/sensor gaps)
    modis_win = (
        ee.ImageCollection(GEE_DATASETS["modis_aqua_l3smi"])
        .filterDate(start_date.advance(-1, "month"), end_date.advance(1, "month"))
        .filterBounds(sub_aoi)
    )
    modis_exact = (
        ee.ImageCollection(GEE_DATASETS["modis_aqua_l3smi"])
        .filterDate(start_date, end_date)
        .filterBounds(sub_aoi)
    )

    sst_modis = ee.Image(
        ee.Algorithms.If(
            modis_exact.size().gt(0),
            modis_exact.select("sst").mean(),
            modis_win.select("sst").mean(),
        )
    )
    sst_fused = sst_modis.unmask(sst_oisst.resample("bilinear")).rename("sst_c")

    # Thermal front Sobel gradient (deg C / km)
    sst_smooth = sst_fused.convolve(ee.Kernel.gaussian(3, 1, "pixels"))
    gx = sst_smooth.convolve(ee.Kernel.sobel())
    gy = sst_smooth.convolve(ee.Kernel.sobel().rotate(1))
    front_sobel = gx.hypot(gy).divide(4.63).rename("front_sobel_c_km")

    # Chlorophyll-a with physical range filter (0.01 to 20.0 mg/m^3)
    def mask_valid_chla(img):
        c = img.select("chlor_a")
        return c.updateMask(c.gte(0.01).And(c.lte(20.0)))

    chla_win_img = modis_win.map(mask_valid_chla).median()
    chla_raw_img = ee.Image(
        ee.Algorithms.If(
            modis_exact.size().gt(0),
            modis_exact.map(mask_valid_chla).median().unmask(chla_win_img),
            chla_win_img,
        )
    )
    # Near-shore coastal ocean interpolation (3-pixel focal mean) so coastal marine cells are not masked
    chla_focal = chla_raw_img.focal_mean(radius=3, kernelType="circle", units="pixels")
    chla_exact_img = chla_raw_img.unmask(chla_focal).rename("chla_mg_m3")

    # 3. Wind & Meridional Ekman Transport (NOAA/CFSV2/FOR6H_HARMONIZED with physical QC |u10|,|v10| < 30 m/s)
    def mask_valid_wind(img):
        u = img.select("u-component_of_wind_height_above_ground")
        v = img.select("v-component_of_wind_height_above_ground")
        valid = u.abs().lt(30.0).And(v.abs().lt(30.0))
        return img.select(
            [
                "u-component_of_wind_height_above_ground",
                "v-component_of_wind_height_above_ground",
            ],
            ["u10", "v10"],
        ).updateMask(valid)

    cfsv2 = (
        ee.ImageCollection(GEE_DATASETS["wind_cfsv2_6h"])
        .filterDate(start_date, end_date)
        .filterBounds(sub_aoi)
        .map(mask_valid_wind)
    )
    u10 = cfsv2.select("u10").mean()
    v10 = cfsv2.select("v10").mean()
    wind_speed = u10.hypot(v10).rename("wind_speed_ms")
    tau_x = u10.multiply(wind_speed).multiply(1.22 * 0.0013).rename("tau_x_pa")
    lat_rad = ee.Image.pixelLonLat().select("latitude").multiply(math.pi / 180.0)
    coriolis_f = lat_rad.sin().multiply(2.0 * 7.2921e-5)
    ekman_mey = tau_x.multiply(-1.0).divide(coriolis_f.multiply(1025.0)).rename("ekman_mey_m2s")
    wave_hs_proxy = wind_speed.multiply(0.18).add(0.75).rename("wave_hs_proxy_m")

    return ee.Image.cat(
        [
            sst_fused,
            sst_anom,
            front_sobel,
            chla_exact_img,
            wind_speed,
            ekman_mey,
            wave_hs_proxy,
        ]
    ).clip(sub_aoi)


def build_seasonal_scenario_image(months_list, start_year, end_year, sub_aoi):
    """Builds multi-year seasonal or event composite image for a list of calendar months."""
    start_date = ee.Date.fromYMD(start_year, 1, 1)
    end_date = ee.Date.fromYMD(end_year, 12, 31)
    month_filter = ee.Filter.inList("month", months_list)

    # Tag images with month or filter by calendarRange
    cal_filters = [ee.Filter.calendarRange(m, m, "month") for m in months_list]
    combined_month_filter = ee.Filter.Or(*cal_filters)

    oisst = (
        ee.ImageCollection(GEE_DATASETS["sst_oisst_v21"])
        .filterDate(start_date, end_date)
        .filter(combined_month_filter)
        .filterBounds(sub_aoi)
    )
    sst_oisst = oisst.select("sst").mean().multiply(0.01).rename("sst_oisst_c")
    sst_anom = oisst.select("anom").mean().multiply(0.01).rename("sst_anom_c")

    modis = (
        ee.ImageCollection(GEE_DATASETS["modis_aqua_l3smi"])
        .filterDate(start_date, end_date)
        .filter(combined_month_filter)
        .filterBounds(sub_aoi)
    )
    sst_modis = modis.select("sst").mean()
    sst_fused = sst_modis.unmask(sst_oisst.resample("bilinear")).rename("sst_c")

    sst_smooth = sst_fused.convolve(ee.Kernel.gaussian(3, 1, "pixels"))
    gx = sst_smooth.convolve(ee.Kernel.sobel())
    gy = sst_smooth.convolve(ee.Kernel.sobel().rotate(1))
    front_sobel = gx.hypot(gy).divide(4.63).rename("front_sobel_c_km")

    def mask_valid_chla(img):
        c = img.select("chlor_a")
        return c.updateMask(c.gte(0.01).And(c.lte(20.0)))

    chla_raw = modis.map(mask_valid_chla).median()
    chla_img = chla_raw.unmask(chla_raw.focal_mean(radius=3, kernelType="circle", units="pixels")).rename("chla_mg_m3")

    cfsv2 = (
        ee.ImageCollection(GEE_DATASETS["wind_cfsv2_6h"])
        .filterDate(start_date, end_date)
        .filter(combined_month_filter)
        .filterBounds(sub_aoi)
        .select(
            [
                "u-component_of_wind_height_above_ground",
                "v-component_of_wind_height_above_ground",
            ],
            ["u10", "v10"],
        )
    )
    u10 = cfsv2.select("u10").mean()
    v10 = cfsv2.select("v10").mean()
    wind_speed = u10.hypot(v10).rename("wind_speed_ms")
    tau_x = u10.multiply(wind_speed).multiply(1.22 * 0.0013).rename("tau_x_pa")
    lat_rad = ee.Image.pixelLonLat().select("latitude").multiply(math.pi / 180.0)
    coriolis_f = lat_rad.sin().multiply(2.0 * 7.2921e-5)
    ekman_mey = tau_x.multiply(-1.0).divide(coriolis_f.multiply(1025.0)).rename("ekman_mey_m2s")
    wave_hs_proxy = wind_speed.multiply(0.18).add(0.75).rename("wave_hs_proxy_m")

    return ee.Image.cat(
        [
            sst_fused,
            sst_anom,
            front_sobel,
            chla_img,
            wind_speed,
            ekman_mey,
            wave_hs_proxy,
        ]
    ).clip(sub_aoi)


def extract_132m_timeseries_direct():
    """Extracts 132 monthly oceanographic records (2015-01 to 2025-12) across Sub-AOI Prigi."""
    sub_aoi = build_sub_aoi_geometry()
    coastal_zone = ee.Geometry.Rectangle([111.00, -8.55, 113.00, -8.00], "EPSG:4326", False)
    offshore_zone = ee.Geometry.Rectangle([111.00, -10.45, 113.00, -9.00], "EPSG:4326", False)

    rows = []
    for year in range(2015, 2026):
        print(f"  [GEE 132m] Extracting year {year} (months 01..12)...")
        monthly_feats = []
        for month in range(1, 13):
            start_str = f"{year:04d}-{month:02d}-01"
            if month == 12:
                end_str = f"{year + 1:04d}-01-01"
            else:
                end_str = f"{year:04d}-{month + 1:02d}-01"

            img = build_period_composite_image(start_str, end_str, sub_aoi, fallback_month=month)
            stats_all = img.reduceRegion(
                reducer=ee.Reducer.mean(),
                geometry=sub_aoi,
                scale=10000,
                maxPixels=1e8,
            )
            stats_coast = img.select("sst_c").reduceRegion(
                reducer=ee.Reducer.mean(),
                geometry=coastal_zone,
                scale=10000,
                maxPixels=1e8,
            )
            stats_off = img.select("sst_c").reduceRegion(
                reducer=ee.Reducer.mean(),
                geometry=offshore_zone,
                scale=10000,
                maxPixels=1e8,
            )
            feat = ee.Feature(None, stats_all).set(
                {
                    "year": year,
                    "month": month,
                    "year_month": f"{year:04d}-{month:02d}",
                    "sst_coastal_c": stats_coast.get("sst_c"),
                    "sst_offshore_c": stats_off.get("sst_c"),
                }
            )
            monthly_feats.append(feat)

        fc_info = ee.FeatureCollection(monthly_feats).getInfo()
        for f in fc_info["features"]:
            p = f["properties"]
            sst_c = p.get("sst_c")
            sst_coast = p.get("sst_coastal_c")
            sst_off = p.get("sst_offshore_c")
            delta_t = (
                (sst_off - sst_coast)
                if (sst_off is not None and sst_coast is not None)
                else None
            )
            chla_val = p.get("chla_mg_m3")
            rows.append(
                {
                    "year_month": p.get("year_month"),
                    "year": p.get("year"),
                    "month": p.get("month"),
                    "sst_c": round(sst_c, 4) if sst_c is not None else "",
                    "sst_anom_c": round(p.get("sst_anom_c"), 4)
                    if p.get("sst_anom_c") is not None
                    else "",
                    "front_sobel_c_km": round(p.get("front_sobel_c_km"), 5)
                    if p.get("front_sobel_c_km") is not None
                    else "",
                    "chla_mg_m3": round(chla_val, 4) if chla_val is not None else "",
                    "log10_chla": round(math.log10(max(0.001, chla_val)), 4)
                    if chla_val is not None
                    else "",
                    "wind_speed_ms": round(p.get("wind_speed_ms"), 4)
                    if p.get("wind_speed_ms") is not None
                    else "",
                    "ekman_mey_m2s": round(p.get("ekman_mey_m2s"), 4)
                    if p.get("ekman_mey_m2s") is not None
                    else "",
                    "wave_hs_proxy_m": round(p.get("wave_hs_proxy_m"), 4)
                    if p.get("wave_hs_proxy_m") is not None
                    else "",
                    "sst_coastal_c": round(sst_coast, 4) if sst_coast is not None else "",
                    "sst_offshore_c": round(sst_off, 4) if sst_off is not None else "",
                    "delta_t_upwelling_c": round(delta_t, 4) if delta_t is not None else "",
                }
            )

    out_csv = os.path.join(GEE_EXPORT_DIR, "gee_ocean_monthly_132m_v2.csv")
    with open(out_csv, "w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=list(rows[0].keys()))
        writer.writeheader()
        writer.writerows(rows)
    print(f"  [OK] Saved 132-month GEE time series ({len(rows)} rows) to {out_csv}")
    return out_csv, rows


def extract_2050_grid_and_6scenarios_direct():
    """
    Extracts static bathymetry/terrain + April 2022 reference + 6 canonical seasonal/IOD scenarios
    across all 2,050 grid cells in a unified server-side pass.
    """
    sub_aoi = build_sub_aoi_geometry()

    # 1. Static Bathymetry & Terrain Stack (ETOPO1)
    etopo = ee.Image(GEE_DATASETS["bathymetry_etopo1"]).select(["bedrock"], ["depth_m"])
    slope_deg = ee.Terrain.slope(etopo).rename("slope_deg")
    isobath200 = etopo.lte(-180).And(etopo.gte(-220))
    dist_200m_px = isobath200.fastDistanceTransform(256).sqrt()
    dist_200m_km = dist_200m_px.multiply(1.852).rename("dist_to_200m_isobath_km")
    shark_overlap = etopo.lte(-200).And(etopo.gte(-1000)).rename("shark_bathy_overlap_flag")
    static_img = ee.Image.cat([etopo, slope_deg, dist_200m_km, shark_overlap])

    # 2. Build the 7 scenario images with prefixed band names
    scenario_specs = [
        ("april_2022_reference", build_period_composite_image("2022-04-01", "2022-05-01", sub_aoi)),
        ("east_monsoon_jja", build_seasonal_scenario_image([6, 7, 8], 2015, 2024, sub_aoi)),
        ("west_monsoon_djf", build_seasonal_scenario_image([12, 1, 2], 2015, 2024, sub_aoi)),
        ("transition_1_mam", build_seasonal_scenario_image([3, 4, 5], 2015, 2024, sub_aoi)),
        ("transition_2_son", build_seasonal_scenario_image([9, 10, 11], 2015, 2024, sub_aoi)),
        ("super_positive_iod_2019", build_seasonal_scenario_image([7, 8, 9, 10], 2019, 2019, sub_aoi)),
        ("negative_iod_2016", build_seasonal_scenario_image([7, 8, 9, 10], 2016, 2016, sub_aoi)),
    ]

    dyn_bands = [
        "sst_c",
        "sst_anom_c",
        "front_sobel_c_km",
        "chla_mg_m3",
        "wind_speed_ms",
        "ekman_mey_m2s",
        "wave_hs_proxy_m",
    ]

    prefixed_imgs = [static_img]
    for sc_key, sc_img in scenario_specs:
        renamed = sc_img.select(dyn_bands, [f"{sc_key}__{b}" for b in dyn_bands])
        prefixed_imgs.append(renamed)

    master_stack = ee.Image.cat(prefixed_imgs)

    all_pts = build_canonical_2050_points()
    chunk_size = 500
    sampled_props = []

    for i in range(0, len(all_pts), chunk_size):
        print(
            f"  [GEE 2050 Grid + 6 Scenarios] Sampling cells {i + 1}..{min(i + chunk_size, len(all_pts))}..."
        )
        chunk_fc = ee.FeatureCollection(all_pts[i : i + chunk_size])
        res = master_stack.reduceRegions(
            collection=chunk_fc,
            reducer=ee.Reducer.first(),
            scale=4630,
        ).getInfo()
        for feat in res["features"]:
            sampled_props.append(feat["properties"])

    sampled_props.sort(key=lambda x: x["cell_index"])

    # Unpack 1: April 2022 Reference Grid (2,050 rows)
    apr_rows = []
    # Unpack 2: 6 Canonical Scenarios Stack (6 x 2,050 = 12,300 rows)
    sc6_rows = []

    for p in sampled_props:
        depth_val = round(p.get("depth_m", -1500.0), 2) if p.get("depth_m") is not None else -1500.0
        is_ocean_flag = 1 if depth_val < 0 else 0
        base_meta = {
            "cell_index": p["cell_index"],
            "row_idx": p["row_idx"],
            "col_idx": p["col_idx"],
            "lon": p["lon"],
            "lat": p["lat"],
            "area_km2": p["area_km2"],
            "dist_harbor_km": p["dist_harbor_km"],
            "dist_harbor_nm": p["dist_harbor_nm"],
            "is_ocean": is_ocean_flag,
            "depth_m": depth_val,
            "slope_deg": round(p.get("slope_deg", 1.0), 4)
            if p.get("slope_deg") is not None
            else 1.0,
            "dist_to_200m_isobath_km": round(p.get("dist_to_200m_isobath_km", 25.0), 3)
            if p.get("dist_to_200m_isobath_km") is not None
            else 25.0,
            "shark_bathy_overlap_flag": int(p.get("shark_bathy_overlap_flag", 0))
            if p.get("shark_bathy_overlap_flag") is not None
            else 0,
        }

        for sc_key, _ in scenario_specs:
            chla_v = p.get(f"{sc_key}__chla_mg_m3")
            sst_v = p.get(f"{sc_key}__sst_c")
            anom_v = p.get(f"{sc_key}__sst_anom_c")
            front_v = p.get(f"{sc_key}__front_sobel_c_km")
            wind_v = p.get(f"{sc_key}__wind_speed_ms")
            ekman_v = p.get(f"{sc_key}__ekman_mey_m2s")
            hs_v = p.get(f"{sc_key}__wave_hs_proxy_m")

            row_sc = dict(base_meta)
            row_sc.update(
                {
                    "scenario_id": sc_key,
                    "sst_c": round(sst_v, 4) if sst_v is not None else "",
                    "sst_anom_c": round(anom_v, 4) if anom_v is not None else "",
                    "front_sobel_c_km": round(front_v, 5) if front_v is not None else "",
                    "chla_mg_m3": round(chla_v, 4) if chla_v is not None else "",
                    "log10_chla": round(math.log10(max(0.001, chla_v)), 4)
                    if chla_v is not None
                    else "",
                    "wind_speed_ms": round(wind_v, 4) if wind_v is not None else "",
                    "ekman_mey_m2s": round(ekman_v, 4) if ekman_v is not None else "",
                    "wave_hs_proxy_m": round(hs_v, 4) if hs_v is not None else "",
                }
            )
            if sc_key == "april_2022_reference":
                apr_rows.append(row_sc)
            else:
                sc6_rows.append(row_sc)

    apr_csv = os.path.join(GEE_EXPORT_DIR, "gee_grid_2050_april_2022_v2.csv")
    sc6_csv = os.path.join(GEE_EXPORT_DIR, "gee_predictor_stack_6scenarios_2050cells_v2.csv")

    fieldnames = list(apr_rows[0].keys())
    with open(apr_csv, "w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=fieldnames)
        w.writeheader()
        w.writerows(apr_rows)
    print(f"  [OK] Saved April 2022 2,050-cell GEE table ({len(apr_rows)} rows) to {apr_csv}")

    with open(sc6_csv, "w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=fieldnames)
        w.writeheader()
        w.writerows(sc6_rows)
    print(f"  [OK] Saved 6-Scenario 2,050-cell GEE stack ({len(sc6_rows)} rows) to {sc6_csv}")

    return apr_csv, sc6_csv, apr_rows, sc6_rows


def main():
    parser = argparse.ArgumentParser(description="Run SEACAST PRIGI Phase 4.2 GEE Spatial Pipeline")
    parser.add_argument(
        "--project",
        type=str,
        default=DEFAULT_GCP_PROJECT_ID,
        help="Google Cloud Project ID registered for Earth Engine",
    )
    args = parser.parse_args()

    auth = check_gee_auth(project_id=args.project)
    if not auth["test_query_passed"]:
        print("[AUTH REQUIRED] Google Earth Engine is not yet authenticated or project ID is invalid:")
        print(json.dumps(auth, indent=2))
        sys.exit(2)

    os.makedirs(GEE_EXPORT_DIR, exist_ok=True)
    print(f"[START] Connected to Google Earth Engine (Project: {auth['project_id']}, ee v{ee.__version__})")

    ts_csv, ts_rows = extract_132m_timeseries_direct()
    apr_csv, sc6_csv, apr_rows, sc6_rows = extract_2050_grid_and_6scenarios_direct()

    # Compute summary QC statistics
    sst_vals = [float(r["sst_c"]) for r in ts_rows if r["sst_c"] != ""]
    chla_vals = [float(r["chla_mg_m3"]) for r in ts_rows if r["chla_mg_m3"] != ""]
    ekman_vals = [float(r["ekman_mey_m2s"]) for r in ts_rows if r["ekman_mey_m2s"] != ""]
    apr_depths = [float(r["depth_m"]) for r in apr_rows]
    shark_cells = sum(1 for r in apr_rows if int(r["shark_bathy_overlap_flag"]) == 1)

    manifest = {
        "pipeline": "SEACAST PRIGI Phase 4.2 Google Earth Engine Spatial Pipeline",
        "run_id": f"GEE-P42-{datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ')}",
        "executed_at": datetime.datetime.now(datetime.timezone.utc).isoformat(),
        "ee_version": ee.__version__,
        "gcp_project_id": auth["project_id"],
        "datasets_used": GEE_DATASETS,
        "sub_aoi": SUB_AOI,
        "qc_summary": {
            "timeseries_months_extracted": len(ts_rows),
            "timeseries_valid_sst_months": len(sst_vals),
            "timeseries_valid_chla_months": len(chla_vals),
            "sst_132m_min_max_mean_c": [
                round(min(sst_vals), 3),
                round(max(sst_vals), 3),
                round(sum(sst_vals) / len(sst_vals), 3),
            ],
            "chla_132m_min_max_mean_mg_m3": [
                round(min(chla_vals), 3),
                round(max(chla_vals), 3),
                round(sum(chla_vals) / len(chla_vals), 3),
            ],
            "ekman_mey_132m_min_max_mean_m2s": [
                round(min(ekman_vals), 3),
                round(max(ekman_vals), 3),
                round(sum(ekman_vals) / len(ekman_vals), 3),
            ],
            "grid_cells_extracted": len(apr_rows),
            "scenario_rows_extracted": len(sc6_rows),
            "depth_m_min_max": [round(min(apr_depths), 1), round(max(apr_depths), 1)],
            "shark_shelf_slope_overlap_cells_200_1000m": shark_cells,
        },
        "files": [
            {
                "path": "data/gee/exports/gee_ocean_monthly_132m_v2.csv",
                "rows": len(ts_rows),
                "bytes": os.path.getsize(ts_csv),
                "sha256": sha256_of_file(ts_csv),
            },
            {
                "path": "data/gee/exports/gee_grid_2050_april_2022_v2.csv",
                "rows": len(apr_rows),
                "bytes": os.path.getsize(apr_csv),
                "sha256": sha256_of_file(apr_csv),
            },
            {
                "path": "data/gee/exports/gee_predictor_stack_6scenarios_2050cells_v2.csv",
                "rows": len(sc6_rows),
                "bytes": os.path.getsize(sc6_csv),
                "sha256": sha256_of_file(sc6_csv),
            },
        ],
    }

    manifest_path = os.path.join(GEE_EXPORT_DIR, "gee_export_manifest_v2.json")
    with open(manifest_path, "w", encoding="utf-8") as f:
        json.dump(manifest, f, indent=2)
    print(f"[SUCCESS] GEE Export Manifest saved to {manifest_path}")


if __name__ == "__main__":
    main()
