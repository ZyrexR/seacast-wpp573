/**
 * SEACAST PRIGI — WPPNRI 573 SPATIAL RECONSTRUCTION
 * Module 11: Multi-Scale Grid Generation (0.25°, 0.10°, 0.05°)
 * File: gee/spatial_reconstruction/11_GRID_GENERATION.js
 */
var cfg = require('users/seacast_prigi/spatial_reconstruction:00_CONFIG').CONFIG;

function generateRegularGrid(west, south, east, north, stepDeg, gridName) {
  var dLon = east - west;
  var dLat = north - south;
  var nCols = Math.round(dLon / stepDeg);
  var nRows = Math.round(dLat / stepDeg);
  
  print(gridName + ' Grid Dimensions:', nRows + ' rows x ' + nCols + ' cols = ' + (nRows * nCols) + ' cells (BBox)');
}

generateRegularGrid(cfg.AOI_BOUNDS.WEST, cfg.AOI_BOUNDS.SOUTH, cfg.AOI_BOUNDS.EAST, cfg.AOI_BOUNDS.NORTH, 0.25, 'Regional WPP 573 (0.25 deg)');
generateRegularGrid(cfg.AOI_BOUNDS.WEST, cfg.AOI_BOUNDS.SOUTH, cfg.AOI_BOUNDS.EAST, cfg.AOI_BOUNDS.NORTH, 0.10, 'Intermediate WPP 573 (0.10 deg)');
generateRegularGrid(cfg.SUB_AOI_PRIGI.WEST, cfg.SUB_AOI_PRIGI.SOUTH, cfg.SUB_AOI_PRIGI.EAST, cfg.SUB_AOI_PRIGI.NORTH, 0.05, 'Sub-AOI Prigi Canonical (0.05 deg)');
