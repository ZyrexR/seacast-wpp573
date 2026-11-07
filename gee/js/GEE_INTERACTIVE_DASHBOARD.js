/**
 * ============================================================================
 * SEACAST PRIGI — WPPNRI 573 (GEE CODE EDITOR INTERACTIVE VIEWER)
 * Standalone All-In-One Interactive Visualizer for Google Earth Engine Web
 * File: gee/js/GEE_INTERACTIVE_DASHBOARD.js
 * ============================================================================
 * Petunjuk Penggunaan:
 *   1. Buka https://code.earthengine.google.com di browser Anda.
 *   2. Pastikan Cloud Project di pojok kanan atas mengarah ke: seca-513505
 *   3. Salin (copy) SELURUH isi script ini dan tempel (paste) di editor GEE.
 *   4. Klik tombol "Run" di bagian atas editor.
 *   5. Gunakan tab "Layers" di pojok kanan atas peta untuk menghidupkan/mematikan layer.
 *   6. Klik tab "Inspector" di panel kanan lalu klik sembarang titik di laut untuk
 *      melihat nilai kedalaman, SST, front, klorofil, dan transpor Ekman secara langsung!
 */

// ----------------------------------------------------------------------------
// 1. DEFINISI GEOMETRI AOI & PELABUHAN PPN PRIGI
// ----------------------------------------------------------------------------
var subAoiBox = ee.Geometry.Rectangle([111.00, -10.45, 113.00, -8.00], 'EPSG:4326', false);
var prigiHarbor = ee.Geometry.Point([111.7314, -8.2818]);

// ----------------------------------------------------------------------------
// 2. LAYER 1: BATIMETRI ETOPO1 & ZONA TUBIR (-200m s/d -1000m)
// ----------------------------------------------------------------------------
var etopo = ee.Image('NOAA/NGDC/ETOPO1').select(['bedrock'], ['depth_m']).clip(subAoiBox);
var slope = ee.Terrain.slope(etopo).rename('slope_deg');

// Garis tubir -200m & zona lereng kontinen (kandidat habitat hiu pelagis CITES)
var isobath200Mask = etopo.lte(-180).and(etopo.gte(-220));
var sharkShelfZone = etopo.lte(-200).and(etopo.gte(-1000)).rename('shark_bathy_zone');

// ----------------------------------------------------------------------------
// 3. LAYER 2: SST (MODIS + OISST FUSION) & SOBL THERMAL FRONTS (APRIL 2022)
// ----------------------------------------------------------------------------
var startDate = ee.Date('2022-04-01');
var endDate = ee.Date('2022-05-01');

// OISST v2.1 (0.25 deg, gap-free)
var oisst = ee.ImageCollection('NOAA/CDR/OISST/V2_1')
  .filterDate(startDate, endDate)
  .filterBounds(subAoiBox);
var sstOisst = oisst.select('sst').mean().multiply(0.01).rename('sst_c');

// MODIS Aqua 4km
var modis = ee.ImageCollection('NASA/OCEANDATA/MODIS-Aqua/L3SMI')
  .filterDate(startDate, endDate)
  .filterBounds(subAoiBox);
var sstModis = modis.select('sst').mean();
var sstFused = sstModis.unmask(sstOisst.resample('bilinear')).clip(subAoiBox);

// Sobel Gradient (°C/km)
var sstSmooth = sstFused.convolve(ee.Kernel.gaussian(3, 1, 'pixels'));
var gx = sstSmooth.convolve(ee.Kernel.sobel());
var gy = sstSmooth.convolve(ee.Kernel.sobel().rotate(1));
var frontSobel = gx.hypot(gy).divide(4.63).rename('front_sobel_c_km').clip(subAoiBox);

// ----------------------------------------------------------------------------
// 4. LAYER 3: KLOROFIL-A MODIS-AQUA (APRIL 2022)
// ----------------------------------------------------------------------------
var chlaRaw = modis.select('chlor_a').median();
var chlaFocal = chlaRaw.focal_mean(3, 'circle', 'pixels');
var chlaFused = chlaRaw.unmask(chlaFocal).rename('chla_mg_m3').clip(subAoiBox);

// ----------------------------------------------------------------------------
// 5. LAYER 4: ANGIN 10M & TRANSPOR EKMAN MERIDIONAL (CFSv2 HARMONIZED)
// ----------------------------------------------------------------------------
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

var tauX = u10.multiply(windSpeed).multiply(1.22 * 0.0013);
var latRad = ee.Image.pixelLonLat().select('latitude').multiply(Math.PI / 180.0);
var coriolisF = latRad.sin().multiply(2.0 * 7.2921e-5);
var ekmanMey = tauX.multiply(-1.0).divide(coriolisF.multiply(1025.0)).rename('ekman_mey_m2s').clip(subAoiBox);

