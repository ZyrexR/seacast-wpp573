import sys
import unittest
from pathlib import Path

import pandas as pd

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "scripts"))

from validate_spatial_occurrences import validate_occurrence_coverage


class SpatialOccurrenceValidationTests(unittest.TestCase):
    def setUp(self):
        self.occurrences = pd.DataFrame(
            {
                "source_id": [f"obs-{i}" for i in range(8)],
                "species": ["tuna_sirip_kuning"] * 8,
                "lat": [-8.25 - (i * 0.01) for i in range(8)],
                "lon": [103.24 + (i * 0.01) for i in range(8)],
            }
        )

    def test_zero_coverage_is_proxy(self):
        result = validate_occurrence_coverage(
            self.occurrences,
            pd.DataFrame(columns=["cell_id", "lat_center", "lon_center"]),
            scope="full_grid",
        )

        self.assertEqual(result["evidence_status"], "PROXY_HSI")
        self.assertEqual(result["matched"], 0)
        self.assertEqual(result["validation_scope"], "full_grid")

    def test_partial_coverage_cannot_be_empirically_validated(self):
        source = pd.read_csv(ROOT / "data" / "modeling" / "tct_occurrences.csv")
        spatial = source.head(3).rename(
            columns={"lat": "lat_center", "lon": "lon_center"}
        )[["lat_center", "lon_center"]].copy()
        spatial["cell_id"] = ["CELL_1", "CELL_2", "CELL_3"]
        spatial["dist_port_nm"] = 45.0

        result = validate_occurrence_coverage(
            source,
            spatial,
            scope="operational_60nm",
        )

        self.assertEqual(result["total"], 31)
        self.assertEqual(result["matched"], 3)
        self.assertEqual(result["evidence_status"], "PARTIAL_SPATIAL_VALIDATION")
        self.assertNotEqual(result["evidence_status"], "EMPIRICALLY_VALIDATED")

    def test_sufficient_coverage_is_empirically_validated(self):
        spatial = self.occurrences.copy().rename(
            columns={"lat": "lat_center", "lon": "lon_center"}
        )
        spatial["cell_id"] = [f"CELL_{i}" for i in range(len(spatial))]

        result = validate_occurrence_coverage(
            self.occurrences,
            spatial,
            scope="full_grid",
        )

        self.assertEqual(result["matched"], 8)
        self.assertEqual(result["evidence_status"], "EMPIRICALLY_VALIDATED")
        self.assertEqual(result["by_species"]["tuna_sirip_kuning"]["matched"], 8)


if __name__ == "__main__":
    unittest.main()
