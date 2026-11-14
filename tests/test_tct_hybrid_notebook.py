import json
import ast
import re
import tempfile
import sys
import unittest
from pathlib import Path

import numpy as np
import pandas as pd

sys.path.insert(0, str(Path(__file__).parents[1] / "scripts"))


NOTEBOOK = Path(__file__).parents[1] / "notebooks" / "SEACAST_TCT_HYBRID_MODELING_2015_2025.ipynb"


class TctHybridNotebookTest(unittest.TestCase):
    def test_model_fits_monthly_data_and_holds_out_2025(self):
        from pygam import LinearGAM, s
        from sklearn.ensemble import GradientBoostingRegressor, RandomForestRegressor
        from sklearn.impute import SimpleImputer
        from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
        from sklearn.model_selection import TimeSeriesSplit
        from sklearn.preprocessing import StandardScaler
        from tct_calibration_contract import build_temporal_calibration_payload
        from tct_local_modeling import FEATURE_COLS as MODEL_FEATURE_COLS

        payload = json.loads(NOTEBOOK.read_text(encoding="utf-8"))
        code = next("".join(cell["source"]) for cell in payload["cells"]
                    if cell["cell_type"] == "code" and "def fit_species(" in "".join(cell["source"]))
        tree = ast.parse(code)
        functions = ast.Module(body=[node for node in tree.body if isinstance(node, ast.FunctionDef)], type_ignores=[])
        raw = pd.read_csv(Path(__file__).parents[1] / "data" / "master_prigi_all_inclusive.csv")
        monthly = pd.DataFrame({
            "year": raw["year"],
            "month": raw["month"],
            "month_key": raw["year"].astype(str) + "-" + raw["month"].astype(str).str.zfill(2),
            "port": "prigi",
            "sst_c": raw["sst_mean"],
            "chla_mg_m3": raw["chl_mean"],
            "sst_gradient": raw["month"] / 12,
            "vbd_pct_detect": raw["total_trip_alat"] / 1000,
            "cakalang_cpue_kg_trip": raw["cakalang_kg"] / raw["total_trip_alat"],
        })
        for index, column in enumerate(MODEL_FEATURE_COLS, start=1):
            if column not in monthly:
                monthly[column] = raw["month"] / (12.0 * index)
        namespace = dict(np=np, pd=pd, LinearGAM=LinearGAM, s=s,
                         RandomForestRegressor=RandomForestRegressor,
                         GradientBoostingRegressor=GradientBoostingRegressor,
                         SimpleImputer=SimpleImputer, StandardScaler=StandardScaler,
                         TimeSeriesSplit=TimeSeriesSplit,
                         mean_absolute_error=mean_absolute_error,
                         mean_squared_error=mean_squared_error, r2_score=r2_score,
                         monthly=monthly, FEATURE_COLS=MODEL_FEATURE_COLS,
                         build_temporal_calibration_payload=build_temporal_calibration_payload,
                         GAM_FEATURES=["sst_c", "chla_mg_m3", "upwelling_contrast_index"],
                         RANDOM_STATE=42, train_last_year=2024, final_validation_year=2025,
                         maxent_status={"status": "skipped"})
        exec(compile(functions, "model", "exec"), namespace)
        bundle, evaluation = namespace["fit_species"]("cakalang")
        self.assertEqual(bundle["train_last_year"], 2024)
        self.assertEqual(bundle["final_validation_year"], 2025)
        self.assertEqual(set(evaluation["holdout_2025_ensemble"]), {"rmse", "mae", "r2"})
        self.assertIn("holdout_2025_baseline_train_median", evaluation)
        self.assertIn("holdout_2025_baseline_month_climatology", evaluation)
        self.assertIn("holdout_2025_ensemble_log", evaluation)
        self.assertIn("ensemble_r2_log_2025", evaluation)
        self.assertEqual(evaluation["spatial_evidence"], "dual_port_local_aggregate_proxy_no_catch_coordinates")

    def test_eog_loader_ignores_stale_unfinished_final_file(self):
        payload = json.loads(NOTEBOOK.read_text(encoding="utf-8"))
        code = next("".join(cell["source"]) for cell in payload["cells"]
                    if cell["cell_type"] == "code" and "def load_eog_complete():" in "".join(cell["source"]))
        tree = ast.parse(code)
        functions = ast.Module(body=[node for node in tree.body if isinstance(node, ast.FunctionDef)], type_ignores=[])
        with tempfile.TemporaryDirectory() as root:
            directory = Path(root)
            (directory / "vbd_monthly_wpp573_grid.parquet").write_bytes(b"incomplete parquet")
            (directory / "parts").mkdir()
            valid = directory / "parts" / "vbd_fixture.parquet"
            pd.DataFrame({"month": [month for month in ("2024-01", "2024-02") for _ in range(8129)],
                          "cell_id": [f"cell_{i}" for _ in range(2) for i in range(8129)]}).to_parquet(valid)
            valid.with_name("vbd_fixture_progress.json").write_text('{"status":"complete"}')
            namespace = {"pd": pd, "json": json, "EOG_DIR": directory, "EXPECTED_MONTHS": 2}
            exec(compile(functions, "eog_loader", "exec"), namespace)
            frame = namespace["load_eog_complete"]()
            self.assertEqual(frame["month_key"].unique().tolist(), ["2024-01", "2024-02"])

    def test_gee_loader_keeps_year_and_month_for_export(self):
        payload = json.loads(NOTEBOOK.read_text(encoding="utf-8"))
        code = next("".join(cell["source"]) for cell in payload["cells"]
                    if cell["cell_type"] == "code" and "def load_gee():" in "".join(cell["source"]))
        tree = ast.parse(code)
        functions = ast.Module(body=[node for node in tree.body if isinstance(node, ast.FunctionDef)], type_ignores=[])
        with tempfile.TemporaryDirectory() as root:
            directory = Path(root)
            for year, month in [(2015, 1), (2025, 12)]:
                pd.DataFrame({"cell_id": [f"cell_{i}" for i in range(8129)]}).to_csv(
                    directory / f"SEACAST_PRIGI_GRID_8129_{year}_{month:02d}.csv", index=False)
            namespace = {"pd": pd, "np": np, "re": re, "Path": Path, "tqdm": lambda x, **_: x,
                         "GEE_DIR": directory, "EXPECTED_MONTHS": 2}
            exec(compile(functions, "gee_loader", "exec"), namespace)
            frame = namespace["load_gee"]()
            self.assertEqual(set(frame.loc[frame["month_key"] == "2015-01", "year"]), {2015})
            self.assertEqual(set(frame.loc[frame["month_key"] == "2025-12", "month"]), {12})

    def test_latest_tct_notebook_is_valid_and_uses_the_new_method(self):
        self.assertTrue(NOTEBOOK.exists())
        payload = json.loads(NOTEBOOK.read_text(encoding="utf-8"))
        self.assertEqual(payload["nbformat"], 4)
        for index, cell in enumerate(payload["cells"]):
            if cell.get("cell_type") == "code":
                source = "\n".join(
                    line for line in cell.get("source", []) if not line.lstrip().startswith("!")
                )
                compile(source, f"cell-{index}", "exec")
        source = "\n".join(
            line for cell in payload["cells"] for line in cell.get("source", [])
        )

        self.assertIn("tuna_sirip_kuning", source)
        self.assertIn("tongkol_krai", source)
        self.assertIn("final_validation_year = 2025", source)
        self.assertIn("RandomForestRegressor", source)
        self.assertIn("GradientBoostingRegressor", source)
        self.assertIn("np.log1p", source)
        self.assertIn("np.expm1", source)
        self.assertIn("LinearGAM", source)
        self.assertIn("MaxEnt", source)
        self.assertIn("NO SPATIAL OCCURRENCE", source)
        self.assertIn("MAX_OPERATIONAL_NM = 60.0", source)
        self.assertIn("CILACAP_RAW_PATH", source)
        self.assertIn("residual_shrinkage", source)
        self.assertIn("apply_oceanographic_qc", source)
        self.assertIn("fleet_gt_30gt", source)
        self.assertIn("GAM_FEATURES", source)
        self.assertIn("n_splines=3", source)
        self.assertIn("shrinkage_grid = np.array([0.0", source)
        self.assertIn("tct_occurrence_hsi_matches.csv", source)
        self.assertIn("validate_occurrence_coverage", source)
        self.assertIn("full_grid_occurrence_validation", source)
        self.assertIn("scope='operational_60nm'", source)
        self.assertIn("build_temporal_calibration_payload", source)
        self.assertIn("temporal_calibration", source)
        self.assertNotIn("monthly_env = df.groupby('month_key')[FEATURE_COLS].mean()", source)
        self.assertNotIn("legacy_species_removed", source)


if __name__ == "__main__":
    unittest.main()
