"""Normalize EOG monthly VBD GeoTIFF archives onto the SEACAST grid.

The raw EOG archives contain national 15-arcsecond summary rasters. This
pipeline aggregates them to the canonical 0.1-degree SEACAST cells so the
result can be joined to the monthly GEE predictor CSVs by ``cell_id`` and
``month``.

Run with the QGIS Python runtime because it provides GDAL, GeoPandas and
PyArrow on Windows:

    "C:\\Program Files\\QGIS 4.0.3\\apps\\Python312\\python.exe" \
        scripts/prepare_vbd_monthly.py
"""

from __future__ import annotations

import argparse
import csv
import json
import math
import os
import re
import tempfile
import zipfile
from datetime import date, datetime
from pathlib import Path
from typing import Iterable, Sequence

# On Windows, PostgreSQL may put an incompatible proj.db earlier on the
# search path than QGIS. Prefer the QGIS database when this runtime exists.
_qgis_proj = Path(r"C:\Program Files\QGIS 4.0.3\share\proj")
if _qgis_proj.exists():
    os.environ["PROJ_LIB"] = str(_qgis_proj)
    os.environ.setdefault(
        "GDAL_DATA", r"C:\Program Files\QGIS 4.0.3\share\gdal"
    )

import geopandas as gpd
import numpy as np
import pandas as pd
from osgeo import gdal


ARCHIVE_RE = re.compile(
    r"^VBD_npp_(?P<start>\d{8})-(?P<end>\d{8})_idn_v23\.zip$"
)
METRIC_NAMES = ("cvg", "avg_rade9", "n_detect", "pct_detect")
OUTPUT_COLUMNS = (
    "month",
    "cell_id",
    "lat_center",
    "lon_center",
    "ocean_frac",
    "vbd_cvg",
    "vbd_avg_rade9",
    "vbd_n_detect",
    "vbd_pct_detect",
    "vbd_valid_pixel_count",
    "vbd_avg_rade9_missing",
    "vbd_source_start",
    "vbd_source_end",
    "vbd_partial_period",
    "vbd_source_file",
)


def progress_payload(
    completed_months: Sequence[str],
    target_months: Sequence[str],
    rows: int,
    status: str,
    error: str | None = None,
) -> dict[str, object]:
    completed = list(dict.fromkeys(completed_months))
    targets = list(dict.fromkeys(target_months))
    payload: dict[str, object] = {
        "status": status,
        "completed_months": len(completed),
        "target_months": len(targets),
        "completed": completed,
        "remaining_months": [month for month in targets if month not in completed],
        "rows": rows,
        "updated_at": datetime.now().astimezone().isoformat(timespec="seconds"),
    }
    if error:
        payload["error"] = error
    return payload


def month_key_from_filename(filename: str) -> str:
    """Return the month represented by the archive start date.

    EOG has one known malformed archive name (February 2024 has a 2022 end
    year). The start date remains authoritative for joining to GEE data.
    """

    match = ARCHIVE_RE.match(Path(filename).name)
    if not match:
        raise ValueError(f"Unsupported EOG archive name: {filename}")
    return datetime.strptime(match.group("start"), "%Y%m%d").strftime("%Y-%m")


def _parse_archive_dates(filename: str) -> tuple[date, date]:
    match = ARCHIVE_RE.match(Path(filename).name)
    if not match:
        raise ValueError(f"Unsupported EOG archive name: {filename}")

    start = datetime.strptime(match.group("start"), "%Y%m%d").date()
    raw_end = match.group("end")
    try:
        end = datetime.strptime(raw_end, "%Y%m%d").date()
    except ValueError:
        end = None

    # Repair the known 2024-02 filename typo without changing the raw archive.
    if end is None or (end.year != start.year and end.month == start.month):
        end = date(start.year, int(raw_end[4:6]), int(raw_end[6:8]))
    return start, end


