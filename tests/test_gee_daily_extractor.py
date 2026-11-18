import importlib
import os
import sys
import tempfile
import unittest
from pathlib import Path
from unittest.mock import MagicMock, patch

sys.path.insert(0, str(Path(__file__).parents[1] / "scripts"))


class GeeDailyExtractorTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.fake_ee = MagicMock()
        cls.ee_patch = patch.dict(sys.modules, {"ee": cls.fake_ee})
        cls.ee_patch.start()
        cls.extractor = importlib.import_module("gee_daily_extractor")

    @classmethod
    def tearDownClass(cls):
        cls.ee_patch.stop()
        sys.modules.pop("gee_daily_extractor", None)

    def setUp(self):
        self.fake_ee.reset_mock()
        self.fake_ee.Initialize.side_effect = None

    def test_service_account_auth_uses_env_project_and_does_not_expose_key(self):
        with patch.dict(os.environ, {
            "GEE_PROJECT": "production-project",
            "GEE_SERVICE_ACCOUNT_EMAIL": "bot@example.test",
            "GEE_SERVICE_ACCOUNT_KEY_PATH": "/private/secret-key.json",
        }):
            self.extractor.init_earth_engine()
        self.fake_ee.ServiceAccountCredentials.assert_called_once_with(
            "bot@example.test", "/private/secret-key.json"
        )
        self.fake_ee.Initialize.assert_called_once_with(
            self.fake_ee.ServiceAccountCredentials.return_value, project="production-project"
        )

    def test_oauth_auth_when_service_account_vars_absent(self):
        with patch.dict(os.environ, {
            "GEE_PROJECT": "dev-project",
        }, clear=True):
            self.extractor.init_earth_engine()
        self.fake_ee.ServiceAccountCredentials.assert_not_called()
        self.fake_ee.Initialize.assert_called_once_with(project="dev-project")

    def test_auth_failure_is_sanitized_and_not_retried(self):
        self.fake_ee.Initialize.side_effect = RuntimeError("secret-key-contents")
        with patch.dict(os.environ, {"GEE_SYNC_MAX_RETRIES": "3"}, clear=True):
            with self.assertRaises(RuntimeError) as caught:
                self.extractor.init_earth_engine()
        self.assertNotIn("secret-key-contents", str(caught.exception))
        self.assertEqual(self.fake_ee.Initialize.call_count, 1)

    def test_temporary_initialize_timeout_retries_without_exposing_error(self):
        self.fake_ee.Initialize.side_effect = [TimeoutError("temporary secret"), None]
        with patch.object(self.extractor.time, "sleep") as sleep:
            self.extractor.init_earth_engine("dev-project")
        self.assertEqual(self.fake_ee.Initialize.call_count, 2)
        sleep.assert_called_once_with(1)

    def test_transient_errors_retry_with_exponential_backoff(self):
        work = MagicMock(side_effect=[TimeoutError("network timeout"),
                                      ConnectionError("network"), "ok"])
        with patch.dict(os.environ, {"GEE_SYNC_MAX_RETRIES": "3"}, clear=True), \
                patch.object(self.extractor.time, "sleep") as sleep:
            self.assertEqual(self.extractor.retry_gee_operation(work), "ok")
        self.assertEqual(work.call_count, 3)
        self.assertEqual(sleep.call_args_list[0].args, (1,))
        self.assertEqual(sleep.call_args_list[1].args, (2,))

    def test_quota_error_retries_but_auth_error_fails_fast(self):
        quota_work = MagicMock(side_effect=[RuntimeError("GEE quota exceeded: 429"), "ok"])
        auth_work = MagicMock(side_effect=RuntimeError("403 permission denied"))
        with patch.object(self.extractor.time, "sleep") as sleep:
            self.assertEqual(self.extractor.retry_gee_operation(quota_work), "ok")
            with self.assertRaises(RuntimeError):
                self.extractor.retry_gee_operation(auth_work)
        self.assertEqual(quota_work.call_count, 2)
        self.assertEqual(auth_work.call_count, 1)
        self.assertEqual(sleep.call_count, 1)

    def test_permanent_error_fails_without_retry(self):
        work = MagicMock(side_effect=RuntimeError("permission denied"))
        with patch.object(self.extractor.time, "sleep") as sleep:
            with self.assertRaises(RuntimeError):
                self.extractor.retry_gee_operation(work)
        self.assertEqual(work.call_count, 1)
        sleep.assert_not_called()

    def test_dry_run_checks_auth_without_writing_or_querying(self):
        with tempfile.TemporaryDirectory() as directory, \
                patch.object(self.extractor, "EXPORTS_DIR", directory), \
                patch.object(self.extractor, "init_earth_engine") as auth, \
                patch.object(self.extractor, "detect_latest_oisst_date") as latest, \
                patch.object(self.extractor, "build_daily_satellite_stack") as stack:
            self.extractor.main(["--dry-run", "--persist-db"])
            self.extractor.main(["--check-auth", "--persist-db"])
            self.assertEqual(list(Path(directory).iterdir()), [])
        self.assertEqual(auth.call_count, 2)
        latest.assert_not_called()
        stack.assert_not_called()

    def test_requested_date_is_resolved_to_actual_oisst_observation(self):
        self.fake_ee.ImageCollection.return_value.filterDate.return_value.sort.return_value.first.return_value.get.return_value.getInfo.return_value = 1704067200000
        self.assertEqual(self.extractor.resolve_oisst_observation_date("2024-01-03"), "2024-01-01")

    def test_main_passes_observation_date_to_inference_and_keeps_requested_date(self):
        transformer = MagicMock()
        transformer.run_pipeline.return_value = {"total_cells": 8129, "hotspots_count": 50}
        with patch.dict(sys.modules, {"gee_pipeline_transformer": transformer}), \
                patch("builtins.print"), \
                patch.object(self.extractor, "init_earth_engine"), \
                patch.object(self.extractor, "resolve_oisst_observation_date", return_value="2026-11-14"), \
                patch.object(self.extractor, "build_daily_satellite_stack", return_value="stack"), \
                patch.object(self.extractor, "extract_canonical_grid", return_value=[]), \
                patch.object(self.extractor, "format_and_save_dataset", return_value="data.csv"):
            self.extractor.main(["--date", "2026-11-16", "--persist-db"])
        self.assertEqual(transformer.run_pipeline.call_args.kwargs["target_date"], "2026-11-14")
        self.assertEqual(transformer.run_pipeline.call_args.kwargs["requested_date"], "2026-11-16")

    def test_database_failure_exits_after_failed_manifest(self):
        transformer = MagicMock()
        transformer.run_pipeline.return_value = {"db_persist_warning": "denied"}
        with patch.dict(sys.modules, {"gee_pipeline_transformer": transformer}), \
                patch("builtins.print"), \
                patch.object(self.extractor, "init_earth_engine"), \
                patch.object(self.extractor, "resolve_oisst_observation_date", return_value="2026-11-14"), \
                patch.object(self.extractor, "build_daily_satellite_stack", return_value="stack"), \
                patch.object(self.extractor, "extract_canonical_grid", return_value=[]), \
                patch.object(self.extractor, "format_and_save_dataset", return_value="data.csv"):
            with self.assertRaisesRegex(RuntimeError, "Database persistence failed"):
                self.extractor.main(["--date", "2026-11-16", "--persist-db"])


if __name__ == "__main__":
    unittest.main()
