import sys
import unittest
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "scripts"))

from evaluate_tct_scientific_gate import evaluate_species


class TctScientificGateTests(unittest.TestCase):
    def test_rejects_temporal_model_that_loses_to_baselines(self):
        result = evaluate_species(
            species="cakalang",
            model_rmse=26.9,
            model_r2=-0.63,
            median_baseline_rmse=21.7,
            seasonal_baseline_rmse=22.4,
        )
        self.assertEqual(result["temporal_status"], "REJECT")
        self.assertEqual(result["web_status"], "HOLD")
        self.assertEqual(result["spatial_status"], "PROXY_HSI")
        self.assertEqual(result["validation_scope"], "operational_60nm")

    def test_marks_partial_spatial_coverage_without_releasing_the_model(self):
        result = evaluate_species(
            species="tongkol_krai",
            model_rmse=13.8,
            model_r2=0.29,
            median_baseline_rmse=19.6,
            seasonal_baseline_rmse=14.0,
            spatial_validation={
                "evidence_status": "PARTIAL_SPATIAL_VALIDATION",
                "validation_scope": "operational_60nm",
            },
        )
        self.assertEqual(result["temporal_status"], "REJECT")
        self.assertEqual(result["spatial_status"], "PARTIAL_SPATIAL_VALIDATION")
        self.assertEqual(result["validation_scope"], "operational_60nm")
        self.assertEqual(result["web_status"], "HOLD")

    def test_approves_when_real_scale_r2_reaches_target_and_beats_seasonal(self):
        result = evaluate_species(
            species="tuna_sirip_kuning",
            model_rmse=10.0,
            model_r2=0.55,
            model_r2_log=0.55,
            median_baseline_rmse=15.0,
            seasonal_baseline_rmse=12.0,
            spatial_validation={
                "evidence_status": "EMPIRICALLY_VALIDATED",
                "validation_scope": "full_grid",
            },
            temporal_calibration_status="ENABLED",
        )
        self.assertEqual(result["temporal_status"], "APPROVED_FOR_RELEASE")
        self.assertTrue(result["meets_target_r2_log"])
        self.assertTrue(result["meets_target_r2_real"])
        self.assertEqual(result["spatial_status"], "EMPIRICALLY_VALIDATED")
        self.assertEqual(result["spatial_release_allowed"], False)
        self.assertEqual(result["web_status"], "HOLD")

    def test_log_scale_r2_alone_cannot_approve_temporal_release(self):
        result = evaluate_species(
            species="cakalang",
            model_rmse=10.0,
            model_r2=0.20,
            model_r2_log=0.55,
            median_baseline_rmse=15.0,
            seasonal_baseline_rmse=12.0,
        )
        self.assertEqual(result["temporal_status"], "REJECT")
        self.assertTrue(result["meets_target_r2_log"])
        self.assertFalse(result["meets_target_r2_real"])


if __name__ == "__main__":
    unittest.main()