def aggregate_cell_metrics(
    cell_indices: Sequence[int],
    cvg: Sequence[float],
    n_detect: Sequence[float],
    pct_detect: Sequence[float] | None,
    avg_rade9: Sequence[float] | None,
) -> list[dict[str, float | bool | None]]:
    """Aggregate source raster pixels belonging to each canonical cell.

    ``cvg`` and ``n_detect`` are additive. Detection percentage is recomputed
    from their sums, while radiance is weighted by the number of detections.
    This avoids averaging a low-coverage pixel equally with a high-coverage
    pixel.
    """

    cell_indices_arr = np.asarray(cell_indices, dtype=np.int64)
    cvg_arr = np.asarray(cvg, dtype=np.float64)
    detect_arr = np.asarray(n_detect, dtype=np.float64)
    pct_arr = None if pct_detect is None else np.asarray(pct_detect, dtype=np.float64)
    rade_arr = None if avg_rade9 is None else np.asarray(avg_rade9, dtype=np.float64)

    unique_cells, inverse = np.unique(cell_indices_arr, return_inverse=True)
    safe_cvg = np.nan_to_num(cvg_arr, nan=0.0)
    safe_detect = np.nan_to_num(detect_arr, nan=0.0)
    cvg_sum = np.bincount(inverse, weights=safe_cvg, minlength=len(unique_cells))
    detect_sum = np.bincount(inverse, weights=safe_detect, minlength=len(unique_cells))
    pct_values = np.divide(
        detect_sum * 100.0,
        cvg_sum,
        out=np.full(len(unique_cells), np.nan),
        where=cvg_sum > 0,
    )

    if pct_arr is not None:
        valid_pct = np.isfinite(pct_arr)
        pct_sum = np.bincount(
            inverse[valid_pct],
            weights=pct_arr[valid_pct] * safe_cvg[valid_pct],
            minlength=len(unique_cells),
        )
        fallback_pct = np.bincount(
            inverse[valid_pct], weights=safe_cvg[valid_pct], minlength=len(unique_cells)
        )
        fallback_values = np.divide(
            pct_sum,
            fallback_pct,
            out=np.full(len(unique_cells), np.nan),
            where=fallback_pct > 0,
        )
        pct_values = np.where(np.isfinite(pct_values), pct_values, fallback_values)

    rade_values = np.full(len(unique_cells), np.nan)
    rade_missing = np.ones(len(unique_cells), dtype=bool)
    if rade_arr is not None:
        valid_rade = np.isfinite(rade_arr) & np.isfinite(safe_detect) & (safe_detect > 0)
        rade_num = np.bincount(
            inverse[valid_rade],
            weights=rade_arr[valid_rade] * safe_detect[valid_rade],
            minlength=len(unique_cells),
        )
        rade_den = np.bincount(
            inverse[valid_rade], weights=safe_detect[valid_rade], minlength=len(unique_cells)
        )
        rade_values = np.divide(
            rade_num,
            rade_den,
            out=np.full(len(unique_cells), np.nan),
            where=rade_den > 0,
        )
        rade_missing = rade_den <= 0

    return [
        {
            "cell_index": int(cell_index),
            "vbd_cvg": float(cvg_sum[position]),
            "vbd_n_detect": float(detect_sum[position]),
            "vbd_pct_detect": None
            if not np.isfinite(pct_values[position])
            else float(pct_values[position]),
            "vbd_avg_rade9": None
            if not np.isfinite(rade_values[position])
            else float(rade_values[position]),
            "vbd_avg_rade9_missing": bool(rade_missing[position]),
        }
        for position, cell_index in enumerate(unique_cells)
    ]


def _metric_path(extracted_dir: Path, metric: str) -> Path | None:
    matches = sorted(extracted_dir.glob(f"*.{metric}.tif"))
    return matches[0] if matches else None


def _read_grid(grid_path: Path) -> gpd.GeoDataFrame:
    grid = gpd.read_file(grid_path)
    if "cell_id" not in grid.columns:
        raise ValueError("Grid is missing column: cell_id")
    if "lat_center" not in grid.columns or "lon_center" not in grid.columns:
        bounds = grid.geometry.bounds
        grid["lon_center"] = (bounds["minx"] + bounds["maxx"]) / 2.0
        grid["lat_center"] = (bounds["miny"] + bounds["maxy"]) / 2.0
    if "ocean_frac" not in grid.columns:
        grid["ocean_frac"] = np.nan
    return grid.reset_index(drop=True)


