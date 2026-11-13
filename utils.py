"""Fungsi bersama aplikasi. Tanpa data/master.csv atau unggahan, dipakai DATA DUMMY."""
import math
from pathlib import Path

import numpy as np
import pandas as pd
import streamlit as st
from sklearn.ensemble import GradientBoostingRegressor, RandomForestRegressor
from sklearn.inspection import permutation_importance
from sklearn.linear_model import Ridge
from sklearn.pipeline import make_pipeline
from sklearn.preprocessing import StandardScaler

DATA = Path(__file__).parent / "data"
TARGET = "total_pelagis_besar_kg"
SPECIES = ["cakalang_kg", "tuna_kg", "tongkol_kg", "tenggiri_kg"]
CLIMATE = ["sst_mean", "chl_mean", "wind_speed", "hs_mean", "dmi", "oni"]
SEASON = ["month_sin", "month_cos"]
LAGS = ["lag1_prod", "lag12_prod"]
FEATURES = CLIMATE + SEASON + LAGS
REQUIRED = ["year_month", TARGET] + CLIMATE
LABELS = {"sst_mean": "SST (C)", "chl_mean": "Klorofil-a (mg/m3)", "wind_speed": "Angin (m/s)",
          "hs_mean": "Tinggi gelombang (m)", "dmi": "IOD / DMI", "oni": "ENSO / ONI"}
MONTHS = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"]
# ASUMSI koordinat (verifikasi sendiri): pelabuhan dan kotak ekstraksi iklim
# Koordinat terverifikasi Phase 4.1: PPN Prigi & Sub-AOI Prigi (50x41 = 2,050 grid centers, 59,550.43 km2)
PORT = (-8.2818, 111.7314)
STUDY_BOX = {"lat": (-10.45, -8.00), "lon": (111.00, 113.00)}


def species_in(d):
    return [s for s in SPECIES if s in d.columns]


def _ar1(rng, n, phi, sd):
    x = np.zeros(n)
    for i in range(1, n):
        x[i] = phi * x[i - 1] + rng.normal(0, sd)
    return x


def _make_dummy() -> pd.DataFrame:
    """DUMMY: hanya untuk uji tampilan. Bukan data Prigi."""
    rng = np.random.default_rng(42)
    d = pd.DataFrame({"year_month": pd.date_range("2015-01-01", "2025-12-01", freq="MS")})
    n = len(d)
    m = d.year_month.dt.month.values
    yrs = d.year_month.dt.year.values - 2015
    c = lambda peak: np.cos(2 * np.pi * (m - peak) / 12)
    d["dmi"] = _ar1(rng, n, 0.7, 0.25)
    d["oni"] = _ar1(rng, n, 0.9, 0.2)
    d["sst_mean"] = 28.5 + 1.3 * c(2) - 0.3 * d.dmi + rng.normal(0, .25, n)
    d["chl_mean"] = 0.28 + 0.15 * c(8) + 0.05 * d.dmi + rng.normal(0, .03, n)
    d["wind_speed"] = 5 + 2 * c(8) + rng.normal(0, .5, n)
    d["hs_mean"] = 1.3 + 0.6 * c(7) + rng.normal(0, .12, n)
    d["bad_weather_days"] = np.clip(np.round((d.hs_mean - 1.3) * 10 + 4 + rng.normal(0, 1.5, n)), 0, 30)
    d["effort_trips"] = np.round(400 + 60 * c(9) - 40 * (d.hs_mean - 1.3) + rng.normal(0, 15, n))
    d["cakalang_kg"] = 2200 + 900 * c(9) + 4000 * (d.chl_mean - .28) + 400 * d.dmi - 30 * yrs + rng.normal(0, 150, n)
    d["tuna_kg"] = 1200 + 500 * c(10) + 300 * d.dmi + rng.normal(0, 100, n)
    d["tongkol_kg"] = 2000 + 600 * c(6) - 250 * (d.wind_speed - 5) + rng.normal(0, 120, n)
    d["tenggiri_kg"] = 800 + 250 * c(4) + rng.normal(0, 60, n)
    for s in SPECIES:
        d[s] = d[s].clip(lower=0)
    d[TARGET] = d[SPECIES].sum(axis=1)
    d["cpue_proxy"] = d[TARGET] / d.effort_trips
    return d


def add_features(d):
    d = d.sort_values("year_month").drop_duplicates("year_month").reset_index(drop=True)
    ang = 2 * np.pi * d.year_month.dt.month / 12
    d["month_sin"], d["month_cos"] = np.sin(ang), np.cos(ang)
    d["lag1_prod"], d["lag12_prod"] = d[TARGET].shift(1), d[TARGET].shift(12)
    return d


@st.cache_data
def _load_default():
    p = DATA / "master.csv"
    if p.exists():
        return add_features(pd.read_csv(p, parse_dates=["year_month"])), False
    return add_features(_make_dummy()), True


def get_master():
    """Mengembalikan (dataframe, is_dummy). Unggahan pengguna diprioritaskan."""
    if "uploaded_df" in st.session_state:
        return add_features(st.session_state["uploaded_df"].copy()), False
    return _load_default()


def banner(dummy: bool):
    if dummy:
        st.warning("DATA DUMMY: bukan data PPN Prigi. Unggah data nyata di halaman 'Data dan Keterbatasan' "
                   "atau letakkan di data/master.csv. Semua angka di sini tidak bermakna ilmiah.")


