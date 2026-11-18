"""Stable positive-class probabilities for persisted binary ensemble models.

The Tongkol Krai logistic estimators were serialized with a newer scikit-learn
version than the pinned production runtime. Its binary coefficients are sufficient
to calculate the same sigmoid probability without version-specific model fields.
"""

import numpy as np
from scipy.special import expit
from sklearn.linear_model import LogisticRegression


def positive_class_probability(estimator, features):
    """Return P(class=1), including for logistic artifacts lacking multi_class."""
    classes = np.asarray(estimator.classes_)
    positive = np.flatnonzero(classes == 1)
    if len(positive) != 1:
        raise ValueError("Ensemble component must contain positive class 1")

    if isinstance(estimator, LogisticRegression):
        if len(classes) != 2:
            raise ValueError("Logistic ensemble component must be binary")
        probability_for_last_class = expit(estimator.decision_function(features))
        return probability_for_last_class if positive[0] == 1 else 1 - probability_for_last_class

    return estimator.predict_proba(features)[:, int(positive[0])]