def _grid_lookup(grid: gpd.GeoDataFrame) -> tuple[np.ndarray, float, float, float, float]:
    lats = np.sort(grid["lat_center"].astype(float).unique())
    lons = np.sort(grid["lon_center"].astype(float).unique())
    if len(lats) < 2 or len(lons) < 2:
        raise ValueError("Canonical grid must contain at least two latitude and longitude centers")
    lat_step = float(np.median(np.diff(lats)))
    lon_step = float(np.median(np.diff(lons)))
    lat_indices = np.rint((grid["lat_center"].to_numpy(dtype=float) - lats[0]) / lat_step).astype(int)
    lon_indices = np.rint((grid["lon_center"].to_numpy(dtype=float) - lons[0]) / lon_step).astype(int)
    lookup = np.full((lat_indices.max() + 1, lon_indices.max() + 1), -1, dtype=np.int64)
    lookup[lat_indices, lon_indices] = np.arange(len(grid), dtype=np.int64)
    return lookup, float(lats[0]), float(lons[0]), lat_step, lon_step


def _read_grid_window(
    raster_path: Path,
    grid: gpd.GeoDataFrame,
    lookup: np.ndarray,
    lat_min: float,
    lon_min: float,
    lat_step: float,
    lon_step: float,
) -> tuple[np.ndarray, np.ndarray]:
    ds = gdal.Open(str(raster_path), gdal.GA_ReadOnly)
    if ds is None:
        raise RuntimeError(f"Unable to open GeoTIFF: {raster_path}")
    band = ds.GetRasterBand(1)
    transform = ds.GetGeoTransform()
    origin_x, pixel_x, _, origin_y, _, pixel_y = transform

    min_lon = float(grid["lon_center"].min()) - lon_step / 2
    max_lon = float(grid["lon_center"].max()) + lon_step / 2
    min_lat = float(grid["lat_center"].min()) - lat_step / 2
    max_lat = float(grid["lat_center"].max()) + lat_step / 2

    xoff = max(0, int(math.floor((min_lon - origin_x) / pixel_x)))
    xend = min(ds.RasterXSize, int(math.ceil((max_lon - origin_x) / pixel_x)))
    yoff = max(0, int(math.floor((max_lat - origin_y) / pixel_y)))
    yend = min(ds.RasterYSize, int(math.ceil((min_lat - origin_y) / pixel_y)))
    width = xend - xoff
    height = yend - yoff
    if width <= 0 or height <= 0:
        raise ValueError(f"Grid does not overlap raster: {raster_path.name}")

    values = band.ReadAsArray(xoff, yoff, width, height).astype(np.float64)
    nodata = band.GetNoDataValue()
    if nodata is not None:
        values[values == nodata] = np.nan

    cols = np.arange(xoff, xend, dtype=np.float64)
    rows = np.arange(yoff, yend, dtype=np.float64)
    pixel_lons = origin_x + (cols + 0.5) * pixel_x
    pixel_lats = origin_y + (rows + 0.5) * pixel_y
    lon_indices = np.rint((pixel_lons - lon_min) / lon_step).astype(np.int64)
    lat_indices = np.rint((pixel_lats - lat_min) / lat_step).astype(np.int64)

    valid_lat = (lat_indices >= 0) & (lat_indices < lookup.shape[0])
    valid_lon = (lon_indices >= 0) & (lon_indices < lookup.shape[1])
    safe_lat = np.clip(lat_indices, 0, lookup.shape[0] - 1)
    safe_lon = np.clip(lon_indices, 0, lookup.shape[1] - 1)
    cell_index_grid = lookup[safe_lat[:, None], safe_lon[None, :]].copy()
    cell_index_grid[~valid_lat[:, None] | ~valid_lon[None, :]] = -1

    valid = cell_index_grid >= 0
    return values[valid], cell_index_grid[valid]


def _read_metric_values(
    raster_path: Path | None,
    grid: gpd.GeoDataFrame,
    lookup: np.ndarray,
    lat_min: float,
    lon_min: float,
    lat_step: float,
    lon_step: float,
) -> tuple[np.ndarray, np.ndarray] | None:
    if raster_path is None:
        return None
    return _read_grid_window(raster_path, grid, lookup, lat_min, lon_min, lat_step, lon_step)