def smape(y, p):
    y, p = np.asarray(y, float), np.asarray(p, float)
    return float(np.mean(2 * np.abs(p - y) / (np.abs(y) + np.abs(p) + 1e-9)) * 100)


def _row(name, y, p):
    y, p = np.asarray(y, float), np.asarray(p, float)
    return {"Model": name, "MAE": float(np.mean(np.abs(y - p))),
            "RMSE": float(np.sqrt(np.mean((y - p) ** 2))), "sMAPE %": smape(y, p)}


def get_cutoff(d, test_months=24):
    return d.year_month.max() - pd.DateOffset(months=test_months - 1)


def _models():
    return {
        "Ridge": make_pipeline(StandardScaler(), Ridge(alpha=1.0)),
        "Random Forest": RandomForestRegressor(n_estimators=300, min_samples_leaf=3, random_state=0),
        "Gradient Boosting": GradientBoostingRegressor(random_state=0),
    }


@st.cache_resource(show_spinner="Melatih model...")
def train_models(d):
    dd = d.dropna(subset=FEATURES + [TARGET])
    cutoff = get_cutoff(dd)
    tr, te = dd[dd.year_month < cutoff], dd[dd.year_month >= cutoff]
    models, rows = {}, [_row("Baseline (seasonal naive)", te[TARGET], te.lag12_prod)]
    preds = pd.DataFrame({"year_month": te.year_month.values, "aktual": te[TARGET].values,
                          "Baseline (seasonal naive)": te.lag12_prod.values})
    for n, mdl in _models().items():
        mdl.fit(tr[FEATURES], tr[TARGET])
        preds[n] = mdl.predict(te[FEATURES])
        rows.append(_row(n, te[TARGET], preds[n]))
        models[n] = mdl
    pi = permutation_importance(models["Random Forest"], te[FEATURES], te[TARGET], n_repeats=20,
                                random_state=0, scoring="neg_mean_absolute_error")
    return {"models": models, "metrics": pd.DataFrame(rows), "preds": preds, "train": tr,
            "test": te, "cutoff": cutoff, "perm": pd.Series(pi.importances_mean, index=FEATURES)}


@st.cache_data(show_spinner="Menghitung ablasi...")
def ablation(d):
    dd = d.dropna(subset=FEATURES + [TARGET])
    cutoff = get_cutoff(dd)
    tr, te = dd[dd.year_month < cutoff], dd[dd.year_month >= cutoff]
    sets = {"Lengkap (iklim + musim + lag)": FEATURES, "Tanpa iklim (musim + lag)": SEASON + LAGS,
            "Tanpa lag (iklim + musim)": CLIMATE + SEASON}
    rows = []
    for n, f in sets.items():
        m = RandomForestRegressor(n_estimators=300, min_samples_leaf=3, random_state=0).fit(tr[f], tr[TARGET])
        rows.append({"Skenario": n, "MAE uji": float(np.mean(np.abs(te[TARGET] - m.predict(te[f]))))})
    rows.append({"Skenario": "Baseline musiman", "MAE uji": float(np.mean(np.abs(te[TARGET] - te.lag12_prod)))})
    return pd.DataFrame(rows)


@st.cache_data(show_spinner="Rolling-origin CV...")
def rolling_cv(d, n_folds=4):
    dd = d.dropna(subset=FEATURES + [TARGET])
    years = sorted(dd.year_month.dt.year.unique())[-n_folds:]
    rows = []
    for y in years:
        tr, te = dd[dd.year_month.dt.year < y], dd[dd.year_month.dt.year == y]
        if len(tr) < 36 or len(te) == 0:
            continue
        m = RandomForestRegressor(n_estimators=200, min_samples_leaf=3, random_state=0).fit(tr[FEATURES], tr[TARGET])
        rows.append({"Tahun uji": int(y), "MAE Random Forest": float(np.mean(np.abs(te[TARGET] - m.predict(te[FEATURES])))),
                     "MAE Baseline": float(np.mean(np.abs(te[TARGET] - te.lag12_prod)))})
    return pd.DataFrame(rows)


def mann_kendall(x):
    """Uji tren Mann-Kendall sederhana (tanpa koreksi ties) + kemiringan Sen."""
    x = np.asarray(x, float)
    n = len(x)
    s = sum(np.sign(x[j] - x[i]) for i in range(n - 1) for j in range(i + 1, n))
    var = n * (n - 1) * (2 * n + 5) / 18
    z = 0.0 if s == 0 else (s - np.sign(s)) / math.sqrt(var)
    p = math.erfc(abs(z) / math.sqrt(2))
    sen = float(np.median([(x[j] - x[i]) / (j - i) for i in range(n - 1) for j in range(i + 1, n)]))
    return {"S": float(s), "Z": float(z), "p": float(p), "sen": sen}


def decompose(s: pd.Series):
    """Dekomposisi multiplikatif klasik (tren rata-rata bergerak 12 bulan)."""
    trend = s.rolling(12, center=True, min_periods=12).mean()
    idx = (s / trend).groupby(s.index.month).mean()
    idx = idx / idx.mean()
    seasonal = pd.Series(idx.reindex(s.index.month).values, index=s.index)
    return trend, seasonal, s / (trend * seasonal)
