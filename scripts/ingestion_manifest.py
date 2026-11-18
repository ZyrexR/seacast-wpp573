"""Atomic provenance manifest writer shared by SEACAST inference runners."""

from __future__ import annotations

import json
import os
import tempfile
from datetime import datetime, timedelta, timezone
from pathlib import Path
from typing import Any


MANIFEST_VERSION = "1.0"
MAX_RECORDS = 100


def _iso_timestamp(value: datetime | None = None) -> str:
    value = value or datetime.now(timezone.utc)
    if value.tzinfo is None:
        value = value.replace(tzinfo=timezone.utc)
    return value.astimezone(timezone.utc).isoformat()


def _status_for(provenance: dict[str, Any], warnings: list[str], requested: str | None) -> str:
    if requested == "FAILED":
        return "FAILED"
    source_mode = str(provenance.get("source_mode", "")).upper()
    if source_mode == "LOCAL_SIMULATION_FALLBACK":
        return "DEGRADED"
    if not source_mode.startswith("GEE_INGESTED"):
        return "UNVERIFIED"
    return "VALID_WITH_FALLBACK" if warnings or "FALLBACK" in source_mode else "VALID"


def _read_records(path: Path) -> list[dict[str, Any]]:
    if not path.is_file():
        return []
    try:
        payload = json.loads(path.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError):
        return []
    if isinstance(payload, list):
        return [record for record in payload if isinstance(record, dict)]
    if isinstance(payload, dict) and isinstance(payload.get("records"), list):
        return [record for record in payload["records"] if isinstance(record, dict)]
    return []


def write_ingestion_manifest(
    *,
    manifest_path: str | Path,
    dataset: str,
    target_date: str,
    provenance: dict[str, Any] | None = None,
    total_cells: int | None = None,
    hotspots_count: int | None = None,
    warnings: list[str] | None = None,
    date_start: str | None = None,
    date_end: str | None = None,
    completed_at: datetime | None = None,
    requested_status: str | None = None,
) -> dict[str, Any]:
    """Write one canonical manifest entry and replace duplicate date/dataset entries."""

    provenance = provenance or {}
    warnings = [str(item) for item in (warnings or [])]
    completed_at = completed_at or datetime.now(timezone.utc)
    completed_iso = _iso_timestamp(completed_at)
    source = provenance.get("sources") or provenance.get("source") or provenance.get("source_mode", "UNKNOWN")
    status = _status_for(provenance, warnings, requested_status)

    record: dict[str, Any] = {
        "manifest_version": MANIFEST_VERSION,
        "dataset": dataset,
        "source": source,
        "source_mode": provenance.get("source_mode", "UNKNOWN"),
        "evidence_level": provenance.get("evidence_level", "UNVERIFIED"),
        "model_version": provenance.get("model_version", "unknown"),
        "prediction_timestamp": provenance.get("prediction_timestamp", completed_iso),
        "validation_status": provenance.get("validation_status", "UNVERIFIED"),
        "status": status,
        "ingestion_status": status,
        "fallback_used": status in {"VALID_WITH_FALLBACK", "DEGRADED"},
        "target_date": target_date,
        "requested_date": provenance.get("requested_date", target_date),
        "date_coverage": {
            "start": date_start or target_date,
            "end": date_end or target_date,
        },
        "completed_at": completed_iso,
        "valid_until": _iso_timestamp(completed_at + timedelta(hours=48)) if status in {"VALID", "VALID_WITH_FALLBACK"} else None,
        "total_cells": total_cells,
        "hotspots_count": hotspots_count,
        "warnings": warnings,
    }

    path = Path(manifest_path)
    path.parent.mkdir(parents=True, exist_ok=True)
    records = _read_records(path)
    records = [
        existing for existing in records
        if not (existing.get("dataset") == dataset and existing.get("target_date") == target_date)
    ]
    records.insert(0, record)
    records = records[:MAX_RECORDS]

    with tempfile.NamedTemporaryFile(
        mode="w",
        encoding="utf-8",
        dir=path.parent,
        prefix=f".{path.name}.",
        suffix=".tmp",
        delete=False,
    ) as temporary:
        json.dump(records, temporary, ensure_ascii=False, indent=2)
        temporary.write("\n")
        temporary_path = Path(temporary.name)

    os.replace(temporary_path, path)
    return record
