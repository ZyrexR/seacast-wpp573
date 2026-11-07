/**
 * ============================================================================
 * SEACAST PRIGI — WPPNRI 573 (PHASE 4.2 GEE SPATIAL PIPELINE)
 * Module 02: Monthly SST, Cloud-Gap Sensor Fusion & Sobel Thermal Fronts
 * File: gee/js/02_sst_and_thermal_fronts.js
 * ============================================================================
 * Datasets:
 *   - NASA/OCEANDATA/MODIS-Aqua/L3SMI (band: 'sst', 4.63 km daily)
 *   - NOAA/CDR/OISST/V2_1 (bands: 'sst', 'anom', 0.25 deg daily cloud-free)
 * Time Range: 2015-01-01 to 2025-12-31 (132 Months)
 */

var subAoiBox = ee.Geometry.Rectangle([111.00, -10.45, 113.00, -8.00], 'EPSG:4326', false);
var coastalZone = ee.Geometry.Rectangle([111.00, -8.55, 113.00, -8.00], 'EPSG:4326', false);
var offshoreZone = ee.Geometry.Rectangle([111.00, -10.45, 113.00, -9.00], 'EPSG:4326', false);

function getMonthlySSTAndFronts(year, month) {
  year = ee.Number(year);
  month = ee.Number(month);
  var startDate = ee.Date.fromYMD(year, month, 1);
  var endDate = startDate.advance(1, 'month');

  // 1. OISST v2.1 (scale 0.01 deg C) - gap-free baseline
  var oisstCol = ee.ImageCollection('NOAA/CDR/OISST/V2_1')
    .filterDate(startDate, endDate)
    .filterBounds(subAoiBox);

  var oisstMean = oisstCol.select('sst').mean().multiply(0.01).rename('sst_oisst_c');
  var oisstAnom = oisstCol.select('anom').mean().multiply(0.01).rename('sst_anom_c');

  // 2. MODIS-Aqua L3SMI SST (4km) - high spatial detail where cloud-free
  var modisCol = ee.ImageCollection('NASA/OCEANDATA/MODIS-Aqua/L3SMI')
    .filterDate(startDate, endDate)
    .filterBounds(subAoiBox)
    .select('sst');

  var modisMean = modisCol.mean().rename('sst_modis_c');
  var validModisMask = modisMean.mask();

  // Sensor Fusion: Use MODIS 4km where available, fill cloud gaps with bilinear-resampled OISST
  var oisstInterp = oisstMean.resample('bilinear');
  var sstFused = modisMean.unmask(oisstInterp).rename('sst_c');

  // 3. Thermal Front Intensity via Sobel Gradient (deg C / km)
  var sstSmooth = sstFused.convolve(ee.Kernel.gaussian(3, 1, 'pixels'));
  var gx = sstSmooth.convolve(ee.Kernel.sobel());
  var gy = sstSmooth.convolve(ee.Kernel.sobel().rotate(1));
  // Nominal 4.63 km pixel spacing -> divide gradient magnitude by ~4.63 km
  var frontSobel = gx.hypot(gy).divide(4.63).rename('front_sobel_c_km');

  // 4. Coastal-Offshore Upwelling Thermal Contrast (Delta T = SST_offshore - SST_coastal)
  var sstCoastal = sstFused.reduceRegion({
    reducer: ee.Reducer.mean(),
    geometry: coastalZone,
    scale: 5000,
    maxPixels: 1e8
  }).get('sst_c');

  var sstOffshore = sstFused.reduceRegion({
    reducer: ee.Reducer.mean(),
    geometry: offshoreZone,
    scale: 5000,
    maxPixels: 1e8
  }).get('sst_c');

  var deltaT = ee.Number(sstOffshore).subtract(ee.Number(sstCoastal));

  return ee.Image.cat([sstFused, oisstAnom, frontSobel, validModisMask.rename('modis_valid_fraction')])
    .clip(subAoiBox)
    .set({
      'year': year,
      'month': month,
      'year_month': startDate.format('YYYY-MM'),
      'sst_coastal_c': sstCoastal,
      'sst_offshore_c': sstOffshore,
      'delta_t_upwelling_c': deltaT
    });
}

var april2022SST = getMonthlySSTAndFronts(2022, 4);
Map.centerObject(subAoiBox, 8);
Map.addLayer(april2022SST.select('sst_c'), {min: 25, max: 30, palette: ['040274', '0502ce', '307ef3', '3ff38f', 'fff705', 'ff0000']}, 'Fused SST April 2022 (deg C)');
Map.addLayer(april2022SST.select('front_sobel_c_km'), {min: 0, max: 0.15, palette: ['000000', '00ffff', 'ffff00', 'ff0000']}, 'Thermal Front Intensity (deg C/km)');

exports.getMonthlySSTAndFronts = getMonthlySSTAndFronts;
