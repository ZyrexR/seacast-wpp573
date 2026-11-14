"""Build the clean Colab notebook for the evidence-based TCT workflow."""

import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
NOTEBOOK_PATH = ROOT / "notebooks" / "SEACAST_TCT_HYBRID_MODELING_2015_2025.ipynb"


def md(text):
    return {"cell_type": "markdown", "metadata": {}, "source": text.splitlines(True)}


def py(text):
    return {
        "cell_type": "code",
        "execution_count": None,
        "metadata": {},
        "outputs": [],
        "source": text.splitlines(True),
    }


cells = [
    md(
        r"""# SEACAST TCT — Hybrid Habitat Suitability Modeling (2015–2025)

Komoditas aktif:
- Tuna Sirip Kuning (Thunnus albacares)
- Cakalang (Katsuwonus pelamis)
- Tongkol Krai (Auxis thazard)

Metode baru:
- GAM untuk hubungan non-linear CPUE bulanan dan prediktor oseanografi.
- Random Forest sebagai model pembanding.
- Ensemble hanya GAM + Random Forest, dengan bobot dari error validasi time-series.
- Panel dual-hub PPN Prigi + PPS Cilacap; fitur lingkungan dibatasi pada radius operasional 60 NM.
- Fitur anomali klimatologis, lag-1 bulan, dan Upwelling Contrast Index (SST inshore - SST offshore).
- Target residual terhadap baseline musiman per pelabuhan; shrinkage koreksi dipilih dari CV, bukan dijamin otomatis unggul.
- EOG VIIRS VBD sebagai proxy aktivitas kapal/effort, bukan label spesies.
- MaxEnt **tidak dilatih** karena **NO SPATIAL OCCURRENCE**: belum ada koordinat tangkapan/occurrence per spesies.

Pembagian waktu:
- 2015–2024: fitting.
- 2025: final holdout validation, tidak digunakan untuk fitting.
- Hasil spasial disebut relative suitability/proxy sampai ada logbook berkoordinat, VMS/AIS yang terhubung dengan operasi, observer, atau occurrence lain yang sah.

Feeding ground dan nursery ground ditulis sebagai potential proxy, bukan area biologis terkonfirmasi.

Luaran spasial dipisah menjadi: (1) HSI lingkungan, (2) aktivitas EOG, dan (3) prediksi temporal agregat pelabuhan.
"""
    ),
    md("## 1. Setup Colab, Drive, dan direktori proyek\n"),
    py(
        r"""!pip install -q pygam scikit-learn joblib pyarrow geopandas shapely tqdm

import json
import os
import re
import warnings
from pathlib import Path

import joblib
import numpy as np
import pandas as pd
from tqdm.auto import tqdm
from sklearn.ensemble import RandomForestRegressor
from sklearn.impute import SimpleImputer
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
from sklearn.model_selection import TimeSeriesSplit
from sklearn.preprocessing import StandardScaler
from pygam import LinearGAM, s

warnings.filterwarnings('ignore')
RANDOM_STATE = 42
train_last_year = 2024
final_validation_year = 2025
EXPECTED_MONTHS = 132
TCT_SPECIES = {
    'tuna_sirip_kuning': 'madidihang_kg',
    'cakalang': 'cakalang_kg',
    'tongkol_krai': 'tongkol_krai_kg',
}

try:
    from google.colab import drive
    drive.mount('/content/drive')
    IN_COLAB = True
except Exception:
    IN_COLAB = False

default_root = Path('/content/drive/MyDrive/SEACAST_PRIGI') if IN_COLAB else Path.cwd()
PROJECT_ROOT = Path(os.environ.get('SEACAST_PROJECT_ROOT', str(default_root)))
if not (PROJECT_ROOT / 'data').exists() and (PROJECT_ROOT / 'prigi-pelagis' / 'data').exists():
    PROJECT_ROOT = PROJECT_ROOT / 'prigi-pelagis'

DATA_DIR = PROJECT_ROOT / 'data'
GEE_DIR = DATA_DIR / 'oceanographic' / 'monthly_csv'
EOG_DIR = DATA_DIR / 'eog' / 'vbd' / 'processed'
MASTER_PATH = DATA_DIR / 'master_prigi_all_inclusive.csv'
CILACAP_RAW_PATH = PROJECT_ROOT / 'seacast-prigi-laravel' / 'database' / 'data' / 'raw' / 'pps_cilacap_statistics_2016_2025.csv'
GRID_PATH = DATA_DIR / 'geojson' / 'canonical_grid_8129_cells.geojson'
OCCURRENCE_PATH = DATA_DIR / 'modeling' / 'tct_occurrences.parquet'
OUTPUT_DIR = PROJECT_ROOT / 'outputs' / 'tct_hybrid_2015_2025'
MODEL_DIR = OUTPUT_DIR / 'models'
PREDICTION_DIR = OUTPUT_DIR / 'predictions'
REPORT_DIR = OUTPUT_DIR / 'reports'
GEOJSON_DIR = OUTPUT_DIR / 'geojson'
for directory in (MODEL_DIR, PREDICTION_DIR, REPORT_DIR, GEOJSON_DIR):
    directory.mkdir(parents=True, exist_ok=True)

import sys
sys.path.insert(0, str(PROJECT_ROOT / 'scripts'))
from tct_local_modeling import (
    add_anomaly_and_lag_features,
    build_local_features,
    haversine_nm,
    parse_cilacap_statistics,
    select_radius,
)

print('Runtime:', 'Google Colab' if IN_COLAB else 'local/Jupyter')
print('PROJECT_ROOT:', PROJECT_ROOT)
"""
    ),
    md(
        "## 2. Ingestion GEE, EOG, grid, dan statistik pendaratan\n\n"
        "Cell ini berhenti bila EOG belum lengkap 132 bulan. File part berstatus running tidak dipakai.\n"
    ),
    py(
        r"""def numeric(df, names):
    for name in names:
        if name in df.columns:
            return pd.to_numeric(df[name], errors='coerce')
    return pd.Series(np.nan, index=df.index, dtype='float64')


def month_from_name(path):
    match = re.search(r'(20\d{2})_(0[1-9]|1[0-2])\.csv$', Path(path).name)
    if not match:
        raise ValueError(f'Nama file GEE tidak dikenali: {path}')
    return f'{match.group(1)}-{match.group(2)}'


def load_gee():
    files = sorted(GEE_DIR.glob('SEACAST_PRIGI_GRID_8129_*.csv'))
    if len(files) != EXPECTED_MONTHS:
        raise RuntimeError(f'GEE membutuhkan {EXPECTED_MONTHS} file, ditemukan {len(files)}')
    frames = []
    for path in tqdm(files, desc='Load GEE monthly'):
        raw = pd.read_csv(path)
        key = month_from_name(path)
        frames.append(pd.DataFrame({
            'cell_id': raw['cell_id'].astype(str),
            'cell_class': raw.get('cell_class', 'UNKNOWN'),
            'lat_center': numeric(raw, ['lat_center', 'center_lat', 'lat']),
            'lon_center': numeric(raw, ['lon_center', 'center_lon', 'lon']),
            'month_key': key,
            'year': int(key[:4]),
            'month': int(key[-2:]),
            'sst_c': numeric(raw, ['sst_mean_c_mean', 'sst_mean_c', 'v_sst_mean_c']),
            'chla_mg_m3': numeric(raw, ['chla_mg_m3_mean', 'chla_mg_m3', 'v_chla_mg_m3_mean']),
            'sst_gradient': numeric(raw, ['sst_gradient_deg_km_mean', 'sst_gradient_deg_km', 'v_sst_gradient_deg_km']),
            'thermal_front': numeric(raw, ['thermal_front_flag_mean', 'thermal_front_flag', 'v_thermal_front_flag']),
            'current_speed': numeric(raw, ['current_speed_m_s_mean', 'current_speed_m_s', 'v_current_speed_m_s_mean']),
            'wind_speed': numeric(raw, ['wind_speed_mean_m_s_mean', 'wind_speed_mean_m_s', 'v_wind_speed_mean_m_s']),
            'wind_stress': numeric(raw, ['wind_stress_tau_mag_mean', 'wind_stress_tau_mag', 'v_wind_stress_tau_mag']),
            'ekman_upwelling': numeric(raw, ['ekman_w_e_m_s_mean', 'ekman_w_e_m_s', 'v_ekman_w_e_m_s']),
            'depth_m': numeric(raw, ['depth_m_mean', 'depth_m', 'v_depth_m_mean']),
        }))
    result = pd.concat(frames, ignore_index=True)
    if result['cell_id'].nunique() != 8129:
        raise RuntimeError(f'Grid GEE bukan 8.129 cell: {result.cell_id.nunique()}')
    return result


def progress_for(path):
    progress_path = path.with_name(path.stem + '_progress.json')
    if not progress_path.exists():
        return {}
    return json.loads(progress_path.read_text(encoding='utf-8'))


def load_eog_complete():
    final = EOG_DIR / 'vbd_monthly_wpp573_grid.parquet'
    candidates = ([final] if final.exists() else []) + sorted((EOG_DIR / 'parts').glob('vbd_*.parquet'))
    for path in candidates:
        if path == final:
            manifest = final.with_name(final.stem + '_manifest.json')
            status = json.loads(manifest.read_text(encoding='utf-8')).get('status') if manifest.exists() else None
        else:
            status = progress_for(path).get('status')
        if status == 'complete':
            frame = pd.read_parquet(path)
            if (frame['month'].astype(str).nunique() == EXPECTED_MONTHS
                    and frame.groupby('month')['cell_id'].nunique().eq(8129).all()
                    and not frame.duplicated(['month', 'cell_id']).any()):
                frame['month_key'] = frame['month'].astype(str)
                return frame
    status_lines = []
    for path in sorted((EOG_DIR / 'parts').glob('vbd_*.parquet')):
        p = progress_for(path)
        status_lines.append(f"{path.name}: {p.get('status')} {p.get('completed_months')}/{p.get('target_months')}")
    detail = '; '.join(status_lines) if status_lines else 'file EOG belum ditemukan'
    raise RuntimeError('EOG belum lengkap 132 bulan. Tunggu prepare_vbd_monthly.py. ' + detail)


gee = load_gee()
eog = load_eog_complete()
for column in ['vbd_n_detect', 'vbd_pct_detect', 'vbd_avg_rade9']:
    eog[column] = pd.to_numeric(eog[column], errors='coerce')
eog['vbd_n_detect'] = eog['vbd_n_detect'].fillna(0)
eog['vbd_pct_detect'] = eog['vbd_pct_detect'].fillna(0)

df = gee.merge(
    eog[['cell_id', 'month_key', 'vbd_cvg', 'vbd_avg_rade9', 'vbd_n_detect', 'vbd_pct_detect', 'vbd_partial_period']],
    on=['cell_id', 'month_key'], how='left', validate='one_to_one'
)
df['vbd_n_detect'] = df['vbd_n_detect'].fillna(0)
df['vbd_pct_detect'] = df['vbd_pct_detect'].fillna(0)
df['vbd_partial_period'] = df['vbd_partial_period'].fillna(True).astype(bool)

if not MASTER_PATH.exists():
    raise FileNotFoundError(MASTER_PATH)
master = pd.read_csv(MASTER_PATH)
master['month_key'] = master['year'].astype(int).astype(str) + '-' + master['month'].astype(int).astype(str).str.zfill(2)
master['effort_trips'] = pd.to_numeric(
    master.get('total_trip_alat', master.get('total_trip_gt')), errors='coerce'
).fillna(0).clip(lower=1)
for species, catch_column in TCT_SPECIES.items():
    master[species + '_catch_kg'] = pd.to_numeric(master[catch_column], errors='coerce').fillna(0)
    master[species + '_cpue_kg_trip'] = master[species + '_catch_kg'] / master['effort_trips']

print('GEE rows:', f'{len(gee):,}')
print('EOG rows:', f'{len(eog):,}')
print('Port months:', len(master))
"""
    ),
    md("## 3. Feature engineering dan target CPUE bulanan\n"),
    py(
        r"""PORTS = {
    'prigi': {'label': 'PPN Prigi', 'lon': 111.7275, 'lat': -8.2833},
    'cilacap': {'label': 'PPS Cilacap', 'lon': 109.0049, 'lat': -7.7478},
}
MAX_OPERATIONAL_NM = 60.0
INSHORE_NM = 12.0
OFFSHORE_MIN_NM = 30.0

FEATURE_COLS = [
    'sst_c', 'chla_mg_m3', 'sst_gradient', 'upwelling_contrast_index',
    'anom_sst', 'anom_chla_mg_m3', 'anom_upwelling_contrast_index',
    'sst_lag1', 'chla_lag1', 'upwelling_lag1',
    'vbd_pct_detect', 'local_active_cell_share', 'vbd_active_pct_detect',
    'port_cilacap',
]
PORT_VALUE_COLS = ['sst_c', 'chla_mg_m3', 'upwelling_contrast_index']


def make_prigi_panel():
    columns = ['month_key', 'year', 'month', 'effort_trips']
    result = master[columns].copy()
    for species, catch_column in TCT_SPECIES.items():
        result[species + '_catch_kg'] = pd.to_numeric(master[catch_column], errors='coerce').fillna(0)
        result[species + '_cpue_kg_trip'] = result[species + '_catch_kg'] / result['effort_trips'].clip(lower=1)
    result['port'] = 'prigi'
    return result


if not CILACAP_RAW_PATH.exists():
    raise FileNotFoundError(
        f'Sumber PPS Cilacap tidak ditemukan: {CILACAP_RAW_PATH}. '
        'Salin hasil ekstrak workbook ke lokasi tersebut sebelum menjalankan notebook.'
    )
prigi_panel = make_prigi_panel()
cilacap_panel = parse_cilacap_statistics(CILACAP_RAW_PATH)
port_stats = pd.concat([prigi_panel, cilacap_panel], ignore_index=True, sort=False)

local_feature_frames = []
for port_name, port in PORTS.items():
    local = build_local_features(
        df,
        port_name=port_name,
        port_lon=port['lon'],
        port_lat=port['lat'],
        max_nm=MAX_OPERATIONAL_NM,
        inshore_nm=INSHORE_NM,
        offshore_min_nm=OFFSHORE_MIN_NM,
    )
    local['year'] = local['month_key'].str[:4].astype(int)
    local['month'] = local['month_key'].str[-2:].astype(int)
    local_feature_frames.append(local)
local_features = pd.concat(local_feature_frames, ignore_index=True)

monthly = local_features.merge(
    port_stats,
    on=['port', 'month_key', 'year', 'month'],
    how='inner',
    validate='one_to_one',
).sort_values(['port', 'month_key']).reset_index(drop=True)
monthly = add_anomaly_and_lag_features(
    monthly,
    value_columns=PORT_VALUE_COLS,
    group_columns=['port'],
    climatology_mask=monthly['year'].le(train_last_year),
)
monthly['anom_sst'] = monthly['anom_sst_c']
monthly['anom_chla_mg_m3'] = monthly['anom_chla_mg_m3']
monthly['anom_upwelling_contrast_index'] = monthly['anom_upwelling_contrast_index']
monthly['sst_lag1'] = monthly['sst_c_lag1']
monthly['chla_lag1'] = monthly['chla_mg_m3_lag1']
monthly['upwelling_lag1'] = monthly['upwelling_contrast_index_lag1']
monthly['port_cilacap'] = monthly['port'].eq('cilacap').astype(float)
monthly = monthly.sort_values(['month_key', 'port']).reset_index(drop=True)

if monthly['port'].nunique() != 2 or len(monthly) != 252:
    raise RuntimeError(f'Panel dual-hub harus 252 baris (132 Prigi + 120 Cilacap), ditemukan {len(monthly)}')
print('Panel ports:', monthly.groupby('port')['month_key'].agg(['min', 'max', 'count']).to_dict('index'))
print('Local cells per port:', local_features.groupby('port')['local_cell_count'].first().to_dict())
print('Feature columns:', FEATURE_COLS)
"""
    ),
    md(
        "## 4. Gate MaxEnt dan training GAM + Random Forest\n\n"
        "MaxEnt hanya tersedia sebagai workflow terpisah bila occurrence berkoordinat benar-benar disediakan. Notebook ini tidak membuat presence sintetis.\n"
    ),
    py(
        r"""maxent_status = {
    'enabled': False,
    'status': 'skipped',
    'reason': 'NO SPATIAL OCCURRENCE: belum ada koordinat tangkapan/occurrence per spesies TCT.',
    'input_path': str(OCCURRENCE_PATH),
}
if OCCURRENCE_PATH.exists():
    occurrence = pd.read_parquet(OCCURRENCE_PATH)
    if {'species', 'lat', 'lon'}.issubset(occurrence.columns) and occurrence['species'].isin(TCT_SPECIES).any():
        maxent_status = {
            'enabled': True,
            'status': 'available_for_separate_occurrence_run',
            'reason': 'Occurrence tersedia; jalankan MaxEnt dengan spatial block CV terpisah.',
            'input_path': str(OCCURRENCE_PATH),
        }
print('MaxEnt gate:', maxent_status)


def make_gam(n_features):
    terms = s(0, n_splines=5)
    for index in range(1, n_features):
        terms += s(index, n_splines=5)
    return LinearGAM(terms, max_iter=300)


def metrics(y_true, y_pred):
    y_true, y_pred = np.asarray(y_true, dtype=float), np.asarray(y_pred, dtype=float)
    return {
        'rmse': float(np.sqrt(mean_squared_error(y_true, y_pred))),
        'mae': float(mean_absolute_error(y_true, y_pred)),
        'r2': float(r2_score(y_true, y_pred)) if len(np.unique(y_true)) > 1 else None,
    }


def baseline_values(frame, reference, target):
    # Port-specific same-month median baseline with safe fallbacks.
    seasonal = reference.groupby(['port', 'month'])[target].median().rename('seasonal_baseline').reset_index()
    port_median = reference.groupby('port')[target].median().rename('port_baseline').reset_index()
    result = frame[['port', 'month']].merge(seasonal, on=['port', 'month'], how='left')
    result = result.merge(port_median, on='port', how='left')
    result['seasonal_baseline'] = result['seasonal_baseline'].fillna(result['port_baseline'])
    result['seasonal_baseline'] = result['seasonal_baseline'].fillna(reference[target].median())
    return result['seasonal_baseline'].to_numpy(dtype=float)


def fit_species(species):
    target = species + '_cpue_kg_trip'
    train_mask = monthly['year'] <= train_last_year
    holdout_mask = monthly['year'] == final_validation_year
    train_months = sorted(monthly.loc[train_mask, 'month_key'].unique())
    month_splitter = TimeSeriesSplit(n_splits=4)
    folds = []
    for train_month_indices, val_month_indices in month_splitter.split(train_months):
        fold_train_months = set(np.asarray(train_months)[train_month_indices])
        fold_val_months = set(np.asarray(train_months)[val_month_indices])
        folds.append((
            monthly.index[monthly['month_key'].isin(fold_train_months)].to_numpy(),
            monthly.index[monthly['month_key'].isin(fold_val_months)].to_numpy(),
        ))
    gam_cv, rf_cv, fold_ensemble_predictions = [], [], []
    for fold, (tr, va) in enumerate(folds, start=1):
        train_frame, val_frame = monthly.loc[tr], monthly.loc[va]
        y_train_actual = train_frame[target].clip(lower=0).to_numpy()
        y_val = val_frame[target].clip(lower=0).to_numpy()
        baseline_train = baseline_values(train_frame, train_frame, target)
        baseline_val = baseline_values(val_frame, train_frame, target)
        y_residual = y_train_actual - baseline_train
        x_train_raw = train_frame[FEATURE_COLS].to_numpy()
        x_val_raw = val_frame[FEATURE_COLS].to_numpy()
        fold_imputer, fold_scaler = SimpleImputer(strategy='median'), StandardScaler()
        x_fold_train = fold_scaler.fit_transform(fold_imputer.fit_transform(x_train_raw))
        x_fold_val = fold_scaler.transform(fold_imputer.transform(x_val_raw))
        gam = make_gam(len(FEATURE_COLS)).fit(x_fold_train, y_residual)
        rf = RandomForestRegressor(
            n_estimators=250, max_features='sqrt', min_samples_leaf=4,
            random_state=RANDOM_STATE + fold, n_jobs=-1
        ).fit(x_fold_train, y_residual)
        gam_correction = gam.predict(x_fold_val)
        rf_correction = rf.predict(x_fold_val)
        gam_prediction = np.clip(baseline_val + gam_correction, 0, None)
        rf_prediction = np.clip(baseline_val + rf_correction, 0, None)
        gam_cv.append(metrics(y_val, gam_prediction))
        rf_cv.append(metrics(y_val, rf_prediction))
        fold_ensemble_predictions.append((y_val, baseline_val, gam_correction, rf_correction))

    gam_rmse = np.mean([x['rmse'] for x in gam_cv])
    rf_rmse = np.mean([x['rmse'] for x in rf_cv])
    inverse = np.array([1 / max(gam_rmse, 1e-9), 1 / max(rf_rmse, 1e-9)])
    weights = inverse / inverse.sum()
    shrinkage_grid = np.array([0.0, 0.25, 0.50, 0.75, 1.0])
    shrinkage_scores = {}
    for shrinkage in shrinkage_grid:
        scores = []
        for y_val, baseline_val, gam_correction, rf_correction in fold_ensemble_predictions:
            correction = weights[0] * gam_correction + weights[1] * rf_correction
            scores.append(metrics(y_val, np.clip(baseline_val + shrinkage * correction, 0, None))['rmse'])
        shrinkage_scores[float(shrinkage)] = float(np.mean(scores))
    correction_shrinkage = min(shrinkage_scores, key=shrinkage_scores.get)

    train_frame = monthly.loc[train_mask]
    holdout_frame = monthly.loc[holdout_mask]
    y_train_actual = train_frame[target].clip(lower=0).to_numpy()
    y_holdout = holdout_frame[target].clip(lower=0).to_numpy()
    baseline_train = baseline_values(train_frame, train_frame, target)
    baseline_holdout = baseline_values(holdout_frame, train_frame, target)
    y_residual = y_train_actual - baseline_train
    imputer, scaler = SimpleImputer(strategy='median'), StandardScaler()
    x_train = scaler.fit_transform(imputer.fit_transform(train_frame[FEATURE_COLS].to_numpy()))
    x_holdout = scaler.transform(imputer.transform(holdout_frame[FEATURE_COLS].to_numpy()))
    gam = make_gam(len(FEATURE_COLS)).fit(x_train, y_residual)
    rf = RandomForestRegressor(
        n_estimators=400, max_features='sqrt', min_samples_leaf=4,
        random_state=RANDOM_STATE, n_jobs=-1
    ).fit(x_train, y_residual)
    gam_holdout = gam.predict(x_holdout)
    rf_holdout = rf.predict(x_holdout)
    ensemble_correction = weights[0] * gam_holdout + weights[1] * rf_holdout
    ensemble_holdout = np.clip(baseline_holdout + correction_shrinkage * ensemble_correction, 0, None)
    baseline_evaluation = metrics(y_holdout, np.full(len(y_holdout), np.median(y_train_actual)))
    port_median_holdout = metrics(y_holdout, train_frame.groupby('port')[target].median().reindex(holdout_frame['port']).to_numpy())
    seasonal_evaluation = metrics(y_holdout, baseline_holdout)
    ensemble_evaluation = metrics(y_holdout, ensemble_holdout)

    evaluation = {
        'species': species,
        'cv_gam': {key: float(np.mean([x[key] for x in gam_cv])) for key in ['rmse', 'mae', 'r2']},
        'cv_random_forest': {key: float(np.mean([x[key] for x in rf_cv])) for key in ['rmse', 'mae', 'r2']},
        'holdout_2025_gam': metrics(y_holdout, gam_holdout),
        'holdout_2025_random_forest': metrics(y_holdout, rf_holdout),
        'holdout_2025_ensemble': ensemble_evaluation,
        'holdout_2025_baseline_train_median': baseline_evaluation,
        'holdout_2025_baseline_port_median': port_median_holdout,
        'holdout_2025_baseline_month_climatology': seasonal_evaluation,
        'beats_baseline_2025': bool(ensemble_evaluation['rmse'] < seasonal_evaluation['rmse']),
        'ensemble_weights': {'gam': float(weights[0]), 'random_forest': float(weights[1])},
        'residual_shrinkage': float(correction_shrinkage),
        'residual_cv_rmse_by_shrinkage': shrinkage_scores,
        'maxent': maxent_status,
        'spatial_evidence': 'dual_port_local_aggregate_proxy_no_catch_coordinates',
    }
    seasonal_table = train_frame.groupby(['port', 'month'])[target].median().reset_index(name='baseline')
    port_table = train_frame.groupby('port')[target].median().reset_index(name='baseline')
    bundle = {
        'species': species,
        'feature_cols': FEATURE_COLS,
        'imputer': imputer,
        'scaler': scaler,
        'gam': gam,
        'random_forest': rf,
        'weights': {'gam': float(weights[0]), 'random_forest': float(weights[1])},
        'method': 'Seasonal residual GAM + Random Forest with CV shrinkage; MaxEnt gated off without occurrence coordinates',
        'train_last_year': train_last_year,
        'final_validation_year': final_validation_year,
        'seasonal_baseline_table': seasonal_table,
        'port_baseline_table': port_table,
        'global_baseline': float(np.median(y_train_actual)),
        'correction_shrinkage': float(correction_shrinkage),
    }
    return bundle, evaluation


model_bundles, evaluations = {}, {}
for species in TCT_SPECIES:
    model_bundles[species], evaluations[species] = fit_species(species)
    print(species, evaluations[species]['holdout_2025_ensemble'])
"""
    ),
    md(
        "## 5. Inference spasial: HSI, fishery ground, feeding potential, nursery potential\n\n"
        "HSI adalah ranking relatif per bulan. EOG hanya menguatkan fishery-ground proxy. Nursery/feeding diberi confidence rendah karena belum ada observasi biologis langsung.\n"
    ),
    py(
        r"""def month_minmax(series):
    minimum, maximum = series.min(), series.max()
    if pd.isna(minimum) or pd.isna(maximum) or maximum <= minimum:
        return pd.Series(0.0, index=series.index)
    return (series - minimum) / (maximum - minimum)


spatial_frames = []
for port_name, port in PORTS.items():
    local = select_radius(
        df,
        lon=port['lon'], lat=port['lat'], min_nm=0, max_nm=MAX_OPERATIONAL_NM,
    )
    local['port'] = port_name
    local['year'] = local['month_key'].str[:4].astype(int)
    local['month'] = local['month_key'].str[-2:].astype(int)
    local['vbd_activity_raw'] = np.log1p(local['vbd_n_detect']) + 0.25 * np.log1p(local['vbd_pct_detect'])
    local_month_features = local_features[local_features['port'] == port_name]
    local = local.merge(
        local_month_features[['port', 'month_key', 'local_active_cell_share', 'vbd_active_pct_detect', 'upwelling_contrast_index']],
        on=['port', 'month_key'], how='left', validate='many_to_one',
    )
    local = local.sort_values(['cell_id', 'month_key']).reset_index(drop=True)
    for column in PORT_VALUE_COLS:
        climate = (
            local.loc[local['year'] <= train_last_year]
            .groupby(['port', 'month'])[column].mean()
            .rename('_climate')
            .reset_index()
        )
        local = local.merge(climate, on=['port', 'month'], how='left', validate='many_to_one')
        local['anom_' + column] = local[column] - local['_climate']
        local.drop(columns=['_climate'], inplace=True)
        local[column + '_lag1'] = local.groupby(['cell_id'])[column].shift(1)
    local['anom_sst'] = local['anom_sst_c']
    local['anom_chla_mg_m3'] = local['anom_chla_mg_m3']
    local['anom_upwelling_contrast_index'] = local['anom_upwelling_contrast_index']
    local['sst_lag1'] = local['sst_c_lag1']
    local['chla_lag1'] = local['chla_mg_m3_lag1']
    local['upwelling_lag1'] = local['upwelling_contrast_index_lag1']
    local['port_cilacap'] = local['port'].eq('cilacap').astype(float)
    local['activity_score'] = local.groupby(['port', 'month_key'])['vbd_activity_raw'].transform(month_minmax)
    local['chla_score'] = local.groupby(['port', 'month_key'])['chla_mg_m3'].transform(lambda x: month_minmax(x.clip(lower=0)))
    local['front_score'] = local.groupby(['port', 'month_key'])['sst_gradient'].transform(lambda x: month_minmax(x.clip(lower=0)))
    local['ekman_score'] = local.groupby(['port', 'month_key'])['ekman_upwelling'].transform(lambda x: month_minmax(x.clip(lower=0)))
    local['feeding_ground_potential'] = (
        0.40 * local['chla_score'] + 0.30 * local['front_score']
        + 0.20 * local['thermal_front'].clip(0, 1).fillna(0)
        + 0.10 * local['ekman_score']
    ).clip(0, 1)
    local['nursery_ground_potential'] = np.where(
        (local['depth_m'] <= 150) & (local['dist_port_nm'] <= 15),
        (0.60 * local['chla_score'] + 0.40 * (1 - (local['dist_port_nm'] / 15).clip(0, 1))).clip(0, 1),
        0.0,
    )
    cell_matrix = pd.DataFrame({column: pd.to_numeric(local[column], errors='coerce') for column in FEATURE_COLS})
    for species, bundle in model_bundles.items():
        x_cells = bundle['scaler'].transform(bundle['imputer'].transform(cell_matrix))
        correction = (
            bundle['weights']['gam'] * bundle['gam'].predict(x_cells)
            + bundle['weights']['random_forest'] * bundle['random_forest'].predict(x_cells)
        )
        seasonal = bundle['seasonal_baseline_table']
        local = local.merge(seasonal.rename(columns={'baseline': '_baseline'}), on=['port', 'month'], how='left')
        port_base = bundle['port_baseline_table'].rename(columns={'baseline': '_port_baseline'})
        local = local.merge(port_base, on='port', how='left')
        baseline = local['_baseline'].fillna(local['_port_baseline']).fillna(bundle['global_baseline'])
        pred = np.clip(baseline.to_numpy() + bundle['correction_shrinkage'] * correction, 0, None)
        raw_col, hsi_col = 'predicted_cpue_' + species, 'hsi_' + species
        local[raw_col] = pred
        local[hsi_col] = local.groupby(['port', 'month_key'])[raw_col].rank(pct=True).fillna(0).clip(0, 1)
        local['fishery_ground_' + species] = (local[hsi_col] * (0.5 + 0.5 * local['activity_score'])).clip(0, 1)
        local.drop(columns=['_baseline', '_port_baseline'], inplace=True)
    local['hsi_tct_max'] = local[['hsi_' + species for species in TCT_SPECIES]].max(axis=1)
    local['fishery_ground_score'] = local[['fishery_ground_' + species for species in TCT_SPECIES]].max(axis=1)
    local['fishery_ground_flag'] = (local['fishery_ground_score'] >= 0.70) & (local['activity_score'] >= 0.50)
    local['fishery_ground_evidence_level'] = 'EOG_ACTIVITY_AND_ENVIRONMENT_PROXY'
    local['feeding_ground_flag'] = local['feeding_ground_potential'] >= 0.60
    local['nursery_ground_flag'] = local['nursery_ground_potential'] >= 0.60
    local['spatial_evidence_level'] = 'LOW_PROXY_NO_COORDINATED_CATCH'
    local['feeding_evidence_level'] = 'POTENTIAL_PROXY_NO_FEEDING_OBSERVATION'
    local['nursery_evidence_level'] = 'POTENTIAL_PROXY_NO_JUVENILE_OBSERVATION'
    local['maxent_status'] = maxent_status['status']
    local['model_ensemble'] = 'SEASONAL_RESIDUAL_GAM_RF'
    spatial_frames.append(local)

spatial_df = pd.concat(spatial_frames, ignore_index=True)
df = spatial_df


def zone(row):
    if row['nursery_ground_flag']:
        return 'NURSERY_POTENTIAL_PROXY'
    if row['feeding_ground_flag']:
        return 'FEEDING_POTENTIAL_PROXY'
    if row['fishery_ground_flag']:
        return 'FISHERY_GROUND_PROXY'
    return 'GENERAL_OCEANIC'


df['zone_label'] = df.apply(zone, axis=1)
print('Spatial rows:', f'{len(df):,}')
print(df['zone_label'].value_counts())
"""
    ),
    md("## 6. Validasi 2025 dan ekspor untuk website\n"),
    py(
        r"""evaluation_payload = {
    'project': 'SEACAST WPPNRI 573',
    'commodities': list(TCT_SPECIES),
    'training_period': '2015-01 to 2024-12',
    'final_validation_period': '2025-01 to 2025-12',
    'models': evaluations,
    'maxent_gate': maxent_status,
    'limitations': [
        'Statistik pelabuhan adalah agregat bulanan tanpa koordinat tangkapan per spesies.',
        'EOG VIIRS VBD adalah proxy aktivitas kapal, bukan identifikasi spesies.',
        'Feeding dan nursery adalah potential proxy sampai observasi biologis tersedia.',
        '2025 adalah final holdout dan tidak boleh dipakai untuk tuning.',
    ],
}
(REPORT_DIR / 'tct_hybrid_model_validation_2015_2025.json').write_text(
    json.dumps(evaluation_payload, indent=2, ensure_ascii=False), encoding='utf-8'
)
pd.DataFrame([
    {
        'species': species,
        'gam_rmse_cv': evaluations[species]['cv_gam']['rmse'],
        'rf_rmse_cv': evaluations[species]['cv_random_forest']['rmse'],
        'ensemble_rmse_2025': evaluations[species]['holdout_2025_ensemble']['rmse'],
        'ensemble_mae_2025': evaluations[species]['holdout_2025_ensemble']['mae'],
        'ensemble_r2_2025': evaluations[species]['holdout_2025_ensemble']['r2'],
        'baseline_rmse_2025': evaluations[species]['holdout_2025_baseline_train_median']['rmse'],
        'port_median_baseline_rmse_2025': evaluations[species]['holdout_2025_baseline_port_median']['rmse'],
        'seasonal_baseline_rmse_2025': evaluations[species]['holdout_2025_baseline_month_climatology']['rmse'],
        'beats_baseline_2025': evaluations[species]['beats_baseline_2025'],
        'residual_shrinkage': evaluations[species]['residual_shrinkage'],
        'maxent_status': maxent_status['status'],
    }
    for species in TCT_SPECIES
]).to_csv(REPORT_DIR / 'tct_hybrid_model_validation_2015_2025.csv', index=False)

for species, bundle in model_bundles.items():
    joblib.dump(bundle, MODEL_DIR / f'{species}_gam_rf_2015_2025.joblib')

prediction_path = PREDICTION_DIR / 'SEACAST_TCT_HSI_2015_2025.parquet'
df.to_parquet(prediction_path, index=False, compression='snappy')
monthly.to_csv(REPORT_DIR / 'tct_dual_port_monthly_panel.csv', index=False)
spatial_df[['port', 'cell_id', 'month_key', 'year', 'month', 'dist_port_nm', 'vbd_n_detect', 'vbd_pct_detect', 'activity_score']].to_parquet(
    PREDICTION_DIR / 'SEACAST_TCT_EOG_ACTIVITY_2015_2025.parquet', index=False, compression='snappy'
)
monthly[[
    'port', 'month_key', 'year', 'month', 'effort_trips',
    'tuna_sirip_kuning_cpue_kg_trip', 'cakalang_cpue_kg_trip', 'tongkol_krai_cpue_kg_trip',
]].to_csv(PREDICTION_DIR / 'SEACAST_TCT_TEMPORAL_PORT_PREDICTION_INPUT.csv', index=False)
df[df['year'] == final_validation_year].head(50000).to_csv(
    PREDICTION_DIR / 'SEACAST_TCT_HSI_2025_SAMPLE.csv', index=False
)

if GRID_PATH.exists():
    import geopandas as gpd
    grid = gpd.read_file(GRID_PATH)
    map_2025 = df[df['year'] == final_validation_year].groupby('cell_id', as_index=False).agg({
        'hsi_tct_max': 'max',
        'fishery_ground_score': 'max',
        'feeding_ground_potential': 'max',
        'nursery_ground_potential': 'max',
        'fishery_ground_flag': 'max',
        'feeding_ground_flag': 'max',
        'nursery_ground_flag': 'max',
    })
    grid.merge(map_2025, on='cell_id', how='left').to_file(
        GEOJSON_DIR / 'SEACAST_TCT_ZONES_2025.geojson', driver='GeoJSON'
    )

manifest = {
    'prediction_path': str(prediction_path),
    'model_dir': str(MODEL_DIR),
    'report_dir': str(REPORT_DIR),
    'geojson_dir': str(GEOJSON_DIR),
    'rows': int(len(df)),
    'months': int(df['month_key'].nunique()),
    'cells': int(df['cell_id'].nunique()),
    'ports': sorted(df['port'].dropna().unique().tolist()),
    'operational_radius_nm': MAX_OPERATIONAL_NM,
    'spatial_rows_are_local_port_zones': True,
    'maxent_status': maxent_status,
    'spatial_evidence_level': 'LOW_PROXY_NO_COORDINATED_CATCH',
    'web_integration_status': 'HOLD_FOR_SCIENTIFIC_REVIEW',
}
(REPORT_DIR / 'run_manifest.json').write_text(
    json.dumps(manifest, indent=2, ensure_ascii=False), encoding='utf-8'
)
print(json.dumps(manifest, indent=2, ensure_ascii=False))
print('SELESAI: artefak TCT GAM + RF siap untuk scientific review; integrasi web tetap HOLD.')
"""
    ),
]

payload = {
    "nbformat": 4,
    "nbformat_minor": 5,
    "metadata": {
        "colab": {"provenance": [], "toc_visible": True},
        "kernelspec": {"display_name": "Python 3", "name": "python3"},
        "language_info": {"name": "python"},
    },
    "cells": cells,
}
NOTEBOOK_PATH.parent.mkdir(parents=True, exist_ok=True)
NOTEBOOK_PATH.write_text(
    json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8"
)
print(NOTEBOOK_PATH)
