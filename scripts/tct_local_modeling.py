"""Reusable local-port feature engineering for the SEACAST TCT workflow."""

from __future__ import annotations

from pathlib import Path
from typing import Sequence
import re
import csv

import numpy as np
import pandas as pd


EARTH_RADIUS_KM = 6371.0
KM_PER_NAUTICAL_MILE = 1.852
MONTHS = {
    "januari": 1,
    "februari": 2,
    "maret": 3,
    "april": 4,
    "mei": 5,
    "juni": 6,
    "juli": 7,
    "agustus": 8,
    "september": 9,
    "oktober": 10,
    "november": 11,
    "desember": 12,
}


def haversine_nm(
    lon: pd.Series | np.ndarray | float,
    lat: pd.Series | np.ndarray | float,
    lon0: float,
    lat0: float,
) -> np.ndarray:
    """Return geodesic distance in nautical miles from one port."""

    lon_rad, lat_rad = np.radians(lon), np.radians(lat)
    lon0_rad, lat0_rad = np.radians(lon0), np.radians(lat0)
    delta_lon = lon_rad - lon0_rad
    delta_lat = lat_rad - lat0_rad
    a = np.sin(delta_lat / 2.0) ** 2 + np.cos(lat_rad) * np.cos(lat0_rad) * np.sin(delta_lon / 2.0) ** 2
    return (EARTH_RADIUS_KM * 2.0 * np.arcsin(np.sqrt(np.clip(a, 0.0, 1.0)))) / KM_PER_NAUTICAL_MILE


def select_radius(
    frame: pd.DataFrame,
    *,
    lon: float,
    lat: float,
    min_nm: float = 0.0,
    max_nm: float = 60.0,
    active_only: bool = False,
) -> pd.DataFrame:
    """Select grid cells in a port radius, optionally requiring EOG activity."""

    if min_nm < 0 or max_nm <= min_nm:
        raise ValueError("Radius harus memenuhi 0 <= min_nm < max_nm")
    result = frame.copy()
    result["dist_port_nm"] = haversine_nm(
        result["lon_center"], result["lat_center"], lon, lat
    )
    mask = result["dist_port_nm"].between(min_nm, max_nm, inclusive="both")
    if active_only:
        if "vbd_n_detect" not in result.columns:
            raise ValueError("active_only membutuhkan kolom vbd_n_detect")
        mask &= pd.to_numeric(result["vbd_n_detect"], errors="coerce").fillna(0).gt(0)
    return result.loc[mask].copy()


def _mean_or_nan(frame: pd.DataFrame, column: str) -> float:
    if column not in frame.columns or frame.empty:
        return float("nan")
    values = pd.to_numeric(frame[column], errors="coerce")
    return float(values.mean()) if values.notna().any() else float("nan")


def build_local_features(
    frame: pd.DataFrame,
    *,
    port_name: str,
    port_lon: float,
    port_lat: float,
    max_nm: float = 60.0,
    inshore_nm: float = 12.0,
    offshore_min_nm: float = 30.0,
) -> pd.DataFrame:
    """Aggregate grid-month data inside a port's operational zone.

    The upwelling contrast follows the requested definition:
    ``SST inshore - SST offshore``. A negative value therefore means the
    inshore water is cooler than the offshore reference.
    """

    local = select_radius(
        frame,
        lon=port_lon,
        lat=port_lat,
        max_nm=max_nm,
    )
    if local.empty:
        raise ValueError(f"Tidak ada sel dalam radius {max_nm} NM untuk {port_name}")
    local["eog_active"] = pd.to_numeric(local.get("vbd_n_detect", 0), errors="coerce").fillna(0).gt(0)
    records: list[dict[str, object]] = []
    for month_key, group in local.groupby("month_key", sort=True):
        inshore = group[group["dist_port_nm"] <= inshore_nm]
        offshore = group[group["dist_port_nm"] >= offshore_min_nm]
        row: dict[str, object] = {
            "port": port_name,
            "month_key": month_key,
            "local_cell_count": int(len(group)),
            "local_active_cell_count": int(group["eog_active"].sum()),
            "local_active_cell_share": float(group["eog_active"].mean()),
            "dist_port_nm_max": float(group["dist_port_nm"].max()),
            "sst_inshore_mean": _mean_or_nan(inshore, "sst_c"),
            "sst_offshore_mean": _mean_or_nan(offshore, "sst_c"),
        }
        row["upwelling_contrast_index"] = row["sst_inshore_mean"] - row["sst_offshore_mean"]
        for column in (
            "sst_c",
            "chla_mg_m3",
            "sst_gradient",
            "thermal_front",
            "current_speed",
            "wind_speed",
            "wind_stress",
            "ekman_upwelling",
            "depth_m",
            "vbd_n_detect",
            "vbd_pct_detect",
        ):
            row[column] = _mean_or_nan(group, column)
        row["vbd_active_n_detect"] = _mean_or_nan(group[group["eog_active"]], "vbd_n_detect")
        row["vbd_active_pct_detect"] = _mean_or_nan(group[group["eog_active"]], "vbd_pct_detect")
        records.append(row)
    return pd.DataFrame.from_records(records).sort_values("month_key").reset_index(drop=True)


