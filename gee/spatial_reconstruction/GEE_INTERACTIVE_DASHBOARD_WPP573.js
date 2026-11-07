/**
 * ============================================================================
 * SEACAST PRIGI — OFFICIAL WPPNRI 573 RECONSTRUCTED INTERACTIVE VIEWER
 * Standalone All-In-One Interactive Visualizer for Full WPPNRI 573
 * File: gee/spatial_reconstruction/GEE_INTERACTIVE_DASHBOARD_WPP573.js
 * ============================================================================
 */
var aoiWpp573Box = ee.Geometry.Rectangle([102.5778, -13.9382, 126.4996, -6.8018], 'EPSG:4326', false);
var subAoiPrigiBox = ee.Geometry.Rectangle([111.00, -10.45, 113.00, -8.00], 'EPSG:4326', false);
var prigiHarbor = ee.Geometry.Point([111.7314, -8.2818]);

// 1. Bathymetry (ETOPO1)
var etopo = ee.Image('NOAA/NGDC/ETOPO1').select(['bedrock'], ['depth_m']).clip(aoiWpp573Box);

// 2. SST NOAA OISST v2.1 (Full WPPNRI 573)
var sstOisst = ee.ImageCollection('NOAA/CDR/OISST/V2_1')
  .filterDate('2022-04-01', '2022-05-01')
  .filterBounds(aoiWpp573Box)
  .select('sst').mean().multiply(0.01).clip(aoiWpp573Box);

// 3. Chlorophyll-a MODIS-Aqua (Full WPPNRI 573)
var chla = ee.ImageCollection('NASA/OCEANDATA/MODIS-Aqua/L3SMI')
  .filterDate('2022-04-01', '2022-05-01')
  .filterBounds(aoiWpp573Box)
  .select('chlor_a').median().clip(aoiWpp573Box);

// 4. Winds NOAA CFSv2 Harmonized
var cfsv2 = ee.ImageCollection('NOAA/CFSV2/FOR6H_HARMONIZED')
  .filterDate('2022-04-01', '2022-05-01')
  .filterBounds(aoiWpp573Box);
var u10 = cfsv2.select('u-component_of_wind_height_above_ground').mean();
var v10 = cfsv2.select('v-component_of_wind_height_above_ground').mean();
var windSpeed = u10.hypot(v10).rename('wind_speed_ms').clip(aoiWpp573Box);

// Visualizations
Map.centerObject(aoiWpp573Box, 6);
Map.setOptions('HYBRID');

Map.addLayer(etopo, {min: -6000, max: 0, palette: ['03052d', '18538a', '51bdd4', 'c8f2dc']}, '1. Batimetri ETOPO1 (WPPNRI 573)', false);
Map.addLayer(sstOisst, {min: 26, max: 31, palette: ['blue', 'cyan', 'yellow', 'red']}, '2. SST OISST v2.1 April 2022 (°C)', true);
Map.addLayer(chla, {min: 0.05, max: 1.5, palette: ['darkblue', 'blue', 'green', 'yellow', 'orange', 'red']}, '3. Klorofil-a MODIS (mg/m³)', true);
Map.addLayer(windSpeed, {min: 0, max: 12, palette: ['white', 'lightblue', 'yellow', 'red']}, '4. Kecepatan Angin 10m (m/s)', false);
Map.addLayer(aoiWpp573Box, {color: 'FFFF00'}, '5. Batas Bounding WPPNRI 573', true);
Map.addLayer(subAoiPrigiBox, {color: '00FFFF'}, '6. Fokus Sub-AOI PPN Prigi [111-113 E]', true);
Map.addLayer(prigiHarbor, {color: 'FF0000'}, '7. PPN Prigi', true);

print('SEACAST PRIGI WPPNRI 573 Full Domain Loaded.');
