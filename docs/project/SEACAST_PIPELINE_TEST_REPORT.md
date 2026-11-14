# SEACAST — Pipeline and Scientific Gate Test Report

**Run date:** 14 November 2026
**Scope:** TCT two-output architecture, notebook 2015–2025, WPPNRI 573.

## Verification summary

| Check | Result |
|---|---|
| Python regression suite (`unittest discover -s tests -t . -v`) | 30/30 passed |
| Notebook builder | Passed; notebook regenerated |
| Full notebook runner | Passed; metrics and manifests regenerated |
| Spatial occurrence validator | Full grid 31/31; operational 3/31 |
| Scientific gate | `HOLD_FOR_SCIENTIFIC_REVIEW` |
| Focused Laravel contract/API suites | Passed: nomenclature, provenance, planner, tile/export/WebGIS suites |
| Full PHPUnit suite (`php vendor/bin/phpunit --colors=never`) | 199/199 passed, 3.485 assertions |

The Python command uses `-t .` intentionally so `tests/` and the legacy `scripts/` test module names are discovered without collision.

## Holdout metrics

Training period: 2015–2024. Final holdout: 2025 (12 months per operational panel; current modeling panel is dual-port).

| Komoditas | RMSE model | Baseline musiman | R² riil | R² log | Beats baseline | Temporal calibration |
|---|---:|---:|---:|---:|---|---|
| Tuna Sirip Kuning | 274,103 | 305,572 | -0,282 | 0,089 | Ya | `ENABLED` |
| Cakalang | 178,809 | 178,809 | 0,146 | 0,509 | Tidak | `BASELINE_ONLY` |
| Tongkol Krai | 13,537 | 13,537 | -0,077 | -1,212 | Tidak | `BASELINE_ONLY` |

The log-scale Cakalang result is diagnostic only. It does not authorize a real-scale calibration because the model does not improve on the seasonal baseline.

## Spatial validation

| Scope | Matched | Rate | Evidence status |
|---|---:|---:|---|
| `full_grid` — canonical 8.129 cells | 31/31 | 100% | `EMPIRICALLY_VALIDATED` for grid proximity |
| `operational_60nm` — local Prigi/Cilacap subset | 3/31 | 9,68% | `PARTIAL_SPATIAL_VALIDATION` |

The full-grid result must not be conflated with operational fleet coverage. The latter controls the web release label.

## Contract/API checks

- Internal species normalization still returns `madidihang`, `cakalang`, `tongkol`.
- Public payloads expose `tuna_sirip_kuning`, `cakalang`, and `tongkol_krai` while retaining legacy aliases.
- Spatial payloads include evidence/source/model/timestamp/validation metadata.
- Map-to-planner links carry `cell_id`; fallback coordinates and HSI are explicitly labeled.
- `TEMPORAL_CPUE_CALIBRATED` is rejected unless an enabled manifest includes a numeric correction factor that is applied to catch projection; current run therefore remains `FORMULA_SCENARIO`.
- Tile properties and cache keys use schema version `v2`.
- No database migration was introduced for provenance fields.

## Release decision

`HOLD_FOR_SCIENTIFIC_REVIEW` for operational fish-location claims. The WebGIS may expose exploratory HSI/DOM maps with proxy labels, and the Trip Planner may expose labeled cost/catch/GT scenarios. Neither output should be presented as a guaranteed fish location, catch, or profit estimate.
