# PHASE 4.1.1 — FLEET DATA RECONCILIATION REPORT (`R-02`)
## Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi — WPPNRI 573

- **Audit Area:** `R-02` — PPN Prigi Fishing Fleet Composition by Gross Tonnage (GT) & Historical Peak Verification
- **Primary Source Inspected:** `Laporan Statistik Perikanan PPN Prigi 2025.xlsx` — **Sheet 2 (`JUMLAH ARMADA PENANGKAPAN UKURAN GT`, Rows 2–13)** and **Sheet 1 (`JUMLAH ARMADA PENANGKAPAN`, Rows 2–15)**
- **Canonical Output Dataset:** [`data/prigi_fleet_by_gt_2015_2025.csv`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/prigi_fleet_by_gt_2015_2025.csv)
- **Audit Status:** `PASS` (`0` cross-file discrepancies)

---

## 1. Primary-Source Extraction (`Sheet 2 — JUMLAH ARMADA PENANGKAPAN UKURAN GT`)

Direct XML inspection of `sheet2.xml` from `Laporan Statistik Perikanan PPN Prigi 2025.xlsx` confirms the official annual fleet counts by GT class for 2015–2025:

| Year | Excel Row (`Sheet 2`) | `< 10 GT` (Units) | `10 – < 20 GT` (Units) | `20 – < 30 GT` (Units) | `> 30 GT` (Units) | Total Registered Fleet (Units) | `<10 GT` (%) | `10–<20 GT` (%) | `20–<30 GT` (%) | `>30 GT` (%) |
| :---: | :---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| **2015** | Row 3 | `474` | `82` | `144` | `5` | **`705`** | `67.23%` | `11.63%` | `20.43%` | `0.71%` |
| **2016** | Row 4 | `546` | `94` | `151` | `3` | **`794`** *(Peak)* | `68.77%` | `11.84%` | `19.02%` | `0.38%` |
| **2017** | Row 5 | `530` | `94` | `151` | `3` | **`778`** | `68.12%` | `12.08%` | `19.41%` | `0.39%` |
| **2018** | Row 6 | `534` | `92` | `158` | `2` | **`786`** | `67.94%` | `11.70%` | `20.10%` | `0.25%` |
| **2019** | Row 7 | `453` | `24` | `128` | `3` | **`608`** | `74.51%` | `3.95%` | `21.05%` | `0.49%` |
| **2020** | Row 8 | `490` | `81` | `91` | `2` | **`664`** | `73.80%` | `12.20%` | `13.70%` | `0.30%` |
| **2021** | Row 9 | `518` | `80` | `88` | `2` | **`688`** | `75.29%` | `11.63%` | `12.79%` | `0.29%` |
| **2022** | Row 10 | `535` | `64` | `97` | `2` | **`698`** | `76.65%` | `9.17%` | `13.90%` | `0.29%` |
| **2023** | Row 11 | `448` | `55` | `99` | `3` | **`605`** | `74.05%` | `9.09%` | `16.36%` | `0.50%` |
| **2024** | Row 12 | `445` | `50` | `87` | `2` | **`584`** | `76.20%` | `8.56%` | `14.90%` | `0.34%` |
| **2025** | **Row 13** | **`296`** | **`53`** | **`91`** | **`3`** | **`443`** | **`66.82%`** | **`11.96%`** | **`20.54%`** | **`0.68%`** |

---

## 2. Root-Cause Explanation of Prior Discrepancies (`55` vs `53`, `1` vs `3`, and `855` vs `794`)

1. **Why `296 + 53 + 91 + 3 = 443` is Canonical for 2025:** Row 13 of `Sheet 2` explicitly records `296` (`<10 GT`), `53` (`10–<20 GT`), `91` (`20–<30 GT`), and `3` (`>30 GT`), summing to `443` vessels. Both [`html/index.html`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/html/index.html) (lines 459, 582–592) and [`data/sdm/bias/effort_bias_surface.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/sdm/bias/effort_bias_surface.json) already contained these exact figures.
2. **Where `55` and `1` came from:** In Phase 4.1 Stage 6, [`scratch/test_phase_4_1_integrity.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/test_phase_4_1_integrity.py) (lines 233 and 853) accidentally hardcoded `10–<20 GT: 55` (which was the **2023** count from Row 11 of `Sheet 2`) and `>30 GT: 1` (which was the December 2025 **monthly trip count** `gt4: 1` from `Sheet 4`, conflated with vessel units) and overwrote [`PHASE_3_MODEL_METADATA.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/PHASE_3_MODEL_METADATA.json).
3. **Where `855 (2021)` came from:** `Sheet 2` proves that the 2021 registered fleet was **`688` units**, and the 2015–2025 peak registered fleet was **`794` units in 2016** (while `Sheet 1` shows the all-time 2012–2025 peak was `1,004` units in 2014). The number `855` was an unverified narrative figure (`855` only appears in `masterData` as the May 2020 purse-seine trip count `trPukat: 855`) and has been removed from [`PHASE_4_1_DATA_INTEGRITY_REPORT.md`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/PHASE_4_1_DATA_INTEGRITY_REPORT.md).
