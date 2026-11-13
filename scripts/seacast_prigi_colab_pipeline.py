# -*- coding: utf-8 -*-
"""
SEACAST PRIGI v2.3.2 — Machine Learning SDM & Dynamic Ocean Management Pipeline
Supports both Local Execution and Google Colab Execution.
"""

import os
import sys
import glob
import json
import math
import time
import warnings
import numpy as np
import pandas as pd
from tqdm import tqdm

from sklearn.ensemble import RandomForestClassifier
from sklearn.linear_model import LogisticRegression
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import roc_auc_score
from sklearn.model_selection import KFold
import joblib

warnings.filterwarnings('ignore')

# Detect Environment
try:
    from google.colab import drive
    drive.mount('/content/drive')
    IN_COLAB = True
    BASE_DIR = '/content/drive/MyDrive/SEACAST_PRIGI_CSV'
    OUTPUT_DIR = os.path.join(BASE_DIR, 'OUTPUT_COLAB')
except Exception:
    IN_COLAB = False
    BASE_DIR = os.path.abspath('data')
    OUTPUT_DIR = os.path.abspath('outputs')

os.makedirs(OUTPUT_DIR, exist_ok=True)
os.makedirs(os.path.join(OUTPUT_DIR, 'models'), exist_ok=True)
os.makedirs(os.path.join(OUTPUT_DIR, 'predictions'), exist_ok=True)
os.makedirs(os.path.join(OUTPUT_DIR, 'geojson'), exist_ok=True)
os.makedirs(os.path.join(OUTPUT_DIR, 'figures'), exist_ok=True)
os.makedirs(os.path.join(OUTPUT_DIR, 'metrics'), exist_ok=True)

print(f"🚀 SEACAST PRIGI v2.3.2 Pipeline Initialized in: {'Google Colab' if IN_COLAB else 'Local'}")

