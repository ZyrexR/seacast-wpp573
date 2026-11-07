/**
 * SEACAST PRIGI — WPPNRI 573 SPATIAL RECONSTRUCTION
 * Module 08: Thermal Front Intensity (Spherical Metric Sobel Gradient)
 * File: gee/spatial_reconstruction/08_THERMAL_FRONT.js
 * Scientific Rigor:
 *   Replaces arbitrary /4.63 divisor with physical spherical metric:
 *   dx = dLon * 111.320 * cos(lat) [km]
 *   dy = dLat * 110.574 [km]
 *   Gradient magnitude: ||grad(SST)|| = sqrt((dT/dx)^2 + (dT/dy)^2) [°C/km]
 */
function computeThermalFrontSobel(sstImage, aoi) {
  var sstSmooth = sstImage.convolve(ee.Kernel.gaussian(3, 1, 'pixels'));
  
  // Sobel convolution
  var gx = sstSmooth.convolve(ee.Kernel.sobel());
  var gy = sstSmooth.convolve(ee.Kernel.sobel().rotate(1));
  
  // Metric pixel scale in km accounting for latitude variation
  var latRad = ee.Image.pixelLonLat().select('latitude').multiply(Math.PI / 180.0);
  var cosLat = latRad.cos().max(0.1);
  
  // Nominal pixel step (at ~0.05 deg: dLat ~ 5.53 km, dLon ~ 5.56*cosLat km)
  var dLatKm = ee.Image.constant(110.574 * 0.05);
  var dLonKm = cosLat.multiply(111.320 * 0.05);
  
  var gradX = gx.divide(dLonKm);
  var gradY = gy.divide(dLatKm);
  
  var frontIntensity = gradX.hypot(gradY).rename('thermal_front_c_km').clip(aoi);
  return frontIntensity;
}

exports.computeThermalFrontSobel = computeThermalFrontSobel;
