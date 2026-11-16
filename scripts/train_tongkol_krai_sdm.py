#!/usr/bin/env python3
"""Train Tongkol Krai (Auxis thazard) SDM Ensemble Proxy (MaxEnt + RF + GLM).

Uses the exact 132-month WPPNRI 573 oceanographic panel (2015-2025) and shared
scaler (`models/scaler_oceanographic.joblib`) matching Cakalang and Tuna Sirip
Kuning, with an explicit neritic ecological proxy envelope (`PROXY_HSI`).
"""

from __future__ import annotations

import glob
import json
import os
import warnings

import joblib
import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import roc_auc_score, roc_curve
from sklearn.model_selection import KFold

warnings.filterwarnings("ignore")

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
CSV_PATTERN = os.path.join(BASE_DIR, "data", "oceanographic", "monthly_csv", "SEACAST_PRIGI_GRID_8129_*.csv")
MODELS_DIR = os.path.join(BASE_DIR, "models")
SCALER_PATH = os.path.join(MODELS_DIR, "scaler_oceanographic.joblib")
OUTPUT_MODEL_PATH = os.path.join(MODELS_DIR, "tongkol_krai_ensemble_model.joblib")
SUMMARY_PATH = os.path.join(MODELS_DIR, "sdm", "ensemble", "model_evaluations_summary.json")

PRIGI_LON = 111.7275
PRIGI_LAT = -8.2833

FEATURE_COLS = [
    "sst",
    "grad_sst",
    "front_indicator",
    "chla",
    "current_velocity",
    "wind_speed",
    "wind_stress",
    "ekman_upwelling_velocity",
    "depth",
    "dist_port_km",
]


def haversine_np(lon1, lat1, lon2, lat2):
    lon1, lat1, lon2, lat2 = map(np.radians, [lon1, lat1, lon2, lat2])
    dlon = lon2 - lon1
    dlat = lat2 - lat1
    a = np.sin(dlat / 2.0) ** 2 + np.cos(lat1) * np.cos(lat2) * np.sin(dlon / 2.0) ** 2
    return 6371.0 * (2.0 * np.arcsin(np.sqrt(a)))


def gaussian_suitability(x, opt_min: float, opt_max: float, tol: float):
    center = (opt_min + opt_max) / 2.0
    half_width = (opt_max - opt_min) / 2.0 + tol
    return np.clip(np.exp(-0.5 * ((x - center) / half_width) ** 2), 0.0, 1.0)


def compute_tss(y_true: np.ndarray, y_prob: np.ndarray, threshold: float) -> float:
    preds = y_prob >= threshold
    pos = y_true == 1
    neg = y_true == 0
    tp = np.sum(preds & pos)
    fn = np.sum((~preds) & pos)
    tn = np.sum((~preds) & neg)
    fp = np.sum(preds & neg)
    sensitivity = tp / (tp + fn) if (tp + fn) > 0 else 0.0
    specificity = tn / (tn + fp) if (tn + fp) > 0 else 0.0
    return float(sensitivity + specificity - 1.0)


def optimal_youden_threshold(y_true: np.ndarray, y_prob: np.ndarray) -> tuple[float, float]:
    fpr, tpr, thresholds = roc_curve(y_true, y_prob)
    j_scores = tpr - fpr
    best_idx = int(np.argmax(j_scores))
    return float(thresholds[best_idx]), float(j_scores[best_idx])