def add_anomaly_and_lag_features(
    panel: pd.DataFrame,
    *,
    value_columns: Sequence[str],
    group_columns: Sequence[str] = ("port",),
    climatology_mask: pd.Series | None = None,
) -> pd.DataFrame:
    """Add calendar-month climatology anomalies and one-month lags."""

    result = panel.copy()
    if climatology_mask is None:
        result["_climatology_mask"] = True
    else:
        result["_climatology_mask"] = pd.Series(climatology_mask, index=panel.index).fillna(False).to_numpy()
    result = result.sort_values([*group_columns, "month_key"]).reset_index(drop=True)
    climatology_mask = result.pop("_climatology_mask").astype(bool)
    for column in value_columns:
        numeric = pd.to_numeric(result[column], errors="coerce")
        key_columns = [*group_columns, "month"]
        climate = (
            result.loc[climatology_mask]
            .assign(_value=numeric.loc[climatology_mask])
            .groupby(key_columns)["_value"]
            .mean()
        )
        climate_values = pd.MultiIndex.from_frame(result[key_columns]).map(climate)
        result[f"{column}_climatology"] = np.asarray(climate_values, dtype=float)
        result[f"anom_{column}"] = numeric - result[f"{column}_climatology"]
        result[f"{column}_lag1"] = result.groupby(list(group_columns), sort=False)[column].shift(1)
    return result


def _text(value: object) -> str:
    if pd.isna(value):
        return ""
    return str(value).strip()


def _number(value: object, default: float = 0.0) -> float:
    try:
        value = float(str(value).replace(",", "").strip())
    except (TypeError, ValueError):
        return default
    return value if np.isfinite(value) else default


def parse_cilacap_statistics(path: str | Path) -> pd.DataFrame:
    """Parse the normalized PPS Cilacap CSV exported from its workbook.

    The source workbook contains monthly landing and GT-trip blocks for
    2016--2025. Production values are tonnes in the workbook/CSV and are
    converted to kilograms here to match the PPN Prigi master table.
    """

    with Path(path).open("r", encoding="utf-8-sig", newline="") as handle:
        rows = list(csv.reader(handle))
    fleet_by_year: dict[int, dict[str, float]] = {}
    trip_by_month: dict[str, float] = {}
    production_by_month: dict[str, dict[str, float]] = {}
    current_trip_year: int | None = None
    current_production_year: int | None = None
    for row in rows:
        row = list(row)
        while len(row) < 8:
            row.append(None)
        first, second, third, fourth = (_text(row[i]) for i in range(4))
        if third.lower() == "tahun" and "kapal motor" in fourth.lower():
            continue
        if re.fullmatch(r"20\d{2}", third) and "kapal motor" not in fourth.lower():
            year = int(third)
            fleet_by_year[year] = {
                "fleet_lt_10gt": _number(row[3]),
                "fleet_10_20gt": _number(row[4]),
                "fleet_20_30gt": _number(row[5]),
                "fleet_gt_30gt": _number(row[6]),
                "total_fleet": _number(row[7]),
            }
        if re.fullmatch(r"20\d{2}", first) and third.lower() == "bulan" and fourth.lower().startswith("km"):
            current_trip_year = int(first)
            continue
        if re.fullmatch(r"20\d{2}", first) and third.lower() == "bulan" and "yellowfin" in fourth.lower():
            current_trip_year = None
            current_production_year = int(first)
            continue
        month = MONTHS.get(third.lower())
        if month is None:
            continue
        if current_trip_year is not None:
            key = f"{current_trip_year:04d}-{month:02d}"
            trip_by_month[key] = _number(row[7])
        if current_production_year is not None:
            key = f"{current_production_year:04d}-{month:02d}"
            production_by_month[key] = {
                "tuna_sirip_kuning_catch_kg": _number(row[3]) * 1000.0,
                "cakalang_catch_kg": _number(row[4]) * 1000.0,
                "tongkol_krai_catch_kg": _number(row[5]) * 1000.0,
            }
    records: list[dict[str, object]] = []
    for month_key, catches in sorted(production_by_month.items()):
        year, month = (int(value) for value in month_key.split("-"))
        fleet = fleet_by_year.get(year, {})
        trips = trip_by_month.get(month_key, 0.0)
        total_catch = sum(catches.values())
        record = {
            "port": "cilacap",
            "month_key": month_key,
            "year": year,
            "month": month,
            "effort_trips": trips,
            "total_trip_gt": trips,
            "total_fleet": fleet.get("total_fleet", np.nan),
            **fleet,
            **catches,
        }
        record["total_tct_catch_kg"] = total_catch
        record["cpue_tct_kg_trip"] = total_catch / max(trips, 1.0)
        for species in ("tuna_sirip_kuning", "cakalang", "tongkol_krai"):
            record[f"{species}_cpue_kg_trip"] = record[f"{species}_catch_kg"] / max(trips, 1.0)
        records.append(record)
    result = pd.DataFrame.from_records(records)
    if result.empty:
        raise ValueError(f"Tidak ada blok bulanan TCT yang terbaca dari {path}")
    return result.sort_values("month_key").reset_index(drop=True)

