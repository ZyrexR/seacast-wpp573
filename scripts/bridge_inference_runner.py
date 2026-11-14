#!/usr/bin/env python3
"""
SEACAST PRIGI V3 — Bridge Scientific Inference Runner
Standardized CLI interface for Laravel Python Computation Bridge.
Enforces locked scientific weights, strict schema validation, and pure JSON output to stdout.
"""

import os
import sys
import json
import argparse
import datetime
import warnings

from species_contract import PUBLIC_SPECIES, PUBLIC_TO_INTERNAL, normalize_species

# Suppress sklearn unpickle warnings from polluting stdout
warnings.filterwarnings("ignore")

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MODELS_DIR = os.path.join(BASE_DIR, "models")
GEOJSON_PATH = os.path.join(BASE_DIR, "data", "geojson", "canonical_grid_8129_cells.geojson")

# Locked scientific baseline weights from model_evaluations_summary.json
LOCKED_ENSEMBLE_WEIGHTS = {
    "cakalang": {"maxent": 0.320, "rf": 0.355, "glm": 0.325},
    "madidihang": {"maxent": 0.295, "rf": 0.365, "glm": 0.340}
}

PRIGI_LON = 111.7275
PRIGI_LAT = -8.2833

def main():
    parser = argparse.ArgumentParser(description="SEACAST PRIGI Inference Runner")
    parser.add_argument("--date", type=str, default=datetime.date.today().strftime("%Y-%m-%d"), help="Target prediction date (YYYY-MM-DD)")
    parser.add_argument("--species", type=str, default="all", help="Target species: all | tuna_sirip_kuning | cakalang | tongkol_krai")
    parser.add_argument("--limit", type=int, default=0, help="Optional limit on returned records")
    args = parser.parse_args()

    try:
        import numpy as np
        import pandas as pd
        import joblib
    except ImportError as e:
        error_payload = {
            "status": "ERROR",
            "error_type": "DEPENDENCY_MISSING",
            "message": f"Required scientific Python dependency missing: {str(e)}",
            "generated_at": datetime.datetime.now().isoformat()
        }
        sys.stdout.write(json.dumps(error_payload))
        sys.exit(1)

    try:
        target_date = datetime.datetime.strptime(args.date, "%Y-%m-%d").date()
    except ValueError:
        error_payload = {
            "status": "ERROR",
            "error_type": "INVALID_PARAMETER",
            "message": f"Invalid date format: {args.date}. Expected YYYY-MM-DD.",
            "generated_at": datetime.datetime.now().isoformat()
        }
        sys.stdout.write(json.dumps(error_payload))
        sys.exit(1)

    # 1. Load Canonical Grid
    if not os.path.exists(GEOJSON_PATH):
        error_payload = {
            "status": "ERROR",
            "error_type": "DATASET_MISSING",
            "message": f"Canonical grid geojson not found at {GEOJSON_PATH}",
            "generated_at": datetime.datetime.now().isoformat()
        }
        sys.stdout.write(json.dumps(error_payload))
        sys.exit(1)

    with open(GEOJSON_PATH, "r") as f:
        gj = json.load(f)

    records = []
    for feat in gj["features"]:
        props = feat["properties"]
        coords = feat["geometry"]["coordinates"][0]
        lons = [c[0] for c in coords]
        lats = [c[1] for c in coords]
        records.append({
            "cell_id": props["cell_id"],
            "cell_class": props.get("cell_class", "PURE_OCEAN"),
            "center_lon": np.mean(lons[:4]),
            "center_lat": np.mean(lats[:4]),
            "depth": float(props.get("depth_m", 1000.0)),
            "dist_port_nm": float(props.get("dist_nm", 100.0)),
            "dist_port_km": float(props.get("dist_nm", 100.0)) * 1.852
        })
    df_grid = pd.DataFrame(records)

    # 2. Daily Oceanographic Simulation (Calibrated BMKG local fallback)
    day_of_year = target_date.timetuple().tm_yday
    upwelling_phase = np.exp(-0.5 * ((day_of_year - 240) / 45.0) ** 2)

    lat_factor = np.clip((abs(df_grid['center_lat']) - 8.0) / 4.0, 0.0, 1.0)
    inshore_factor = np.exp(-df_grid['dist_port_nm'] / 40.0)
    base_sst = 28.5 - (3.5 * upwelling_phase * inshore_factor) - (1.5 * lat_factor)

    np.random.seed(int(target_date.strftime("%Y%m%d")) % 10000)
    sst_noise = np.random.normal(0.0, 0.25, len(df_grid))
    df_grid['sst'] = np.clip(base_sst + sst_noise, 21.0, 31.0)
    df_grid['grad_sst'] = np.clip(0.015 + (0.045 * upwelling_phase * inshore_factor) + np.random.exponential(0.01, len(df_grid)), 0.005, 0.15)
    df_grid['front_indicator'] = (df_grid['grad_sst'] >= 0.035).astype(float)

    base_chla = 0.18 + (0.65 * upwelling_phase * inshore_factor) + (0.15 * (df_grid['depth'] < 200))
    df_grid['chla'] = np.clip(base_chla + np.random.normal(0.0, 0.04, len(df_grid)), 0.05, 3.5)

    df_grid['current_velocity'] = np.clip(0.25 + (0.35 * upwelling_phase) + np.random.normal(0.0, 0.05, len(df_grid)), 0.05, 1.5)
    df_grid['wind_speed'] = np.clip(4.5 + (4.0 * upwelling_phase) + np.random.normal(0.0, 0.5, len(df_grid)), 1.5, 15.0)
    df_grid['wind_stress'] = df_grid['wind_speed'] * 0.008
    df_grid['ekman_upwelling_velocity'] = np.clip((df_grid['wind_speed'] - 4.0) * 1e-5 * (df_grid['center_lat'] < -8.0), 0.0, 1e-4)

    base_wave = 1.3 + (1.2 * upwelling_phase) + (df_grid['dist_port_nm'] / 150.0)
    df_grid['wave_height_m'] = np.round(np.clip(base_wave + np.random.normal(0.0, 0.3, len(df_grid)), 0.5, 4.5), 1)

    # 3. Model Inference with Scaler
    scaler_path = os.path.join(MODELS_DIR, "scaler_oceanographic.joblib")
    scaler = joblib.load(scaler_path)

    feature_cols = [
        'sst', 'grad_sst', 'front_indicator', 'chla', 'current_velocity',
        'wind_speed', 'wind_stress', 'ekman_upwelling_velocity', 'depth', 'dist_port_km'
    ]
    X_raw = np.nan_to_num(df_grid[feature_cols].values, nan=0.0)
    X_scaled = scaler.transform(X_raw)
    X_scaled = np.nan_to_num(X_scaled, nan=0.0)

    if args.species == "all":
        target_species_list = list(PUBLIC_SPECIES)
    else:
        try:
            internal_species = normalize_species(args.species)
        except ValueError as exc:
            error_payload = {
                "status": "ERROR",
                "error_type": "INVALID_PARAMETER",
                "message": str(exc),
                "generated_at": datetime.datetime.now().isoformat()
            }
            sys.stdout.write(json.dumps(error_payload))
            sys.exit(1)
        target_species_list = [
            public_species for public_species, internal_key in PUBLIC_TO_INTERNAL.items()
            if internal_key == internal_species
        ]

    for sp in ["cakalang", "madidihang"]:
        model_path = os.path.join(MODELS_DIR, f"{sp}_ensemble_model.joblib")
        bundle = joblib.load(model_path)
        # ENFORCE locked baseline weights!
        w = LOCKED_ENSEMBLE_WEIGHTS[sp]

        p_maxent = bundle["maxent"].predict_proba(X_scaled)[:, 1]
        p_rf = bundle["rf"].predict_proba(X_scaled)[:, 1]
        p_glm = bundle["glm"].predict_proba(X_scaled)[:, 1]

        raw_hsi = w["maxent"] * p_maxent + w["rf"] * p_rf + w["glm"] * p_glm
        min_v, max_v = raw_hsi.min(), raw_hsi.max()
        hsi_norm = (raw_hsi - min_v) / (max_v - min_v) if max_v > min_v else raw_hsi
        df_grid[f"hsi_{sp}"] = np.round(hsi_norm, 3)

    # Tongkol Krai (Auxis thazard) Coastal/Neritic HSI
    si_sst_tongkol = np.exp(-((df_grid['sst'] - 28.75) / ((30.5 - 27.0) / 1.5)) ** 2)
    si_chla_tongkol = np.exp(-((df_grid['chla'] - 0.35) / ((0.50 - 0.20) / 1.5)) ** 2)
    si_depth_tongkol = np.where(df_grid['depth'] <= 150.0, 1.0, np.exp(-((df_grid['depth'] - 150.0) / 150.0) ** 2))
    df_grid['hsi_tongkol'] = np.round(np.clip(
        (0.45 * si_sst_tongkol) + (0.40 * si_chla_tongkol) + (0.15 * si_depth_tongkol),
        0.0,
        1.0,
    ), 3)

    # Public TCT aliases; legacy DB keys remain the source of truth above.
    df_grid['hsi_tuna_sirip_kuning'] = df_grid['hsi_madidihang']
    df_grid['hsi_tongkol_krai'] = df_grid['hsi_tongkol']

    # Hotspot threshold
    df_grid['is_hotspot'] = (df_grid['hsi_cakalang'] >= 0.70) | (df_grid['hsi_madidihang'] >= 0.70)

    # 4. DOM Zones (Strict Priority: Z4 -> Z5 -> Z3 -> Z1 -> Z2 -> GEN)
    is_inshore = (df_grid['dist_port_nm'] <= 12.0)
    is_offshore = (df_grid['dist_port_nm'] > 12.0) & (df_grid['dist_port_nm'] <= 60.0)
    has_tuna = (df_grid['hsi_cakalang'] >= 0.55) | (df_grid['hsi_madidihang'] >= 0.55)
    is_feeding = (df_grid['chla'] >= 0.40) & (df_grid['grad_sst'] >= 0.035)
    is_nursery = (df_grid['depth'] <= 150.0) & (df_grid['dist_port_nm'] <= 15.0)
    is_shelf_break = (df_grid['depth'] >= 150.0) & (df_grid['depth'] <= 1200.0)
    is_bycatch_risk = is_shelf_break & (df_grid['sst'] <= 26.5) & (df_grid['grad_sst'] >= 0.035)

    dom_zone = np.where(is_nursery, "Z4_NURSERY_GROUND",
               np.where(is_bycatch_risk, "Z5_BYCATCH_SCREENING",
               np.where(is_feeding, "Z3_FEEDING_CORE",
               np.where(is_inshore & has_tuna, "Z1_PFG_INSHORE",
               np.where(is_offshore & has_tuna, "Z2_PFG_OFFSHORE",
               "GENERAL_OCEANIC")))))
    df_grid['dom_zone'] = dom_zone

    # Bycatch Traffic Light
    df_grid['bycatch_traffic_light'] = np.where(~is_shelf_break, "GREEN",
                                        np.where(is_bycatch_risk & (df_grid['hsi_cakalang'] >= 0.70), "RED", "YELLOW"))

    # BMKG Safety
    safety = np.where(df_grid['wave_height_m'] >= 3.5, "BAHAYA_GELOMBANG_EKSTREM",
             np.where(df_grid['wave_height_m'] >= 2.0, "WASPADA_KAPAL_KECIL_GELOMBANG_TINGGI",
             "AMAN_MELAUT"))
    df_grid['safety_advisory'] = safety

    sector = np.where(df_grid['center_lon'] < 110.5, "JABAR_JATENG",
             np.where(df_grid['center_lon'] <= 114.5, "JATIM",
             "BALI_NUSA_TENGGARA"))
    df_grid['sector'] = sector

    export_cols = [
        "cell_id", "hsi_cakalang", "hsi_madidihang", "hsi_tuna_sirip_kuning", "hsi_tongkol", "hsi_tongkol_krai",
        "is_hotspot", "dom_zone", "bycatch_traffic_light", "wave_height_m",
        "safety_advisory", "sector", "sst", "chla", "depth", "dist_port_nm"
    ]
    if args.limit > 0:
        records_out = df_grid.head(args.limit)[export_cols].to_dict(orient="records")
    else:
        records_out = df_grid[export_cols].to_dict(orient="records")

    output_payload = {
        "status": "SUCCESS",
        "engine": "SEACAST_PRIGI_SDM_ENGINE",
        "model_version": "2.3.2",
        "species": target_species_list,
        "generated_at": datetime.datetime.now().isoformat(),
        "parameters": {
            "prediction_date": args.date,
            "target_species": args.species,
            "limit": args.limit
        },
        "total_cells": len(df_grid),
        "hotspots_count": int(df_grid['is_hotspot'].sum()),
        "data": {
            "summary": {
                "mean_hsi_cakalang": round(float(df_grid['hsi_cakalang'].mean()), 3),
                "mean_hsi_madidihang": round(float(df_grid['hsi_madidihang'].mean()), 3),
                "mean_hsi_tongkol": round(float(df_grid['hsi_tongkol'].mean()), 3),
                "safe_cells_pct": round(float((df_grid['safety_advisory'] == 'AMAN_MELAUT').mean() * 100), 1),
                "red_bycatch_cells": int((df_grid['bycatch_traffic_light'] == 'RED').sum()),
                "yellow_bycatch_cells": int((df_grid['bycatch_traffic_light'] == 'YELLOW').sum()),
                "green_bycatch_cells": int((df_grid['bycatch_traffic_light'] == 'GREEN').sum())
            },
            "records": records_out
        },
        "warnings": [],
        "provenance": {
            "evidence_level": "PROXY_HSI",
            "source_mode": "LOCAL_SIMULATION_FALLBACK",
            "model_version": "2.3.2",
            "prediction_timestamp": datetime.datetime.now(datetime.timezone.utc).isoformat(),
            "validation_status": "UNVERIFIED_NO_OCCURRENCE_AUDIT",
            "python_runtime": sys.version,
            "applied_weights": LOCKED_ENSEMBLE_WEIGHTS,
            "homeport_coordinates": {"lat": PRIGI_LAT, "lon": PRIGI_LON},
            "sources": {
                "wave": "Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)",
                "sdm": "Ensemble SDM (MaxEnt, Random Forest, GLM)",
                "bycatch": "Screening Lereng Batimetri 150-1200m Standar IOTC"
            }
        }
    }

    sys.stdout.write(json.dumps(output_payload))
    sys.exit(0)

if __name__ == "__main__":
    main()

