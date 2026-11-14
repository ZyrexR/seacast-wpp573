import sys
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "scripts"))

from tct_calibration_contract import build_temporal_calibration_payload, select_temporal_calibration


class TctCalibrationContractTests(unittest.TestCase):
    def test_baseline_only_when_rolling_origin_model_loses(self):
        result = select_temporal_calibration({
            "rolling_origin": {"model_rmse": 12.0, "seasonal_baseline_rmse": 10.0},
            "holdout_2025": {"model_r2_log": 0.72, "model_rmse": 11.0},
        })

        self.assertEqual(result["status"], "BASELINE_ONLY")
        self.assertFalse(result["correction_enabled"])
        self.assertEqual(result["reason"], "ROLLING_ORIGIN_RMSE_NOT_BETTER_THAN_BASELINE")

    def test_enables_correction_only_when_real_scale_rmse_improves(self):
        result = select_temporal_calibration({
            "rolling_origin": {"model_rmse": 8.0, "seasonal_baseline_rmse": 10.0},
            "holdout_2025": {"model_r2": 0.54, "model_rmse": 9.0},
        })

        self.assertEqual(result["status"], "ENABLED")
        self.assertTrue(result["correction_enabled"])

    def test_high_log_r2_does_not_approve_real_scale_correction(self):
        result = select_temporal_calibration({
            "rolling_origin": {
                "model_rmse": 11.5,
                "seasonal_baseline_rmse": 10.0,
                "model_r2_log": 0.81,
            },
            "holdout_2025": {"model_r2_log": 0.81},
        })

        self.assertEqual(result["status"], "BASELINE_ONLY")
        self.assertFalse(result["correction_enabled"])

    def test_payload_preserves_context_and_uncertainty_provenance(self):
        payload = build_temporal_calibration_payload(
            {
                "port": "prigi",
                "commodity": "cakalang",
                "rolling_origin": {"model_rmse": 8.0, "seasonal_baseline_rmse": 10.0},
                "holdout_2025": {"model_rmse": 9.0, "model_r2": 0.54},
            },
            {"train_period": "2015-01 to 2024-12", "holdout_period": "2025-01 to 2025-12"},
        )

        self.assertEqual(payload["port"], "prigi")
        self.assertEqual(payload["commodity"], "cakalang")
        self.assertEqual(payload["target_scale"], "log1p_residual")
        self.assertEqual(payload["calibration_status"], "ENABLED")
        self.assertEqual(payload["validation_context"]["holdout_period"], "2025-01 to 2025-12")
        self.assertTrue(payload["production_hsi_unchanged"])
        self.assertIn("uncertainty", payload)


if __name__ == "__main__":
    unittest.main()
