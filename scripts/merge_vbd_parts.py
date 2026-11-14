"""Validate and combine completed VBD batches into the canonical monthly Parquet."""

import argparse
import json
from pathlib import Path

import pandas as pd


PART_NAMES = (
    "vbd_2015_2017.parquet",
    "vbd_2018_2020.parquet",
    "vbd_2021_2023.parquet",
    "vbd_2024_2025.parquet",  # verified January–April before the May schema failure
    "vbd_2024_05_to_2024_09.parquet",
    "vbd_2024_10_to_2025_02.parquet",
    "vbd_2025_03_to_2025_07.parquet",
    "vbd_2025_08_to_2025_12.parquet",
)


def month_range(start: str, end: str) -> list[str]:
    return pd.period_range(start, end, freq="M").astype(str).tolist()


def merge_parts(paths: list[Path], expected_months: list[str], expected_cells: int) -> pd.DataFrame:
    frames = [pd.read_parquet(path) for path in paths]
    merged = pd.concat(frames, ignore_index=True)
    actual_months = set(merged["month"].astype(str))
    missing = set(expected_months) - actual_months
    extra = actual_months - set(expected_months)
    if missing or extra:
        raise ValueError(f"EOG month mismatch: missing={sorted(missing)} extra={sorted(extra)}")
    duplicate_count = int(merged.duplicated(["month", "cell_id"]).sum())
    if duplicate_count:
        raise ValueError(f"EOG has {duplicate_count} duplicate cell-month rows")
    counts = merged.groupby("month")["cell_id"].nunique()
    if len(merged) != len(expected_months) * expected_cells or (counts != expected_cells).any():
        raise ValueError(f"EOG cell counts invalid: {counts[counts != expected_cells].to_dict()}")
    return merged.sort_values(["month", "cell_id"]).reset_index(drop=True)


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--parts-dir", type=Path, default=Path("data/eog/vbd/processed/parts"))
    parser.add_argument("--output", type=Path, default=Path("data/eog/vbd/processed/vbd_monthly_wpp573_grid.parquet"))
    args = parser.parse_args()
    paths = [args.parts_dir / name for name in PART_NAMES]
    for path in paths:
        if not path.exists():
            raise FileNotFoundError(path)
    for path in paths[-4:]:
        progress_path = path.with_name(path.stem + "_progress.json")
        if json.loads(progress_path.read_text(encoding="utf-8"))["status"] != "complete":
            raise RuntimeError(f"EOG batch not complete: {path.name}")
    result = merge_parts(paths, month_range("2015-01", "2025-12"), 8129)
    args.output.parent.mkdir(parents=True, exist_ok=True)
    result.to_parquet(args.output, index=False)
    manifest = {"status": "complete", "months": 132, "cells": 8129, "rows": len(result), "parts": [str(p) for p in paths]}
    args.output.with_name(args.output.stem + "_manifest.json").write_text(json.dumps(manifest, indent=2), encoding="utf-8")
    print(json.dumps(manifest, indent=2))


if __name__ == "__main__":
    main()
