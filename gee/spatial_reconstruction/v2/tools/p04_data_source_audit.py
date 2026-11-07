# -*- coding: utf-8 -*-
"""
Phase 0.4 - Independent Data Source Audit executed against Google Earth Engine (project seca-513505).

Every check is computed server-side and the raw responses are stored in DATA_SOURCE_METADATA.json.
Nothing here downloads full datasets. Checks:
  - existence, first/last image date, band names, band data types, native nominal scale
  - monthly image counts for all 132 months 2015-01..2025-12 (missing months detected, never filled)
  - empirical scale-factor check (raw values sampled over the AOI)
  - valid-pixel fraction over the WPP 573 AOI (simplified GEE geometry) for selected/all months
  - sign convention for ETOPO1 (known land / trench locations)
  - ocean coverage of ERA5-Land over the marine AOI
  - availability probes for alternative collection IDs (recorded as found / not found)
"""
import json
import os
import sys
import datetime
import traceback
import ee

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "..", "..", ".."))
OUT = os.path.join(ROOT, "reports", "spatial_reconstruction", "phase_0_4")
os.makedirs(OUT, exist_ok=True)
PROJECT = "seca-513505"
ee.Initialize(project=PROJECT)

with open(os.path.join(ROOT, "data", "spatial_reconstruction", "aoi_qc", "AOI_WPP573_SIMPLIFIED_0p01deg_FOR_GEE.geojson"), encoding="utf-8") as f:
    AOI = ee.Geometry(json.load(f)["features"][0]["geometry"], "EPSG:4326", False)

MONTHS = [(y, m) for y in range(2015, 2026) for m in range(1, 13)]
START, END = "2015-01-01", "2026-01-01"
R = {"project": PROJECT, "ee_version": ee.__version__, "executed_at_utc": datetime.datetime.now(datetime.timezone.utc).isoformat(),
     "aoi_geometry_used": "data/spatial_reconstruction/aoi_qc/AOI_WPP573_SIMPLIFIED_0p01deg_FOR_GEE.geojson", "datasets": {}, "probes": {}}


def safe(fn, *a, **k):
    try:
        return fn(*a, **k)
    except Exception as e:
        return {"error": f"{type(e).__name__}: {str(e)[:300]}"}


def monthly_counts(col):
    seq = ee.List([ee.Date.fromYMD(y, m, 1).millis() for (y, m) in MONTHS])
    counts = seq.map(lambda t: col.filterDate(ee.Date(t), ee.Date(t).advance(1, "month")).size())
    vals = counts.getInfo()
    out = {f"{y:04d}-{m:02d}": v for (y, m), v in zip(MONTHS, vals)}
    return {"counts": out, "months_with_zero_images": [k for k, v in out.items() if v == 0],
            "min": min(vals), "max": max(vals), "total": sum(vals)}


def col_basic(cid, filt_bounds=False):
    col = ee.ImageCollection(cid)
    c2 = col.filterBounds(AOI) if filt_bounds else col
    first = c2.sort("system:time_start", True).first()
    last = c2.sort("system:time_start", False).first()
    n_images = col.filterDate(START, END).filterBounds(AOI).size() if filt_bounds else col.filterDate(START, END).size()
    info = ee.Dictionary({
        "first": ee.Date(first.get("system:time_start")).format("YYYY-MM-dd'T'HH:mm"),
        "last": ee.Date(last.get("system:time_start")).format("YYYY-MM-dd'T'HH:mm"),
        "bands": first.bandNames(),
        "band_types": first.bandTypes(),
        "nominal_scale_m_band0": first.select(0).projection().nominalScale(),
        "crs_band0": first.select(0).projection().crs(),
        "study_period_images": n_images,
        "first_image_properties_keys": first.propertyNames(),
    }).getInfo()
    return info


def valid_fraction(img, band, scale):
    """Fraction of AOI pixels (at `scale`) with a valid (unmasked) value."""
    m = img.select(band).mask().gt(0).rename("v")
    d = ee.Image.cat([m, ee.Image(1).rename("n")]).reduceRegion(
        reducer=ee.Reducer.sum(), geometry=AOI, scale=scale, maxPixels=1e9, bestEffort=False)
    return ee.Number(d.get("v")).divide(ee.Number(d.get("n")))


def raw_stats(img, band, scale):
    return img.select(band).reduceRegion(
        reducer=ee.Reducer.minMax().combine(ee.Reducer.mean(), sharedInputs=True),
        geometry=AOI, scale=scale, maxPixels=1e9).getInfo()


def run(name, fn):
    print(f"[0.4] {name} ...", flush=True)
    t = datetime.datetime.now()
    R["datasets"][name] = safe(fn)
    R["datasets"][name]["_elapsed_s"] = (datetime.datetime.now() - t).total_seconds() if isinstance(R["datasets"][name], dict) else None
    with open(os.path.join(OUT, "DATA_SOURCE_METADATA.json"), "w", encoding="utf-8") as f:
        json.dump(R, f, indent=2, default=str)