def _month_rows(archive_path: Path, grid: gpd.GeoDataFrame) -> pd.DataFrame:
    source_start, source_end = _parse_archive_dates(archive_path.name)
    month = source_start.strftime("%Y-%m")
    lookup, lat_min, lon_min, lat_step, lon_step = _grid_lookup(grid)
    n_cells = len(grid)

    with tempfile.TemporaryDirectory(prefix="seacast-vbd-") as temp_dir:
        with zipfile.ZipFile(archive_path) as archive:
            archive.extractall(temp_dir)
        extracted = Path(temp_dir)

        metric_data = {
            metric: _read_metric_values(
                _metric_path(extracted, metric),
                grid,
                lookup,
                lat_min,
                lon_min,
                lat_step,
                lon_step,
            )
            for metric in METRIC_NAMES
        }

    def aligned(metric: str) -> np.ndarray:
        values = metric_data[metric]
        if values is None:
            return np.full(0, np.nan, dtype=np.float64)
        return values[0]

    cvg_data = metric_data["cvg"]
    detect_data = metric_data["n_detect"]
    pct_data = metric_data["pct_detect"]
    if cvg_data is None or detect_data is None:
        raise ValueError(f"{archive_path.name} must contain cvg and n_detect layers")
    if not np.array_equal(cvg_data[1], detect_data[1]):
        raise ValueError(f"Cell alignment mismatch in {archive_path.name}")

    cell_indices = cvg_data[1]
    agg = aggregate_cell_metrics(
        cell_indices,
        cvg_data[0],
        detect_data[0],
        None if pct_data is None else pct_data[0],
        None if metric_data["avg_rade9"] is None else metric_data["avg_rade9"][0],
    )
    by_index = {int(item["cell_index"]): item for item in agg}

    rows = []
    expected_end = date(source_start.year, source_start.month, 1)
    if source_start.month == 12:
        expected_end = date(source_start.year, 12, 31)
    else:
        next_month = date(source_start.year + (source_start.month == 12), (source_start.month % 12) + 1, 1)
        expected_end = next_month.fromordinal(next_month.toordinal() - 1)
    partial = source_start.day != 1 or source_end != expected_end

    for cell_index, row in grid.iterrows():
        item = by_index.get(int(cell_index), {})
        rows.append(
            {
                "month": month,
                "cell_id": row["cell_id"],
                "lat_center": float(row["lat_center"]),
                "lon_center": float(row["lon_center"]),
                "ocean_frac": float(row["ocean_frac"]) if pd.notna(row["ocean_frac"]) else None,
                "vbd_cvg": item.get("vbd_cvg"),
                "vbd_avg_rade9": item.get("vbd_avg_rade9"),
                "vbd_n_detect": item.get("vbd_n_detect"),
                "vbd_pct_detect": item.get("vbd_pct_detect"),
                "vbd_valid_pixel_count": int(np.sum(cell_indices == cell_index)),
                "vbd_avg_rade9_missing": bool(item.get("vbd_avg_rade9_missing", True)),
                "vbd_source_start": source_start.isoformat(),
                "vbd_source_end": source_end.isoformat(),
                "vbd_partial_period": partial,
                "vbd_source_file": archive_path.name,
            }
        )
    return pd.DataFrame(rows, columns=OUTPUT_COLUMNS)


def _archives(input_dir: Path, start: str, end: str) -> list[Path]:
    selected = []
    for path in sorted(input_dir.glob("*.zip")):
        try:
            month = month_key_from_filename(path.name)
        except ValueError:
            continue
        if start <= month <= end:
            selected.append(path)
    return selected


def stable_month_frame(frame: pd.DataFrame) -> pd.DataFrame:
    """Keep nullable VIIRS measurements numeric across Parquet row groups."""
    numeric_columns = (
        "ocean_frac", "vbd_cvg", "vbd_avg_rade9", "vbd_n_detect", "vbd_pct_detect"
    )
    return frame.astype({name: "float64" for name in numeric_columns if name in frame})


