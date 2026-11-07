/**
 * ============================================================================
 * SEACAST PRIGI — WPPNRI 573 (PHASE 4.2 GEE SPATIAL PIPELINE)
 * Module 06: Automated Export & QC Manifest Generator
 * File: gee/js/06_export_and_qc_manifest.js
 * ============================================================================
 * Exports to Google Drive folder 'SEACAST_PRIGI_573':
 *   1. gee_ocean_monthly_132m_v2.csv (132 monthly zonal records, 2015-01 to 2025-12)
 *   2. gee_grid_2050_april_2022_v2.csv (2,050 cells x static & dynamic predictors)
 *   3. gee_predictor_stack_6scenarios_2050cells_v2.csv (12,300 rows = 6 x 2,050)
 *   4. gee_raster_stack_april_2022_v2.tif (Multi-band GeoTIFF)
 */

print('Use gee/py/run_gee_pipeline.py for automated CLI extraction and export with SHA-256 provenance.');
