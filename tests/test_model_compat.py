import sys
import unittest
from pathlib import Path

import joblib
import numpy as np
from scipy.special import expit

ROOT = Path(__file__).parents[1]
sys.path.insert(0, str(ROOT / "scripts"))

from model_compat import positive_class_probability


class ModelCompatTests(unittest.TestCase):
    def test_tongkol_logistic_artifact_uses_stored_binary_coefficients(self):
        bundle = joblib.load(ROOT / "models" / "tongkol_krai_ensemble_model.joblib")
        features = np.random.default_rng(42).normal(size=(20, len(bundle["feature_cols"])))

        for name in ("maxent", "glm"):
            estimator = bundle[name]
            expected = expit(estimator.decision_function(features))
            actual = positive_class_probability(estimator, features)
            np.testing.assert_allclose(actual, expected, rtol=1e-12, atol=1e-12)

    def test_existing_cakalang_models_keep_positive_class_probability(self):
        bundle = joblib.load(ROOT / "models" / "cakalang_ensemble_model.joblib")
        features = np.random.default_rng(7).normal(size=(20, len(bundle["feature_cols"])))

        for name in ("maxent", "rf", "glm"):
            estimator = bundle[name]
            expected = estimator.predict_proba(features)[:, list(estimator.classes_).index(1)]
            actual = positive_class_probability(estimator, features)
            np.testing.assert_allclose(actual, expected, rtol=1e-12, atol=1e-12)


if __name__ == "__main__":
    unittest.main()
