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
- EOG VIIRS VBD sebagai proxy aktivitas kapal/effort, bukan label spesies.
- MaxEnt **tidak dilatih** karena **NO SPATIAL OCCURRENCE**: belum ada koordinat tangkapan/occurrence per spesies.

Pembagian waktu:
- 2015–2024: fitting.
- 2025: final holdout validation, tidak digunakan untuk fitting.
- Hasil spasial disebut relative suitability/proxy sampai ada logbook berkoordinat, VMS/AIS yang terhubung dengan operasi, observer, atau occurrence lain yang sah.

Feeding ground dan nursery ground ditulis sebagai potential proxy, bukan area biologis terkonfirmasi.
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
GRID_PATH = DATA_DIR / 'geojson' / 'canonical_grid_8129_cells.geojson'
OCCURRENCE_PATH = DATA_DIR / 'modeling' / 'tct_occurrences.parquet'
OUTPUT_DIR = PROJECT_ROOT / 'outputs' / 'tct_hybrid_2015_2025'
MODEL_DIR = OUTPUT_DIR / 'models'
PREDICTION_DIR = OUTPUT_DIR / 'predictions'
REPORT_DIR = OUTPUT_DIR / 'reports'
GEOJSON_DIR = OUTPUT_DIR / 'geojson'
for directory in (MODEL_DIR, PREDICTION_DIR, REPORT_DIR, GEOJSON_DIR):
    directory.mkdir(parents=True, exist_ok=True)

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
        r"""PRIGI_LON, PRIGI_LAT = 111.7275, -8.2833
FEATURE_COLS = [
    'sst_c', 'chla_mg_m3', 'sst_gradient', 'vbd_pct_detect',
]


def haversine_km(lon, lat, lon0, lat0):
    lon, lat, lon0, lat0 = map(np.radians, [lon, lat, lon0, lat0])
    a = np.sin((lat - lat0) / 2) ** 2 + np.cos(lat) * np.cos(lat0) * np.sin((lon - lon0) / 2) ** 2
    return 6371.0 * 2 * np.arcsin(np.sqrt(np.clip(a, 0, 1)))


df['dist_port_km'] = haversine_km(df['lon_center'], df['lat_center'], PRIGI_LON, PRIGI_LAT)
df['dist_port_nm'] = df['dist_port_km'] / 1.852
df['vbd_activity_raw'] = np.log1p(df['vbd_n_detect']) + 0.25 * np.log1p(df['vbd_pct_detect'])

monthly_env = df.groupby('month_key', as_index=False)[FEATURE_COLS].mean()
monthly_env['vbd_active_cells'] = df.assign(active=df['vbd_n_detect'] > 0).groupby('month_key')['active'].mean().to_numpy()
monthly = monthly_env.merge(
    master[['month_key', 'year', 'month', 'effort_trips']
           + [s + '_catch_kg' for s in TCT_SPECIES]
           + [s + '_cpue_kg_trip' for s in TCT_SPECIES]],
    on='month_key', how='inner', validate='one_to_one'
).sort_values('month_key').reset_index(drop=True)

if len(monthly) != EXPECTED_MONTHS:
    raise RuntimeError(f'Gabungan bulanan harus {EXPECTED_MONTHS}, ditemukan {len(monthly)}')
print('Train period:', monthly.month_key.min(), 'to', monthly.loc[monthly.year <= train_last_year, 'month_key'].max())
print('Final holdout:', monthly.loc[monthly.year == final_validation_year, 'month_key'].min(), 'to', monthly.loc[monthly.year == final_validation_year, 'month_key'].max())
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


def fit_species(species):
    target = species + '_cpue_kg_trip'
    train_mask = monthly['year'] <= train_last_year
    holdout_mask = monthly['year'] == final_validation_year
    x_train_raw = monthly.loc[train_mask, FEATURE_COLS].to_numpy()
    x_holdout_raw = monthly.loc[holdout_mask, FEATURE_COLS].to_numpy()
    y_train = np.log1p(monthly.loc[train_mask, target].clip(lower=0).to_numpy())
    y_holdout = monthly.loc[holdout_mask, target].clip(lower=0).to_numpy()

    tscv, gam_cv, rf_cv = TimeSeriesSplit(n_splits=4), [], []
    for fold, (tr, va) in enumerate(tscv.split(x_train_raw), start=1):
        fold_imputer, fold_scaler = SimpleImputer(strategy='median'), StandardScaler()
        x_fold_train = fold_scaler.fit_transform(fold_imputer.fit_transform(x_train_raw[tr]))
        x_fold_val = fold_scaler.transform(fold_imputer.transform(x_train_raw[va]))
        gam = make_gam(len(FEATURE_COLS)).fit(x_fold_train, y_train[tr])
        rf = RandomForestRegressor(
            n_estimators=250, max_features='sqrt', min_samples_leaf=4,
            random_state=RANDOM_STATE + fold, n_jobs=-1
        ).fit(x_fold_train, y_train[tr])
        y_va = np.expm1(y_train[va]).clip(min=0)
        gam_cv.append(metrics(y_va, np.expm1(gam.predict(x_fold_val)).clip(min=0)))
        rf_cv.append(metrics(y_va, np.expm1(rf.predict(x_fold_val)).clip(min=0)))

    imputer, scaler = SimpleImputer(strategy='median'), StandardScaler()
    x_train = scaler.fit_transform(imputer.fit_transform(x_train_raw))
    x_holdout = scaler.transform(imputer.transform(x_holdout_raw))
    gam = make_gam(len(FEATURE_COLS)).fit(x_train, y_train)
    rf = RandomForestRegressor(
        n_estimators=400, max_features='sqrt', min_samples_leaf=4,
        random_state=RANDOM_STATE, n_jobs=-1
    ).fit(x_train, y_train)
    gam_rmse, rf_rmse = np.mean([x['rmse'] for x in gam_cv]), np.mean([x['rmse'] for x in rf_cv])
    inverse = np.array([1 / max(gam_rmse, 1e-9), 1 / max(rf_rmse, 1e-9)])
    weights = inverse / inverse.sum()
    gam_holdout = np.expm1(gam.predict(x_holdout)).clip(min=0)
    rf_holdout = np.expm1(rf.predict(x_holdout)).clip(min=0)
    ensemble_holdout = weights[0] * gam_holdout + weights[1] * rf_holdout
    baseline_holdout = np.full(len(y_holdout), np.median(np.expm1(y_train)))
    baseline_evaluation = metrics(y_holdout, baseline_holdout)
    seasonal_medians = monthly.loc[train_mask].groupby('month')[target].median()
    seasonal_holdout = monthly.loc[holdout_mask, 'month'].map(seasonal_medians).to_numpy()
    seasonal_evaluation = metrics(y_holdout, seasonal_holdout)
    ensemble_evaluation = metrics(y_holdout, ensemble_holdout)

    evaluation = {
        'species': species,
        'cv_gam': {key: float(np.mean([x[key] for x in gam_cv])) for key in ['rmse', 'mae', 'r2']},
        'cv_random_forest': {key: float(np.mean([x[key] for x in rf_cv])) for key in ['rmse', 'mae', 'r2']},
        'holdout_2025_gam': metrics(y_holdout, gam_holdout),
        'holdout_2025_random_forest': metrics(y_holdout, rf_holdout),
        'holdout_2025_ensemble': ensemble_evaluation,
        'holdout_2025_baseline_train_median': baseline_evaluation,
        'holdout_2025_baseline_month_climatology': seasonal_evaluation,
        'beats_baseline_2025': bool(ensemble_evaluation['rmse'] < min(
            baseline_evaluation['rmse'], seasonal_evaluation['rmse'])),
        'ensemble_weights': {'gam': float(weights[0]), 'random_forest': float(weights[1])},
        'maxent': maxent_status,
        'spatial_evidence': 'monthly_port_aggregate_proxy_no_catch_coordinates',
    }
    bundle = {
        'species': species,
        'feature_cols': FEATURE_COLS,
        'imputer': imputer,
        'scaler': scaler,
        'gam': gam,
        'random_forest': rf,
        'weights': {'gam': float(weights[0]), 'random_forest': float(weights[1])},
        'method': 'GAM + Random Forest; MaxEnt gated off without occurrence coordinates',
        'train_last_year': train_last_year,
        'final_validation_year': final_validation_year,
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


df['activity_score'] = df.groupby('month_key')['vbd_activity_raw'].transform(month_minmax)
df['chla_score'] = df.groupby('month_key')['chla_mg_m3'].transform(lambda x: month_minmax(x.clip(lower=0)))
df['front_score'] = df.groupby('month_key')['sst_gradient'].transform(lambda x: month_minmax(x.clip(lower=0)))
df['ekman_score'] = df.groupby('month_key')['ekman_upwelling'].transform(lambda x: month_minmax(x.clip(lower=0)))
df['feeding_ground_potential'] = (
    0.40 * df['chla_score'] + 0.30 * df['front_score']
    + 0.20 * df['thermal_front'].clip(0, 1).fillna(0)
    + 0.10 * df['ekman_score']
).clip(0, 1)
df['nursery_ground_potential'] = np.where(
    (df['depth_m'] <= 150) & (df['dist_port_nm'] <= 15),
    (0.60 * df['chla_score'] + 0.40 * (1 - (df['dist_port_nm'] / 15).clip(0, 1))).clip(0, 1),
    0.0,
)

cell_matrix = pd.DataFrame({column: pd.to_numeric(df[column], errors='coerce') for column in FEATURE_COLS})
for species, bundle in model_bundles.items():
    x_cells = bundle['scaler'].transform(bundle['imputer'].transform(cell_matrix))
    pred_gam = np.expm1(bundle['gam'].predict(x_cells)).clip(min=0)
    pred_rf = np.expm1(bundle['random_forest'].predict(x_cells)).clip(min=0)
    pred = bundle['weights']['gam'] * pred_gam + bundle['weights']['random_forest'] * pred_rf
    raw_col, hsi_col = 'predicted_cpue_' + species, 'hsi_' + species
    df[raw_col] = pred
    df[hsi_col] = df.groupby('month_key')[raw_col].rank(pct=True).fillna(0).clip(0, 1)
    df['fishery_ground_' + species] = (df[hsi_col] * (0.5 + 0.5 * df['activity_score'])).clip(0, 1)

df['hsi_tct_max'] = df[['hsi_' + species for species in TCT_SPECIES]].max(axis=1)
df['fishery_ground_score'] = df[['fishery_ground_' + species for species in TCT_SPECIES]].max(axis=1)
df['fishery_ground_flag'] = (df['fishery_ground_score'] >= 0.70) & (df['activity_score'] >= 0.50)
df['feeding_ground_flag'] = df['feeding_ground_potential'] >= 0.60
df['nursery_ground_flag'] = df['nursery_ground_potential'] >= 0.60
df['spatial_evidence_level'] = 'LOW_PROXY_NO_COORDINATED_CATCH'
df['feeding_evidence_level'] = 'POTENTIAL_PROXY_NO_FEEDING_OBSERVATION'
df['nursery_evidence_level'] = 'POTENTIAL_PROXY_NO_JUVENILE_OBSERVATION'
df['maxent_status'] = maxent_status['status']
df['model_ensemble'] = 'GAM_RF_only'


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
        'seasonal_baseline_rmse_2025': evaluations[species]['holdout_2025_baseline_month_climatology']['rmse'],
        'beats_baseline_2025': evaluations[species]['beats_baseline_2025'],
        'maxent_status': maxent_status['status'],
    }
    for species in TCT_SPECIES
]).to_csv(REPORT_DIR / 'tct_hybrid_model_validation_2015_2025.csv', index=False)

for species, bundle in model_bundles.items():
    joblib.dump(bundle, MODEL_DIR / f'{species}_gam_rf_2015_2025.joblib')

prediction_path = PREDICTION_DIR / 'SEACAST_TCT_HSI_2015_2025.parquet'
df.to_parquet(prediction_path, index=False, compression='snappy')
df[df['year'] == final_validation_year].head(50000).to_csv(
    PREDICTION_DIR / 'SEACAST_TCT_HSI_2025_SAMPLE.csv', index=False
)

if GRID_PATH.exists():
    import geopandas as gpd
    grid = gpd.read_file(GRID_PATH)
    map_2025 = df[df['year'] == final_validation_year].groupby('cell_id', as_index=False).agg({
        'hsi_tct_max': 'mean',
        'fishery_ground_score': 'mean',
        'feeding_ground_potential': 'mean',
        'nursery_ground_potential': 'mean',
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
