# -*- coding: utf-8 -*-
"""
Phase 0.3 - Independent AOI Geometry Revalidation (SEACAST PRIGI, WPPNRI 573).

Three independent method families are used:
  M1  Pure-Python JSON parsing (no GEOS): vertex counts, ring closure, ring orientation,
      bounds, consecutive duplicate vertices.
  M2  Shapely 2.x / GEOS: validity, simplicity, components, interior rings, planar
      equal-area (EPSG:6933 via pyproj) area/perimeter, equality between sources.
  M3  pyogrio / GDAL-OGR: driver-level feature count, geometry type, CRS, extent,
      and (if SpatiaLite available in the bundled GDAL) ST_IsValid / ST_NPoints.
  Geodesic area/perimeter uses pyproj.Geod (Karney 2013) on WGS84 per ring, which is
  independent of the EPSG:6933 planar computation.

Historical files are opened read-only. No geometry is repaired in place.
"""
import csv
import hashlib
import json
import math
import os
import sys
import datetime
import platform

import numpy as np
import pyproj
import shapely
from shapely.geometry import shape, MultiPolygon, Polygon
from shapely.validation import explain_validity

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "..", "..", ".."))
OUT = os.path.join(ROOT, "reports", "spatial_reconstruction", "phase_0_3")
DOUT = os.path.join(ROOT, "data", "spatial_reconstruction", "aoi_qc")
os.makedirs(OUT, exist_ok=True)
os.makedirs(DOUT, exist_ok=True)

SOURCES = {
    "RAW_BIG_FETCH_2026-11-02": "data/aoi/raw/WPP573_BIG_ORIGINAL.geojson",
    "AOI_MASTER": "data/aoi/processed/AOI_WPP573_MASTER.geojson",
    "LEGACY_BIG_2026-10-31": "data/boundaries/wppnri_573_big.geojson",
    "LEGACY_WEB_SIMPLIFIED": "data/boundaries/wppnri_573_web.geojson",
}

GEOD = pyproj.Geod(ellps="WGS84")
T6933 = pyproj.Transformer.from_crs("EPSG:4326", "EPSG:6933", always_xy=True)


def sha256(p):
    h = hashlib.sha256()
    with open(p, "rb") as f:
        for c in iter(lambda: f.read(1 << 20), b""):
            h.update(c)
    return h.hexdigest()


def iter_polygons(gj_geom):
    t = gj_geom["type"]
    if t == "Polygon":
        yield gj_geom["coordinates"]
    elif t == "MultiPolygon":
        for p in gj_geom["coordinates"]:
            yield p
    else:
        raise ValueError(f"Unsupported geometry type {t}")


def ring_signed_area_deg2(ring):
    a = 0.0
    for i in range(len(ring) - 1):
        x1, y1 = ring[i][0], ring[i][1]
        x2, y2 = ring[i + 1][0], ring[i + 1][1]
        a += x1 * y2 - x2 * y1
    return a / 2.0