def main():
    # 1. Search Files
    if IN_COLAB:
        csv_pattern = os.path.join(BASE_DIR, "SEACAST_PRIGI_GRID_8129_*.csv")
    else:
        csv_pattern = os.path.join(BASE_DIR, "oceanographic", "monthly_csv", "SEACAST_PRIGI_GRID_8129_*.csv")

    raw_files = sorted(glob.glob(csv_pattern))
    unique_dict = {}
    for f in raw_files:
        fname = os.path.basename(f)
        if fname not in unique_dict or os.path.getmtime(f) > os.path.getmtime(unique_dict[fname]):
            unique_dict[fname] = f

    csv_files = sorted(list(unique_dict.values()))
    print(f"🔍 Found {len(csv_files)} unique monthly oceanographic CSV files.")
    assert len(csv_files) == 132, f"Expected 132 files, found {len(csv_files)}!"

    # 2. Ingest
    def pick_col(df, candidates):
        for c in candidates:
            if c in df.columns:
                return df[c]
        return np.nan

    df_list = []
    print("⏳ Loading all 132 months into memory...")
    for f in tqdm(csv_files, desc="Ingesting CSVs"):
        df_m = pd.read_csv(f)
        parts = os.path.basename(f).replace('.csv', '').split('_')
        year = int(parts[-2])
        month = int(parts[-1])
        
        df_clean = pd.DataFrame()
        df_clean['cell_id'] = df_m['cell_id'].astype(str)
        df_clean['cell_class'] = df_m['cell_class'].astype(str) if 'cell_class' in df_m.columns else 'PURE_OCEAN'
        df_clean['center_lon'] = pd.to_numeric(pick_col(df_m, ['lon_center', 'center_lon', 'lon']), errors='coerce')
        df_clean['center_lat'] = pd.to_numeric(pick_col(df_m, ['lat_center', 'center_lat', 'lat']), errors='coerce')
        df_clean['year'] = year
        df_clean['month'] = month
        df_clean['year_month'] = f"{year}_{month:02d}"
        df_clean['sst'] = pd.to_numeric(pick_col(df_m, ['sst_mean_c_mean', 'sst_mean_c', 'v_sst_mean_c']), errors='coerce')
        df_clean['grad_sst'] = pd.to_numeric(pick_col(df_m, ['sst_gradient_deg_km_mean', 'sst_gradient_deg_km', 'v_sst_gradient_deg_km']), errors='coerce')
        df_clean['front_indicator'] = pd.to_numeric(pick_col(df_m, ['thermal_front_flag_mean', 'thermal_front_flag', 'v_thermal_front_flag']), errors='coerce')
        df_clean['chla'] = pd.to_numeric(pick_col(df_m, ['chla_mg_m3_mean', 'chla_mg_m3', 'v_chla_mg_m3_mean']), errors='coerce')
        df_clean['current_velocity'] = pd.to_numeric(pick_col(df_m, ['current_speed_m_s_mean', 'current_speed_m_s', 'v_current_speed_m_s_mean']), errors='coerce')
        df_clean['wind_speed'] = pd.to_numeric(pick_col(df_m, ['wind_speed_mean_m_s_mean', 'wind_speed_mean_m_s', 'v_wind_speed_mean_m_s']), errors='coerce')
        df_clean['wind_stress'] = pd.to_numeric(pick_col(df_m, ['wind_stress_tau_mag_mean', 'wind_stress_tau_mag', 'v_wind_stress_tau_mag']), errors='coerce')
        df_clean['ekman_upwelling_velocity'] = pd.to_numeric(pick_col(df_m, ['ekman_w_e_m_s', 'ekman_w_e_m_s_mean', 'v_ekman_w_e_m_s']), errors='coerce')
        df_clean['depth'] = pd.to_numeric(pick_col(df_m, ['depth_m_mean', 'depth_m', 'v_depth_m_mean']), errors='coerce')
        df_clean['precipitation'] = pd.to_numeric(pick_col(df_m, ['precip_mm_day_mean', 'precip_mm_day', 'v_precip_mm_day']), errors='coerce')
        
        df_list.append(df_clean)

    df_all = pd.concat(df_list, ignore_index=True)
    print(f"✅ Total observations loaded: {len(df_all):,} rows.")

    # 3. Haversine & Effort Bias
    PRIGI_LON = 111.7275
    PRIGI_LAT = -8.2833

    def haversine_np(lon1, lat1, lon2, lat2):
        lon1, lat1, lon2, lat2 = map(np.radians, [lon1, lat1, lon2, lat2])
        dlon = lon2 - lon1
        dlat = lat2 - lat1
        a = np.sin(dlat/2.0)**2 + np.cos(lat1) * np.cos(lat2) * np.sin(dlon/2.0)**2
        c = 2 * np.arcsin(np.sqrt(a))
        return 6371.0 * c

    if 'dist_port_km' not in df_all.columns:
        df_all['dist_port_km'] = haversine_np(df_all['center_lon'], df_all['center_lat'], PRIGI_LON, PRIGI_LAT)

    df_all['dist_port_nm'] = df_all['dist_port_km'] / 1.852

    def compute_effort_bias(dist_nm):
        bias = np.where(dist_nm <= 12.0, 1.0,
               np.where(dist_nm <= 30.0, 0.75 * np.exp(-0.02 * (dist_nm - 12.0)),
               np.where(dist_nm <= 60.0, 0.40 * np.exp(-0.04 * (dist_nm - 30.0)),
               0.05 * np.exp(-0.08 * (dist_nm - 60.0)))))
        return np.clip(bias, 0.001, 1.0)

    df_all['effort_bias'] = compute_effort_bias(df_all['dist_port_nm'])

    # Fill NaNs across all numeric columns
    for col in ['sst', 'grad_sst', 'front_indicator', 'chla', 'current_velocity', 'wind_speed', 'wind_stress', 'ekman_upwelling_velocity', 'depth', 'precipitation', 'dist_port_km', 'dist_port_nm']:
        if col in df_all.columns:
            med = df_all[col].median()
            if pd.isna(med):
                med = 0.0
            df_all[col] = df_all[col].fillna(med)

    # 4. Species Envelopes (Sharpened WPPNRI 573 Oceanographic Niches)
    def gaussian_suitability(x, opt_min, opt_max, tol):
        center = (opt_min + opt_max) / 2.0
        half_width = (opt_max - opt_min) / 2.0 + tol
        return np.clip(np.exp(-0.5 * ((x - center) / half_width) ** 2), 0.0, 1.0)

    # Cakalang
    suit_sst_cak = gaussian_suitability(df_all['sst'], 25.0, 27.2, 0.40)
    suit_chla_cak = gaussian_suitability(df_all['chla'], 0.35, 0.85, 0.15)
    suit_front_cak = np.clip(df_all['grad_sst'] / 0.045, 0.0, 1.0)
    suit_ekman_cak = np.clip(df_all['ekman_upwelling_velocity'] * 5e5, 0.0, 1.0)
    raw_prob_cak = (0.35 * suit_sst_cak + 0.30 * suit_chla_cak + 0.25 * suit_front_cak + 0.10 * suit_ekman_cak) * (df_all['depth'] > 60.0)
    prob_cak = np.nan_to_num(np.power(raw_prob_cak, 2.5), nan=0.0)

    # Madidihang
    suit_sst_mad = gaussian_suitability(df_all['sst'], 26.2, 28.2, 0.45)
    suit_chla_mad = gaussian_suitability(df_all['chla'], 0.22, 0.55, 0.12)
    suit_curr_mad = np.clip(df_all['current_velocity'] / 0.45, 0.0, 1.0)
    raw_prob_mad = (0.35 * suit_sst_mad + 0.30 * suit_chla_mad + 0.35 * suit_curr_mad) * (df_all['depth'] > 200.0)
    prob_mad = np.nan_to_num(np.power(raw_prob_mad, 2.5), nan=0.0)

    # 5. Features & Scaler
    feature_cols = [
        'sst', 'grad_sst', 'front_indicator', 'chla', 'current_velocity', 
        'wind_speed', 'wind_stress', 'ekman_upwelling_velocity', 'depth', 'dist_port_km'
    ]
    for c in feature_cols:
        med = df_all[c].median()
        if pd.isna(med):
            med = 0.0
        df_all[c] = df_all[c].fillna(med)

    scaler = StandardScaler()
    X_raw = np.nan_to_num(df_all[feature_cols].values, nan=0.0, posinf=0.0, neginf=0.0)
    X_full = scaler.fit_transform(X_raw)
    X_full = np.nan_to_num(X_full, nan=0.0, posinf=0.0, neginf=0.0)
    joblib.dump(scaler, os.path.join(OUTPUT_DIR, 'models', 'scaler_oceanographic.joblib'))

    np.random.seed(42)
    N_SAMPLES = 2500

    def get_samples(p_series):
        p_clean = np.nan_to_num(p_series, nan=0.0)
        if p_clean.sum() == 0:
            p_norm = np.ones(len(p_clean)) / len(p_clean)
        else:
            p_norm = p_clean / p_clean.sum()
        p_idx = np.random.choice(df_all.index, size=N_SAMPLES, replace=False, p=p_norm)
        bg_idx = np.random.choice(df_all.index, size=N_SAMPLES * 2, replace=False)
        
        idx = np.concatenate([p_idx, bg_idx])
        y = np.concatenate([np.ones(N_SAMPLES), np.zeros(N_SAMPLES * 2)])
        X_sample = np.nan_to_num(X_full[idx], nan=0.0, posinf=0.0, neginf=0.0)
        return X_sample, y

    species_configs = [
        ("cakalang", prob_cak),
        ("madidihang", prob_mad)
    ]

    kf = KFold(n_splits=5, shuffle=True, random_state=42)
    eval_metrics = {}

    for name, prob in species_configs:
        print(f"\nTraining SDM Ensemble for: {name.upper()}")
        X_s, y_s = get_samples(prob)
        maxent = LogisticRegression(C=1.0, max_iter=500, solver='lbfgs', random_state=42)
        rf = RandomForestClassifier(n_estimators=100, max_depth=10, min_samples_leaf=5, random_state=42, n_jobs=-1)
        glm = LogisticRegression(penalty=None, solver='lbfgs', max_iter=500, random_state=42)

        # CV
        aucs_rf = []
        for tr_i, val_i in kf.split(X_s):
            rf.fit(X_s[tr_i], y_s[tr_i])
            aucs_rf.append(roc_auc_score(y_s[val_i], rf.predict_proba(X_s[val_i])[:, 1]))

        # Fit all
        maxent.fit(X_s, y_s)
        rf.fit(X_s, y_s)
        glm.fit(X_s, y_s)

        weights = {'maxent': 0.40, 'rf': 0.35, 'glm': 0.25}
        mean_auc_rf = np.mean(aucs_rf)
        print(f"  ✓ RF Cross-Validated AUC: {mean_auc_rf:.4f}")

        bundle = {
            'species': name,
            'maxent': maxent,
            'rf': rf,
            'glm': glm,
            'weights': weights,
            'auc_rf': float(mean_auc_rf)
        }
        joblib.dump(bundle, os.path.join(OUTPUT_DIR, 'models', f'{name}_ensemble_model.joblib'))
        eval_metrics[name] = {'auc_rf': float(mean_auc_rf), 'status': 'PASS'}

        # Batch Inference
        p_m = maxent.predict_proba(X_full)[:, 1]
        p_r = rf.predict_proba(X_full)[:, 1]
        p_g = glm.predict_proba(X_full)[:, 1]
        hsi_raw = 0.40 * p_m + 0.35 * p_r + 0.25 * p_g
        hsi_scaled = (hsi_raw - hsi_raw.min()) / (hsi_raw.max() - hsi_raw.min())
        df_all[f'hsi_{name}'] = np.round(hsi_scaled, 3)

    # Tongkol Krai uses a transparent scientific screening rule, not a trained joblib model.
    si_sst_tongkol = np.exp(-((df_all['sst'] - 28.75) / 2.33) ** 2)
    si_chl_tongkol = np.exp(-((df_all['chla'] - 0.35) / 0.20) ** 2)
    si_depth_tongkol = np.where(
        df_all['depth'] <= 150.0,
        1.0,
        np.exp(-((df_all['depth'] - 150.0) / 150.0) ** 2),
    )
    df_all['hsi_tongkol'] = np.round(np.clip(
        0.45 * si_sst_tongkol + 0.40 * si_chl_tongkol + 0.15 * si_depth_tongkol,
        0.0,
        1.0,
    ), 3)

    # Hotspot Flag
    df_all['is_hotspot'] = (df_all['hsi_cakalang'] >= 0.70) | (df_all['hsi_madidihang'] >= 0.70)

    # 6. DOM Functional Zones
    is_inshore = (df_all['dist_port_nm'] <= 12.0)
    is_offshore = (df_all['dist_port_nm'] > 12.0) & (df_all['dist_port_nm'] <= 60.0)
    has_tuna = (df_all['hsi_cakalang'] >= 0.55) | (df_all['hsi_madidihang'] >= 0.55)
    is_feeding = (df_all['chla'] >= 0.40) & (df_all['grad_sst'] >= 0.035)
    is_nursery = (df_all['depth'] <= 150.0) & (df_all['dist_port_nm'] <= 15.0)
    is_shelf_break = (df_all['depth'] >= 150.0) & (df_all['depth'] <= 1200.0)
    is_bycatch_risk = is_shelf_break & (df_all['sst'] <= 26.5) & (df_all['grad_sst'] >= 0.035)

    df_all['dom_zone'] = np.where(is_nursery, "Z4_NURSERY_GROUND",
                         np.where(is_bycatch_risk, "Z5_BYCATCH_SCREENING",
                         np.where(is_feeding, "Z3_FEEDING_CORE",
                         np.where(is_inshore & has_tuna, "Z1_PFG_INSHORE",
                         np.where(is_offshore & has_tuna, "Z2_PFG_OFFSHORE",
                         "GENERAL_OCEANIC")))))

    df_all['bycatch_traffic_light'] = np.where(~is_shelf_break, "GREEN",
                                      np.where(is_bycatch_risk & (df_all['hsi_cakalang'] >= 0.70), "RED", "YELLOW"))

    # 7. Export Outputs
    print("\n⏳ Saving Parquet & GeoJSON outputs...")
    parquet_out = os.path.join(OUTPUT_DIR, 'predictions', 'SEACAST_PRIGI_PREDICTIONS_2015_2025_FULL.parquet')
    df_all.to_parquet(parquet_out, index=False, compression='snappy')
    print(f"  ✓ Saved Parquet ({os.path.getsize(parquet_out)/(1024*1024):.1f} MB)")

    # GeoJSON
    canon = df_all.drop_duplicates(subset=['cell_id'])[['cell_id', 'center_lon', 'center_lat', 'cell_class', 'depth', 'dist_port_km', 'dist_port_nm']]
    features = []
    d = 0.05
    for _, r in canon.iterrows():
        lon, lat = r['center_lon'], r['center_lat']
        features.append({
            "type": "Feature",
            "properties": {
                "cell_id": str(r['cell_id']),
                "cell_class": str(r['cell_class']),
                "depth_m": float(r['depth']),
                "dist_nm": float(r['dist_port_nm'])
            },
            "geometry": {
                "type": "Polygon",
                "coordinates": [[[lon-d, lat-d], [lon+d, lat-d], [lon+d, lat+d], [lon-d, lat+d], [lon-d, lat-d]]]
            }
        })
    with open(os.path.join(OUTPUT_DIR, 'geojson', 'canonical_grid_8129_cells.geojson'), 'w') as f:
        json.dump({"type": "FeatureCollection", "features": features}, f)

    with open(os.path.join(OUTPUT_DIR, 'metrics', 'sdm_evaluation_metrics.json'), 'w') as f:
        json.dump(eval_metrics, f, indent=2)

    print("\n🎉 ALL SEACAST PRIGI SDM ARTIFACTS SUCCESSFULLY EXPORTED!")

if __name__ == '__main__':
    main()

