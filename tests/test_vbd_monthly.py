import sys
import unittest
from pathlib import Path


PROJECT_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(PROJECT_ROOT / "scripts"))

from prepare_vbd_monthly import (
    aggregate_cell_metrics,
    month_key_from_filename,
    progress_payload,
    stable_month_frame,
)


class VbdMonthlyFormatTests(unittest.TestCase):
    def test_radiance_schema_stays_float_when_entire_month_is_missing(self):
        import pandas as pd
        import pyarrow as pa

        missing = stable_month_frame(pd.DataFrame({"vbd_avg_rade9": [None, None], "ocean_frac": [None, None]}))
        present = stable_month_frame(pd.DataFrame({"vbd_avg_rade9": [12.3], "ocean_frac": [0.5]}))
        self.assertEqual(pa.Table.from_pandas(missing, preserve_index=False).schema.types,
                         pa.Table.from_pandas(present, preserve_index=False).schema.types)

    def test_month_key_uses_start_date_when_archive_end_date_has_typo(self):
        name = "VBD_npp_20240201-20220229_idn_v23.zip"
        self.assertEqual(month_key_from_filename(name), "2024-02")

    def test_aggregate_metrics_preserves_detection_semantics(self):
        result = aggregate_cell_metrics(
            cell_indices=[0, 0, 1],
            cvg=[10, 20, 10],
            n_detect=[2, 4, 1],
            pct_detect=[20.0, 20.0, 10.0],
            avg_rade9=[100.0, 200.0, 50.0],
        )

        self.assertEqual(result[0]["vbd_cvg"], 30.0)
        self.assertEqual(result[0]["vbd_n_detect"], 6.0)
        self.assertAlmostEqual(result[0]["vbd_pct_detect"], 20.0)
        self.assertAlmostEqual(result[0]["vbd_avg_rade9"], 166.6666667, places=5)
        self.assertEqual(result[1]["vbd_cvg"], 10.0)

    def test_missing_radiance_is_explicit(self):
        result = aggregate_cell_metrics(
            cell_indices=[0],
            cvg=[10],
            n_detect=[2],
            pct_detect=[20.0],
            avg_rade9=None,
        )

        self.assertIsNone(result[0]["vbd_avg_rade9"])
        self.assertTrue(result[0]["vbd_avg_rade9_missing"])

    def test_progress_payload_reports_completed_and_remaining_months(self):
        payload = progress_payload(
            completed_months=["2015-01", "2015-02"],
            target_months=["2015-01", "2015-02", "2015-03"],
            rows=16258,
            status="running",
        )

        self.assertEqual(payload["status"], "running")
        self.assertEqual(payload["completed_months"], 2)
        self.assertEqual(payload["target_months"], 3)
        self.assertEqual(payload["remaining_months"], ["2015-03"])
        self.assertEqual(payload["rows"], 16258)


if __name__ == "__main__":
    unittest.main()
