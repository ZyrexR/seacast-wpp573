# TCT Commodity Migration Design

**Date:** 13 November 2026  
**Status:** Approved direction from project owner

## Intent

Menjadikan TCT sebagai satu-satunya trio komoditas utama SEACAST:

1. Tuna Sirip Kuning (*Thunnus albacares*)
2. Cakalang (*Katsuwonus pelamis*)
3. Tongkol Krai (*Auxis thazard*)

Perubahan ini harus konsisten pada dokumentasi, model inference, kontrak data, API, UI, test, dataset turunan, dan artefak model.

## Canonical naming contract

| Public commodity | Scientific name | Existing internal compatibility key | Prediction field |
|---|---|---|---|
| Tuna Sirip Kuning | *Thunnus albacares* | `madidihang` | `hsi_madidihang` |
| Cakalang | *Katsuwonus pelamis* | `cakalang` | `hsi_cakalang` |
| Tongkol Krai | *Auxis thazard* | `tongkol` | `hsi_tongkol` |

The existing `madidihang` and `hsi_tongkol` keys remain internal compatibility keys for existing persisted data and database columns. All user-facing labels, documentation, species selectors, API display metadata, and scientific summaries use “Tuna Sirip Kuning” and “Tongkol Krai”. New species input aliases use `tuna_sirip_kuning`, `cakalang`, and `tongkol_krai`; legacy internal keys are accepted only where required for persisted-data compatibility.

## Legacy species removed from TCT scope removal boundary

Legacy species removed from TCT scope is removed from the active repository surface completely:

- delete the Legacy species removed from TCT scope `.joblib` model and Legacy species removed from TCT scope presence/zone artifacts;
- remove Legacy species removed from TCT scope from Python inference, transformer, bridge payloads, and model evaluation metadata;
- remove Legacy species removed from TCT scope columns/rows from active species datasets and derived output contracts;
- remove Legacy species removed from TCT scope from Laravel models, services, repositories, controllers, views, routes, exports, and tests;
- remove Legacy species removed from TCT scope from `docs/project/`, public HTML/Blade content, and project reports that describe the active system.

Historical source material that is not used by the active system may only remain if it is explicitly marked as archived and cannot be consumed by runtime code; otherwise it is removed or rewritten to TCT.

## Goals

- A fresh inference run emits only the three TCT commodities.
- The UI and exported summaries show exactly TCT nomenclature.
- No active runtime or project document references Legacy species removed from TCT scope.
- Existing records using `madidihang` and `hsi_tongkol` remain readable.
- Tests pin both the TCT contract and the absence of Legacy species removed from TCT scope from active output.

## Non-goals

- Retraining the Tuna Sirip Kuning or Cakalang models.
- Changing the environmental feature vector or ensemble weights for existing models.
- Deleting unrelated non-TCT bycatch species or historical fishery observations.
- Changing the dual-hub, GEE, BMKG, PostGIS, or WebGIS architecture beyond commodity contracts.

## Selected approach

The selected approach is a canonical migration with compatibility aliases. A temporary text-only alias would leave Legacy species removed from TCT scope and generic species contracts active, while a database-wide column rename would create unnecessary migration risk. Keeping the existing internal keys while changing the public contract allows the current persisted data to remain readable and makes the TCT boundary explicit at the application edge.

## Verification requirements

- Static repository scan confirms no active `legacy_species_removed`, `legacy species removed from TCT scope`, or `Current TCT scope exclusion` references outside explicitly archived material.
- Python inference tests confirm output species and columns are TCT-only.
- Laravel feature/unit tests confirm TCT labels, selectors, exports, and API payloads.
- Documentation audit confirms all `docs/project/` documents identify TCT and no longer advertise Legacy species removed from TCT scope.