def a_oisst():
    cid = "NOAA/CDR/OISST/V2_1"
    col = ee.ImageCollection(cid)
    d = {"collection_id": cid, "basic": col_basic(cid), "monthly_counts": monthly_counts(col.filterDate(START, END))}
    img = col.filterDate("2020-07-15", "2020-07-16").first()
    d["raw_sample_2020-07-15_sst_anom_err"] = {b: raw_stats(img, b, 27830) for b in ["sst", "anom", "err", "ice"]}
    d["valid_fraction_sst_2020-07-15_at_27830m"] = valid_fraction(img, "sst", 27830).getInfo()
    return d


def a_modis():
    cid = "NASA/OCEANDATA/MODIS-Aqua/L3SMI"
    col = ee.ImageCollection(cid)
    d = {"collection_id": cid, "basic": col_basic(cid), "monthly_counts": monthly_counts(col.filterDate(START, END))}
    img = col.filterDate("2020-07-01", "2020-08-01").median()
    d["raw_sample_2020-07_median"] = {b: raw_stats(img, b, 9260) for b in ["chlor_a", "sst"]}
    # Valid (cloud-free, unflagged) fraction sampled across seasons and years (Jan=peak wet/cloud, Jul=peak upwelling/dry)
    sample_months = [(y, m) for y in range(2015, 2026) for m in [1, 7]]
    def vf(ym):
        y, m = ym
        c = col.filterDate(ee.Date.fromYMD(y, m, 1), ee.Date.fromYMD(y, m, 1).advance(1, "month")).select("chlor_a")
        return ee.Algorithms.If(c.size().gt(0), valid_fraction(c.mean(), "chlor_a", 15000), 0.0)
    vals = [vf(ym).getInfo() for ym in sample_months]
    d["seasonal_sample_composite_valid_fraction_chlor_a"] = {f"{y:04d}-{m:02d}": v for (y, m), v in zip(sample_months, vals)}
    days = ["2020-01-15", "2020-07-15", "2023-01-15", "2023-07-15"]
    def dvf(dt):
        c = col.filterDate(ee.Date(dt), ee.Date(dt).advance(1, "day")).select("chlor_a")
        return ee.Algorithms.If(c.size().gt(0), valid_fraction(c.first(), "chlor_a", 15000), 0.0).getInfo()
    d["daily_valid_fraction_examples"] = {dt: dvf(dt) for dt in days}
    return d


def a_cfsv2():
    cid = "NOAA/CFSV2/FOR6H_HARMONIZED"
    col = ee.ImageCollection(cid)
    b = col_basic(cid)
    d = {"collection_id": cid, "basic": {k: (v if k != "bands" else [x for x in v if "wind" in x.lower()] + [f"... {len(v)} bands total"]) for k, v in b.items() if k != "band_types"},
         "monthly_counts": monthly_counts(col.filterDate(START, END))}
    uv = ["u-component_of_wind_height_above_ground", "v-component_of_wind_height_above_ground"]
    d["wind_band_types"] = {k: v for k, v in b["band_types"].items() if k in uv}
    img = col.filterDate("2020-07-15", "2020-07-16").first()
    d["raw_sample_2020-07-15T00_u_v"] = {x: raw_stats(img, x, 22264) for x in uv}
    # Min and max across entire 11-year study period over AOI
    min_img = col.filterDate(START, END).select(uv).reduce(ee.Reducer.min())
    max_img = col.filterDate(START, END).select(uv).reduce(ee.Reducer.max())
    d["study_period_min_wind_components_m_s"] = min_img.reduceRegion(
        reducer=ee.Reducer.min(), geometry=AOI, scale=25000, maxPixels=1e9).getInfo()
    d["study_period_max_wind_components_m_s"] = max_img.reduceRegion(
        reducer=ee.Reducer.max(), geometry=AOI, scale=25000, maxPixels=1e9).getInfo()
    return d


def a_era5land():
    cid = "ECMWF/ERA5_LAND/MONTHLY_AGGR"
    col = ee.ImageCollection(cid)
    d = {"collection_id": cid, "basic": {k: v for k, v in col_basic(cid).items() if k not in ("band_types",)}}
    d["basic"]["bands"] = [x for x in d["basic"]["bands"] if "wind" in x] + ["..."]
    img = col.filterDate("2020-07-01", "2020-08-01").first()
    d["valid_fraction_u10_over_marine_AOI_2020-07_at_11132m"] = valid_fraction(img, "u_component_of_wind_10m", 11132).getInfo()
    for alt in ["ECMWF/ERA5/MONTHLY", "ECMWF/ERA5/DAILY"]:
        d[f"alternative_{alt}"] = safe(lambda a=alt: ee.Dictionary({"last": ee.Date(ee.ImageCollection(a).sort("system:time_start", False).first().get("system:time_start")).format("YYYY-MM-dd")}).getInfo())
    return d


