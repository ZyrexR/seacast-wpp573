/**
 * ============================================================================
 * SEACAST PRIGI — WPPNRI 573 (PHASE 4.2 GEE SPATIAL PIPELINE)
 * Module 03: Monthly Chlorophyll-a, Log10 Transform & Cloud-Gap QC
 * File: gee/js/03_chla_and_productivity.js
 * ============================================================================
 * Dataset:
 *   - NASA/OCEANDATA/MODIS-Aqua/L3SMI (band: 'chlor_a', mg/m^3, 4.63 km)
 */

var subAoiBox = ee.Geometry.Rectangle([111.00, -10.45, 113.00, -8.00], 'EPSG:4326', false);

function getMonthlyChla(year, month) {
  year = ee.Number(year);
  month = ee.Number(month);
  var startDate = ee.Date.fromYMD(year, month, 1);
  var endDate = startDate.advance(1, 'month');

  // Filter physically plausible ocean color range (0.01 to 20.0 mg/m^3)
  var filterValidChla = function(img) {
    var chla = img.select('chlor_a');
    var mask = chla.gte(0.01).and(chla.lte(20.0));
    return chla.updateMask(mask);
  };

  var modisMonth = ee.ImageCollection('NASA/OCEANDATA/MODIS-Aqua/L3SMI')
    .filterDate(startDate, endDate)
    .filterBounds(subAoiBox)
    .map(filterValidChla);

  // 3-month rolling window fallback for severe West Monsoon cloud gaps
  var modisWindow = ee.ImageCollection('NASA/OCEANDATA/MODIS-Aqua/L3SMI')
    .filterDate(startDate.advance(-1, 'month'), endDate.advance(1, 'month'))
    .filterBounds(subAoiBox)
    .map(filterValidChla);

  var chlaPrimary = modisMonth.median();
  var chlaFallback = modisWindow.median();
  var chlaFilled = chlaPrimary.unmask(chlaFallback).rename('chla_mg_m3');
  var log10Chla = chlaFilled.log10().rename('log10_chla');
  var gapFilledFlag = chlaPrimary.mask().not().and(chlaFallback.mask()).rename('qc_chla_gap_filled');

  return ee.Image.cat([chlaFilled, log10Chla, gapFilledFlag])
    .clip(subAoiBox)
    .set({
      'year': year,
      'month': month,
      'year_month': startDate.format('YYYY-MM')
    });
}

var april2022Chla = getMonthlyChla(2022, 4);
Map.centerObject(subAoiBox, 8);
Map.addLayer(april2022Chla.select('chla_mg_m3'), {min: 0.08, max: 1.5, palette: ['00004c', '004c99', '00cc66', 'ffff00', 'ff3300']}, 'Chlorophyll-a April 2022 (mg/m^3)');

exports.getMonthlyChla = getMonthlyChla;