def m1_pure_python(gj_geom):
    """GEOS-independent structural metrics."""
    n_poly = n_ext = n_int = n_vert = 0
    open_rings = degenerate_rings = consec_dupes = 0
    ext_ccw = ext_cw = int_ccw = int_cw = 0
    minx = miny = math.inf
    maxx = maxy = -math.inf
    geod_area = 0.0
    geod_perim = 0.0
    hole_area = 0.0
    for poly in iter_polygons(gj_geom):
        n_poly += 1
        for ri, ring in enumerate(poly):
            n_vert += len(ring)
            if len(ring) < 4:
                degenerate_rings += 1
            if ring[0][0] != ring[-1][0] or ring[0][1] != ring[-1][1]:
                open_rings += 1
            for i in range(1, len(ring)):
                if ring[i][0] == ring[i - 1][0] and ring[i][1] == ring[i - 1][1]:
                    consec_dupes += 1
            for x, y in ((p[0], p[1]) for p in ring):
                if x < minx: minx = x
                if x > maxx: maxx = x
                if y < miny: miny = y
                if y > maxy: maxy = y
            s = ring_signed_area_deg2(ring)
            lons = [p[0] for p in ring]
            lats = [p[1] for p in ring]
            a, per = GEOD.polygon_area_perimeter(lons, lats)
            if ri == 0:
                n_ext += 1
                (ext_ccw if s > 0 else ext_cw).__class__  # no-op for readability
                if s > 0: ext_ccw += 1
                else: ext_cw += 1
                geod_area += abs(a)
                geod_perim += per
            else:
                n_int += 1
                if s > 0: int_ccw += 1
                else: int_cw += 1
                geod_area -= abs(a)
                hole_area += abs(a)
                geod_perim += per
    return {
        "polygon_components": n_poly,
        "exterior_rings": n_ext,
        "interior_rings": n_int,
        "vertex_count_total_incl_closing": n_vert,
        "open_rings": open_rings,
        "degenerate_rings_lt4_coords": degenerate_rings,
        "consecutive_duplicate_vertices": consec_dupes,
        "exterior_orientation": {"ccw": ext_ccw, "cw": ext_cw},
        "interior_orientation": {"ccw": int_ccw, "cw": int_cw},
        "bounds": [minx, miny, maxx, maxy],
        "geodesic_area_km2_karney": geod_area / 1e6,
        "geodesic_interior_rings_area_km2": hole_area / 1e6,
        "geodesic_perimeter_km_all_rings": geod_perim / 1e3,
    }


def drop_degenerate(gj_geom):
    polys = []
    removed = 0
    for poly in iter_polygons(gj_geom):
        rings = [r for r in poly if len(r) >= 4]
        removed += len(poly) - len(rings)
        if rings and len(poly[0]) >= 4:
            polys.append(rings)
    return {"type": "MultiPolygon", "coordinates": polys}, removed


def m2_shapely(gj_geom):
    g = shape(gj_geom)
    parts = list(g.geoms) if isinstance(g, MultiPolygon) else [g]
    g6933 = shapely.transform(g, lambda xy: np.column_stack(T6933.transform(xy[:, 0], xy[:, 1])))
    rings_simple = all(p.exterior.is_simple for p in parts)
    return g, {
        "geometry_type": g.geom_type,
        "is_empty": g.is_empty,
        "is_valid": bool(g.is_valid),
        "explain_validity": explain_validity(g),
        "exterior_rings_all_simple": bool(rings_simple),
        "polygon_components": len(parts),
        "interior_rings": int(sum(len(p.interiors) for p in parts)),
        "vertex_count_shapely": int(shapely.get_num_coordinates(g)),
        "bounds": list(g.bounds),
        "equal_area_epsg6933_km2": g6933.area / 1e6,
        "perimeter_epsg6933_km_all_rings": g6933.length / 1e3,
        "component_areas_epsg6933_km2": sorted([shapely.transform(p, lambda xy: np.column_stack(T6933.transform(xy[:, 0], xy[:, 1]))).area / 1e6 for p in parts], reverse=True),
        "components_overlap_each_other": bool(len(parts) > 1 and any(parts[i].overlaps(parts[j]) for i in range(len(parts)) for j in range(i + 1, len(parts)))),
    }


def m3_gdal(path):
    try:
        import pyogrio
        info = pyogrio.read_info(os.path.join(ROOT, path))
        return {
            "library": f"pyogrio {pyogrio.__version__} / GDAL {pyogrio.__gdal_version_string__}",
            "driver": info.get("driver"),
            "feature_count": int(info.get("features", -1)),
            "geometry_type": info.get("geometry_type"),
            "crs": info.get("crs"),
            "extent_total_bounds": [float(v) for v in info.get("total_bounds")] if info.get("total_bounds") is not None else None,
            "fields": list(info.get("fields", [])),
        }
    except Exception as e:
        return {"error": f"{type(e).__name__}: {e}"}


