# PHASE 4.1.1 — FISHERIES METRIC & TAXONOMIC RECONCILIATION (`R-03`)
## Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi — WPPNRI 573

- **Audit Area:** `R-03` — 12-Category Taxonomic Disaggregation & Metric Naming Consistency
- **Primary Source Inspected:** `Laporan Statistik Perikanan PPN Prigi 2025.xlsx` — **Sheet 5 (`PRODUKSI IKAN PER SPESIES`, 132 Monthly Rows, 2015–2025)**
- **Audit Status:** `PASS` (`0` kg difference across all 132 months and 12 recorded categories)

---

## 1. Exact Primary-Source Reconciliation (`Sheet 5` vs Workspace Datasets)

Direct XML parsing of `sheet5.xml` confirms that [`data/master_prigi_all_inclusive.csv`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/master_prigi_all_inclusive.csv), [`data/master.csv`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/master.csv), and [`data/spatial_species_dictionary.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/spatial_species_dictionary.json) match `Sheet 5` with **`0 kg` discrepancy** across all 12 categories. The preliminary pre-audit numbers that had lingered in the markdown table of [`PHASE_4_1_DATA_INTEGRITY_REPORT.md`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/PHASE_4_1_DATA_INTEGRITY_REPORT.md) (`Gulamah = 86,347 kg`, `Julung = 207,462 kg`, `Krai = 3,551,695 kg`, `Como = 1,916,527 kg`) have been replaced with the exact `Sheet 5` values:

| Rank | Sheet 5 Column | Database Key | Local Name | Scientific Name | Ecological Group | Verified 11-Year Catch (kg) | Share of 12 Taxa (%) | Share of Pelagic Subset (%) |
| :---: | :---: | :--- | :--- | :--- | :--- | ---: | ---: | ---: |
| 1 | Col D | `cakalang_kg` | Cakalang (Blereng) | *Katsuwonus pelamis* | True Oceanic Large Pelagic | `8,294,092` | `45.06%` | `45.90%` |
| 2 | Col F | `tongkol_krai_kg` | Tongkol Krai | *Auxis thazard* | Neritic Large Pelagic | `3,094,621` | `16.81%` | `17.12%` |
| 3 | Col E | `madidihang_kg` | Madidihang (Balong) | *Thunnus albacares* | True Oceanic Large Pelagic | `2,933,924` | `15.94%` | `16.24%` |
| 4 | Col H | `tongkol_como_kg` | Tongkol Como | *Euthynnus affinis* | Neritic Large Pelagic | `2,412,039` | `13.11%` | `13.35%` |
| 5 | Col G | `tongkol_kenyar_kg` | Tongkol Kenyar | *Sarda orientalis* | Neritic Large Pelagic | `520,888` | `2.83%` | `2.88%` |
| 6 | Col O | `legacy_species_removed_kg` | Legacy species removed from TCT scope | *Current TCT scope exclusion* | True Oceanic Large Pelagic | `316,632` | `1.72%` | `1.75%` |
| 7 | Col L | `marlin_kg` | Setuhuk / Marlin | *Istiompax indica* | True Oceanic Large Pelagic | `315,281` | `1.71%` | `1.74%` |
| 8 | Col I | `gulamah_kg` | Gulamah / Awon | *Johnius / Nibea spp.* | **Demersal Exception (Sciaenidae)** | **`207,156`** | **`1.13%`** | *Excluded* |
| 9 | Col K | `cucut_kg` | Hiu Cucut | *Carcharhinus spp.* | **Elasmobranch Bycatch Exception** | **`127,452`** | **`0.69%`** | *Excluded* |
| 10 | Col M | `julung_kg` | Julung-julung | *Hemiramphus spp.* | Coastal Epipelagic Small Pelagic | **`68,906`** | **`0.37%`** | `0.38%` |
| 11 | Col N | `tuna_mata_besar_kg` | Tuna Mata Besar | *Thunnus obesus* | True Oceanic Large Pelagic | `63,135` | `0.34%` | `0.35%` |
| 12 | Col J | `tenggiri_kg` | Tenggiri | *Scomberomorus commerson* | Neritic Large Pelagic | `51,135` | `0.28%` | `0.28%` |

---

## 2. Disaggregated Summary Metrics Added Across All Datasets

To preserve 100% backward compatibility while eliminating taxonomic ambiguity in `"total_pelagis_kg"` / `"total_pelagis_besar_kg"`, six explicit disaggregated columns/fields have been added to [`data/master_prigi_all_inclusive.csv`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/master_prigi_all_inclusive.csv), [`data/master.csv`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/master.csv), [`data/oceanographic/statistics/monthly/monthly_stats_prigi.csv`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/oceanographic/statistics/monthly/monthly_stats_prigi.csv), [`data/oceanographic/statistics/monthly/ocean_fisheries_master_132m.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/data/oceanographic/statistics/monthly/ocean_fisheries_master_132m.json), and [`html/oceanographic_data.js`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/html/oceanographic_data.js):