def load_monthly_oceanography() -> pd.DataFrame:
    raw_files = sorted(glob.glob(CSV_PATTERN))
    unique_files = {}
    for f in raw_files:
        fname = os.path.basename(f)
        if fname not in unique_files or os.path.getmtime(f) > os.path.getmtime(unique_files[fname]):
            unique_files[fname] = f
    csv_files = sorted(unique_files.values())
    if not csv_files:
        raise FileNotFoundError(f"No monthly oceanographic CSVs found matching {CSV_PATTERN}")

    def pick_col(df: pd.DataFrame, candidates: list[str]):
        for col in candidates:
            if col in df.columns:
                return df[col]
        return np.nan

    frames = []
    for f in csv_files:
        df_m = pd.read_csv(f)
        df_clean = pd.DataFrame()
        df_clean["cell_id"] = df_m["cell_id"].astype(str)
        df_clean["center_lon"] = pd.to_numeric(pick_col(df_m, ["lon_center", "center_lon", "lon"]), errors="coerce")
        df_clean["center_lat"] = pd.to_numeric(pick_col(df_m, ["lat_center", "center_lat", "lat"]), errors="coerce")
        df_clean["sst"] = pd.to_numeric(pick_col(df_m, ["sst_mean_c_mean", "sst_mean_c", "v_sst_mean_c"]), errors="coerce")
        df_clean["grad_sst"] = pd.to_numeric(
            pick_col(df_m, ["sst_gradient_deg_km_mean", "sst_gradient_deg_km", "v_sst_gradient_deg_km"]), errors="coerce"
        )
        df_clean["front_indicator"] = pd.to_numeric(
            pick_col(df_m, ["thermal_front_flag_mean", "thermal_front_flag", "v_thermal_front_flag"]), errors="coerce"
        )
        df_clean["chla"] = pd.to_numeric(pick_col(df_m, ["chla_mg_m3_mean", "chla_mg_m3", "v_chla_mg_m3_mean"]), errors="coerce")
        df_clean["current_velocity"] = pd.to_numeric(
            pick_col(df_m, ["current_speed_m_s_mean", "current_speed_m_s", "v_current_speed_m_s_mean"]), errors="coerce"
        )
        df_clean["wind_speed"] = pd.to_numeric(
            pick_col(df_m, ["wind_speed_mean_m_s_mean", "wind_speed_mean_m_s", "v_wind_speed_mean_m_s"]), errors="coerce"
        )
        df_clean["wind_stress"] = pd.to_numeric(
            pick_col(df_m, ["wind_stress_tau_mag_mean", "wind_stress_tau_mag", "v_wind_stress_tau_mag"]), errors="coerce"
        )
        df_clean["ekman_upwelling_velocity"] = pd.to_numeric(
            pick_col(df_m, ["ekman_w_e_m_s", "ekman_w_e_m_s_mean", "v_ekman_w_e_m_s"]), errors="coerce"
        )
        df_clean["depth"] = pd.to_numeric(pick_col(df_m, ["depth_m_mean", "depth_m", "v_depth_m_mean"]), errors="coerce")
        frames.append(df_clean)

    df_all = pd.concat(frames, ignore_index=True)
    df_all["dist_port_km"] = haversine_np(df_all["center_lon"], df_all["center_lat"], PRIGI_LON, PRIGI_LAT)

    for col in FEATURE_COLS:
        med = df_all[col].median()
        df_all[col] = df_all[col].fillna(0.0 if pd.isna(med) else med)

    return df_all


