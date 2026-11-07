/**
 * SEACAST PRIGI — WPPNRI 573 SPATIAL RECONSTRUCTION
 * Module 10: Bathymetric Terrain Analysis & Depth Stratification
 * File: gee/spatial_reconstruction/10_BATHYMETRY.js
 */
function getBathymetryTerrain(aoi) {
  var etopo = ee.Image('NOAA/NGDC/ETOPO1').select(['bedrock'], ['depth_m']).clip(aoi);
  var slope = ee.Terrain.slope(etopo).rename('slope_deg');
  var isOcean = etopo.lt(0).rename('is_ocean');
  
  // Scientific Depth Stratification:
  // 1: Epipelagic / Neritic (0 to -200m)
  // 2: Upper Mesopelagic / Shelf-Break (-200m to -1000m)
  // 3: Bathypelagic / Oceanic Trench (< -1000m)
  var depthZone = ee.Image(0)
    .where(etopo.gte(-200).and(etopo.lt(0)), 1)
    .where(etopo.gte(-1000).and(etopo.lt(-200)), 2)
    .where(etopo.lt(-1000), 3)
    .rename('depth_zone_class');
    
  return ee.Image.cat([etopo, slope, isOcean, depthZone]);
}

exports.getBathymetryTerrain = getBathymetryTerrain;
