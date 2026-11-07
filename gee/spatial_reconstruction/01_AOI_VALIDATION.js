/**
 * SEACAST PRIGI — WPPNRI 573 SPATIAL RECONSTRUCTION
 * Module 01: Official AOI Acquisition & Validation
 * File: gee/spatial_reconstruction/01_AOI_VALIDATION.js
 */
var cfg = require('users/seacast_prigi/spatial_reconstruction:00_CONFIG').CONFIG;

// Official Bounding Box (Bounding Envelope)
var aoiEnvelope = ee.Geometry.Rectangle([
  cfg.AOI_BOUNDS.WEST, cfg.AOI_BOUNDS.SOUTH,
  cfg.AOI_BOUNDS.EAST, cfg.AOI_BOUNDS.NORTH
], 'EPSG:4326', false);

// PPN Prigi Fishing Port location
var prigiPort = ee.Geometry.Point([cfg.SUB_AOI_PRIGI.HARBOR_LON, cfg.SUB_AOI_PRIGI.HARBOR_LAT]);

Map.centerObject(aoiEnvelope, 6);
Map.setOptions('HYBRID');
Map.addLayer(aoiEnvelope, {color: 'FFFF00'}, 'WPPNRI 573 Bounding Envelope [102.58-126.50 E, -13.94 to -6.80 S]');
Map.addLayer(prigiPort, {color: 'FF0000'}, 'PPN Prigi Fishing Port');

print('WPPNRI 573 Spatial Domain Initialized.');
print('Geodesic Area Target: 933,749.49 km² (BIG Layer 21)');
