/**
 * ============================================================================
 * SEACAST PRIGI — WPPNRI 573 (PHASE 4.2 GEE SPATIAL PIPELINE)
 * Module 01: Bathymetry, Seafloor Slope, & Shelf-Break (-200m) Distance
 * File: gee/js/01_bathymetry_and_terrain.js
 * ============================================================================
 * Datasets:
 *   - Primary: NOAA/NGDC/ETOPO1 (band: 'bedrock', 1 arc-minute ~ 1.85 km)
 *   - Optional High-Res: projects/sat-io/open-datasets/gebco/gebco_2023 (450m)
 */

var subAoiBox = ee.Geometry.Rectangle([111.00, -10.45, 113.00, -8.00], 'EPSG:4326', false);

function getBathymetryTerrainStack() {
  var etopo = ee.Image('NOAA/NGDC/ETOPO1').select(['bedrock'], ['depth_m']);
  var oceanMask = etopo.lt(0);

  // Compute seafloor slope in degrees using ETOPO1 bedrock
  var slopeDeg = ee.Terrain.slope(etopo).rename('slope_deg');

  // Identify the -200m shelf-break contour zone (-220m <= depth <= -180m)
  var isobath200Mask = etopo.lte(-180).and(etopo.gte(-220));
  var distTo200mPixels = isobath200Mask.fastDistanceTransform(256).sqrt();
  var pixelScaleKm = etopo.projection().nominalScale().divide(1000.0);
  var distTo200mKm = distTo200mPixels.multiply(pixelScaleKm).rename('dist_to_200m_isobath_km');

  // CITES App II Pelagic Shark Bathymetric Overlap Candidate (-1000m <= depth <= -200m)
  var sharkShelfOverlap = etopo.lte(-200).and(etopo.gte(-1000)).rename('shark_bathy_overlap_flag');

  return ee.Image.cat([
    etopo,
    slopeDeg,
    distTo200mKm,
    sharkShelfOverlap,
    oceanMask.rename('is_ocean')
  ]).clip(subAoiBox);
}

var bathyStack = getBathymetryTerrainStack();
Map.centerObject(subAoiBox, 8);
Map.addLayer(bathyStack.select('depth_m'), {min: -4000, max: 0, palette: ['081d58', '225ea8', '41b6c4', 'c7e9b4']}, 'ETOPO1 Bathymetry (depth_m)');
Map.addLayer(bathyStack.select('slope_deg'), {min: 0, max: 10, palette: ['black', 'yellow', 'red']}, 'Seafloor Slope (deg)');
Map.addLayer(bathyStack.select('shark_bathy_overlap_flag').selfMask(), {palette: ['ff9900']}, 'Pelagic Shark Shelf-Slope Zone (-200m to -1000m)');

exports.getBathymetryTerrainStack = getBathymetryTerrainStack;
