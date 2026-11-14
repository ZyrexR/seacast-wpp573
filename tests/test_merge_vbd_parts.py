import tempfile
import unittest
from pathlib import Path
import sys

import pandas as pd

sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "scripts"))
from merge_vbd_parts import merge_parts


class MergeVbdPartsTests(unittest.TestCase):
    def test_merges_unique_cell_months_and_rejects_missing_month(self):
        with tempfile.TemporaryDirectory() as temp:
            root = Path(temp)
            first = root / "first.parquet"
            second = root / "second.parquet"
            pd.DataFrame({"month": ["2024-01"] * 2, "cell_id": ["a", "b"], "vbd_n_detect": [0.0, 1.0]}).to_parquet(first)
            pd.DataFrame({"month": ["2024-02"] * 2, "cell_id": ["a", "b"], "vbd_n_detect": [2.0, 3.0]}).to_parquet(second)
            result = merge_parts([first, second], expected_months=["2024-01", "2024-02"], expected_cells=2)
            self.assertEqual(len(result), 4)
            self.assertEqual(result.loc[result["month"] == "2024-02", "vbd_n_detect"].sum(), 5.0)
            with self.assertRaisesRegex(ValueError, "missing"):
                merge_parts([first], expected_months=["2024-01", "2024-02"], expected_cells=2)


if __name__ == "__main__":
    unittest.main()
