"""Turn holdout metrics into an explicit scientific and web release decision."""

import argparse
import json
from pathlib import Path

import pandas as pd


SPATIAL_STATUSES = {"PROXY_HSI", "PARTIAL_SPATIAL_VALIDATION", "EMPIRICALLY_VALIDATED"}


def _spatial_gate_result(spatial_validation: dict[str, object] | None) -> tuple[str, str]:
    validation = spatial_validation or {}
    status = str(validation.get("evidence_status", validation.get("status", "PROXY_HSI")))
    if status not in SPATIAL_STATUSES:
        status = "PROXY_HSI"
    scope = str(validation.get("validation_scope", validation.get("scope", "operational_60nm")))
    return status, scope


def evaluate_species(
    species: str,
    model_rmse: float,
    model_r2: float,
    median_baseline_rmse: float,
    seasonal_baseline_rmse: float,
    model_r2_log: float | None = None,
    target_r2_threshold: float = 0.50,
    spatial_validation: dict[str, object] | None = None,
) -> dict[str, object]:
    beats_median = model_rmse < median_baseline_rmse
    beats_seasonal = model_rmse < seasonal_baseline_rmse
    meets_target_r2_real = model_r2 >= target_r2_threshold
    meets_target_r2_log = model_r2_log is not None and model_r2_log >= target_r2_threshold
    meets_target_r2 = meets_target_r2_real or meets_target_r2_log
    temporal_approved = beats_seasonal and meets_target_r2
    spatial_status, validation_scope = _spatial_gate_result(spatial_validation)
    return {
        "species": species,
        "temporal_status": "APPROVED_FOR_RELEASE" if temporal_approved else "REJECT",
        "spatial_status": spatial_status,
        "validation_scope": validation_scope,
        "spatial_release_allowed": spatial_status == "EMPIRICALLY_VALIDATED",
        "web_status": "CANDIDATE_HIGH_CONFIDENCE" if temporal_approved and spatial_status == "EMPIRICALLY_VALIDATED" else "HOLD",
        "target_r2_threshold": target_r2_threshold,
        "meets_target_r2_real": meets_target_r2_real,
        "meets_target_r2_log": meets_target_r2_log,
        "meets_target_r2": meets_target_r2,
        "model_rmse_2025": model_rmse,
        "model_r2_2025": model_r2,
        "model_r2_log_2025": model_r2_log,
        "median_baseline_rmse_2025": median_baseline_rmse,
        "seasonal_baseline_rmse_2025": seasonal_baseline_rmse,
        "beats_median_baseline": beats_median,
        "beats_seasonal_baseline": beats_seasonal,
    }


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--metrics",
        type=Path,
        default=Path("outputs/tct_hybrid_2015_2025/reports/tct_hybrid_model_validation_2015_2025.csv"),
    )
    parser.add_argument(
        "--output-dir",
        type=Path,
        default=Path("outputs/tct_hybrid_2015_2025/reports"),
    )
    parser.add_argument(
        "--spatial-report",
        type=Path,
        default=Path("outputs/tct_hybrid_2015_2025/reports/tct_spatial_validation.json"),
    )
    args = parser.parse_args()
    metrics = pd.read_csv(args.metrics)
    spatial_validation = {}
    if args.spatial_report.exists():
        spatial_payload = json.loads(args.spatial_report.read_text(encoding="utf-8"))
        spatial_validation = spatial_payload.get("operational_60nm", spatial_payload)
    decisions = [
        evaluate_species(
            species=row.species,
            model_rmse=float(row.ensemble_rmse_2025),
            model_r2=float(row.ensemble_r2_2025),
            median_baseline_rmse=float(row.baseline_rmse_2025),
            seasonal_baseline_rmse=float(row.seasonal_baseline_rmse_2025),
            model_r2_log=float(row.ensemble_r2_log_2025) if hasattr(row, "ensemble_r2_log_2025") else None,
            spatial_validation=spatial_validation,
        )
        for row in metrics.itertuples(index=False)
    ]
    payload = {
        "status": "HOLD_FOR_SCIENTIFIC_REVIEW",
        "web_integration_allowed": False,
        "maxent_status": "AVAILABLE_FOR_SEPARATE_OCCURRENCE_RUN",
        "validation_year": 2025,
        "species": decisions,
        "interpretation": {
            "temporal_candidate": "Approved only when real or log R2 reaches 0.50 and RMSE beats the seasonal baseline.",
            "spatial_proxy": "Spatial release is controlled only by the occurrence validator and its explicit scope; temporal R² cannot approve a spatial claim.",
            "allowed_use": "Research review and exploratory maps with an explicit proxy label.",
            "prohibited_use": "Operational fish-location claim or replacement of production HSI models.",
        },
    }
    args.output_dir.mkdir(parents=True, exist_ok=True)
    json_path = args.output_dir / "tct_scientific_gate.json"
    json_path.write_text(json.dumps(payload, indent=2, ensure_ascii=False), encoding="utf-8")

    labels = {
        "tuna_sirip_kuning": "Tuna Sirip Kuning",
        "cakalang": "Cakalang",
        "tongkol_krai": "Tongkol Krai",
    }
    rows = [
        f"| {labels.get(item['species'], item['species'])} | {item['model_rmse_2025']:.3f} | "
        f"{item['median_baseline_rmse_2025']:.3f} | {item['seasonal_baseline_rmse_2025']:.3f} | "
        f"{item['model_r2_2025']:.3f} | {item['temporal_status']} | {item['web_status']} |"
        for item in decisions
    ]
    temporal_summary = "; ".join(
        f"{labels.get(item['species'], item['species'])}: {item['temporal_status']}"
        for item in decisions
    )
    report = "\n".join(
        [
            "# SEACAST TCT Scientific Gate — 2025 Holdout",
            "",
            "**Overall decision:** HOLD_FOR_SCIENTIFIC_REVIEW",
            "",
            "| Komoditas | RMSE model | Baseline median | Baseline musiman | R² | Temporal | Web |",
            "|---|---:|---:|---:|---:|---|---|",
            *rows,
            "",
            "MaxEnt belum dijalankan; occurrence tersedia untuk run terpisah dengan spatial-block cross-validation.",
            "Cakupan peta operasional saat ini masih parsial dan seluruh hasil tetap berstatus proxy spasial.",
            f"Status temporal holdout 2025: {temporal_summary}.",
        ]
    )
    (args.output_dir / "TCT_SCIENTIFIC_GATE_2025.md").write_text(report + "\n", encoding="utf-8")
    print(json.dumps(payload, indent=2, ensure_ascii=False))


if __name__ == "__main__":
    main()
