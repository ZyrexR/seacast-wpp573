"""Contract separating temporal CPUE calibration from production HSI output."""

from __future__ import annotations

from typing import Any


def _first_number(mapping: dict[str, Any], *keys: str) -> float | None:
    for key in keys:
        value = mapping.get(key)
        if value is not None:
            return float(value)
    return None


def select_temporal_calibration(metrics: dict[str, Any]) -> dict[str, Any]:
    """Enable CPUE correction only when rolling-origin real-scale RMSE improves."""
    rolling = metrics.get("rolling_origin", {}) or {}
    model_rmse = _first_number(rolling, "model_rmse", "ensemble_rmse", "rmse")
    baseline_rmse = _first_number(
        rolling,
        "seasonal_baseline_rmse",
        "baseline_rmse",
        "port_baseline_rmse",
    )

    if model_rmse is None or baseline_rmse is None:
        return {
            "status": "BASELINE_ONLY",
            "correction_enabled": False,
            "correction_scale": 0.0,
            "reason": "ROLLING_ORIGIN_METRICS_MISSING",
            "rolling_origin_model_rmse": model_rmse,
            "rolling_origin_baseline_rmse": baseline_rmse,
        }

    beats_baseline = model_rmse < baseline_rmse
    return {
        "status": "ENABLED" if beats_baseline else "BASELINE_ONLY",
        "correction_enabled": beats_baseline,
        "correction_scale": 1.0 if beats_baseline else 0.0,
        "reason": "ROLLING_ORIGIN_RMSE_BETTER_THAN_BASELINE" if beats_baseline else "ROLLING_ORIGIN_RMSE_NOT_BETTER_THAN_BASELINE",
        "rolling_origin_model_rmse": model_rmse,
        "rolling_origin_baseline_rmse": baseline_rmse,
        "rolling_origin_rmse_margin": baseline_rmse - model_rmse,
    }


def build_temporal_calibration_payload(
    metrics: dict[str, Any],
    validation_context: dict[str, Any],
) -> dict[str, Any]:
    selection = select_temporal_calibration(metrics)
    holdout = metrics.get("holdout_2025", {}) or {}
    rolling = metrics.get("rolling_origin", {}) or {}
    return {
        "port": metrics.get("port", "all_ports"),
        "commodity": metrics.get("commodity", metrics.get("species", "unknown")),
        "target_scale": "log1p_residual",
        "baseline_metrics": {
            "rolling_origin": rolling,
            "holdout_2025": holdout,
        },
        "holdout_metrics": holdout,
        "calibration_status": selection["status"],
        "correction_enabled": selection["correction_enabled"],
        "correction_scale": selection["correction_scale"],
        "validation_context": validation_context,
        "uncertainty": {
            "rolling_origin_rmse_margin": selection.get("rolling_origin_rmse_margin"),
            "log_scale_is_diagnostic_only": True,
        },
        "provenance": {
            "method": "rolling_origin_real_scale_rmse_vs_port_seasonal_baseline",
            "holdout_period": validation_context.get("holdout_period"),
        },
        "production_hsi_unchanged": True,
    }
