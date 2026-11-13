# TCT Commodity Migration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Migrate SEACAST's active commodity contract to TCT—Tuna Sirip Kuning, Cakalang, and Tongkol Krai—and remove Albakor from active code, data artifacts, UI, tests, and project documentation.

**Architecture:** Keep `madidihang` and `hsi_tongkol` only as internal compatibility keys for persisted records, while exposing canonical public slugs and labels through a shared species contract. The Python bridge and transformer emit only TCT outputs; Laravel consumes the same TCT contract. Albakor artifacts and branches are deleted after tests establish the new contract.

**Tech Stack:** Python 3, pandas, NumPy, joblib/scikit-learn; Laravel 13, PHP 8.3, PHPUnit 11, Blade/Alpine; CSV/JSON/GeoJSON; Markdown project documentation.

**Spec:** `docs/superpowers/specs/2026-11-13-tct-commodity-migration-design.md`

## Global Constraints

- Public commodity trio is exactly Tuna Sirip Kuning (*Thunnus albacares*), Cakalang (*Katsuwonus pelamis*), and Tongkol Krai (*Auxis thazard*).
- Existing persisted compatibility keys `madidihang`/`hsi_madidihang` and `tongkol`/`hsi_tongkol` remain readable.
- New public aliases are `tuna_sirip_kuning`, `cakalang`, and `tongkol_krai`.
- Albakor is not a supported runtime species and must not appear in active output contracts.
- Do not retrain or alter the existing Cakalang/Tuna Sirip Kuning ensemble weights.
- Work directly on `master` because the project owner explicitly requested the master tree.

## Review Focus

- Legacy persisted rows using `madidihang` and `hsi_madidihang` remain readable; test in Laravel model/repository paths.
- A request using `tuna_sirip_kuning` maps to the existing Yellowfin model without exposing the old label; test Python and Laravel aliases.
- Tongkol Krai is present in inference payloads even though its current HSI is rule-based; test `hsi_tongkol` and public `tongkol_krai` metadata.
- Albakor model files, requested species, and serialized output keys are absent; test static output and filesystem artifacts.
- Existing DOM/hotspot rules still use the Cakalang/Tuna Sirip Kuning signals; test the unchanged thresholds after species-list reduction.

---

### Task 1: Establish and test the canonical TCT contract

**Files:**
- Create: `scripts/species_contract.py`
- Modify: `seacast-prigi-laravel/app/Domain/Species/Services/SpeciesPredictionService.php`
- Modify: `seacast-prigi-laravel/app/Domain/Species/DTO/PythonComputationResult.php`
- Test: `scripts/test_species_contract.py`
- Test: `seacast-prigi-laravel/tests/Unit/SpeciesAndHsiEngineTest.php`
- Test: `seacast-prigi-laravel/tests/Unit/ScientificToleranceRegressionTest.php`

**Interfaces:**
- Python `PUBLIC_SPECIES`, `PUBLIC_LABELS`, `PUBLIC_TO_INTERNAL`, and `normalize_species(value: str) -> str` expose the canonical public contract.
- Laravel `SpeciesPredictionService::getAllSpecies()` returns the three canonical public slugs; `getEnsembleWeights()` accepts both canonical public aliases and persisted internal keys; evaluation summaries expose public labels.

- [ ] Write failing Python tests for the exact three public species, alias normalization, and rejection of `albakor`.
- [ ] Run `python scripts/test_species_contract.py`; confirm RED because the contract module does not exist.
- [ ] Write failing Laravel assertions for three species only, Tuna Sirip Kuning/Tongkol Krai labels, and Albakor rejection.
- [ ] Run the focused PHPUnit tests; confirm RED on legacy three-species expectations.
- [ ] Implement the minimal Python and Laravel contract changes without touching model weights.
- [ ] Run both focused test groups; confirm GREEN.
- [ ] Commit `feat: define canonical TCT species contract`.

### Task 2: Migrate Python inference and remove Albakor model branches

**Files:**
- Modify: `scripts/bridge_inference_runner.py`
- Modify: `scripts/daily_inference.py`
- Modify: `scripts/gee_pipeline_transformer.py`
- Modify: `scripts/test_model_decisions.py`
- Modify: `scripts/test_gee_extractor.py` if output schema assertions require it
- Test: `scripts/test_species_contract.py`
- Delete: `models/albakor_ensemble_model.joblib`
- Delete: `data/sdm/presence/presence_proxy_albakor.geojson`
- Delete: `outputs/sdm/zones/functional_zones_albakor.geojson`

**Interfaces:**
- `bridge_inference_runner.py` and `gee_pipeline_transformer.py` output `hsi_cakalang`, `hsi_madidihang`, and `hsi_tongkol` only, with public species metadata `tuna_sirip_kuning`, `cakalang`, `tongkol_krai`.
- `hsi_tongkol` uses the existing rule-based Tongkol Krai HSI formula; no new training run is introduced.

