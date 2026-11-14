"""Validate occurrence coordinates against full-grid and operational HSI outputs."""

from __future__ import annotations

import argparse
import json
from pathlib import Path
from typing import Literal

import numpy as np
import pandas as pd


EARTH_RADIUS_KM = 6371.0
KM_PER_NAUTICAL_MILE = 1.852
VALID_SCOPES = {"full_grid", "operational_60nm"}


def _load_frame(source: pd.DataFrame | str | Path) -> pd.DataFrame:
    if isinstance(source, pd.DataFrame):
        return source.copy()
    path = Path(source)
    if path.suffix.lower() == ".csv":
        return pd.read_csv(path)
    if path.suffix.lower() == ".parquet":
        return pd.read_parquet(path)
    if path.suffix.lower() in {".json", ".geojson"}:
        payload = json.loads(path.read_text(encoding="utf-8"))
        if payload.get("type") == "FeatureCollection":
            rows = []
            for feature in payload.get("features", []):
                properties = dict(feature.get("properties") or {})
                coordinates = feature.get("geometry", {}).get("coordinates", [])
                ring = coordinates[0] if coordinates and isinstance(coordinates[0], list) else coordinates
                if ring:
                    properties["lon_center"] = float(np.mean([point[0] for point in ring]))
                    properties["lat_center"] = float(np.mean([point[1] for point in ring]))
                rows.append(properties)
            return pd.DataFrame(rows)
        return pd.DataFrame(payload)
    raise ValueError(f"Unsupported spatial input format: {path}")


def _coordinate_columns(frame: pd.DataFrame) -> tuple[str, str]:
    for lat_column, lon_column in (
        ("lat", "lon"),
        ("lat_center", "lon_center"),
        ("center_lat", "center_lon"),
    ):
        if lat_column in frame.columns and lon_column in frame.columns:
            return lat_column, lon_column
    raise ValueError("Spatial data must provide lat/lon or *_center coordinate columns")


def _haversine_nm(lon: np.ndarray, lat: np.ndarray, lon0: float, lat0: float) -> np.ndarray:
    lon_rad, lat_rad = np.radians(lon), np.radians(lat)
    lon0_rad, lat0_rad = np.radians(lon0), np.radians(lat0)
    delta_lon = lon_rad - lon0_rad
    delta_lat = lat_rad - lat0_rad
    a = np.sin(delta_lat / 2.0) ** 2 + np.cos(lat_rad) * np.cos(lat0_rad) * np.sin(delta_lon / 2.0) ** 2
    return (EARTH_RADIUS_KM * 2.0 * np.arcsin(np.sqrt(np.clip(a, 0.0, 1.0)))) / KM_PER_NAUTICAL_MILE


def _prepare_spatial_predictions(
    spatial_predictions: pd.DataFrame | str | Path,
    scope: Literal["full_grid", "operational_60nm"],
) -> tuple[pd.DataFrame, str]:
    frame = _load_frame(spatial_predictions)
    lat_column, lon_column = _coordinate_columns(frame)
    result = frame.copy()
    result["lat_center"] = pd.to_numeric(result[lat_column], errors="coerce")
    result["lon_center"] = pd.to_numeric(result[lon_column], errors="coerce")
    result = result.dropna(subset=["lat_center", "lon_center"]).reset_index(drop=True)
    if "cell_id" not in result.columns:
        result["cell_id"] = [f"spatial_cell_{index}" for index in range(len(result))]
    if "dist_port_nm" not in result.columns and "dist_nm" in result.columns:
        result["dist_port_nm"] = pd.to_numeric(result["dist_nm"], errors="coerce")
    if scope == "operational_60nm" and "dist_port_nm" in result.columns:
        result = result[result["dist_port_nm"].le(60.0)].reset_index(drop=True)
    source = "canonical_grid_8129_cells.geojson" if scope == "full_grid" else "tct_hybrid_operational_60nm_outputs"
    return result, source


def _evidence_status(total: int, matched: int, by_species: dict[str, dict[str, int]]) -> str:
    if total == 0 or matched == 0:
        return "PROXY_HSI"
    enough_rate = matched / total >= 0.50
    enough_per_species = all(
        details["matched"] >= 5 for details in by_species.values() if details["total"] > 0
    )
    if matched >= 5 and enough_rate and enough_per_species:
        return "EMPIRICALLY_VALIDATED"
    return "PARTIAL_SPATIAL_VALIDATION"