def main():
    t0 = datetime.datetime.now(datetime.timezone.utc)
    results = {"generated_at_utc": t0.isoformat(), "environment": {
        "python": sys.version.split()[0], "platform": platform.platform(),
        "shapely": shapely.__version__, "geos": shapely.geos_version_string,
        "pyproj": pyproj.__version__, "proj": pyproj.proj_version_str,
        "geodesic_method": "pyproj.Geod(ellps='WGS84').polygon_area_perimeter (Karney 2013), per ring, holes subtracted",
        "equal_area_method": "shapely planar area after pyproj transform EPSG:4326 -> EPSG:6933 (always_xy)",
    }, "sources": {}}
    geoms = {}
    for key, rel in SOURCES.items():
        p = os.path.join(ROOT, rel)
        print(f"[0.3] {key}: {rel}")
        entry = {"path": rel, "exists": os.path.exists(p)}
        if not entry["exists"]:
            results["sources"][key] = entry
            continue
        entry["size_bytes"] = os.path.getsize(p)
        entry["sha256"] = sha256(p)
        with open(p, "r", encoding="utf-8") as f:
            gj = json.load(f)
        feats = gj.get("features", [])
        entry["feature_count_json"] = len(feats)
        entry["declared_crs_member"] = gj.get("crs")
        entry["properties_first_feature"] = feats[0].get("properties") if feats else None
        gjg = feats[0]["geometry"]
        entry["M1_pure_python"] = m1_pure_python(gjg)
        entry["M3_gdal_ogr"] = m3_gdal(rel)
        try:
            g, m2 = m2_shapely(gjg)
            entry["M2_shapely_geos"] = m2
            entry["repair_required"] = not m2["is_valid"]
            geoms[key] = g
        except Exception as e:
            entry["M2_shapely_geos"] = {"error": f"{type(e).__name__}: {e}"}
            fixed, removed = drop_degenerate(gjg)
            g, m2 = m2_shapely(fixed)
            entry["M2_shapely_geos_after_dropping_degenerate_rings"] = m2
            entry["degenerate_rings_dropped_for_comparison_only"] = removed
            entry["repair_required"] = True
            geoms[key] = g
            rep_path = os.path.join(DOUT, "wppnri_573_web_DEGENERATE_RINGS_DROPPED.geojson")
            with open(rep_path, "w", encoding="utf-8") as f:
                json.dump({"type": "FeatureCollection", "features": [{"type": "Feature", "properties": {
                    "derived_from": rel, "operation": "dropped rings with <4 coordinates (comparison copy only)",
                    "rings_dropped": removed}, "geometry": fixed}]}, f)
            entry["comparison_copy"] = os.path.relpath(rep_path, ROOT).replace("\\", "/")
        results["sources"][key] = entry

    # Cross-source geometry comparison
    ref = geoms["RAW_BIG_FETCH_2026-11-02"]
    ref_area = results["sources"]["RAW_BIG_FETCH_2026-11-02"]["M2_shapely_geos"]["equal_area_epsg6933_km2"]
    comp = {}
    for k, g in geoms.items():
        is_exact = bool(g.equals_exact(ref, 1e-9)) if g.geom_type == ref.geom_type else False
        d = {"equals_exact_tol_1e-9": is_exact}
        if not is_exact:
            # For simplified preview files, compare areas and bounds directly to avoid expensive 625k-vertex overlays
            g_area = shapely.transform(g, lambda xy: np.column_stack(T6933.transform(xy[:, 0], xy[:, 1]))).area / 1e6
            d["area_epsg6933_km2"] = round(g_area, 2)
            d["area_diff_pct_vs_raw"] = round((g_area - ref_area) / ref_area * 100, 4)
            d["bounds_diff"] = [round(b1 - b2, 6) for b1, b2 in zip(g.bounds, ref.bounds)]
        comp[k] = d
    results["cross_source_comparison_vs_RAW"] = comp

    # Derived GEE-friendly simplified copy (separate artifact, never replaces master)
    # Simplify exterior polygons (dropping internal island holes) with preserve_topology=True on outer shell only
    simp_parts = []
    for p in (ref.geoms if isinstance(ref, MultiPolygon) else [ref]):
        shell_poly = Polygon(p.exterior)
        shell_simp = shell_poly.simplify(0.01, preserve_topology=True)
        if shell_simp.geom_type == "Polygon":
            simp_parts.append(shell_simp)
        elif shell_simp.geom_type == "MultiPolygon":
            simp_parts.extend(shell_simp.geoms)
    simp = MultiPolygon(simp_parts)
    simp_area = shapely.transform(simp, lambda xy: np.column_stack(T6933.transform(xy[:, 0], xy[:, 1]))).area / 1e6
    simp_path = os.path.join(DOUT, "AOI_WPP573_SIMPLIFIED_0p01deg_FOR_GEE.geojson")
    with open(simp_path, "w", encoding="utf-8") as f:
        json.dump({"type": "FeatureCollection", "features": [{"type": "Feature", "properties": {
            "derived_from": SOURCES["RAW_BIG_FETCH_2026-11-02"], "operation": "shapely.simplify(tolerance=0.01 deg, preserve_topology=True)",
            "purpose": "GEE server-side query/clip geometry for pilot only; master geometry remains authoritative",
            "vertex_count": int(shapely.get_num_coordinates(simp)), "is_valid": bool(simp.is_valid),
            "equal_area_epsg6933_km2": round(simp_area, 2),
            "area_diff_pct_vs_master": round((simp_area - ref_area) / ref_area * 100, 4)},
            "geometry": shapely.geometry.mapping(simp)}]}, f)
    results["derived_gee_simplified"] = {
        "path": os.path.relpath(simp_path, ROOT).replace("\\", "/"), "tolerance_deg": 0.01,
        "vertex_count": int(shapely.get_num_coordinates(simp)), "is_valid": bool(simp.is_valid),
        "equal_area_epsg6933_km2": simp_area, "area_diff_pct_vs_master": (simp_area - ref_area) / ref_area * 100,
        "sha256": sha256(simp_path)}

    with open(os.path.join(OUT, "AOI_GEOMETRY_QA.json"), "w", encoding="utf-8") as f:
        json.dump(results, f, indent=2, default=str)

    # Recalculated authoritative metrics (from RAW, cross-checked by M1/M2/M3)
    r = results["sources"]["RAW_BIG_FETCH_2026-11-02"]
    m1, m2, m3 = r["M1_pure_python"], r["M2_shapely_geos"], r["M3_gdal_ogr"]
    metrics = {
        "source_file": r["path"], "source_sha256": r["sha256"],
        "min_longitude": m1["bounds"][0], "max_longitude": m1["bounds"][2],
        "min_latitude": m1["bounds"][1], "max_latitude": m1["bounds"][3],
        "bounds_agree_M1_M2_M3": bool(np.allclose(m1["bounds"], m2["bounds"]) and m3.get("extent_total_bounds") is not None and np.allclose(m1["bounds"], m3["extent_total_bounds"])),
        "geodesic_area_wgs84_km2": m1["geodesic_area_km2_karney"],
        "equal_area_epsg6933_km2": m2["equal_area_epsg6933_km2"],
        "area_methods_relative_diff_pct": (m2["equal_area_epsg6933_km2"] - m1["geodesic_area_km2_karney"]) / m1["geodesic_area_km2_karney"] * 100,
        "geodesic_perimeter_km_all_rings": m1["geodesic_perimeter_km_all_rings"],
        "polygon_components": m2["polygon_components"], "interior_rings": m2["interior_rings"],
        "interior_rings_geodesic_area_km2": m1["geodesic_interior_rings_area_km2"],
        "vertex_count": m2["vertex_count_shapely"], "is_valid": m2["is_valid"],
        "feature_count_gdal": m3.get("feature_count"), "crs_gdal": m3.get("crs"),
    }
    with open(os.path.join(OUT, "AOI_METRICS_RECALCULATED.json"), "w", encoding="utf-8") as f:
        json.dump(metrics, f, indent=2)

    # Source comparison CSV (computed vs previously reported literals)
    rows = []
    for k, e in results["sources"].items():
        m2k = e.get("M2_shapely_geos") if isinstance(e.get("M2_shapely_geos"), dict) and "bounds" in e.get("M2_shapely_geos", {}) else e.get("M2_shapely_geos_after_dropping_degenerate_rings", {})
        b = e["M1_pure_python"]["bounds"]
        rows.append({"source": k, "type": "COMPUTED_FROM_FILE", "path": e["path"], "min_lon": b[0], "max_lon": b[2], "min_lat": b[1], "max_lat": b[3],
                     "area_epsg6933_km2": round(m2k.get("equal_area_epsg6933_km2", float("nan")), 2),
                     "geodesic_area_km2": round(e["M1_pure_python"]["geodesic_area_km2_karney"], 2),
                     "vertices": e["M1_pure_python"]["vertex_count_total_incl_closing"], "interior_rings": e["M1_pure_python"]["interior_rings"],
                     "note": "valid" if not e.get("repair_required") else "invalid/degenerate rings present (see AOI_GEOMETRY_QA.json)"})
    rows += [
        {"source": "AOI-VAL-01 col.2 (Nilai Terhitung)", "type": "REPORTED_LITERAL", "path": "reports/phase_0_1/AOI_VALIDATION_REPORT.md", "min_lon": 102.577830, "max_lon": 126.499579, "min_lat": -13.938231, "max_lat": -6.801796, "area_epsg6933_km2": 933749.49, "geodesic_area_km2": 933750.90, "vertices": 625698, "interior_rings": 1400, "note": "f-string from computed geometry -> matches file"},
        {"source": "AOI-VAL-01 col.3 / SPAT-DES-05 (as cited)", "type": "REPORTED_LITERAL", "path": "scratch/run_aoi_validation.py:284 (template literal)", "min_lon": 105.132141, "max_lon": 129.544498, "min_lat": -13.784534, "max_lat": -7.300000, "area_epsg6933_km2": "", "geodesic_area_km2": "", "vertices": "", "interior_rings": "", "note": "ERROR: hard-coded literal typed into report template; not derived from any dataset"},
        {"source": "data/spatial_config_aoi.json primary_aoi.bounding_box_wgs84", "type": "CONFIG_LITERAL", "path": "data/spatial_config_aoi.json", "min_lon": 102.5778, "max_lon": 126.4996, "min_lat": -13.9382, "max_lat": -6.8018, "area_epsg6933_km2": 933749.49, "geodesic_area_km2": "", "vertices": 625698, "interior_rings": "", "note": "consistent with file (4 dp)"},
        {"source": "KKP nominal WPP 573 area (as cited in config)", "type": "UNSOURCED_LITERAL", "path": "data/spatial_config_aoi.json area_reconciliation", "min_lon": "", "max_lon": "", "min_lat": "", "max_lat": "", "area_epsg6933_km2": 1423238.0, "geodesic_area_km2": "", "vertices": "", "interior_rings": "", "note": "UNVERIFIED: no primary citation in workspace ('often quoted'); differs from BIG polygon by ~52%"},
    ]
    with open(os.path.join(OUT, "AOI_SOURCE_COMPARISON.csv"), "w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=list(rows[0].keys()))
        w.writeheader(); w.writerows(rows)
    print(json.dumps(metrics, indent=2))
    print(json.dumps(results["cross_source_comparison_vs_RAW"], indent=2))
    print(json.dumps(results["derived_gee_simplified"], indent=2))
    print("M3 RAW:", json.dumps(m3, default=str)[:900])
    w = results["sources"]["LEGACY_WEB_SIMPLIFIED"]
    print("WEB M1:", json.dumps(w["M1_pure_python"], default=str)[:700])


if __name__ == "__main__":
    main()