- [ ] Extend failing Python contract tests to assert TCT-only payload keys and no Albakor model load.
- [ ] Run the tests and confirm RED against current bridge/transformer output.
- [ ] Remove Albakor loops, weights, summaries, export fields, and model-path references; add Tongkol Krai output to the bridge/transformer where missing.
- [ ] Update the model decision test to calculate/report TCT only.
- [ ] Delete the three exact Albakor artifacts after verifying their paths.
- [ ] Run Python contract, model decision, and extractor tests; confirm GREEN or record environment blockers.
- [ ] Commit `feat: emit TCT-only Python inference outputs`.

### Task 3: Migrate Laravel API, persistence adapters, UI, and tests

**Files:**
- Modify: `app/Domain/ScientificData/Repositories/PostGisScientificDataRepository.php`
- Modify: `app/Services/PostGisService.php`
- Modify: `app/Services/ScientificExportService.php`
- Modify: `app/Domain/ScientificData/Services/TimeMachineService.php`
- Modify: `app/Domain/ScientificData/Services/SimulationService.php`
- Modify: `app/Http/Controllers/Api/TileApiController.php`
- Modify: `app/Http/Controllers/Web/HomeController.php`
- Modify: `app/Http/Controllers/Web/DashboardController.php`
- Modify: `app/Http/Controllers/Web/TripPlannerController.php`
- Modify: `app/Models/DailyPrediction.php`
- Modify: `app/Models/GridMonthlyPrediction.php`
- Modify: relevant Blade views under `resources/views/public`, `resources/views/dashboard`, and `resources/views/components/ui`
- Modify: affected Feature/Unit tests under `tests/Feature` and `tests/Unit`

- [ ] Add failing assertions for public TCT labels/selectors, TCT-only export headers, and absence of `hsi_albakor` in API payloads.
- [ ] Run focused Laravel tests and confirm RED.
- [ ] Replace user-facing Madidihang/Tongkol labels with Tuna Sirip Kuning/Tongkol Krai, normalize new public slugs, and remove Albakor fields from active response/export contracts while preserving DB compatibility reads where required.
- [ ] Update DOM, hotspot, time-machine, simulation, trip-planner, radar, fleet, public science, and inspector tests to TCT semantics.
- [ ] Run the focused Laravel test groups and confirm GREEN.
- [ ] Commit `feat: expose TCT across Laravel application surfaces`.

### Task 4: Remove Albakor from active datasets and generated metadata

**Files:**
- Modify: `data/species_traits.csv`
- Modify: `data/master.csv`
- Modify: `data/master_prigi_all_inclusive.csv`
- Modify: `data/spatial_species_dictionary.json`
- Modify: active JSON/CSV prediction, HSI, correlation, and evaluation outputs containing Albakor fields
- Modify: `models/sdm/ensemble/model_evaluations_summary.json`
- Modify: active HTML/Streamlit assets that expose species metadata

- [ ] Add a repository contract test/script that scans active text outputs and asserts no Albakor terms, fields, or species keys.
- [ ] Run it and confirm RED against the baseline artifacts.
- [ ] Remove Albakor rows/columns/keys only from active datasets and regenerate or rewrite derived outputs so JSON/CSV schemas remain valid.
- [ ] Run the contract scanner and parse every changed JSON/CSV file to confirm valid syntax and no Albakor references.
- [ ] Commit `chore: remove Albakor data artifacts from active outputs`.

### Task 5: Rewrite project documentation and perform full verification

**Files:**
- Modify: all eight files in `docs/project/`
- Modify: active `docs/deployment/`, `docs/superpowers/`, `html/`, `pages/`, and report files that describe the current active system
- Create: `reports/validation_gates/tct_commodity_contract.json` if useful as machine-readable evidence

- [ ] Add a documentation contract test requiring TCT terminology and rejecting Albakor in `docs/project/`.
- [ ] Run it and confirm RED against the baseline documents.
- [ ] Rewrite all active project documents, formulas, tables, diagrams, output examples, and test-report claims to the TCT contract; remove outdated Albakor claims.
- [ ] Run static scans for `albakor`, `albacore`, and `Thunnus alalunga`, validate JSON/CSV syntax, run Python tests, and run the focused/full Laravel suite with the current environment documented.
- [ ] Review the complete diff and commit `docs: align project documentation with TCT contract`.

## Final verification

- `git status --short` shows only intentional post-commit state.
- `rg -n -i "albakor|albacore|thunnus alalunga"` returns no active runtime/docs/data hits.
- `python scripts/test_species_contract.py` passes.
- `python scripts/test_model_decisions.py` passes without Albakor output.
- Focused Laravel tests pass; full suite result is reported exactly, including any pre-existing environment failure.
- All changed JSON/CSV files parse successfully.
