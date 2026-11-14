import unittest
import sys
from pathlib import Path

import numpy as np
import pandas as pd

sys.path.insert(0, str(Path(__file__).parents[1] / "scripts"))


class TctLocalModelingTest(unittest.TestCase):
    def test_select_radius_uses_port_distance_and_optional_active_cells(self):
        from tct_local_modeling import select_radius

        frame = pd.DataFrame(
            {
                "cell_id": ["near", "far", "near_inactive"],
                "lon_center": [111.74, 112.8, 111.8],
                "lat_center": [-8.2833, -8.2833, -8.3],
                "vbd_n_detect": [3, 7, 0],
            }
        )
        selected = select_radius(
            frame,
            lon=111.7275,
            lat=-8.2833,
            min_nm=0,
            max_nm=60,
            active_only=True,
        )
        self.assertEqual(selected["cell_id"].tolist(), ["near"])
        self.assertGreater(float(selected["dist_port_nm"].iloc[0]), 0.0)

    def test_build_local_features_calculates_upwelling_contrast(self):
        from tct_local_modeling import build_local_features

        frame = pd.DataFrame(
            {
                "month_key": ["2024-01"] * 4,
                "cell_id": ["in1", "in2", "off1", "off2"],
                "lon_center": [111.7275, 111.75, 112.5, 112.6],
                "lat_center": [-8.2833, -8.30, -8.2833, -8.30],
                "sst_c": [25.0, 25.2, 27.0, 27.2],
                "chla_mg_m3": [0.8, 0.9, 0.2, 0.3],
                "sst_gradient": [0.04, 0.05, 0.01, 0.02],
                "vbd_n_detect": [2, 1, 0, 0],
                "vbd_pct_detect": [0.2, 0.1, 0.0, 0.0],
            }
        )
        result = build_local_features(
            frame,
            port_name="prigi",
            port_lon=111.7275,
            port_lat=-8.2833,
            max_nm=60,
            inshore_nm=12,
            offshore_min_nm=30,
        )
        row = result.iloc[0]
        self.assertAlmostEqual(row["sst_inshore_mean"], 25.1, places=5)
        self.assertAlmostEqual(row["sst_offshore_mean"], 27.1, places=5)
        self.assertAlmostEqual(row["upwelling_contrast_index"], -2.0, places=5)
        self.assertEqual(row["local_cell_count"], 4)

    def test_add_anomaly_and_lag_features_are_grouped_by_calendar_month(self):
        from tct_local_modeling import add_anomaly_and_lag_features

        panel = pd.DataFrame(
            {
                "port": ["prigi"] * 4,
                "month_key": ["2023-01", "2023-02", "2024-01", "2024-02"],
                "year": [2023, 2023, 2024, 2024],
                "month": [1, 2, 1, 2],
                "sst_c": [25.0, 28.0, 27.0, 30.0],
                "chla_mg_m3": [0.1, 0.2, 0.3, 0.4],
                "upwelling_contrast_index": [-2.0, -1.0, 0.0, 1.0],
            }
        )
        result = add_anomaly_and_lag_features(
            panel,
            value_columns=["sst_c", "chla_mg_m3", "upwelling_contrast_index"],
        )
        self.assertAlmostEqual(result.loc[2, "sst_c_climatology"], 26.0)
        self.assertAlmostEqual(result.loc[2, "anom_sst_c"], 1.0)
        self.assertTrue(np.isnan(result.loc[0, "sst_c_lag1"]))
        self.assertAlmostEqual(result.loc[2, "sst_c_lag1"], 28.0)

    def test_parse_cilacap_statistics_returns_monthly_tct_panel(self):
        from tct_local_modeling import parse_cilacap_statistics

        raw_path = Path(__file__).parents[1] / "seacast-prigi-laravel" / "database" / "data" / "raw" / "pps_cilacap_statistics_2016_2025.csv"
        result = parse_cilacap_statistics(raw_path)
        self.assertEqual(len(result), 120)
        self.assertEqual(result["port"].unique().tolist(), ["cilacap"])
        self.assertEqual(result["month_key"].min(), "2016-01")
        self.assertEqual(result["month_key"].max(), "2025-12")
        self.assertTrue((result[["tuna_sirip_kuning_catch_kg", "cakalang_catch_kg", "tongkol_krai_catch_kg"]] >= 0).all().all())
        self.assertTrue(result[["tuna_sirip_kuning_cpue_kg_trip", "cakalang_cpue_kg_trip", "tongkol_krai_cpue_kg_trip"]].notna().all().all())

    def test_add_temporal_model_features_adds_effort_log_and_harmonics(self):
        from tct_local_modeling import FEATURE_COLS, add_temporal_model_features

        frame = pd.DataFrame({"effort_trips": [0, 10], "month": [3, 6]})
        result = add_temporal_model_features(frame)
        self.assertAlmostEqual(result.loc[1, "effort_trips_log"], np.log1p(10))
        self.assertAlmostEqual(result.loc[0, "sin_month"], np.sin(2 * np.pi * 3 / 12.0))
        self.assertAlmostEqual(result.loc[1, "cos_month"], np.cos(2 * np.pi * 6 / 12.0))
        self.assertIn("wind_speed", FEATURE_COLS)
        self.assertIn("effort_trips_log", FEATURE_COLS)
        self.assertIn("sin_month", FEATURE_COLS)


if __name__ == "__main__":
    unittest.main()