| Metric Column / Field Name | Cumulative 11-Year Mass (kg) | Share (%) | Taxonomic Definition |
| :--- | ---: | ---: | :--- |
| `total_selected_taxa_catch_kg` *(alias: `total_pelagis_kg`)* | **`18,405,261 kg`** | `100.00%` | All 12 categories recorded in PPN Prigi `Sheet 5` |
| `pelagic_catch_kg` | **`18,070,653 kg`** | `98.18%` | 10 true teleost pelagic taxa (excludes `gulamah_kg` and `cucut_kg`) |
| `large_pelagic_9_taxa_kg` | **`18,001,747 kg`** | `97.81%` | 9 large/neritic teleost pelagic taxa (excludes `julung_kg`, `gulamah_kg`, `cucut_kg`) |
| `small_pelagic_exception_kg` | **`68,906 kg`** | `0.37%` | Julung-julung (*Hemiramphus spp.*, surface small pelagic) |
| `demersal_exception_kg` | **`207,156 kg`** | `1.13%` | Gulamah (*Johnius / Nibea spp.*, demersal Sciaenidae exception) |
| `elasmobranch_bycatch_kg` | **`127,452 kg`** | `0.69%` | Hiu Cucut (*Carcharhinus spp.*, elasmobranch bycatch) |

---

## 3. Impact on Ocean-Fisheries Correlation (`12-Taxon Total` vs `10-Taxon Pelagic Subset`)

Because the non-pelagic exceptions (`Gulamah + Cucut = 334,608 kg`) constitute only `1.82%` of the 11-year volume, lag-0 Pearson $r$ and Spearman $\rho$ values change by at most `0.0110` when switching from `total_selected_taxa_catch_kg` (`12` taxa) to `pelagic_catch_kg` (`10` teleost pelagic taxa):

| Environmental Variable | `total_selected_taxa_catch_kg` (12 Taxa) $r_0$ | `pelagic_catch_kg` (10 Pelagic Taxa) $r_0$ | Absolute Difference $\Delta |r_0|$ | Direction & Statistical Significance |
| :--- | :---: | :---: | :---: | :--- |
| **SST Pesisir Prigi (`prigi_sst_coastal`)** | `-0.3707` | `-0.3597` | `0.0110` | Unchanged (Negative, $q_{\text{FDR}} < 0.05$) |
| **Klorofil-a Prigi (`prigi_chl_mean`)** | `+0.3652` | `+0.3602` | `0.0050` | Unchanged (Positive, $q_{\text{FDR}} < 0.05$) |
| **SST Contrast (`prigi_cui_thermal`)** | `+0.3718` | `+0.3635` | `0.0083` | Unchanged (Positive, $q_{\text{FDR}} < 0.05$) |
| **Gradien Front Termal Sobel (`thermal_front_sobel_c_km`)** | `+0.3954` | `+0.3880` | `0.0074` | Unchanged (Positive, $q_{\text{FDR}} < 0.05$) |
| **Meridional Ekman Transport (`ekman_transport_m2_s`)** | `-0.4580` | `-0.4498` | `0.0082` | Unchanged (Negative, $q_{\text{FDR}} < 0.01$) |
| **Tinggi Gelombang Hs (`hs_wave_m`)** | `+0.4100` | `+0.4044` | `0.0056` | Unchanged (Positive, $q_{\text{FDR}} < 0.01$) |
