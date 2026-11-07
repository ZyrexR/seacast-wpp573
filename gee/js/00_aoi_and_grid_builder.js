/**
 * ============================================================================
 * SEACAST PRIGI — WPPNRI 573 (PHASE 4.2 GEE SPATIAL PIPELINE)
 * Module 00: Canonical AOI, Port Location & 2,050 Grid Builder (0.05 deg)
 * File: gee/js/00_aoi_and_grid_builder.js
 * ============================================================================
 * Scientific Purpose:
 *   Constructs the exact Sub-AOI Prigi bounding box [111.00, 113.00] E x
 *   [-10.45, -8.00] S, the 50 x 41 = 2,050 canonical 0.05-degree grid cells,
 *   equal-area geodesic surface area (km^2), and Euclidean distance (NM & km)
 *   from PPN Prigi Fishing Port (111.7314 E, -8.2818 S).
 */

var CONFIG = {
  WEST: 111.00,
  EAST: 113.00,
  SOUTH: -10.45,
  NORTH: -8.00,
  STEP_DEG: 0.05,
  NX: 40, // centroid steps or 41 inclusive vertices: 111.00 to 113.00 (41 cols) x -10.45 to -8.00 (50 rows) = 2050 cells
  HARBOR_LON: 111.7314,
  HARBOR_LAT: -8.2818,
  CRS_GEO: 'EPSG:4326',
  CRS_EQUAL_AREA: 'EPSG:6933'
};

var subAoiBox = ee.Geometry.Rectangle(
  [CONFIG.WEST, CONFIG.SOUTH, CONFIG.EAST, CONFIG.NORTH],
  CONFIG.CRS_GEO,
  false
);

var prigiHarbor = ee.Geometry.Point([CONFIG.HARBOR_LON, CONFIG.HARBOR_LAT]);

/**
 * Build the 50 rows (lat: -10.45 to -8.00 step 0.05) x 41 cols (lon: 111.00 to 113.00 step 0.05)
 * = 2,050 canonical grid points & cells matching Phase 4.1.1 baseline coordinates.
 */
function buildCanonicalGrid2050() {
  var rowIndices = ee.List.sequence(0, 49); // 50 latitude rows
  var colIndices = ee.List.sequence(0, 40); // 41 longitude columns
  var halfStep = CONFIG.STEP_DEG / 2.0;

  var features = rowIndices.map(function(r) {
    r = ee.Number(r);
    var lat = ee.Number(CONFIG.SOUTH).add(r.multiply(CONFIG.STEP_DEG));
    var y0 = lat.subtract(halfStep).max(CONFIG.SOUTH);
    var y1 = lat.add(halfStep).min(CONFIG.NORTH);

    return colIndices.map(function(c) {
      c = ee.Number(c);
      var lon = ee.Number(CONFIG.WEST).add(c.multiply(CONFIG.STEP_DEG));
      var x0 = lon.subtract(halfStep).max(CONFIG.WEST);
      var x1 = lon.add(halfStep).min(CONFIG.EAST);

      var cellGeom = ee.Geometry.Rectangle([x0, y0, x1, y1], CONFIG.CRS_GEO, false);
      var centroid = ee.Geometry.Point([lon, lat]);
      var distMeters = centroid.distance(prigiHarbor, 1);
      var areaKm2 = cellGeom.area(1).divide(1e6);
      var cellIdx = r.multiply(41).add(c).add(1).int();

      return ee.Feature(cellGeom, {
        'cell_index': cellIdx,
        'row_idx': r.int(),
        'col_idx': c.int(),
        'lon': lon,
        'lat': lat,
        'area_km2': areaKm2,
        'dist_harbor_km': distMeters.divide(1000.0),
        'dist_harbor_nm': distMeters.divide(1852.0)
      });
    });
  }).flatten();

  return ee.FeatureCollection(features);
}

var grid2050 = buildCanonicalGrid2050();
print('Canonical Sub-AOI Prigi Grid Count (Target = 2050):', grid2050.size());
print('Total Clipped Grid Equal-Area (km^2):', grid2050.aggregate_sum('area_km2'));

Map.centerObject(subAoiBox, 8);
Map.addLayer(subAoiBox, {color: '00FFFF'}, 'Sub-AOI Prigi Bounding Box');
Map.addLayer(grid2050.style({color: '3388ff', width: 0.5, fillColor: '00000000'}), {}, '2,050 Canonical Grid (0.05 deg)');
Map.addLayer(prigiHarbor, {color: 'FF0000'}, 'PPN Prigi Fishing Port');

exports.CONFIG = CONFIG;
exports.subAoiBox = subAoiBox;
exports.prigiHarbor = prigiHarbor;
exports.buildCanonicalGrid2050 = buildCanonicalGrid2050;