def a_hycom():
    cid = "HYCOM/sea_water_velocity"
    col = ee.ImageCollection(cid)
    b = col_basic(cid)
    d = {"collection_id": cid, "basic": b, "monthly_counts": monthly_counts(col.filterDate(START, END))}
    img = col.filterDate("2020-07-15", "2020-07-16").first()
    d["raw_sample_2020-07-15_surface"] = {x: raw_stats(img, x, 8905) for x in b["bands"][:2]}
    d["valid_fraction_band0_2020-07-15_at_8905m"] = valid_fraction(img, b["bands"][0], 8905).getInfo()
    return d


def a_etopo():
    cid = "NOAA/NGDC/ETOPO1"
    img = ee.Image(cid)
    d = {"collection_id": cid, "bands": img.bandNames().getInfo(), "band_types": img.bandTypes().getInfo(),
         "nominal_scale_m": img.projection().nominalScale().getInfo()}
    pts = {"Semeru_summit_112.922E_8.108S": [112.922, -8.108], "Java_Trench_110.5E_10.6S": [110.5, -10.6], "Prigi_Bay_111.73E_8.30S": [111.73, -8.30],
           "Open_ocean_115E_12S": [115.0, -12.0]}
    fc = ee.FeatureCollection([ee.Feature(ee.Geometry.Point(v), {"name": k}) for k, v in pts.items()])
    s = img.select("bedrock").sampleRegions(fc, scale=1855).getInfo()
    d["sign_convention_samples_bedrock_m"] = {f["properties"]["name"]: f["properties"].get("bedrock") for f in s["features"]}
    d["aoi_bedrock_stats_m"] = raw_stats(img, "bedrock", 5000)
    return d


def probes():
    cands = ["NASA/OCEANDATA/VIIRS-SNPP/L3SMI", "NASA/OCEANDATA/SNPP-VIIRS/L3SMI", "NASA/OCEANDATA/VIIRS/L3SMI",
             "NASA/OCEANDATA/NOAA20-VIIRS/L3SMI", "JAXA/GCOM-C/L3/OCEAN/CHLA/V3", "JAXA/GCOM-C/L3/OCEAN/CHLA/V2",
             "HYCOM/sea_temp_salinity", "projects/sat-io/open-datasets/gebco/gebco_grid",
             "projects/sat-io/open-datasets/GEBCO/GEBCO_2023", "ECMWF/ERA5/HOURLY", "NOAA/NWW3/GLOBAL"]
    out = {}
    for c in cands:
        def chk(c=c):
            try:
                col = ee.ImageCollection(c)
                n = col.size().getInfo()
                last = ee.Date(col.sort("system:time_start", False).first().get("system:time_start")).format("YYYY-MM-dd").getInfo()
                first = ee.Date(col.sort("system:time_start", True).first().get("system:time_start")).format("YYYY-MM-dd").getInfo()
                bands = col.first().bandNames().getInfo()
                return {"found": True, "kind": "ImageCollection", "size": n, "first": first, "last": last, "bands": bands[:12]}
            except Exception as e1:
                try:
                    bands = ee.Image(c).bandNames().getInfo()
                    return {"found": True, "kind": "Image", "bands": bands[:12]}
                except Exception as e2:
                    return {"found": False, "error": str(e1)[:160]}
        print(f"[0.4] probe {c}", flush=True)
        out[c] = chk()
    R["probes"] = out
    try:
        import importlib.util
        R["copernicusmarine_python_package_installed"] = importlib.util.find_spec("copernicusmarine") is not None
    except Exception:
        R["copernicusmarine_python_package_installed"] = False


if __name__ == "__main__":
    only = sys.argv[1:] or ["oisst", "modis", "cfsv2", "era5land", "hycom", "etopo", "probes"]
    fns = {"oisst": a_oisst, "modis": a_modis, "cfsv2": a_cfsv2, "era5land": a_era5land, "hycom": a_hycom, "etopo": a_etopo}
    p = os.path.join(OUT, "DATA_SOURCE_METADATA.json")
    if os.path.exists(p):
        with open(p, encoding="utf-8") as f:
            prev = json.load(f)
        R["datasets"].update(prev.get("datasets", {}))
        R["probes"] = prev.get("probes", {})
    for k in only:
        if k == "probes":
            probes()
            with open(p, "w", encoding="utf-8") as f:
                json.dump(R, f, indent=2, default=str)
        else:
            run(k, fns[k])
    print("[0.4] done")

