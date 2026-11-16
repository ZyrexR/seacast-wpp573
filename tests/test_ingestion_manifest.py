import json
import tempfile
import unittest
from datetime import datetime, timezone
from pathlib import Path

import sys

sys.path.insert(0, str(Path(__file__).parents[1] / "scripts"))


class IngestionManifestTests(unittest.TestCase):
    def test_manifest_record_contains_provenance_and_date_coverage(self):
        from ingestion_manifest import write_ingestion_manifest

        with tempfile.TemporaryDirectory() as tmp:
            manifest_path = Path(tmp) / "ingestion_manifest.json"
            record = write_ingestion_manifest(
                manifest_path=manifest_path,
                dataset="gee_daily_2026-11-15.csv",
                target_date="2026-11-15",
                provenance={
                    "evidence_level": "PROXY_HSI",
                    "source_mode": "GEE_INGESTED_WITH_LOCAL_FALLBACKS",
                    "model_version": "2.3.2",
                    "prediction_timestamp": "2026-11-15T01:00:00+00:00",
                    "validation_status": "UNVERIFIED_NO_OCCURRENCE_AUDIT",
                    "satellite_sources": {"sst": "NOAA OISST v2.1"},
                },
                total_cells=8129,
                warnings=["cloud-gap median imputation"],
                completed_at=datetime(2026, 11, 15, 1, 1, tzinfo=timezone.utc),
            )

            saved = json.loads(manifest_path.read_text(encoding="utf-8"))

        self.assertEqual(len(saved), 1)
        self.assertEqual(record["status"], "VALID_WITH_FALLBACK")
        self.assertEqual(saved[0]["source_mode"], "GEE_INGESTED_WITH_LOCAL_FALLBACKS")
        self.assertEqual(saved[0]["date_coverage"], {"start": "2026-11-15", "end": "2026-11-15"})
        self.assertEqual(saved[0]["total_cells"], 8129)
        self.assertIn("prediction_timestamp", saved[0])
        self.assertIn("valid_until", saved[0])

    def test_manifest_replaces_same_dataset_and_date_instead_of_appending_duplicates(self):
        from ingestion_manifest import write_ingestion_manifest

        with tempfile.TemporaryDirectory() as tmp:
            manifest_path = Path(tmp) / "ingestion_manifest.json"
            for cells in (100, 8129):
                write_ingestion_manifest(
                    manifest_path=manifest_path,
                    dataset="daily_latest.json",
                    target_date="2026-11-15",
                    provenance={"source_mode": "LOCAL_SIMULATION_FALLBACK"},
                    total_cells=cells,
                )

            saved = json.loads(manifest_path.read_text(encoding="utf-8"))

        self.assertEqual(len(saved), 1)
        self.assertEqual(saved[0]["total_cells"], 8129)


if __name__ == "__main__":
    unittest.main()