def validate_occurrence_coverage(
    occurrences: pd.DataFrame | str | Path,
    spatial_predictions: pd.DataFrame | str | Path,
    scope: Literal["full_grid", "operational_60nm"],
    max_distance_nm: float = 8.0,
) -> dict[str, object]:
    """Return an auditable occurrence-to-grid coverage result for one scope."""
    if scope not in VALID_SCOPES:
        raise ValueError(f"scope must be one of {sorted(VALID_SCOPES)}")
    if max_distance_nm <= 0:
        raise ValueError("max_distance_nm must be positive")

    occurrence_frame = _load_frame(occurrences)
    occurrence_frame = occurrence_frame.dropna(subset=["species", "lat", "lon"]).copy()
    spatial_frame, source = _prepare_spatial_predictions(spatial_predictions, scope)
    spatial_hsi_columns = [column for column in spatial_frame.columns if column.startswith("hsi_")]
    matches: list[dict[str, object]] = []

    for occurrence_index, point in occurrence_frame.iterrows():
        record: dict[str, object] = {
            "source_id": point.get("source_id", f"occurrence_{occurrence_index}"),
            "species": str(point["species"]),
        }
        if spatial_frame.empty:
            record.update({
                "nearest_cell_id": None,
                "nearest_distance_nm": None,
                "matched": False,
            })
        else:
            distances = _haversine_nm(
                spatial_frame["lon_center"].to_numpy(dtype=float),
                spatial_frame["lat_center"].to_numpy(dtype=float),
                float(point["lon"]),
                float(point["lat"]),
            )
            nearest_index = int(np.argmin(distances))
            nearest = spatial_frame.iloc[nearest_index]
            nearest_distance = float(distances[nearest_index])
            record.update({
                "nearest_cell_id": str(nearest["cell_id"]),
                "nearest_distance_nm": nearest_distance,
                "matched": nearest_distance <= max_distance_nm,
            })
            for column in spatial_hsi_columns:
                record[column] = float(nearest[column]) if pd.notna(nearest[column]) else None
        matches.append(record)

    match_frame = pd.DataFrame(matches)
    total = int(len(match_frame))
    matched = int(match_frame["matched"].sum()) if total else 0
    by_species = {
        species: {
            "total": int((match_frame["species"] == species).sum()),
            "matched": int(match_frame.loc[match_frame["species"] == species, "matched"].sum()),
        }
        for species in sorted(match_frame["species"].unique())
    } if total else {}
    evidence_status = _evidence_status(total, matched, by_species)

    return {
        "validation_scope": scope,
        "scope": scope,
        "source": source,
        "max_distance_nm": max_distance_nm,
        "total": total,
        "total_records": total,
        "matched": matched,
        "matched_records": matched,
        "match_rate": float(matched / total) if total else 0.0,
        "by_species": by_species,
        "nearest_cell_distances": matches,
        "matches": matches,
        "evidence_status": evidence_status,
        "status": evidence_status,
    }


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--occurrences", type=Path, default=Path("data/modeling/tct_occurrences.csv"))
    parser.add_argument("--grid", type=Path, default=Path("data/geojson/canonical_grid_8129_cells.geojson"))
    parser.add_argument(
        "--operational-predictions",
        type=Path,
        default=Path("outputs/tct_hybrid_2015_2025/predictions/SEACAST_TCT_HSI_2015_2025.parquet"),
    )
    parser.add_argument("--output", type=Path, default=Path("outputs/tct_hybrid_2015_2025/reports/tct_spatial_validation.json"))
    parser.add_argument("--output-csv", type=Path, default=Path("outputs/tct_hybrid_2015_2025/reports/tct_occurrence_hsi_matches.csv"))
    parser.add_argument("--scope", choices=["full_grid", "operational_60nm", "both"], default="both")
    args = parser.parse_args()

    scopes = ["full_grid", "operational_60nm"] if args.scope == "both" else [args.scope]
    results: dict[str, object] = {}
    for scope in scopes:
        spatial_input = args.grid if scope == "full_grid" else args.operational_predictions
        results[scope] = validate_occurrence_coverage(args.occurrences, spatial_input, scope=scope)

    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(results, indent=2, ensure_ascii=False), encoding="utf-8")
    operational = results.get("operational_60nm")
    if isinstance(operational, dict):
        args.output_csv.parent.mkdir(parents=True, exist_ok=True)
        pd.DataFrame(operational["matches"]).to_csv(args.output_csv, index=False)
    print(json.dumps(results, indent=2, ensure_ascii=False))


if __name__ == "__main__":
    main()
