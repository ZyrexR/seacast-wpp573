/**
 * SEACAST PRIGI — WPPNRI 573 SPATIAL RECONSTRUCTION
 * Module 09: Ekman Dynamics & Low-Latitude Coriolis Handling
 * File: gee/spatial_reconstruction/09_EKMAN_TRANSPORT.js
 * Equations:
 *   tau_x = rho_a * C_d * u10 * |U10|
 *   f = 2 * Omega * sin(lat)
 *   M_ey = -tau_x / (rho_w * f) [m²/s]
 * Numerical Stability:
 *   Coriolis damping cutoff: For |lat| < 2.0°, f is capped to sign(lat)*f(2.0°)
 *   to avoid non-physical infinities near equator while preserving Southern Hemisphere negative sign.
 */
function computeEkmanTransport(u10, v10, aoi) {
  var windSpeed = u10.hypot(v10);
  var rhoAir = 1.22;
  var dragCoeff = 0.0013;
  var rhoWater = 1025.0;
  var omega = 7.2921e-5;
  
  var tauX = u10.multiply(windSpeed).multiply(rhoAir * dragCoeff).rename('tau_x_pa');
  var tauY = v10.multiply(windSpeed).multiply(rhoAir * dragCoeff).rename('tau_y_pa');
  
  var latDeg = ee.Image.pixelLonLat().select('latitude');
  var latRad = latDeg.multiply(Math.PI / 180.0);
  
  // Damped Coriolis parameter: cutoff at |lat| = 2.0 deg
  var latDampedRad = latDeg.abs().max(2.0).multiply(latDeg.sign()).multiply(Math.PI / 180.0);
  var coriolisF = latDampedRad.sin().multiply(2.0 * omega);
  
  var ekmanMex = tauY.divide(coriolisF.multiply(rhoWater)).rename('ekman_mex_m2s');
  var ekmanMey = tauX.multiply(-1.0).divide(coriolisF.multiply(rhoWater)).rename('ekman_mey_m2s');
  
  return ee.Image.cat([tauX, tauY, ekmanMex, ekmanMey]).clip(aoi);
}

exports.computeEkmanTransport = computeEkmanTransport;
