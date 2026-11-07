/**
 * ============================================================================
 * SEACAST PRIGI — WPPNRI 573 (PHASE 4.2 GEE SPATIAL PIPELINE)
 * Module 04: Surface Winds, Wind Stress (tau), & Meridional Ekman Transport (M_ey)
 * File: gee/js/04_wind_and_ekman_dynamics.js
 * ============================================================================
 * Dataset:
 *   - Primary: NOAA/CFSV2/FOR6H_HARMONIZED (10m u/v wind components, 1979-present)
 * Equations:
 *   tau_x = rho_a * C_d * u10 * sqrt(u10^2 + v10^2)
 *   f = 2 * Omega * sin(lat_rad)  [negative in Southern Hemisphere]
 *   M_ey = -tau_x / (rho_w * f)   [m^2/s; M_ey < 0 indicates offshore Ekman transport]
 */

var subAoiBox = ee.Geometry.Rectangle([111.00, -10.45, 113.00, -8.00], 'EPSG:4326', false);

function getMonthlyWindAndEkman(year, month) {
  year = ee.Number(year);
  month = ee.Number(month);
  var startDate = ee.Date.fromYMD(year, month, 1);
  var endDate = startDate.advance(1, 'month');

  var cfsv2 = ee.ImageCollection('NOAA/CFSV2/FOR6H_HARMONIZED')
    .filterDate(startDate, endDate)
    .filterBounds(subAoiBox)
    .select([
      'u-component_of_wind_height_above_ground',
      'v-component_of_wind_height_above_ground'
    ], ['u10', 'v10']);

  var u10 = cfsv2.select('u10').mean();
  var v10 = cfsv2.select('v10').mean();
  var windSpeed = u10.hypot(v10).rename('wind_speed_ms');

  // Physical constants
  var rhoAir = 1.22;      // kg/m^3
  var dragCoeff = 0.0013; // dimensionless C_d
  var rhoWater = 1025.0;  // kg/m^3
  var omega = 7.2921e-5;  // rad/s

  var tauX = u10.multiply(windSpeed).multiply(rhoAir * dragCoeff).rename('tau_x_pa');
  var tauY = v10.multiply(windSpeed).multiply(rhoAir * dragCoeff).rename('tau_y_pa');

  // Coriolis parameter f = 2 * Omega * sin(latitude_rad) using geographic pixelLonLat()
  var latRad = ee.Image.pixelLonLat().select('latitude').multiply(Math.PI / 180.0);
  var coriolisF = latRad.sin().multiply(2.0 * omega);

  // Meridional Ekman Transport M_ey = -tau_x / (rho_w * f) [m^2/s]
  var ekmanMey = tauX.multiply(-1.0).divide(coriolisF.multiply(rhoWater)).rename('ekman_mey_m2s');

  // Empirical wind-sea Significant Wave Height proxy Hs (m) for South Java open swell + local wind
  var hsProxy = windSpeed.multiply(0.18).add(0.75).rename('wave_hs_proxy_m');

  return ee.Image.cat([u10, v10, windSpeed, tauX, tauY, ekmanMey, hsProxy])
    .clip(subAoiBox)
    .set({
      'year': year,
      'month': month,
      'year_month': startDate.format('YYYY-MM')
    });
}

var aug2019Ekman = getMonthlyWindAndEkman(2019, 8);
Map.centerObject(subAoiBox, 8);
Map.addLayer(aug2019Ekman.select('ekman_mey_m2s'), {min: -2.0, max: 0.5, palette: ['0000ff', '00ffff', 'ffffff', 'ff9900']}, 'Meridional Ekman Transport M_ey Aug 2019 (m^2/s)');

exports.getMonthlyWindAndEkman = getMonthlyWindAndEkman;
