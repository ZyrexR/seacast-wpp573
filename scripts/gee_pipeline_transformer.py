#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
SEACAST PRIGI V3 — GEE Dataset Transformer & Scientific Inference Pipeline
Transforms raw GEE exported CSVs (104-column monthly or 51-column pilot datasets)
into normalized model feature vectors, executes locked-weight scikit-learn Ensemble SDM
inference, performs DOM zone delineation, and generates production outputs.
"""

import os
import sys
import json
import argparse
import datetime
import warnings
import numpy as np
import pandas as pd
import joblib

from ingestion_manifest import write_ingestion_manifest
from model_compat import positive_class_probability

# Suppress sklearn unpickle warnings from polluting stdout
warnings.filterwarnings("ignore")

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MODELS_DIR = os.path.join(BASE_DIR, "models")
GEOJSON_PATH = os.path.join(BASE_DIR, "data", "geojson", "canonical_grid_8129_cells.geojson")
WEB_PUBLIC_DATA = os.path.join(BASE_DIR, "web", "public", "data")
STORAGE_INGEST_DIR = os.path.join(BASE_DIR, "seacast-prigi-laravel", "storage", "app", "gee_ingestion")

os.makedirs(WEB_PUBLIC_DATA, exist_ok=True)
os.makedirs(STORAGE_INGEST_DIR, exist_ok=True)

# Locked scientific ensemble baseline weights
LOCKED_ENSEMBLE_WEIGHTS = {
    "cakalang": {"maxent": 0.320, "rf": 0.355, "glm": 0.325},
    "madidihang": {"maxent": 0.295, "rf": 0.365, "glm": 0.340},
    "tongkol_krai": {"maxent": 0.332, "rf": 0.335, "glm": 0.333},
}

PRIGI_LON = 111.7275
PRIGI_LAT = -8.2833

# Physical bounds for QC validation
PHYSICAL_BOUNDS = {
    "sst": (18.0, 34.0),
    "grad_sst": (0.0, 1.0),
    "chla": (0.01, 20.0),
    "current_velocity": (0.0, 3.0),
    "wind_speed": (0.0, 35.0),
    "wind_stress": (0.0, 1.5),
    "ekman_upwelling_velocity": (-1e-3, 1e-3),
    "depth": (1.0, 8500.0),
    "dist_port_km": (0.0, 1500.0)
}


def load_canonical_grid():
    """Loads canonical grid features (8,129 cells) from GeoJSON"""
    if not os.path.exists(GEOJSON_PATH):
        raise FileNotFoundError(f"Canonical grid geojson not found at {GEOJSON_PATH}")

    with open(GEOJSON_PATH, "r") as f:
        gj = json.load(f)

    grid_map = {}
    for feat in gj["features"]:
        props = feat["properties"]
        cid = props["cell_id"]
        coords = feat["geometry"]["coordinates"][0]
        lons = [c[0] for c in coords]
        lats = [c[1] for c in coords]
        grid_map[cid] = {
            "cell_id": cid,
            "cell_class": props.get("cell_class", "PURE_OCEAN"),
            "center_lon": float(np.mean(lons[:4])),
            "center_lat": float(np.mean(lats[:4])),
            "depth_m": float(props.get("depth_m", 1000.0)),
            "dist_nm": float(props.get("dist_nm", 100.0)),
            "dist_km": float(props.get("dist_nm", 100.0)) * 1.852
        }
    return grid_map


def parse_and_validate_gee_csv(csv_path, grid_map):
    """
    Parses and standardizes GEE CSV (supporting 104-col and 51-col formats),
    validates cell count, and performs scientific fallback imputation for nulls.
    """
    if not os.path.exists(csv_path):
        raise FileNotFoundError(f"GEE CSV file not found: {csv_path}")

    df_raw = pd.read_csv(csv_path)
    total_rows = len(df_raw)

    if total_rows != 8129:
        raise ValueError(f"Invalid canonical cell count: Expected exactly 8,129 cells, found {total_rows}.")

    cols = set(df_raw.columns)

    # Standardize column mapping based on schema variant
    df_std = pd.DataFrame()
    df_std["cell_id"] = df_raw["cell_id"]

    # 1. Coordinate & static spatial properties
    if "lon_center" in cols and "lat_center" in cols:
        df_std["center_lon"] = df_raw["lon_center"].astype(float)
        df_std["center_lat"] = df_raw["lat_center"].astype(float)
    else:
        df_std["center_lon"] = df_std["cell_id"].map(lambda cid: grid_map.get(cid, {}).get("center_lon", 112.0))
        df_std["center_lat"] = df_std["cell_id"].map(lambda cid: grid_map.get(cid, {}).get("center_lat", -10.0))

    # Static depth and distance to Prigi
    if "depth_m_mean" in cols:
        df_std["depth"] = df_raw["depth_m_mean"].fillna(
            df_std["cell_id"].map(lambda cid: grid_map.get(cid, {}).get("depth_m", 1000.0))
        ).abs()
    elif "depth_m" in cols:
        df_std["depth"] = df_raw["depth_m"].fillna(
            df_std["cell_id"].map(lambda cid: grid_map.get(cid, {}).get("depth_m", 1000.0))
        ).abs()
    else:
        df_std["depth"] = df_std["cell_id"].map(lambda cid: grid_map.get(cid, {}).get("depth_m", 1000.0))

    if "dist_prigi_harbor_km" in cols:
        df_std["dist_port_km"] = df_raw["dist_prigi_harbor_km"].astype(float)
        df_std["dist_port_nm"] = df_std["dist_port_km"] / 1.852
    else:
        df_std["dist_port_nm"] = df_std["cell_id"].map(lambda cid: grid_map.get(cid, {}).get("dist_nm", 100.0))
        df_std["dist_port_km"] = df_std["dist_port_nm"] * 1.852

    # 2. SST & Thermal Gradients
    raw_sst = None
    if "sst_mean_c_mean" in cols:
        raw_sst = df_raw["sst_mean_c_mean"]
    elif "sst_c" in cols:
        raw_sst = df_raw["sst_c"]
    elif "sst_mean_c" in cols:
        raw_sst = df_raw["sst_mean_c"]
    elif "sst" in cols:
        raw_sst = df_raw["sst"]

    sst_median = float(raw_sst.median()) if (raw_sst is not None and not np.isnan(raw_sst.median())) else 28.5
    df_std["sst"] = raw_sst.fillna(sst_median) if raw_sst is not None else sst_median

    # Grad SST
    raw_grad = None
    if "sst_gradient_deg_km_mean" in cols:
        raw_grad = df_raw["sst_gradient_deg_km_mean"]
    elif "sst_gradient_deg_km" in cols:
        raw_grad = df_raw["sst_gradient_deg_km"]
    elif "grad_sst" in cols:
        raw_grad = df_raw["grad_sst"]

    grad_median = float(raw_grad.median()) if (raw_grad is not None and not np.isnan(raw_grad.median())) else 0.015
    df_std["grad_sst"] = raw_grad.fillna(grad_median) if raw_grad is not None else grad_median

    # Front indicator
    if "thermal_front_flag_mean" in cols:
        df_std["front_indicator"] = df_raw["thermal_front_flag_mean"].fillna(0.0).clip(0.0, 1.0)
    elif "thermal_front_flag" in cols:
        df_std["front_indicator"] = df_raw["thermal_front_flag"].fillna(0.0).clip(0.0, 1.0)
    elif "upwelling_favorable_flag" in cols:
        df_std["front_indicator"] = df_raw["upwelling_favorable_flag"].fillna(0.0).clip(0.0, 1.0)
    else:
        df_std["front_indicator"] = (df_std["grad_sst"] >= 0.035).astype(float)

    # 3. Chlorophyll-a
    raw_chla = None
    if "chla_mg_m3_mean" in cols:
        raw_chla = df_raw["chla_mg_m3_mean"]
    elif "chla_mg_m3" in cols:
        raw_chla = df_raw["chla_mg_m3"]
    elif "chla" in cols:
        raw_chla = df_raw["chla"]

    chla_median = float(raw_chla.median()) if (raw_chla is not None and not np.isnan(raw_chla.median())) else 0.18
    df_std["chla"] = raw_chla.fillna(chla_median) if raw_chla is not None else chla_median

    # 4. Ocean Currents
    raw_curr = None
    if "current_speed_m_s_mean" in cols:
        raw_curr = df_raw["current_speed_m_s_mean"]
    elif "current_speed_m_s" in cols:
        raw_curr = df_raw["current_speed_m_s"]
    elif "current_velocity" in cols:
        raw_curr = df_raw["current_velocity"]

    curr_median = float(raw_curr.median()) if (raw_curr is not None and not np.isnan(raw_curr.median())) else 0.25
    df_std["current_velocity"] = raw_curr.fillna(curr_median) if raw_curr is not None else curr_median

    # 5. Wind, Wind Stress, and Ekman
    raw_wind = None
    if "wind_speed_mean_m_s_mean" in cols:
        raw_wind = df_raw["wind_speed_mean_m_s_mean"]
    elif "wind_speed_m_s" in cols:
        raw_wind = df_raw["wind_speed_m_s"]
    elif "wind_speed" in cols:
        raw_wind = df_raw["wind_speed"]

    wind_median = float(raw_wind.median()) if (raw_wind is not None and not np.isnan(raw_wind.median())) else 4.5
    df_std["wind_speed"] = raw_wind.fillna(wind_median) if raw_wind is not None else wind_median

    raw_stress = None
    if "wind_stress_tau_mag_mean" in cols:
        raw_stress = df_raw["wind_stress_tau_mag_mean"]
    elif "wind_stress_tau_mag" in cols:
        raw_stress = df_raw["wind_stress_tau_mag"]
    elif "wind_stress" in cols:
        raw_stress = df_raw["wind_stress"]

    stress_median = float(raw_stress.median()) if (raw_stress is not None and not np.isnan(raw_stress.median())) else (wind_median * 0.008)
    df_std["wind_stress"] = raw_stress.fillna(stress_median) if raw_stress is not None else stress_median

    raw_ekman = None
    if "ekman_w_e_m_s_mean" in cols:
        raw_ekman = df_raw["ekman_w_e_m_s_mean"]
    elif "ekman_w_e_m_s" in cols:
        raw_ekman = df_raw["ekman_w_e_m_s"]
    elif "ekman_upwelling_velocity" in cols:
        raw_ekman = df_raw["ekman_upwelling_velocity"]

    df_std["ekman_upwelling_velocity"] = raw_ekman.fillna(0.0) if raw_ekman is not None else 0.0

    # 6. Physical bounds clipping
    for var_name, (b_min, b_max) in PHYSICAL_BOUNDS.items():
        if var_name in df_std.columns:
            df_std[var_name] = np.clip(df_std[var_name], b_min, b_max)

    # 7. Wave height (from wind-wave formula or dataset)
    if "wave_height_m" in cols:
        df_std["wave_height_m"] = df_raw["wave_height_m"].fillna(1.5).round(1)
    else:
        # Approximate significant wave height from wind speed: Hs ≈ 0.025 * U^1.5 + 0.8
        df_std["wave_height_m"] = np.round(np.clip(0.025 * (df_std["wind_speed"] ** 1.5) + 0.8 + (df_std["dist_port_nm"] / 200.0), 0.5, 4.5), 1)

    return df_std


def run_pipeline(input_csv, target_date=None, limit=0, dry_run=False, output_json=None, output_csv=None, persist_db=False, requested_date=None):
    """Executes feature preparation, model inference, DOM classification, and persistence"""
    start_time = datetime.datetime.now()

    if target_date is None:
        # Attempt to infer date from CSV filename (e.g. SEACAST_PRIGI_GRID_8129_2015_01.csv -> 2015-01-01)
        base_name = os.path.basename(input_csv)
        import re
        match = re.search(r"(\d{4})_(\d{2})", base_name)
        if match:
            target_date = f"{match.group(1)}-{match.group(2)}-01"
        else:
            target_date = datetime.date.today().strftime("%Y-%m-%d")

    grid_map = load_canonical_grid()
    df_std = parse_and_validate_gee_csv(input_csv, grid_map)

    # 1. Feature Vector Assembly
    feature_cols = [
        "sst", "grad_sst", "front_indicator", "chla", "current_velocity",
        "wind_speed", "wind_stress", "ekman_upwelling_velocity", "depth", "dist_port_km"
    ]
    X_raw = df_std[feature_cols].values

    # 2. Scaler Transformation
    scaler_path = os.path.join(MODELS_DIR, "scaler_oceanographic.joblib")
    if not os.path.exists(scaler_path):
        raise FileNotFoundError(f"Scaler artifact missing at {scaler_path}")
    scaler = joblib.load(scaler_path)
    X_scaled = scaler.transform(X_raw)
    X_scaled = np.nan_to_num(X_scaled, nan=0.0)

    # 3. Model Inference (Locked Baseline Weights)
    for sp in ["cakalang", "madidihang", "tongkol_krai"]:
        model_path = os.path.join(MODELS_DIR, f"{sp}_ensemble_model.joblib")
        if not os.path.exists(model_path):
            raise FileNotFoundError(f"Model artifact missing at {model_path}")

        bundle = joblib.load(model_path)
        w = LOCKED_ENSEMBLE_WEIGHTS[sp]

        p_maxent = positive_class_probability(bundle["maxent"], X_scaled)
        p_rf = positive_class_probability(bundle["rf"], X_scaled)
        p_glm = positive_class_probability(bundle["glm"], X_scaled)

        raw_hsi = w["maxent"] * p_maxent + w["rf"] * p_rf + w["glm"] * p_glm
        min_v, max_v = raw_hsi.min(), raw_hsi.max()
        hsi_norm = (raw_hsi - min_v) / (max_v - min_v) if max_v > min_v else raw_hsi
        target_col = "hsi_tongkol" if sp == "tongkol_krai" else f"hsi_{sp}"
        df_std[target_col] = np.round(hsi_norm, 3)

    # Hotspot threshold
    df_std["is_hotspot"] = (df_std["hsi_cakalang"] >= 0.70) | (df_std["hsi_madidihang"] >= 0.70)

    # 4. DOM Zones (Waterfall Priority: Z4 -> Z5 -> Z3 -> Z1 -> Z2 -> GEN)
    is_inshore = (df_std["dist_port_nm"] <= 12.0)
    is_offshore = (df_std["dist_port_nm"] > 12.0) & (df_std["dist_port_nm"] <= 60.0)
    has_tuna = (df_std["hsi_cakalang"] >= 0.55) | (df_std["hsi_madidihang"] >= 0.55)
    is_feeding = (df_std["chla"] >= 0.40) & (df_std["grad_sst"] >= 0.035)
    is_nursery = (df_std["depth"] <= 150.0) & (df_std["dist_port_nm"] <= 15.0)
    is_shelf_break = (df_std["depth"] >= 150.0) & (df_std["depth"] <= 1200.0)
    is_bycatch_risk = is_shelf_break & (df_std["sst"] <= 26.5) & (df_std["grad_sst"] >= 0.035)

    dom_zone = np.where(is_nursery, "Z4_NURSERY_GROUND",
               np.where(is_bycatch_risk, "Z5_BYCATCH_SCREENING",
               np.where(is_feeding, "Z3_FEEDING_CORE",
               np.where(is_inshore & has_tuna, "Z1_PFG_INSHORE",
               np.where(is_offshore & has_tuna, "Z2_PFG_OFFSHORE",
               "GENERAL_OCEANIC")))))
    df_std["dom_zone"] = dom_zone

    # Bycatch Traffic Light
    df_std["bycatch_traffic_light"] = np.where(~is_shelf_break, "GREEN",
                                      np.where(is_bycatch_risk & (df_std["hsi_cakalang"] >= 0.70), "RED", "YELLOW"))

    # BMKG Safety Advisory
    safety = np.where(df_std["wave_height_m"] >= 3.5, "BAHAYA_GELOMBANG_EKSTREM",
             np.where(df_std["wave_height_m"] >= 2.0, "WASPADA_KAPAL_KECIL_GELOMBANG_TINGGI",
             "AMAN_MELAUT"))
    df_std["safety_advisory"] = safety

    # Sector Classification
    sector = np.where(df_std["center_lon"] < 110.5, "JABAR_JATENG",
             np.where(df_std["center_lon"] <= 114.5, "JATIM",
             "BALI_NUSA_TENGGARA"))
    df_std["sector"] = sector
    df_std["prediction_date"] = target_date

    # 5. Output Record Assembly
    export_cols = [
        "cell_id", "hsi_cakalang", "hsi_madidihang", "hsi_tongkol",
        "is_hotspot", "dom_zone", "bycatch_traffic_light", "wave_height_m",
        "safety_advisory", "sector", "sst", "chla", "depth", "dist_port_nm"
    ]

    if limit > 0:
        records_out = df_std.head(limit)[export_cols].to_dict(orient="records")
    else:
        records_out = df_std[export_cols].to_dict(orient="records")

    duration_ms = round((datetime.datetime.now() - start_time).total_seconds() * 1000, 2)

    summary = {
        "mean_hsi_cakalang": round(float(df_std["hsi_cakalang"].mean()), 3),
        "mean_hsi_madidihang": round(float(df_std["hsi_madidihang"].mean()), 3),
        "mean_hsi_tongkol": round(float(df_std["hsi_tongkol"].mean()), 3),
        "safe_cells_pct": round(float((df_std["safety_advisory"] == "AMAN_MELAUT").mean() * 100), 1),
        "red_bycatch_cells": int((df_std["bycatch_traffic_light"] == "RED").sum()),
        "yellow_bycatch_cells": int((df_std["bycatch_traffic_light"] == "YELLOW").sum()),
        "green_bycatch_cells": int((df_std["bycatch_traffic_light"] == "GREEN").sum())
    }

    output_payload = {
        "status": "SUCCESS",
        "engine": "SEACAST_PRIGI_GEE_INTEGRATION_PIPELINE",
        "pipeline_version": "3.0.0",
        "model_version": "2.3.2",
        "source_dataset": os.path.basename(input_csv),
        "prediction_date": target_date,
        "total_cells": len(df_std),
        "hotspots_count": int(df_std["is_hotspot"].sum()),
        "execution_duration_ms": duration_ms,
        "summary": summary,
        "provenance": {
            "evidence_level": "PROXY_HSI",
            "source_mode": "GEE_INGESTED_WITH_LOCAL_FALLBACKS",
            "model_version": "2.3.2",
            "prediction_timestamp": datetime.datetime.now(datetime.timezone.utc).isoformat(),
            "validation_status": "UNVERIFIED_NO_OCCURRENCE_AUDIT",
            "applied_weights": LOCKED_ENSEMBLE_WEIGHTS,
            "homeport_coordinates": {"lat": PRIGI_LAT, "lon": PRIGI_LON},
            "satellite_sources": {
                "sst": "NOAA OISST v2.1 + NASA MODIS-Aqua L3SMI",
                "chla": "NASA MODIS-Aqua L3SMI chlor_a (4km)",
                "wind": "NOAA CFSv2 6-Hourly Harmonized",
                "current": "HYCOM GLBu0.08 / CMEMS Global Reanalysis",
                "bathymetry": "GEBCO 2023 Grid / NOAA ETOPO1"
            }
        },
        "data": records_out
    }

    # 6. Disk Persistence & Database Ingestion (if requested)
    if not dry_run:
        # Write web fast-load snapshot
        latest_json_path = os.path.join(WEB_PUBLIC_DATA, "daily_latest.json")
        with open(latest_json_path, "w") as f:
            json.dump({
                "prediction_date": target_date,
                "generated_at": datetime.datetime.now().isoformat(),
                "provenance": output_payload["provenance"],
                "total_cells": len(records_out),
                "hotspots_count": output_payload["hotspots_count"],
                "summary": summary,
                "data": records_out
            }, f)

        # Write output json if requested
        if output_json:
            with open(output_json, "w") as f:
                json.dump(output_payload, f)

        # Write output csv if requested
        if output_csv:
            df_std.to_csv(output_csv, index=False)

        # Direct database persistence if flag provided
        if persist_db:
            try:
                import psycopg2
                db_host = os.getenv("DB_HOST")
                db_name = os.getenv("DB_DATABASE")
                db_user = os.getenv("DB_USERNAME")
                if not all((db_host, db_name, db_user)):
                    raise RuntimeError("DB_HOST, DB_DATABASE, and DB_USERNAME must be configured")
                conn = psycopg2.connect(
                    host=db_host,
                    port=os.getenv("DB_PORT", "5432"),
                    dbname=db_name,
                    user=db_user,
                    password=os.getenv("DB_PASSWORD", ""),
                    connect_timeout=15,
                )
                cur = conn.cursor()

                # Satisfy any un-defaulted NOT NULL numeric columns on legacy schemas cleanly
                base_cols = [
                    "prediction_date", "cell_id", "hsi_cakalang", "hsi_madidihang", "hsi_tongkol",
                    "is_hotspot", "dom_zone", "bycatch_traffic_light", "sst_c", "chla_mg_m3",
                    "current_velocity_ms", "wave_height_m", "wind_speed_ms", "safety_advisory",
                ]
                cur.execute(
                    """
                    SELECT column_name
                    FROM information_schema.columns
                    WHERE table_schema = 'public'
                      AND table_name = 'daily_predictions'
                      AND is_nullable = 'NO'
                      AND column_default IS NULL
                    """
                )
                extra_required_cols = [r[0] for r in cur.fetchall() if r[0] not in base_cols]
                insert_cols = base_cols + extra_required_cols
                placeholders = ", ".join(["%s"] * len(insert_cols)) + ", NOW()"
                update_sets = ", ".join(
                    [f"{col} = EXCLUDED.{col}" for col in insert_cols if col not in ("prediction_date", "cell_id")]
                    + ["created_at = NOW()"]
                )

                # Upsert into daily_predictions
                upsert_sql = f"""
                    INSERT INTO daily_predictions (
                        {", ".join(insert_cols)}, created_at
                    ) VALUES ({placeholders})
                    ON CONFLICT (prediction_date, cell_id) DO UPDATE SET
                        {update_sets};
                """
                db_rows = []
                for _, r in df_std.iterrows():
                    row_vals = [
                        target_date, r["cell_id"], float(r["hsi_cakalang"]), float(r["hsi_madidihang"]), float(r["hsi_tongkol"]),
                        bool(r["is_hotspot"]), str(r["dom_zone"]), str(r["bycatch_traffic_light"]),
                        float(r["sst"]), float(r["chla"]), float(r["current_velocity"]), float(r["wave_height_m"]),
                        float(r["wind_speed"]), str(r["safety_advisory"])
                    ] + [0.0] * len(extra_required_cols)
                    db_rows.append(tuple(row_vals))
                cur.executemany(upsert_sql, db_rows)
                conn.commit()
                cur.close()
                conn.close()
                output_payload["db_persisted"] = len(db_rows)
            except Exception as e:
                output_payload["db_persist_warning"] = f"Database persistence failed ({type(e).__name__})"

        write_ingestion_manifest(
            manifest_path=os.path.join(STORAGE_INGEST_DIR, "ingestion_manifest.json"),
            dataset=os.path.basename(input_csv),
            target_date=target_date,
            provenance={**output_payload["provenance"], "requested_date": requested_date or target_date},
            total_cells=len(df_std),
            hotspots_count=output_payload["hotspots_count"],
            warnings=output_payload.get("warnings", [])
            + ([output_payload["db_persist_warning"]] if output_payload.get("db_persist_warning") else []),
            requested_status="FAILED" if persist_db and output_payload.get("db_persist_warning") else None,
        )

    return output_payload


def main():
    parser = argparse.ArgumentParser(description="SEACAST PRIGI GEE Pipeline Transformer")
    parser.add_argument("--input-csv", type=str, required=True, help="Path to input GEE CSV")
    parser.add_argument("--date", type=str, default=None, help="Target date YYYY-MM-DD")
    parser.add_argument("--limit", type=int, default=0, help="Optional record limit")
    parser.add_argument("--dry-run", action="store_true", help="Execute without modifying disk or DB")
    parser.add_argument("--output-json", type=str, default=None, help="Path for JSON export")
    parser.add_argument("--output-csv", type=str, default=None, help="Path for CSV export")
    parser.add_argument("--persist-db", action="store_true", help="Persist directly into PostgreSQL")
    args = parser.parse_args()

    try:
        payload = run_pipeline(
            input_csv=args.input_csv,
            target_date=args.date,
            limit=args.limit,
            dry_run=args.dry_run,
            output_json=args.output_json,
            output_csv=args.output_csv,
            persist_db=args.persist_db
        )
        sys.stdout.write(json.dumps(payload))
        sys.exit(0)
    except Exception as e:
        error_res = {
            "status": "ERROR",
            "error_type": type(e).__name__,
            "message": str(e),
            "generated_at": datetime.datetime.now().isoformat()
        }
        sys.stdout.write(json.dumps(error_res))
        sys.exit(1)


if __name__ == "__main__":
    main()