def main() -> None:
    print(f"Loading 132-month WPPNRI 573 oceanographic dataset...")
    df_all = load_monthly_oceanography()
    print(f"Loaded {len(df_all):,} rows.")

    scaler = joblib.load(SCALER_PATH)
    X_raw = np.nan_to_num(df_all[FEATURE_COLS].values, nan=0.0, posinf=0.0, neginf=0.0)
    X_scaled = np.nan_to_num(scaler.transform(X_raw), nan=0.0, posinf=0.0, neginf=0.0)

    # Tongkol Krai (Auxis thazard) neritic ecological proxy envelope
    suit_sst = gaussian_suitability(df_all["sst"], 27.0, 30.5, 0.45)
    suit_chla = gaussian_suitability(df_all["chla"], 0.20, 0.50, 0.12)
    suit_depth = np.where(
        df_all["depth"] <= 150.0,
        1.0,
        np.exp(-0.5 * ((df_all["depth"] - 150.0) / 180.0) ** 2),
    )
    suit_front = np.clip(df_all["grad_sst"] / 0.035, 0.0, 1.0)
    shelf_affinity = np.where(df_all["depth"] <= 600.0, 1.0, 0.25)

    raw_prob = ((0.40 * suit_sst) + (0.35 * suit_chla) + (0.15 * suit_depth) + (0.10 * suit_front)) * shelf_affinity
    prob_tongkol = np.nan_to_num(np.power(raw_prob, 2.5), nan=0.0)

    rng = np.random.default_rng(42)
    n_presence = 2500
    n_background = 5000
    p_norm = prob_tongkol / prob_tongkol.sum()

    presence_idx = rng.choice(len(df_all), size=n_presence, replace=False, p=p_norm)
    background_idx = rng.choice(len(df_all), size=n_background, replace=False)

    idx = np.concatenate([presence_idx, background_idx])
    X_s = np.nan_to_num(X_scaled[idx], nan=0.0, posinf=0.0, neginf=0.0)
    y_s = np.concatenate([np.ones(n_presence, dtype=int), np.zeros(n_background, dtype=int)])

    kf = KFold(n_splits=5, shuffle=True, random_state=42)
    oof_probs = {
        "maxent": np.zeros(len(y_s), dtype=float),
        "rf": np.zeros(len(y_s), dtype=float),
        "glm": np.zeros(len(y_s), dtype=float),
    }

    for tr_i, val_i in kf.split(X_s):
        m_maxent = LogisticRegression(C=1.0, max_iter=500, solver="lbfgs", random_state=42)
        m_rf = RandomForestClassifier(n_estimators=100, max_depth=10, min_samples_leaf=5, random_state=42, n_jobs=-1)
        m_glm = LogisticRegression(C=np.inf, max_iter=500, solver="lbfgs", random_state=42)

        m_maxent.fit(X_s[tr_i], y_s[tr_i])
        m_rf.fit(X_s[tr_i], y_s[tr_i])
        m_glm.fit(X_s[tr_i], y_s[tr_i])

        oof_probs["maxent"][val_i] = m_maxent.predict_proba(X_s[val_i])[:, 1]
        oof_probs["rf"][val_i] = m_rf.predict_proba(X_s[val_i])[:, 1]
        oof_probs["glm"][val_i] = m_glm.predict_proba(X_s[val_i])[:, 1]

    comp_metrics = {}
    raw_weights = {}
    for key in ["maxent", "rf", "glm"]:
        auc_val = float(roc_auc_score(y_s, oof_probs[key]))
        opt_thr, j_tss = optimal_youden_threshold(y_s, oof_probs[key])
        comp_metrics[key] = {
            "auc": round(auc_val, 3),
            "tss": round(j_tss, 3),
            "opt_threshold": round(min(max(opt_thr, 0.20), 0.85), 2),
        }
        raw_weights[key] = max(auc_val - 0.5, 0.05)

    weight_total = sum(raw_weights.values())
    w_maxent = round(raw_weights["maxent"] / weight_total, 3)
    w_rf = round(raw_weights["rf"] / weight_total, 3)
    w_glm = round(1.0 - w_maxent - w_rf, 3)
    weights = {"maxent": w_maxent, "rf": w_rf, "glm": w_glm}

    oof_ens = (w_maxent * oof_probs["maxent"]) + (w_rf * oof_probs["rf"]) + (w_glm * oof_probs["glm"])
    ens_auc = round(float(roc_auc_score(y_s, oof_ens)), 3)
    _, ens_tss = optimal_youden_threshold(y_s, oof_ens)
    ens_tss = round(ens_tss, 3)

    # Fit final ensemble on all sampled rows
    final_maxent = LogisticRegression(C=1.0, max_iter=500, solver="lbfgs", random_state=42).fit(X_s, y_s)
    final_rf = RandomForestClassifier(n_estimators=100, max_depth=10, min_samples_leaf=5, random_state=42, n_jobs=-1).fit(X_s, y_s)
    final_glm = LogisticRegression(C=np.inf, max_iter=500, solver="lbfgs", random_state=42).fit(X_s, y_s)

    bundle = {
        "species": "tongkol_krai",
        "maxent": final_maxent,
        "rf": final_rf,
        "glm": final_glm,
        "weights": weights,
        "feature_cols": FEATURE_COLS,
        "auc_score": ens_auc,
    }
    joblib.dump(bundle, OUTPUT_MODEL_PATH)

    imp = dict(zip(FEATURE_COLS, final_rf.feature_importances_))
    var_importance = {
        "sst_celsius": round(float(imp["sst"]) * 100.0, 1),
        "chlor_a_mg_m3": round(float(imp["chla"]) * 100.0, 1),
        "depth_m": round(float(imp["depth"]) * 100.0, 1),
        "thermal_front": round(float(imp["grad_sst"] + imp["front_indicator"]) * 100.0, 1),
        "dist_port_km": round(float(imp["dist_port_km"]) * 100.0, 1),
    }

    with open(SUMMARY_PATH, "r", encoding="utf-8") as f:
        summary = json.load(f)

    summary["tongkol_krai"] = {
        "species_name": "Tongkol Krai (Auxis thazard)",
        "presence_points_count": n_presence,
        "background_points_count": n_background,
        "individual_models": comp_metrics,
        "ensemble": {
            "weights": weights,
            "auc": ens_auc,
            "tss": ens_tss,
            "status": "EXPLORATORY_HEURISTIC_SCREENING (Proxy Discrimination Only; Not Biological Validation)",
            "disagreement_formula": "sigma_w = sqrt(sum_i w_i * (RHSI_i - weighted_mean_rhsi)^2)",
            "advisory_formula": "raw_advisory_score = weighted_mean_rhsi - lambda * sigma_w (baseline lambda = 0.5)",
            "weights_sum": round(sum(weights.values()), 3),
            "weight_provenance_status": "PROVISIONAL_ANALYST_SELECTED_WEIGHTS",
            "weight_provenance_explanation": (
                "Skill-weighted ensemble across MaxEnt, Random Forest, and GLM trained on neritic coastal "
                "environmental proxy suitability (SST 27.0-30.5 C, Chl-a 0.20-0.50 mg/m3, shelf depth <= 150 m) "
                "from 132 WPPNRI 573 monthly grids. Labeled PROXY_HSI because empirical field catch GPS "
                "coordinates for Tongkol Krai are unavailable."
            ),
        },
        "variable_importance_percent": var_importance,
        "model_paradigm": "Ensemble SDM Proxy Screening (MaxEnt + Random Forest + GLM)",
        "ml_training_executed_in_code": True,
        "spatial_cv_executed_in_code": False,
        "methodological_caveat": (
            "Model ensemble fitted in code against deterministic neritic presence-proxy samples from the "
            "2015-2025 WPPNRI 573 oceanographic stack; spatial block cross-validation against empirical "
            "field catch coordinates has not been executed."
        ),
    }

    with open(SUMMARY_PATH, "w", encoding="utf-8") as f:
        json.dump(summary, f, indent=2)
        f.write("\n")

    print(f"Saved model bundle: {OUTPUT_MODEL_PATH}")
    print(f"Updated summary: {SUMMARY_PATH}")
    print(f"Weights: {weights} | Ensemble AUC: {ens_auc} | Ensemble TSS: {ens_tss}")
    print(f"Components: {comp_metrics}")
    print(f"Variable importance (%): {var_importance}")


if __name__ == "__main__":
    main()
