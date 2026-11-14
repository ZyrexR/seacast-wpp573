"""Turn holdout metrics into an explicit scientific and web release decision."""

import argparse
import json
from pathlib import Path

import pandas as pd


def evaluate_species(
    species: str,
    model_rmse: float,
    model_r2: float,
    median_baseline_rmse: float,
    seasonal_baseline_rmse: float,
    model_r2_log: float | None = None,
    target_r2_threshold: float = 0.50,
) -> dict[str, object]:
    beats_median = model_rmse < median_baseline_rmse
    beats_seasonal = model_rmse < seasonal_baseline_rmse
    meets_target_r2_real = model_r2 >= target_r2_threshold
    meets_target_r2_log = model_r2_log is not None and model_r2_log >= target_r2_threshold
    meets_target_r2 = meets_target_r2_real or meets_target_r2_log
    temporal_approved = beats_seasonal and meets_target_r2
    return {
        "species": species,
        "temporal_status": "APPROVED_FOR_RELEASE" if temporal_approved else "REJECT",
        "spatial_status": "PROXY_ONLY_NO_COORDINATES",
        "web_status": "HOLD",
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
    args = parser.parse_args()
    metrics = pd.read_csv(args.metrics)
    decisions = [
        evaluate_species(
            species=row.species,
            model_rmse=float(row.ensemble_rmse_2025),
            model_r2=float(row.ensemble_r2_2025),
            median_baseline_rmse=float(row.baseline_rmse_2025),
            seasonal_baseline_rmse=float(row.seasonal_baseline_rmse_2025),
            model_r2_log=float(row.ensemble_r2_log_2025) if hasattr(row, "ensemble_r2_log_2025") else None,
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
            "spatial_proxy": "Occurrence coordinates are available, but the current 60 NM operational output provides only partial spatial coverage; MaxEnt remains a separate spatial-block-CV run.",
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
