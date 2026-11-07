# Google Earth Engine (GEE) Dual-Stack Spatial Pipeline (`gee/`)
## Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi — WPPNRI 573 (Phase 4.2)

Folder ini berisi implementasi **Opsi A (Full Auto + Dual-Stack)** untuk pemodelan spasial Google Earth Engine:

1. **`gee/js/` (GEE Code Editor JavaScript Modules):**
   - `00_aoi_and_grid_builder.js` — Konstruksi Sub-AOI Prigi `[111.00, 113.00]°BT × [-10.45, -8.00]°LS` dan grid kanonis `50 × 41 = 2.050` sel (`0.05°`).
   - `01_bathymetry_and_terrain.js` — Ekstraksi batimetri `ETOPO1`/`GEBCO`, kemiringan lereng (`slope_deg`), jarak ke kontur `-200 m`, dan zona penapisan batimetri hiu (`-200 m` s/d `-1000 m`).
   - `02_sst_and_thermal_fronts.js` — *Sensor fusion* SST `MODIS-Aqua` (`4 km`) + `NOAA OISST v2.1` (`0.25°`), gradien front termal Sobel (`°C/km`), dan kontras suhu *upwelling* pesisir-lepas pantai ($\Delta T$).
   - `03_chla_and_productivity.js` — Komposit bulanan Klorofil-a `MODIS-Aqua` (`chlor_a`), transformasi $\log_{10}$, dan *cloud gap-filling* 3-bulan.
   - `04_wind_and_ekman_dynamics.js` — Angin permukaan 10m (`NOAA CFSv2` / `ERA5`), tegangan angin ($\tau_x, \tau_y$), dan Transpor Ekman Meridional ($M_{ey}$, `m²/s`).
   - `05_scenario_predictor_stacks.js` — Kompilasi matriks prediktor untuk 6 skenario musiman/IOD + referensi April 2022.
   - `06_export_and_qc_manifest.js` — Orkestrasi ekspor ke Google Drive & pencatatan QC.

2. **`gee/py/` (Earth Engine Python API Automation Package):**
   - `config.py` — Konfigurasi spasial kanonis, ID koleksi satelit GEE, dan definisi 6 skenario.
   - `auth_check.py` — Pemeriksa status kredensial OAuth GEE (`~/.config/earthengine/credentials`) dan konektivitas proyek GCP.
   - `run_gee_pipeline.py` — Pipeline ekstraksi otomatis server-side GEE yang mengunduh deret waktu 132 bulan (`gee_ocean_monthly_132m_v2.csv`) dan tabel prediktor 2.050 sel (`gee_grid_2050_april_2022_v2.csv`) langsung ke `data/gee/exports/` beserta manifest `SHA-256`.