def prepare(
    input_dir: Path,
    grid_path: Path,
    output_prefix: Path,
    start: str,
    end: str,
    limit: int | None = None,
) -> dict[str, object]:
    grid = _read_grid(grid_path)
    archives = _archives(input_dir, start, end)
    if limit is not None:
        archives = archives[:limit]
    if not archives:
        raise ValueError("No EOG archives match the requested period")

    output_prefix.parent.mkdir(parents=True, exist_ok=True)
    csv_path = output_prefix.with_suffix(".csv")
    parquet_path = output_prefix.with_suffix(".parquet")
    manifest_path = output_prefix.with_name(output_prefix.name + "_manifest.json")
    progress_path = output_prefix.with_name(output_prefix.name + "_progress.json")
    if csv_path.exists():
        csv_path.unlink()
    if parquet_path.exists():
        parquet_path.unlink()

    parquet_writer = None
    processed = []
    completed_months: list[str] = []
    total_rows = 0
    target_months = [month_key_from_filename(path.name) for path in archives]
    progress_path.write_text(
        json.dumps(progress_payload([], target_months, 0, "running"), indent=2),
        encoding="utf-8",
    )
    try:
        for archive_path in archives:
            frame = stable_month_frame(_month_rows(archive_path, grid))
            frame.to_csv(csv_path, mode="a", header=not csv_path.exists(), index=False)
            try:
                import pyarrow as pa
                import pyarrow.parquet as pq

                table = pa.Table.from_pandas(frame, preserve_index=False)
                if parquet_writer is None:
                    parquet_writer = pq.ParquetWriter(str(parquet_path), table.schema)
                parquet_writer.write_table(table)
            except ImportError:
                pass
            total_rows += len(frame)
            completed_months.append(month_key_from_filename(archive_path.name))
            progress_path.write_text(
                json.dumps(
                    progress_payload(completed_months, target_months, total_rows, "running"),
                    indent=2,
                ),
                encoding="utf-8",
            )
            processed.append(
                {
                    "file": archive_path.name,
                    "month": month_key_from_filename(archive_path.name),
                    "rows": len(frame),
                }
            )
    except Exception as exc:
        progress_path.write_text(
            json.dumps(
                progress_payload(completed_months, target_months, total_rows, "failed", str(exc)),
                indent=2,
            ),
            encoding="utf-8",
        )
        raise
    finally:
        if parquet_writer is not None:
            parquet_writer.close()

    progress_path.write_text(
        json.dumps(progress_payload(completed_months, target_months, total_rows, "complete"), indent=2),
        encoding="utf-8",
    )

    manifest = {
        "schema_version": "eog-vbd-grid-v1",
        "input_dir": str(input_dir),
        "grid": str(grid_path),
        "output_csv": str(csv_path),
        "output_parquet": str(parquet_path) if parquet_path.exists() else None,
        "cell_count": len(grid),
        "archive_count": len(processed),
        "row_count": len(grid) * len(processed),
        "period": {"start": start, "end": end},
        "aggregation": {
            "cvg": "sum over source pixels",
            "n_detect": "sum over source pixels",
            "pct_detect": "100 * sum(n_detect) / sum(cvg)",
            "avg_rade9": "n_detect-weighted mean",
        },
        "processed": processed,
    }
    manifest_path.write_text(json.dumps(manifest, indent=2), encoding="utf-8")
    return manifest


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--input-dir", type=Path, default=Path("data/eog/vbd/monthly"))
    parser.add_argument(
        "--grid",
        dest="grid_path",
        type=Path,
        default=Path("data/geojson/canonical_grid_8129_cells.geojson"),
    )
    parser.add_argument(
        "--output-prefix",
        type=Path,
        default=Path("data/eog/vbd/processed/vbd_monthly_wpp573_grid"),
    )
    parser.add_argument("--start", default="2015-01")
    parser.add_argument("--end", default="2025-12")
    parser.add_argument("--limit", type=int, default=None, help="Process only the first N archives")
    args = parser.parse_args()
    manifest = prepare(**vars(args))
    print(json.dumps(manifest, indent=2))


if __name__ == "__main__":
    main()
