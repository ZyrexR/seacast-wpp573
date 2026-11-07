/**
 * ============================================================================
 * SEACAST PRIGI — WPPNRI 573 (PHASE 4.2 GEE SPATIAL PIPELINE)
 * Module 05: Multi-Scenario Predictor Stack Builder (6 Scenarios + April 2022)
 * File: gee/js/05_scenario_predictor_stacks.js
 * ============================================================================
 * Scenarios:
 *   1. april_2022_reference   (2022-04 Canonical Transition 1 Reference)
 *   2. east_monsoon_jja       (JJA Climatology 2015-2025)
 *   3. west_monsoon_djf       (DJF Climatology 2015-2025)
 *   4. transition_1_mam       (MAM Climatology 2015-2025)
 *   5. transition_2_son       (SON Climatology 2015-2025)
 *   6. super_positive_iod_2019 (Jul-Oct 2019 Extreme Upwelling Event)
 *   7. negative_iod_2016       (Jul-Oct 2016 La Nina / Warm Downwelling Event)
 */

var m00 = require('users/seacast_prigi/gee:00_aoi_and_grid_builder');
var m01 = require('users/seacast_prigi/gee:01_bathymetry_and_terrain');
var m02 = require('users/seacast_prigi/gee:02_sst_and_thermal_fronts');
var m03 = require('users/seacast_prigi/gee:03_chla_and_productivity');
var m04 = require('users/seacast_prigi/gee:04_wind_and_ekman_dynamics');

// Standalone fallback note: When running in GEE Code Editor, copy functions or adjust require() path.
print('Module 05 ready for scenario predictor stack assembly across 2,050 grid cells.');