// ----------------------------------------------------------------------------
// 6. LAYER 5: GRID KANONIS 2.050 SEL (0.05 DERAJAT)
// ----------------------------------------------------------------------------
var rowSeq = ee.List.sequence(0, 49);
var colSeq = ee.List.sequence(0, 40);
var gridCells = rowSeq.map(function(r) {
  r = ee.Number(r);
  var lat = ee.Number(-10.45).add(r.multiply(0.05));
  return colSeq.map(function(c) {
    c = ee.Number(c);
    var lon = ee.Number(111.00).add(c.multiply(0.05));
    var cell = ee.Geometry.Rectangle([
      lon.subtract(0.025), lat.subtract(0.025),
      lon.add(0.025), lat.add(0.025)
    ], 'EPSG:4326', false);
    return ee.Feature(cell);
  });
}).flatten();
var grid2050Fc = ee.FeatureCollection(gridCells);

// ----------------------------------------------------------------------------
// 7. VISUALISASI PADA PETA GEE
// ----------------------------------------------------------------------------
Map.setCenter(112.00, -9.22, 8);
Map.setOptions('HYBRID');

// Palet warna ilmiah
var bathyVis = {min: -5000, max: 0, palette: ['03052d', '0b2659', '18538a', '2c88b8', '51bdd4', '8fe0dd', 'c8f2dc']};
var sstVis = {min: 27.5, max: 30.5, palette: ['040274', '0502ce', '307ef3', '3ff38f', 'fff705', 'ff0000']};
var frontVis = {min: 0.01, max: 0.12, palette: ['black', 'blue', 'cyan', 'yellow', 'red']};
var chlaVis = {min: 0.10, max: 0.80, palette: ['00004c', '004c99', '00cc66', 'ffff00', 'ff3300']};
var ekmanVis = {min: -3.0, max: 1.0, palette: ['darkblue', 'blue', 'cyan', 'white', 'orange', 'red']};

// Menambahkan Layers ke Peta
Map.addLayer(etopo, bathyVis, '1. Batimetri ETOPO1 (depth_m)', false);
Map.addLayer(sharkShelfZone.selfMask(), {palette: ['ff9900']}, '2. Zona Tubir Hiu Pelagis (-200m s/d -1000m)', false);
Map.addLayer(sstFused, sstVis, '3. SST Satelit April 2022 (°C)', true);
Map.addLayer(frontSobel, frontVis, '4. Thermal Front Sobel (°C/km)', true);
Map.addLayer(chlaFused, chlaVis, '5. Klorofil-a MODIS (mg/m³)', false);
Map.addLayer(ekmanMey, ekmanVis, '6. Transpor Ekman Meridional M_ey (m²/s)', false);
Map.addLayer(grid2050Fc.style({color: '00e5ff', width: 0.4, fillColor: '00000000'}), {}, '7. Grid Kanonis 2.050 Sel (0.05°)', false);
Map.addLayer(subAoiBox, {color: 'ffff00'}, '8. Batas Sub-AOI Prigi [111-113°BT, -10.45 s/d -8°LS]', true);
Map.addLayer(prigiHarbor, {color: 'ff0033'}, '9. Lokasi PPN Prigi (111.7314°BT, -8.2818°LS)', true);

// ----------------------------------------------------------------------------
// 8. PANEL INFORMASI DI LAYAR GEE
// ----------------------------------------------------------------------------
var headerPanel = ui.Panel({
  widgets: [
    ui.Label({
      value: 'SEACAST PRIGI — WPPNRI 573 (GEE Satelit Viewer)',
      style: {fontWeight: 'bold', fontSize: '15px', color: '#1a73e8'}
    }),
    ui.Label({
      value: '• Buka tab "Inspector" di kanan, lalu klik titik laut untuk melihat nilai piksel.',
      style: {fontSize: '11px', color: '#555'}
    }),
    ui.Label({
      value: '• Gunakan tombol "Layers" di kanan atas peta untuk memilih data satelit.',
      style: {fontSize: '11px', color: '#555'}
    })
  ],
  style: {position: 'top-left', padding: '8px', maxWidth: '340px'}
});
Map.add(headerPanel);

print('SEACAST PRIGI Visualizer berhasil dimuat!');
print('Sub-AOI: [111.00, 113.00] E x [-10.45, -8.00] S');
print('Jumlah Grid: 2,050 sel (50 rows x 41 cols)');

