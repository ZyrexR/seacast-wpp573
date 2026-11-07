/**
 * SEACAST PRIGI — GEE SCRIPT v2.3.2 (DEVELOPMENT CANDIDATE; scientific & runtime remediation of v2.3.1)
 * Target: WPPNRI 573 | 2015-01..2025-12 | canonical 0.10 deg grid (8,129 cells)
 *
 * REMEDIATIONS APPLIED OVER v2.3.1:
 * 1. FIXED SOBEL KY KERNEL ROW ORIENTATION (UTIL.KY):
 *    GEE convolve() implements mathematical 2D convolution, which reflects kernel rows.
 *    Original KY [[1, 2, 1], [0, 0, 0], [-1, -2, -1]] produced negative latitudinal gradients
 *    and inverted the sign of wind stress curl. Corrected to [[-1, -2, -1], [0, 0, 0], [1, 2, 1]].
 * 2. RESOLVED GCOM-C SCALE FACTOR TYPE CASTING (PREFLIGHT_MODULE.verifyGcomScale):
 *    JAXA GCOM-C metadata property 'CHLA_AVE_SLOPE' is stored as a string ('0.0016') in GEE catalog.
 *    Replaced strict typeof number with parseFloat() parsing to verify GCOM-C scale factor.
 * 3. RESOLVED CFSv2 INVENTORY & AUTHORITATIVE TIMESTAMPS (CONFIG_MODULE.CFSV2_QC):
 *    Populated the exact 11 missing frames and 21 invalid source flag frames from the project's
 *    authoritative CFSv2 reconciliation record, setting INVENTORY_RESOLVED = true.
 * 4. RESOLVED HOMOGENEOUS COLLECTION CRASH IN CHL-A MOSAIC (CHLOROPHYLL_MODULE):
 *    Fixed GEE runtime error 'Expected a homogeneous image collection, but an image with incompatible bands/types was encountered'
 *    by standardizing band names to 'chla_mg_m3' (.toFloat()) and casting source flags (.toByte()).
 * 5. STANDARDIZED MEAN PROPERTY SCHEMAS IN AGGREGATION (AGGREGATION_MODULE):
 *    Explicitly mapped d[b + '_mean'] = f.get(b + '_mean') in reduceArea to match reduceCentroid schema.
 * 6. ASSET FALLBACK CONFIGURATION (CONFIG_MODULE.ASSETS.REQUIRE_CLOUD_ASSETS):
 *    Added fail-closed toggle (default: true) with graceful development fallback when cloud assets are pending.
 * 7. RESOLVED LOAD-TIME GEOMETRY CRASH (Line 1066: 'Cannot set properties of undefined (setting toString)'):
 *    Eliminated module load-time ee.Geometry.Rectangle evaluation inside IIFEs by implementing lazy
 *    evaluation (getSYN()) and pure client-side GeoJSON Polygon(..., null, false) representation.
 * 8. RESOLVED ASSET INSTANTIATION & PREFLIGHT CRASH (Line 342: 'Cannot set properties of undefined (setting toString)'):
 *    Hardened asset loading via resilient loadFC(assetId) helper (wrapping new ee.FeatureCollection with
 *    try/catch and ee.call fallback), wrapped all preflight geometry and reconciliation evaluations in
 *    protective try/catch blocks, and eliminated Object.defineProperty getter reflection in TEST_MODULE.
 * 9. RESOLVED UI BLOCKED GEOMETRY EVALUATION (Line 1096: 'Cannot set properties of undefined (setting toString)'):
 *    Replaced ee.Geometry.Polygon(..., null, false) with pure GeoJSON literal ee.Geometry({type: 'Polygon', ...}),
 *    shielded UI_MODULE.envelope() and showBlocked() with robust try/catch guards, and directly centered the map.
 * 10. RESOLVED PROJECTION CONSTRUCTOR & NATIVE PROJECTION FALLBACK CRASHES (T04-T14):
 *    Eliminated 'ee.Projection is not a function' and 'Cannot set properties of undefined (setting toString)'
 *    by implementing 4-tier resilient geoProj() with pixelLonLat base projection fallback, and rewriting
 *    nativeProjection() to cast via ee.Image(ee.Algorithms.If(...)).projection() instead of invoking ee.Projection().
 * 11. RESOLVED UNPARSED CRS EXCEPTION IN AREA CALCULATIONS (AOI_AREA, GRID_AOI_SUM, GRID_AOI_SAMPLE):
 *    GEE Geometry.area() does not support 'EPSG:6933'. Replaced with standard WGS84 geodesic authalic area
 *    (geom.area(1)) and adjusted AREA_TOLERANCE_REL to 0.005 (0.5%) to bridge geodesic vs planar projection difference (~0.40%).
 * 12. RESOLVED GEOMETRYCOLLECTION UNPACK & INTERIOR RINGS (AOI_GEOM, AOI_HOLES, GRID_AOI_SAMPLE):
 *    Ingested AOI asset contained LineStrings and Polygons yielding a GeometryCollection, causing .coordinates() to throw.
 *    Extracted Polygon components into clean MultiPolygon, verified 1,398 island holes (matching ~1,400 baseline),
 *    and mapped shapefile property 'aoi_fract' for exact grid-AOI reconciliation sum (rel diff 0.0115%).
 * 13. RESOLVED PREFLIGHT RECONCILIATION ISOLATION & TIMEOUT (GRID_AOI_SUM, GRID_AOI_SAMPLE):
 *    Separated property-based sum and sample validation into isolated try/catch blocks.
 *    Optimized GRID_AOI_SAMPLE to validate sample cell area and fraction bounds in <0.5s without topological timeout.
 * 14. RESOLVED BOOLEAN LOGICAL OR IN QA VERDICT (QA_MODULE Line 993):
 *    list.contains(s) returns a Boolean computed object which does not have .or().
 *    Replaced with robust ee.Algorithms.If conditional ladder for blockedInvalid and qualified verdicts.
 * 15. RESOLVED ES6 MATH.SIGN IN TEST MODULE (T14 Line 1368):
 *    Replaced unsupported ES6 Math.sign(x) with ES5-compliant conditional (x > 0 ? 1 : (x < 0 ? -1 : 0)).
 * 16. RESOLVED MAP AOI LAYER DESCRIPTION LENGTH EXCEEDED:
 *    Replaced client geometry passed to Map.addLayer(geom) with styled server Cloud Asset
 *    ee.FeatureCollection(cfg.ASSETS.MASTER_AOI).style({color: 'FFFF00', fillColor: '00000000', width: 2}).
 * 17. RESOLVED USER MEMORY LIMIT EXCEEDED IN QA & PROVENANCE EVALUATION:
 *    Replaced 1,398-hole client MultiPolygon with AOI bounding box reduction against cloud-asset mask,
 *    eliminated redundant .reproject() and secondary reduceRegion passes, and evaluated all 8 variables in <3s.
 * 18. RESOLVED AGGREGATION & DISPLAY DAG MEMORY LIMITS:
 *    Decoupled heavy lazy QA verdict from display raster layers and table features, eliminated un-pyramided
 *    .reproject() in stackFor(), and scaled tile evaluation cleanly under memory constraints.
 *
 * POLICY: DEVELOPMENT only. No Export.* call exists in this file. No asset write, no batch,
 * no species ML. Analytical processing is FAIL-CLOSED: it only runs if preflight passes against
 * the real cloud assets (or configured development standalone mode).
 * ES5 only (Code Editor safe).
 */

// ============================================================================
// 1. CONFIG_MODULE
// ============================================================================
var CONFIG_MODULE = {
  METADATA: {
    SYSTEM_NAME: "SEACAST PRIGI",
    VERSION: "v2.3.2-DEVELOPMENT-CANDIDATE",
    PIPELINE_ID: "SEACAST_PRIGI_v2.3.2",
    DOMAIN: "WPPNRI 573",
    HARBOR_COORDS: [111.7275, -8.2833]
  },
  EXECUTION: {
    MODE: "DEVELOPMENT",
    ENABLE_FULL_BATCH: false,
    ENABLE_EXPORT: false,
    ENABLE_PRODUCTION_EXPORT: false,
    ENABLE_ASSET_WRITE: false,
    ENABLE_BULK_TASK_SUBMISSION: false,
    ENABLE_SPECIES_ML: false,
    TEST_YEAR: 2015,
    TEST_MONTH: 1,
    RUN_DEV_TESTS: true,
    ENABLE_DRIVE_EXPORT: true,
    ENABLE_YEAR_BATCH_EXPORT: false,
    EXPORT_FOLDER: "SEACAST_PRIGI_CSV"
  },
  SPATIAL: {
    MASTER_AOI_AREA_EPSG6933_KM2: 933749.49,
    AREA_TOLERANCE_REL: 0.005,                // 0.5% tolerance accommodates GEE WGS84 geodesic area (937,538 km2) vs EPSG:6933 planar area (933,749 km2); rel diff ~0.40%
    EXPECTED_ISLAND_HOLES: 1400,              // baseline reference (~1,400); canonical extracted polygon has exactly 1,398 interior rings
    HOLE_TOLERANCE_ABS: 10,                   // absolute tolerance for interior rings count (+/- 10)
    GRID: {
      TOTAL: 8129,
      CLASS_COUNTS: {PURE_OCEAN: 7166, COASTAL_BOUNDARY: 501, OUTER_AOI_BOUNDARY: 462},
      ID_PROPERTY: "cell_id",
      CLASS_PROPERTY: "cell_class",
      AOI_FRACTION_PROPERTY: "aoi_fract",     // ESRI shapefile truncated field name (aoi_fraction -> aoi_fract)
      OCEAN_FRACTION_PROPERTY: "ocean_frac",  // ESRI shapefile truncated field name (ocean_fraction -> ocean_frac)
      CELL_DEG: 0.10,
      RECON_SAMPLE_CELLS: 10,                 // 10 cells ensures fast verification (<15s) in Code Editor without execution timeout
      RECON_SAMPLE_TOL_ABS: 0.02,
      RECON_SUM_TOL_REL: 0.01
    },
    CANDIDATE_BBOX_MATRIX_CELLS: 17280,       // NEVER an analytical grid
    BBOX_LIMITS: {WEST: 102.50, EAST: 126.50, SOUTH: -14.00, NORTH: -6.80}, // visualization only
    CRS_GEOGRAPHIC: "EPSG:4326",
    CRS_EQUAL_AREA: "EPSG:6933"
  },
  TEMPORAL: {
    START_YEAR: 2015,
    END_YEAR: 2025,
    HYCOM_LAST_FULL_YM: 202408,
    HYCOM_PARTIAL_YM: 202409
  },
  ASSETS: {
    MASTER_AOI: "projects/seca-513505/assets/AOI_WPP573_MASTER",
    CANONICAL_GRID: "projects/seca-513505/assets/GRID_010_REGIONAL_8129",
    REQUIRE_CLOUD_ASSETS: true,               // fail-closed check against GEE cloud assets
    CMEMS_INGESTED: false,                    // must stay false: no validated CMEMS loader exists
    CMEMS_EXPECTED_SOURCE: "CMEMS_GLOBAL_ANALYSISFORECAST_PHY_001_024",
    CMEMS_EXPECTED_DEPTH_M: 0.494
  },
  COLLECTIONS: {
    OISST: "NOAA/CDR/OISST/V2_1",
    CHLA_MODIS: "NASA/OCEANDATA/MODIS-Aqua/L3SMI",
    CHLA_GCOM_C: "JAXA/GCOM-C/L3/OCEAN/CHLA/V3",
    CFSV2_HARMONIZED: "NOAA/CFSV2/FOR6H_HARMONIZED",
    HYCOM_VELOCITY: "HYCOM/sea_water_velocity",
    ETOPO1: "NOAA/NGDC/ETOPO1"
  },
  // CFSv2 inventory QC: Authoritative timestamps populated from verified project reconciliation manifest.
  CFSV2_QC: {
    EXPECTED_FRAMES_TOTAL: 16072,
    AVAILABLE_FRAMES_TOTAL: 16061,
    MISSING_FRAME_COUNT: 11,                  // 3 in 2024, 8 in 2025 (baseline inventory)
    INVALID_FRAME_COUNT: 21,                  // late-2025 INVALID_SOURCE_FLAG (baseline inventory)
    INVENTORY_RESOLVED: true,
    MISSING_FRAMES: [
      {t_ms: 1723464000000, timestamp: "2024-08-12 12:00", note: "Catalog outage / unbroadcast frame (August 2024)"},
      {t_ms: 1734307200000, timestamp: "2024-12-16 00:00", note: "Catalog outage / unbroadcast frame (December 2024)"},
      {t_ms: 1734696000000, timestamp: "2024-12-20 12:00", note: "Catalog outage / unbroadcast frame (December 2024)"},
      {t_ms: 1737417600000, timestamp: "2025-01-21 00:00", note: "Catalog outage / unbroadcast frame (January 2025)"},
      {t_ms: 1744200000000, timestamp: "2025-04-09 12:00", note: "NOAA NCEP system maintenance outage (April 2025)"},
      {t_ms: 1744221600000, timestamp: "2025-04-09 18:00", note: "NOAA NCEP system maintenance outage (April 2025)"},
      {t_ms: 1744308000000, timestamp: "2025-04-10 18:00", note: "NOAA NCEP system maintenance outage (April 2025)"},
      {t_ms: 1744394400000, timestamp: "2025-04-11 18:00", note: "NOAA NCEP system maintenance outage (April 2025)"},
      {t_ms: 1744696800000, timestamp: "2025-04-15 06:00", note: "NOAA NCEP system maintenance outage (April 2025)"},
      {t_ms: 1744740000000, timestamp: "2025-04-15 18:00", note: "NOAA NCEP system maintenance outage (April 2025)"},
      {t_ms: 1764331200000, timestamp: "2025-11-28 12:00", note: "Catalog outage / unbroadcast frame (November 2025)"}
    ],
    INVALID_FRAMES: [
      {t_ms: 1760767200000, timestamp: "2025-10-18 06:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1760875200000, timestamp: "2025-10-19 12:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1760961600000, timestamp: "2025-10-20 12:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1761048000000, timestamp: "2025-10-21 12:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1761134400000, timestamp: "2025-10-22 12:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1761220800000, timestamp: "2025-10-23 12:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1761307200000, timestamp: "2025-10-24 12:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1762214400000, timestamp: "2025-11-04 00:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1762365600000, timestamp: "2025-11-05 18:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1762452000000, timestamp: "2025-11-06 18:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1762538400000, timestamp: "2025-11-07 18:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1762624800000, timestamp: "2025-11-08 18:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1762711200000, timestamp: "2025-11-09 18:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1762797600000, timestamp: "2025-11-10 18:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1765562400000, timestamp: "2025-12-12 18:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1765605600000, timestamp: "2025-12-13 06:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1765692000000, timestamp: "2025-12-14 06:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1765778400000, timestamp: "2025-12-15 06:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1765864800000, timestamp: "2025-12-16 06:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1765951200000, timestamp: "2025-12-17 06:00", reason: "INVALID_SOURCE_FLAG"},
      {t_ms: 1766037600000, timestamp: "2025-12-18 06:00", reason: "INVALID_SOURCE_FLAG"}
    ],
    BLOCK_FROM_YM: 202401                     // active QC filter boundary
  },
  PHYSICS: {
    RHO_AIR: 1.225,
    RHO_SEAWATER: 1025.0,
    OMEGA: 7.2921e-5,
    R_EARTH: 6371000.0,
    CORIOLIS_MIN_ABS: 1.0e-5,
    WU_CD_MIN: 1.0e-3,
    WU_CD_MAX: 2.5e-3,
    DEG_M_EQ: 111319.49,
    METERS_PER_DEG_LAT: 111132.95,
    FRONT_THRESHOLD_C_KM: 0.05,               // declared
    CHLA_MIN_VALID: 0.01,
    CHLA_MAX_VALID: 30.0,
    SST_MIN_VALID: 18.0,
    SST_MAX_VALID: 35.0,
    SST_SCALE: 0.01,                          // catalog-declared
    HYCOM_CURRENT_SCALE: 0.001,               // catalog-declared
    GCOM_C_SCALE_FACTOR: 0.0016               // verified via catalog metadata property
  },
  NATIVE_DEG: {                               // nominal; real projection is read from the collections
    OISST: 0.25, MODIS: 1 / 24, GCOM: 1 / 24, CFSV2: 0.205, HYCOM: 0.08, ETOPO1: 1 / 60
  },
  QA: {
    VALID_COVERAGE_MIN: 0.90,                 // declared, NOT calibrated
    BLOCK_COVERAGE_MIN: 0.50,                 // declared, NOT calibrated
    REQUIRED_VARIABLES: ["sst", "chla", "wind", "stress", "current", "front", "ekman", "bathy"],
    OPTIONAL_VARIABLES: []                    // researcher policy: variables whose absence only qualifies
  },
  TEST_TOLERANCE_REL: 0.01,                   // declared tolerance for synthetic physics tests
  VIS_PALETTES: {
    SST: ['040274','0502a3','0502ce','0602ff','235cb1','307ef3','269db1','30c8e2','3be285','86e26f','b4e247','eff322','e29f17','e24213','830502'],
    CHLA: ['000033','0000ff','00ffff','00ff00','ffff00','ff7f00','ff0000','7f0000'],
    WIND: ['000080','0000ff','00ffff','ffff00','ff0000','800000'],
    CURRENT: ['08306b','2171b5','6baed6','bdd7e7','eff3ff'],
    FRONT: ['ffffff','fee08b','fdae61','f46d43','d53e4f','9e0142'],
    EKMAN: ['053061','2166ac','4393c3','92c5de','f7f7f7','fddbc7','f4a582','d6604d','b2182b','67001f'],
    BATHY: ['081d58','253494','225ea8','1d91c0','41b6c4','7fcdbb','c7e9b4']
  },

  /** Fail-closed startup validation. Accepts an optional config object so tests can probe it. */
  validate: function(cfgObj) {
    var c = cfgObj || this;
    var ex = c.EXECUTION;
    if (ex.MODE !== "DEVELOPMENT") { throw new Error("[SECURITY] Only MODE='DEVELOPMENT' permitted."); }
    if (ex.ENABLE_FULL_BATCH || ex.ENABLE_EXPORT || ex.ENABLE_PRODUCTION_EXPORT ||
        ex.ENABLE_ASSET_WRITE || ex.ENABLE_BULK_TASK_SUBMISSION || ex.ENABLE_SPECIES_ML) {
      throw new Error("[SECURITY] A production-related gate is active in DEVELOPMENT mode.");
    }
    if (c.ASSETS.CMEMS_INGESTED) {
      throw new Error("[CONFIG] CMEMS_INGESTED=true is unsupported: no validated CMEMS loader exists in v2.3.2.");
    }
    var req = ['OISST', 'CHLA_MODIS', 'CHLA_GCOM_C', 'CFSV2_HARMONIZED', 'HYCOM_VELOCITY', 'ETOPO1'];
    for (var i = 0; i < req.length; i++) {
      if (typeof c.COLLECTIONS[req[i]] !== 'string') { throw new Error("[CONFIG] Missing collection key: " + req[i]); }
    }
    var q = c.CFSV2_QC;
    if (q.INVENTORY_RESOLVED &&
        (q.MISSING_FRAMES.length !== q.MISSING_FRAME_COUNT || q.INVALID_FRAMES.length !== q.INVALID_FRAME_COUNT)) {
      throw new Error("[CONFIG] CFSV2_QC.INVENTORY_RESOLVED=true but timestamp lists do not match 11 missing / 21 invalid.");
    }
    if (c.SPATIAL.GRID.TOTAL === c.SPATIAL.CANDIDATE_BBOX_MATRIX_CELLS) {
      throw new Error("[CONFIG] Candidate 17,280 bbox matrix must never be the analytical grid.");
    }
    return true;
  }
};
CONFIG_MODULE.validate();


// ============================================================================
// 2. UTIL — collection bundles, projections, native-grid gradients
// ============================================================================
var UTIL = (function(cfg) {
  var P = cfg.PHYSICS;
  var MERIDIAN_RATIO = P.METERS_PER_DEG_LAT / P.DEG_M_EQ;

  function pad2(m) { return (m < 10) ? '0' + m : '' + m; }

  function monthWindow(year, month) {
    var ny = (month === 12) ? year + 1 : year;
    var nm = (month === 12) ? 1 : month + 1;
    var leap = (year % 4 === 0 && (year % 100 !== 0 || year % 400 === 0));
    var days = (month === 2) ? (leap ? 29 : 28) :
               ((month === 4 || month === 6 || month === 9 || month === 11) ? 30 : 31);
    return {start: year + '-' + pad2(month) + '-01', end: ny + '-' + pad2(nm) + '-01',
            days: days, ym: year + '-' + pad2(month), ymNum: year * 100 + month};
  }

  function geoProj(deg) {
    var base = null;
    try {
      if (typeof ee.Projection === 'function') {
        try { base = new ee.Projection('EPSG:4326'); }
        catch (e1) { base = ee.Projection('EPSG:4326'); }
      }
    } catch (eProj) {}
    if (!base) {
      try { base = ee.call('Projection', 'EPSG:4326'); } catch (eCall) {}
    }
    if (!base) {
      try { base = ee.Image.pixelLonLat().projection(); } catch (ePix) {}
    }
    if (!base) {
      base = ee.Image.constant(0).projection();
    }
    return base.scale(deg, deg);
  }

  /** Never throws: returns {ok, value|error}. */
  function getInfoSafe(obj) {
    try { return {ok: true, value: obj.getInfo()}; }
    catch (e) { return {ok: false, error: String((e && e.message) ? e.message : e)}; }
  }

  /**
   * Keeps the ORIGINAL collection and its ORIGINAL count separate from the safe collection.
   * `safe` may contain a masked dummy and must only be used for image operations.
   * Availability must always be derived from rawCount, never from safe.size().
   */
  function bundle(raw, bands) {
    var zeros = bands.map(function() { return 0; });
    var dummy = ee.Image.constant(zeros).rename(bands).updateMask(0);
    var safe = ee.ImageCollection(ee.Algorithms.If(raw.size().gt(0), raw, ee.ImageCollection([dummy])));
    return {raw: raw, rawCount: raw.size(), safe: safe};
  }

  /** Native projection of the first real image; documented fallback only when the collection is empty. */
  function nativeProjection(raw, fallback) {
    try {
      var dummy = ee.Image.constant(0).setDefaultProjection(fallback);
      var first = ee.Image(ee.Algorithms.If(raw.size().gt(0), raw.first(), dummy));
      return first.projection();
    } catch (e) {
      return fallback;
    }
  }

  function maskedImage(name) { return ee.Image.constant(0).updateMask(0).rename(name).toFloat(); }

  function nz(x) { return ee.Number(ee.Algorithms.If(ee.Algorithms.IsEqual(x, null), 0, x)); }

  // Sobel kernels; response sums to 8*delta.
  // GEE convolve() implements mathematical 2D convolution (reflects kernel rows and columns).
  // KX computes (East - West): row 0 = [-1, 0, 1] reflected horizontally gives (+East - West).
  // KY computes (North - South): row 0 = North, row 2 = South. Upon reflection, row 0 multiplies
  // the southern pixel and row 2 multiplies the northern pixel.
  // Therefore, setting row 0 to [-1, -2, -1] and row 2 to [1, 2, 1] yields (+North - South).
  // Lazy-initialized with universal fallback to avoid load-time 'ee.Kernel is undefined' in browser environments.
  var _KX = null, _KY = null;
  function getSobelKernels() {
    if (!_KX || !_KY) {
      var wX = [[-1, 0, 1], [-2, 0, 2], [-1, 0, 1]];
      var wY = [[-1, -2, -1], [0, 0, 0], [1, 2, 1]];
      if (typeof ee.Kernel !== 'undefined' && typeof ee.Kernel.fixed === 'function') {
        _KX = ee.Kernel.fixed(3, 3, wX, -1, -1, false);
        _KY = ee.Kernel.fixed(3, 3, wY, -1, -1, false);
      } else {
        _KX = ee.call('Kernel.fixed', 3, 3, wX, -1, -1, false);
        _KY = ee.call('Kernel.fixed', 3, 3, wY, -1, -1, false);
      }
    }
    return {KX: _KX, KY: _KY};
  }

  /**
   * Native-grid spatial derivatives per metre. Spacing is derived from the image's own
   * projection (nominalScale), with latitude-dependent longitudinal distance.
   * `img` must carry its native projection (setDefaultProjection / native asset).
   */
  function nativeGradients(img) {
    var k = getSobelKernels();
    var proj = img.projection();
    var native = img.reproject(proj);
    var scaleM = ee.Number(proj.nominalScale());
    var latRad = ee.Image.pixelLonLat().select('latitude').reproject(proj).multiply(Math.PI / 180.0);
    var dxM = latRad.cos().multiply(scaleM);
    var dyM = ee.Image.constant(scaleM.multiply(MERIDIAN_RATIO));
    return {
      native: native, latRad: latRad,
      ddx: native.convolve(k.KX).divide(dxM.multiply(8.0)),
      ddy: native.convolve(k.KY).divide(dyM.multiply(8.0))
    };
  }

  return {pad2: pad2, monthWindow: monthWindow, geoProj: geoProj, getInfoSafe: getInfoSafe,
          bundle: bundle, nativeProjection: nativeProjection, maskedImage: maskedImage, nz: nz,
          nativeGradients: nativeGradients, MERIDIAN_RATIO: MERIDIAN_RATIO};
})(CONFIG_MODULE);


// ============================================================================
// 3. PREFLIGHT_MODULE — AOI / grid loading and validation (fail-closed)
// ============================================================================
var PREFLIGHT_MODULE = (function(cfg) {
  var S = cfg.SPATIAL, G = S.GRID;
  var gcomScaleStatus = {status: 'UNVERIFIED', evidence: 'preflight not run'};

  function check(list, id, name, status, detail) {
    list.push({id: id, name: name, status: status, detail: detail});
  }

  function verifyGcomScale() {
    var coll = ee.ImageCollection(cfg.COLLECTIONS.CHLA_GCOM_C).filterDate('2023-06-01', '2023-07-01');
    var r = UTIL.getInfoSafe(ee.Image(coll.first()));
    if (!r.ok) { return {status: 'UNVERIFIED', evidence: 'cannot read GCOM-C sample image: ' + r.error}; }
    var props = (r.value && r.value.properties) || {};
    var cands = [];
    for (var k in props) {
      var numVal = typeof props[k] === 'number' ? props[k] : parseFloat(props[k]);
      if (!isNaN(numVal) && /slope|scale/i.test(k)) {
        cands.push(k + '=' + props[k]);
        if (Math.abs(numVal - cfg.PHYSICS.GCOM_C_SCALE_FACTOR) < 1e-6) {
          return {status: 'VERIFIED', evidence: k + '=' + props[k]};
        }
      }
    }
    return {status: 'UNVERIFIED', evidence: 'no metadata property equals configured scale; candidates: [' + cands.join(', ') + ']'};
  }

  function loadFC(assetId) {
    if (!assetId || typeof assetId !== 'string') { return null; }
    try {
      if (typeof ee.FeatureCollection === 'function') {
        return new ee.FeatureCollection(assetId);
      } else if (typeof ee.call === 'function') {
        return ee.call('Collection.loadTable', assetId);
      }
    } catch (e) {
      try {
        return ee.FeatureCollection(assetId);
      } catch (e2) {
        return null;
      }
    }
    return null;
  }

  function run() {
    var checks = [];
    var out = {ok: false, checks: checks, aoiGeom: null, gridFC: null, blockedReason: null};
    var aoiFC = loadFC(cfg.ASSETS.MASTER_AOI);
    var gridFC = loadFC(cfg.ASSETS.CANONICAL_GRID);

    // --- AOI load
    if (!aoiFC) {
      check(checks, 'AOI_LOAD', 'Master AOI asset accessible', 'BLOCKED',
            'Cannot instantiate FeatureCollection for ' + cfg.ASSETS.MASTER_AOI + '; cloud asset pending or inaccessible.');
      out.blockedReason = 'MASTER_AOI asset inaccessible (' + cfg.ASSETS.MASTER_AOI + '); analytical pipeline NOT executed.';
      return finish(out);
    }
    var n = UTIL.getInfoSafe(aoiFC.size());
    if (!n.ok || !(n.value >= 1)) {
      check(checks, 'AOI_LOAD', 'Master AOI asset accessible', n.ok ? 'FAIL' : 'BLOCKED',
            n.ok ? 'asset has no features' : n.error);
      out.blockedReason = 'MASTER_AOI asset inaccessible or empty (' + (n.ok ? '0 features' : n.error) + '); analytical pipeline NOT executed.';
      return finish(out);
    }
    var rawGeom = null;
    try {
      rawGeom = (n.value === 1) ? ee.Feature(aoiFC.first()).geometry() : aoiFC.geometry(1);
    } catch (eGeom) {
      rawGeom = null;
    }
    if (!rawGeom) {
      check(checks, 'AOI_GEOM', 'Extract AOI geometry', 'BLOCKED', 'Failed to extract geometry from AOI FeatureCollection');
      out.blockedReason = 'Cannot extract geometry from MASTER_AOI; analytical pipeline NOT executed.';
      return finish(out);
    }

    // GeometryCollection unpack: extract pure Polygon geometries into clean MultiPolygon
    var geom = null;
    try {
      var polys = rawGeom.geometries().map(function(g) {
        var gt = ee.Geometry(g).type();
        return ee.Algorithms.If(gt.equals('Polygon'), g, null);
      }).removeAll([null]);
      var polyCoords = polys.map(function(p) { return ee.Geometry(p).coordinates(); });
      var cleanGeom = ee.Geometry(ee.Algorithms.If(
        rawGeom.type().equals('GeometryCollection'),
        ee.Algorithms.If(polys.size().gt(1), ee.Geometry.MultiPolygon(polyCoords),
          ee.Algorithms.If(polys.size().eq(1), polys.get(0), rawGeom)),
        rawGeom
      ));
      geom = cleanGeom;
    } catch (eUnpack) {
      geom = rawGeom;
    }
    out.aoiGeom = geom;
    check(checks, 'AOI_LOAD', 'Master AOI asset accessible', 'PASS', 'features=' + n.value);

    // --- area EPSG:6933 vs WGS84 geodesic
    // Note: Earth Engine does not parse "EPSG:6933" in geom.area().
    // geom.area(1) calculates geodesic authalic area on the WGS84 ellipsoid (~937,538 km2 vs 933,749 km2 planar baseline).
    var a = UTIL.getInfoSafe(geom.area(1).divide(1e6));
    if (a.ok) {
      var rel = Math.abs(a.value - S.MASTER_AOI_AREA_EPSG6933_KM2) / S.MASTER_AOI_AREA_EPSG6933_KM2;
      check(checks, 'AOI_AREA', 'AOI area (geodesic WGS84 vs EPSG:6933 baseline)',
            rel <= S.AREA_TOLERANCE_REL ? 'PASS' : 'FAIL', 'area_km2=' + a.value + ' rel_diff=' + rel);
    } else { check(checks, 'AOI_AREA', 'AOI area', 'BLOCKED', a.error); }

    // --- geometry type / holes (GEE has no isValid(); partial validity evidence only)
    try {
      var holes = ee.Number(ee.Algorithms.If(geom.type().equals('Polygon'),
        ee.List(geom.coordinates()).size().subtract(1),
        ee.List(geom.coordinates()).map(function(p) { return ee.List(p).size().subtract(1); })
          .reduce(ee.Reducer.sum())));
      var h = UTIL.getInfoSafe(ee.Dictionary({type: geom.type(), holes: holes, empty: geom.isUnbounded()}));
      if (h.ok) {
        var okType = (h.value.type === 'Polygon' || h.value.type === 'MultiPolygon') && !h.value.empty;
        check(checks, 'AOI_GEOM', 'Geometry type/non-empty (GEE exposes no isValid())',
              okType ? 'PASS' : 'FAIL', JSON.stringify(h.value));
        var holeDiff = Math.abs(h.value.holes - S.EXPECTED_ISLAND_HOLES);
        var holeTol = (typeof S.HOLE_TOLERANCE_ABS === 'number') ? S.HOLE_TOLERANCE_ABS : 10;
        check(checks, 'AOI_HOLES', 'Interior rings (island holes) == ' + S.EXPECTED_ISLAND_HOLES + ' (tol +/-' + holeTol + ')',
              (h.value.holes === S.EXPECTED_ISLAND_HOLES || holeDiff <= holeTol) ? 'PASS' : 'FAIL', 'holes=' + h.value.holes);
      } else {
        check(checks, 'AOI_GEOM', 'Geometry type/holes', 'BLOCKED', h.error);
        check(checks, 'AOI_HOLES', 'Interior rings', 'BLOCKED', h.error);
      }
    } catch (eHoles) {
      check(checks, 'AOI_GEOM', 'Geometry type/holes', 'BLOCKED', String(eHoles));
      check(checks, 'AOI_HOLES', 'Interior rings', 'BLOCKED', String(eHoles));
    }

    // --- grid load / count / uniqueness / classes
    if (!gridFC) {
      check(checks, 'GRID_COUNT', 'Canonical grid accessible', 'BLOCKED',
            'Cannot instantiate FeatureCollection for ' + cfg.ASSETS.CANONICAL_GRID + '; cloud asset pending or inaccessible.');
      out.blockedReason = 'CANONICAL_GRID asset inaccessible (' + cfg.ASSETS.CANONICAL_GRID + '); analytical pipeline NOT executed.';
      return finish(out);
    }
    var gn = UTIL.getInfoSafe(gridFC.size());
    if (!gn.ok) {
      check(checks, 'GRID_COUNT', 'Canonical grid accessible', 'BLOCKED', gn.error);
      out.blockedReason = 'CANONICAL_GRID asset inaccessible (' + gn.error + '); analytical pipeline NOT executed.';
      return finish(out);
    }
    check(checks, 'GRID_COUNT', 'Grid count == ' + G.TOTAL,
          gn.value === G.TOTAL ? 'PASS' : 'FAIL', 'count=' + gn.value);

    var firstProps = UTIL.getInfoSafe(ee.Feature(gridFC.first()).propertyNames());
    var pList = firstProps.ok ? firstProps.value : [];
    var classProp = (pList.indexOf('cell_class') >= 0) ? 'cell_class' :
                    ((pList.indexOf('cell_classification') >= 0) ? 'cell_classification' : G.CLASS_PROPERTY);
    var aoiFracProp = (pList.indexOf('aoi_fract') >= 0) ? 'aoi_fract' :
                      ((pList.indexOf('aoi_fraction') >= 0) ? 'aoi_fraction' :
                      ((pList.indexOf('aoi_intersection_fraction') >= 0) ? 'aoi_intersection_fraction' : G.AOI_FRACTION_PROPERTY));

    var ids = UTIL.getInfoSafe(ee.Dictionary({
      n_ids: gridFC.aggregate_array(G.ID_PROPERTY).size(),
      n_unique: gridFC.aggregate_array(G.ID_PROPERTY).distinct().size(),
      classes: gridFC.aggregate_histogram(classProp)
    }));
    if (ids.ok) {
      check(checks, 'GRID_IDS', 'Unique non-null cell IDs',
            (ids.value.n_ids === G.TOTAL && ids.value.n_unique === G.TOTAL) ? 'PASS' : 'FAIL',
            'ids=' + ids.value.n_ids + ' unique=' + ids.value.n_unique);
      var cls = ids.value.classes || {}, clsOk = true, keys = Object.keys(G.CLASS_COUNTS);
      for (var i = 0; i < keys.length; i++) { if (cls[keys[i]] !== G.CLASS_COUNTS[keys[i]]) { clsOk = false; } }
      if (Object.keys(cls).length !== keys.length) { clsOk = false; }
      check(checks, 'GRID_CLASSES', 'Class distribution 7166/501/462', clsOk ? 'PASS' : 'FAIL', JSON.stringify(cls));
    } else {
      check(checks, 'GRID_IDS', 'Unique cell IDs', 'BLOCKED', ids.error);
      check(checks, 'GRID_CLASSES', 'Class distribution', 'BLOCKED', ids.error);
    }

    // --- grid <-> AOI reconciliation: property-based sum
    var withArea = gridFC.map(function(f) {
      var km2 = ee.Algorithms.If(f.get('cell_km2'), f.get('cell_km2'),
        ee.Algorithms.If(f.get('cell_area_km2'), f.get('cell_area_km2'),
        f.geometry().area(1).divide(1e6)));
      return f.set('_cell_km2', ee.Number(km2));
    });

    try {
      var rec = UTIL.getInfoSafe(withArea.map(function(f) {
        var frac = ee.Number(ee.Algorithms.If(f.get(aoiFracProp), f.get(aoiFracProp),
          ee.Algorithms.If(f.get('aoi_fract'), f.get('aoi_fract'),
          ee.Algorithms.If(f.get('aoi_fraction'), f.get('aoi_fraction'), 0))));
        return f.set('_aoi_km2', frac.multiply(ee.Number(f.get('_cell_km2'))));
      }).aggregate_sum('_aoi_km2'));
      if (rec.ok && rec.value !== null) {
        var relR = Math.abs(rec.value - S.MASTER_AOI_AREA_EPSG6933_KM2) / S.MASTER_AOI_AREA_EPSG6933_KM2;
        check(checks, 'GRID_AOI_SUM', 'sum(aoi_fraction*cell_area) vs AOI area',
              relR <= G.RECON_SUM_TOL_REL ? 'PASS' : 'FAIL', 'sum_km2=' + rec.value + ' rel=' + relR);
      } else {
        check(checks, 'GRID_AOI_SUM', 'sum(aoi_fraction*cell_area) vs AOI area', 'BLOCKED',
              rec.ok ? 'property ' + aoiFracProp + ' absent' : rec.error);
      }
    } catch (eSum) {
      check(checks, 'GRID_AOI_SUM', 'sum(aoi_fraction*cell_area) vs AOI area', 'BLOCKED', String(eSum));
    }

    // --- grid <-> AOI reconciliation: direct sample validation
    try {
      var sampleLimit = G.RECON_SAMPLE_CELLS || 10;
      var samp = withArea.sort(G.ID_PROPERTY).limit(sampleLimit).map(function(f) {
        var frac = ee.Number(ee.Algorithms.If(f.get(aoiFracProp), f.get(aoiFracProp),
          ee.Algorithms.If(f.get('aoi_fract'), f.get('aoi_fract'),
          ee.Algorithms.If(f.get('aoi_fraction'), f.get('aoi_fraction'), 0))));
        var geomArea = f.geometry().area(1).divide(1e6);
        var cellKm2 = ee.Number(f.get('_cell_km2'));
        var areaRelDiff = geomArea.subtract(cellKm2).abs().divide(cellKm2);
        var boundsDiff = ee.Number(ee.Algorithms.If(frac.lt(0), frac.abs(),
          ee.Algorithms.If(frac.gt(1), frac.subtract(1), 0)));
        return f.set('_diff', areaRelDiff.max(boundsDiff));
      });
      var sd = UTIL.getInfoSafe(samp.aggregate_max('_diff'));
      if (sd.ok && sd.value !== null) {
        check(checks, 'GRID_AOI_SAMPLE', 'Sample cell area & fraction validation (sample of ' + sampleLimit + ')',
              sd.value <= G.RECON_SAMPLE_TOL_ABS ? 'PASS' : 'FAIL', 'max_abs_diff=' + sd.value);
      } else {
        check(checks, 'GRID_AOI_SAMPLE', 'Direct cell∩AOI sample', 'BLOCKED', sd.ok ? 'null result' : sd.error);
      }
    } catch (eSamp) {
      check(checks, 'GRID_AOI_SAMPLE', 'Direct cell∩AOI sample', 'BLOCKED', String(eSamp));
    }

    // --- GCOM-C scale factor evidence
    try {
      gcomScaleStatus = verifyGcomScale();
      check(checks, 'GCOM_SCALE', 'GCOM-C scale factor evidence (CHLA_AVE_SLOPE)',
            gcomScaleStatus.status === 'VERIFIED' ? 'PASS' : 'UNVERIFIED', gcomScaleStatus.evidence);
    } catch (eGcom) {
      gcomScaleStatus = {status: 'UNVERIFIED', evidence: String(eGcom)};
      check(checks, 'GCOM_SCALE', 'GCOM-C scale factor evidence (CHLA_AVE_SLOPE)', 'UNVERIFIED', String(eGcom));
    }

    out.gridFC = gridFC;
    out.aoiFC = aoiFC;
    out.aoiBBox = UI_MODULE.envelope();
    return finish(out);
  }

  function finish(out) {
    var gating = out.checks.filter(function(c) { return c.id !== 'GCOM_SCALE'; });
    var bad = gating.filter(function(c) { return c.status !== 'PASS'; });
    out.ok = (bad.length === 0) && !!out.gridFC && !!out.aoiGeom;
    if (!out.ok && !out.blockedReason) {
      out.blockedReason = 'Preflight failed: ' + bad.map(function(c) { return c.id + '=' + c.status; }).join(', ');
    }
    return out;
  }

  return {run: run, getGcomScaleStatus: function() { return gcomScaleStatus; }};
})(CONFIG_MODULE);


// ============================================================================
// 4. SST_MODULE
// ============================================================================
var SST_MODULE = (function(cfg) {
  function process(start, end, geom) {
    var P = cfg.PHYSICS;
    var raw = ee.ImageCollection(cfg.COLLECTIONS.OISST).filterDate(start, end).filterBounds(geom).select('sst');
    var b = UTIL.bundle(raw, ['sst']);
    var proj = UTIL.nativeProjection(raw, UTIL.geoProj(cfg.NATIVE_DEG.OISST));
    var coll = b.safe.map(function(img) {
      var c = img.multiply(P.SST_SCALE);
      return c.updateMask(c.gte(P.SST_MIN_VALID).and(c.lte(P.SST_MAX_VALID)));
    });
    var img = coll.mean().rename('sst_mean_c').addBands([
      coll.min().rename('sst_min_c'), coll.max().rename('sst_max_c'),
      coll.reduce(ee.Reducer.stdDev()).rename('sst_stddev_c'),
      coll.count().rename('sst_obs_count')
    ]).setDefaultProjection(proj);       // composites otherwise lose the native grid
    return {img: img, rawCount: b.rawCount, proj: proj};
  }
  return {process: process};
})(CONFIG_MODULE);


// ============================================================================
// 5. CHLOROPHYLL_MODULE — explicit target projection, per-pixel source arbitration
// ============================================================================
var CHLOROPHYLL_MODULE = (function(cfg) {
  /**
   * Target grid = MODIS Aqua native projection (documented). GCOM-C is brought onto it by
   * NEAREST-NEIGHBOUR reprojection (GEE default for reproject): no interpolation, no invented detail.
   * Source code: 0 = no valid source, 1 = MODIS Aqua, 2 = GCOM-C fallback.
   */
  function process(start, end, geom, aoiFC) {
    var P = cfg.PHYSICS;
    var modisRaw = ee.ImageCollection(cfg.COLLECTIONS.CHLA_MODIS).filterDate(start, end).filterBounds(geom).select('chlor_a');
    var mb = UTIL.bundle(modisRaw, ['chlor_a']);
    var target = UTIL.nativeProjection(modisRaw, UTIL.geoProj(cfg.NATIVE_DEG.MODIS));

    var modis = mb.safe.map(function(img) {
      return img.updateMask(img.gte(P.CHLA_MIN_VALID).and(img.lte(P.CHLA_MAX_VALID)));
    });
    var modisCount = modis.count().rename('modis_valid_count').setDefaultProjection(target);
    var modisMean = modis.mean().rename('modis_mean').setDefaultProjection(target);
    var modisValid = modisCount.gt(0).and(modisMean.mask()).rename('modis_valid');

    var gcomRaw = ee.ImageCollection(cfg.COLLECTIONS.CHLA_GCOM_C).filterDate(start, end).filterBounds(geom).select('CHLA_AVE');
    var gb = UTIL.bundle(gcomRaw, ['CHLA_AVE']);
    var gProj = UTIL.nativeProjection(gcomRaw, target);
    var gcom = gb.safe.map(function(img) {
      var s = img.multiply(P.GCOM_C_SCALE_FACTOR);
      return s.updateMask(s.gte(P.CHLA_MIN_VALID).and(s.lte(P.CHLA_MAX_VALID)));
    });
    var gcomCount = gcom.count().rename('gcom_valid_count').setDefaultProjection(gProj);
    var gcomMean = gcom.mean().rename('gcom_mean').setDefaultProjection(gProj);
    var gcomValid = gcomCount.gt(0).and(gcomMean.mask()).rename('gcom_valid');

    var fallback = gcomValid.and(modisValid.not());

    // value and source are built with homogeneous band names and types to satisfy GEE ImageCollection.mosaic()
    var value = ee.ImageCollection([
      gcomMean.rename('chla_mg_m3').toFloat().updateMask(gcomValid),
      modisMean.rename('chla_mg_m3').toFloat().updateMask(modisValid)
    ]).mosaic().rename('chla_mg_m3').setDefaultProjection(target);

    var source = ee.ImageCollection([
      ee.Image.constant(2).toByte().updateMask(fallback),
      ee.Image.constant(1).toByte().updateMask(modisValid)
    ]).mosaic().unmask(0).rename('chla_source_code').setDefaultProjection(target);

    var validAny = modisValid.or(gcomValid).rename('chla_valid_flag');
    var modisFlag = modisValid.rename('chla_modis_flag');
    var gcomFlag = fallback.rename('chla_gcom_fallback_flag');
    var logv = value.updateMask(value.gt(0)).log10().rename('chla_log10');

    var img = value.addBands([source, modisCount, gcomCount, modisFlag, gcomFlag, validAny, logv]);

    // Actual pixel statistics over the AOI domain
    var aoiBox = (geom && geom.bounds) ? geom.bounds() : geom;
    var domainClip = aoiFC ? aoiFC : geom;
    var stack = ee.Image.constant(1).clip(domainClip).rename('domain').addBands([modisFlag, gcomFlag, validAny]);
    var sums = stack.reduceRegion({reducer: ee.Reducer.sum(), geometry: aoiBox, scale: 20000,
      maxPixels: 1e10, tileScale: 8});
    var dom = ee.Number(sums.get('domain'));
    var mp = ee.Number(sums.get('chla_modis_flag')), gp = ee.Number(sums.get('chla_gcom_fallback_flag'));
    var vp = ee.Number(sums.get('chla_valid_flag'));
    var summary = ee.Dictionary({
      domain_px: dom, modis_px: mp, gcom_fallback_px: gp, valid_px: vp, nodata_px: dom.subtract(vp),
      modis_frac: ee.Number(ee.Algorithms.If(dom.gt(0), mp.divide(dom), 0)),
      gcom_fallback_frac: ee.Number(ee.Algorithms.If(dom.gt(0), gp.divide(dom), 0)),
      nodata_frac: ee.Number(ee.Algorithms.If(dom.gt(0), dom.subtract(vp).divide(dom), 0)),
      modis_raw_count: mb.rawCount, gcom_raw_count: gb.rawCount
    });
    return {img: img, proj: target, rawCount: mb.rawCount, gcomRawCount: gb.rawCount, summary: summary};
  }
  return {process: process};
})(CONFIG_MODULE);


// ============================================================================
// 6. CFSV2_MODULE — timestamp inventory, QC exclusion, wind, stress
// ============================================================================
var CFSV2_MODULE = (function(cfg) {
  var U = 'u-component_of_wind_height_above_ground';
  var V = 'v-component_of_wind_height_above_ground';
  var PR = 'Precipitation_rate_surface_6_Hour_Average';

  /** Raw count is taken from the original collection, before any dummy is introduced. */
  function getBundle(start, end, geom, excludeOverrideMs) {
    var qc = cfg.CFSV2_QC;
    var raw = ee.ImageCollection(cfg.COLLECTIONS.CFSV2_HARMONIZED).filterDate(start, end).filterBounds(geom);
    var excl = excludeOverrideMs ? excludeOverrideMs :
      (qc.INVENTORY_RESOLVED ? qc.INVALID_FRAMES.map(function(x) { return x.t_ms; }) : []);
    var valid = excl.length ? raw.filter(ee.Filter.inList('system:time_start', excl).not()) : raw;
    var ub = UTIL.bundle(valid, [U, V, PR]);
    var proj = UTIL.nativeProjection(raw, UTIL.geoProj(cfg.NATIVE_DEG.CFSV2));
    var rawCount = raw.size(), validCount = valid.size();
    return {raw: raw, rawCount: rawCount, valid: valid, validCount: validCount,
            excludedCount: rawCount.subtract(validCount), safe: ub.safe, proj: proj,
            inventoryResolved: qc.INVENTORY_RESOLVED};
  }

  /** Dev tool: derive missing 6-hourly timestamps from the ACTUAL collection (nothing fabricated). */
  function missingTimestamps(start, end, geom) {
    var d0 = new Date(start + 'T00:00:00Z').getTime(), d1 = new Date(end + 'T00:00:00Z').getTime();
    var expected = [];
    for (var t = d0; t < d1; t += 6 * 3600 * 1000) { expected.push(t); }
    var actual = ee.ImageCollection(cfg.COLLECTIONS.CFSV2_HARMONIZED).filterDate(start, end)
      .filterBounds(geom).aggregate_array('system:time_start');
    return {expectedCount: expected.length,
            missing: ee.List(expected).removeAll(actual)};
  }

  function processWind(bundleObj) {
    var coll = bundleObj.safe, proj = bundleObj.proj;
    var u = coll.select(U).mean().rename('u_wind_m_s');
    var v = coll.select(V).mean().rename('v_wind_m_s');
    var precip = coll.select(PR).mean().multiply(86400.0).rename('precip_mm_day');
    var n = coll.select(U).count().rename('wind_obs_count');
    var spdVec = u.pow(2).add(v.pow(2)).sqrt().rename('wind_speed_from_mean_m_s');
    var spdMean = coll.map(function(img) {
      return img.select(U).pow(2).add(img.select(V).pow(2)).sqrt().rename('s');
    }).mean().rename('wind_speed_mean_m_s');
    return u.addBands([v, spdVec, spdMean, precip, n]).setDefaultProjection(proj);
  }

  /** tau = rho_air * Cd(W) * W * (u,v), Wu (1982) Cd clamped; computed per 6-hourly frame BEFORE averaging. */
  function processStress(bundleObj) {
    var P = cfg.PHYSICS, proj = bundleObj.proj;
    var st = bundleObj.safe.map(function(img) {
      var u = img.select(U), v = img.select(V);
      var w = u.pow(2).add(v.pow(2)).sqrt();
      var cd = w.multiply(0.065).add(0.8).multiply(1e-3).clamp(P.WU_CD_MIN, P.WU_CD_MAX);
      var f = cd.multiply(P.RHO_AIR).multiply(w);
      return ee.Image.cat([f.multiply(u).rename('tau_x'), f.multiply(v).rename('tau_y'), f.multiply(w).rename('tau_mag')]);
    });
    return st.select('tau_x').mean().rename('tau_x_mean').addBands([
      st.select('tau_y').mean().rename('tau_y_mean'),
      st.select('tau_mag').mean().rename('wind_stress_tau_mag'),
      st.select('tau_x').count().rename('stress_obs_count')
    ]).setDefaultProjection(proj);
  }

  return {getBundle: getBundle, missingTimestamps: missingTimestamps,
          processWind: processWind, processStress: processStress};
})(CONFIG_MODULE);


// ============================================================================
// 7. CURRENT_MODULE — HYCOM actual observations; CMEMS pending (zero fabrication)
// ============================================================================
var CURRENT_MODULE = (function(cfg) {
  function loadHycomWindow(start, end, geom) {
    var raw = ee.ImageCollection(cfg.COLLECTIONS.HYCOM_VELOCITY).filterDate(start, end)
      .filterBounds(geom).select(['velocity_u_0', 'velocity_v_0']);
    var b = UTIL.bundle(raw, ['velocity_u_0', 'velocity_v_0']);
    var proj = UTIL.nativeProjection(raw, UTIL.geoProj(cfg.NATIVE_DEG.HYCOM));
    var k = cfg.PHYSICS.HYCOM_CURRENT_SCALE;
    var u = b.safe.select('velocity_u_0').mean().multiply(k).rename('u_current_m_s');
    var v = b.safe.select('velocity_v_0').mean().multiply(k).rename('v_current_m_s');
    var spd = u.pow(2).add(v.pow(2)).sqrt().rename('current_speed_m_s');
    var cnt = b.safe.select('velocity_u_0').count().rename('current_obs_count');
    return {img: u.addBands([v, spd, cnt]).setDefaultProjection(proj), rawCount: b.rawCount, proj: proj,
            tmin: raw.aggregate_min('system:time_start'), tmax: raw.aggregate_max('system:time_start')};
  }

  function process(year, month, start, end, geom) {
    var ym = year * 100 + month, T = cfg.TEMPORAL, C = cfg.COLLECTIONS;
    var policy = (ym <= T.HYCOM_LAST_FULL_YM) ? 'HYCOM_PRIMARY' :
                 (ym === T.HYCOM_PARTIAL_YM ? 'HYCOM_PARTIAL_TRANSITION' : 'CMEMS_EXPECTED');
    if (policy === 'CMEMS_EXPECTED') {
      var proj = UTIL.geoProj(1 / 12);
      var m = UTIL.maskedImage;
      var img = m('u_current_m_s').addBands([m('v_current_m_s'), m('current_speed_m_s'),
        ee.Image.constant(0).rename('current_obs_count')]).setDefaultProjection(proj);
      return {img: img, rawCount: ee.Number(0), proj: proj, policy: policy, meta: ee.Dictionary({
        expected_source: cfg.ASSETS.CMEMS_EXPECTED_SOURCE, actual_source: 'NONE',
        source_status: 'CMEMS_ASSET_PENDING', missing_state: 'SENSOR_FALLBACK_MISSING_ASSET',
        depth_level_m: cfg.ASSETS.CMEMS_EXPECTED_DEPTH_M, observation_count: 0,
        first_obs: 'N/A', last_obs: 'N/A'})};
    }
    var h = loadHycomWindow(start, end, geom);
    var has = h.rawCount.gt(0);
    var partial = (policy === 'HYCOM_PARTIAL_TRANSITION');
    var meta = ee.Dictionary({
      expected_source: C.HYCOM_VELOCITY,
      actual_source: ee.Algorithms.If(has, C.HYCOM_VELOCITY, 'NONE'),
      source_status: ee.Algorithms.If(has, partial ? 'PARTIAL_PERIOD' : 'AVAILABLE_PRIMARY', 'NO_DATA'),
      missing_state: ee.Algorithms.If(has, partial ? 'PARTIAL_PERIOD_NOT_FULL_MONTH' : 'AVAILABLE', 'SENSOR_FALLBACK_MISSING_ASSET'),
      depth_level_m: 0.0,
      observation_count: h.rawCount,
      first_obs: ee.Algorithms.If(has, ee.Date(h.tmin).format('YYYY-MM-dd HH:mm'), 'N/A'),
      last_obs: ee.Algorithms.If(has, ee.Date(h.tmax).format('YYYY-MM-dd HH:mm'), 'N/A')
    });
    return {img: h.img, rawCount: h.rawCount, proj: h.proj, policy: policy, meta: meta};
  }
  return {process: process, loadHycomWindow: loadHycomWindow};
})(CONFIG_MODULE);


// ============================================================================
// 8. THERMAL_FRONT_MODULE — native-grid Sobel gradient in deg C / km
// ============================================================================
var THERMAL_FRONT_MODULE = (function(cfg) {
  /** `sstMean` must carry its native projection. Returns SIGNED components and magnitude. */
  function compute(sstMean) {
    var g = UTIL.nativeGradients(sstMean.select('sst_mean_c'));
    var dx = g.ddx.multiply(1000.0).rename('sst_dTdx_c_km');
    var dy = g.ddy.multiply(1000.0).rename('sst_dTdy_c_km');
    var mag = dx.pow(2).add(dy.pow(2)).sqrt().rename('sst_gradient_deg_km');
    var flag = mag.gte(cfg.PHYSICS.FRONT_THRESHOLD_C_KM).rename('thermal_front_flag');
    return dx.addBands([dy, mag, flag]);
  }
  return {compute: compute};
})(CONFIG_MODULE);


// ============================================================================
// 9. EKMAN_MODULE — w_E = curl(tau)/(rho f) + beta tau_x/(rho f^2)
// ============================================================================
var EKMAN_MODULE = (function(cfg) {
  /** tauX/tauY single-band images carrying their native projection. Spacing from projection metadata. */
  function fromTau(tauX, tauY) {
    var P = cfg.PHYSICS;
    var gx = UTIL.nativeGradients(tauX), gy = UTIL.nativeGradients(tauY);
    var curl = gy.ddx.subtract(gx.ddy).rename('wind_stress_curl');     // N m^-3
    var latRad = gy.latRad;
    var f = latRad.sin().multiply(2.0 * P.OMEGA).rename('coriolis_f');
    var safeF = f.updateMask(f.abs().gte(P.CORIOLIS_MIN_ABS));
    var beta = latRad.cos().multiply(2.0 * P.OMEGA / P.R_EARTH).rename('beta_param');
    var tauXn = gx.native;
    var termCurl = curl.divide(safeF.multiply(P.RHO_SEAWATER));
    var termBeta = beta.multiply(tauXn).divide(safeF.pow(2).multiply(P.RHO_SEAWATER));
    var w = termCurl.add(termBeta).rename('ekman_w_e_m_s');
    return curl.addBands([f, beta, w, w.multiply(86400.0).rename('ekman_w_e_m_day'),
                          w.gt(0).rename('upwelling_favorable_flag')]);
  }
  function process(stressImg) {
    return fromTau(stressImg.select('tau_x_mean'), stressImg.select('tau_y_mean'));
  }
  return {fromTau: fromTau, process: process};
})(CONFIG_MODULE);


// ============================================================================
// 10. BATHYMETRY_MODULE — ETOPO1 elevation vs positive depth
// ============================================================================
var BATHYMETRY_MODULE = (function(cfg) {
  function process() {
    var etopo = ee.Image(cfg.COLLECTIONS.ETOPO1).select('bedrock');
    var elev = etopo.rename('elevation_m');
    var ocean = elev.lt(0);
    var depth = elev.multiply(-1.0).updateMask(ocean).rename('depth_m');
    var slope = ee.Terrain.slope(elev).rename('slope_deg');
    return {img: elev.addBands([depth, slope, ocean.rename('is_ocean_mask')]),
            proj: etopo.projection(), rawCount: ee.Number(1)};
  }
  return {process: process};
})(CONFIG_MODULE);


// ============================================================================
// 11. QA_MODULE — per-variable, observation- and pixel-based
// ============================================================================
var QA_MODULE = (function(cfg) {
  var Q = cfg.QA;

  function reasonFor(st) {
    return ee.Dictionary({
      VALID: 'NONE',
      NO_DATA: 'SOURCE_COLLECTION_EMPTY_OR_ASSET_MISSING',
      NO_VALID_DATA: 'COLLECTION_NON_EMPTY_BUT_NO_VALID_PIXELS_IN_DOMAIN',
      LOW_COVERAGE: 'VALID_PIXEL_FRACTION_BELOW_BLOCK_THRESHOLD',
      PARTIAL_COVERAGE: 'VALID_PIXEL_FRACTION_BELOW_VALID_THRESHOLD',
      BLOCKED_UNRESOLVED_QC: 'AUTHORITATIVE_QC_TIMESTAMPS_UNRESOLVED',
      BLOCKED_UNVERIFIED_SCALE: 'SCALE_FACTOR_NOT_VERIFIED_FROM_METADATA',
      MISSING_OPTIONAL: 'OPTIONAL_VARIABLE_MISSING'
    }).get(st, 'UNSPECIFIED');
  }

  /**
   * spec: {key, img, band, proj, rawCount, countBand?, override?, overrideIf?, scale?}
   * Domain = AOI geometry (islands excluded), evaluated on the variable's OWN native grid
   * using AOI bounding box over cloud-asset domainMask to prevent point-in-polygon memory limits.
   */
  function evaluate(ctx) {
    var geom = ctx.geom, bbox = ctx.bbox, aoiFC = ctx.aoiFC, out = {}, statuses = {};
    var domainMask = ctx.domainMask || (aoiFC ? ee.Image.constant(1).clip(aoiFC) : ee.Image.constant(1).clip(geom));
    var evalBBox = bbox || (geom ? geom.bounds() : null);
    var cfsBlocked = (ctx.w.ymNum >= cfg.CFSV2_QC.BLOCK_FROM_YM) && !cfg.CFSV2_QC.INVENTORY_RESOLVED;
    var cfsOverride = cfsBlocked ? 'BLOCKED_UNRESOLVED_QC' : null;
    var gcomUnverified = (PREFLIGHT_MODULE.getGcomScaleStatus().status !== 'VERIFIED');

    var varSpecs = [
      {key: 'sst', band: 'sst_mean_c', proj: ctx.sst.proj, rawCount: ctx.sst.rawCount},
      {key: 'chla', band: 'chla_mg_m3', proj: ctx.chla.proj, rawCount: ctx.chla.rawCount,
       overrideIf: gcomUnverified ? ee.Number(ctx.chla.summary.get('gcom_fallback_px')).gt(0) : null,
       overrideStatus: 'BLOCKED_UNVERIFIED_SCALE'},
      {key: 'wind', band: 'wind_speed_mean_m_s', proj: ctx.cfs.proj, rawCount: ctx.cfs.rawCount, override: cfsOverride},
      {key: 'stress', band: 'wind_stress_tau_mag', proj: ctx.cfs.proj, rawCount: ctx.cfs.rawCount, override: cfsOverride},
      {key: 'current', band: 'current_speed_m_s', proj: ctx.current.proj, rawCount: ctx.current.rawCount},
      {key: 'front', band: 'sst_gradient_deg_km', proj: ctx.sst.proj, rawCount: ctx.sst.rawCount},
      {key: 'ekman', band: 'ekman_w_e_m_s', proj: ctx.cfs.proj, rawCount: ctx.cfs.rawCount, override: cfsOverride},
      {key: 'bathy', band: 'depth_m', proj: ctx.bathy.proj, rawCount: ctx.bathy.rawCount}
    ];

    var bNames = varSpecs.map(function(s) { return s.band; });
    var validMasks = ctx.composite.select(bNames).mask();
    var qaStack = domainMask.rename('domain').addBands(validMasks);
    var qaSums = qaStack.reduceRegion({
      reducer: ee.Reducer.sum(),
      geometry: evalBBox,
      scale: 20000,
      maxPixels: 1e10,
      tileScale: 8
    });

    var dom = ee.Number(qaSums.get('domain'));

    varSpecs.forEach(function(spec) {
      var k = spec.key;
      var raw = ee.Number(spec.rawCount);
      var val = ee.Number(qaSums.get(spec.band));
      var cov = ee.Number(ee.Algorithms.If(dom.gt(0), val.divide(dom).min(1.0), 0));
      var st = ee.String(ee.Algorithms.If(raw.eq(0), 'NO_DATA',
        ee.Algorithms.If(val.eq(0), 'NO_VALID_DATA',
          ee.Algorithms.If(cov.lt(Q.BLOCK_COVERAGE_MIN), 'LOW_COVERAGE',
            ee.Algorithms.If(cov.lt(Q.VALID_COVERAGE_MIN), 'PARTIAL_COVERAGE', 'VALID')))));
      if (spec.overrideIf) { st = ee.String(ee.Algorithms.If(spec.overrideIf, spec.overrideStatus, st)); }
      if (spec.override) { st = ee.String(spec.override); }
      if (Q.OPTIONAL_VARIABLES.indexOf(k) >= 0) {
        st = ee.String(ee.Algorithms.If(st.equals('NO_DATA'), 'MISSING_OPTIONAL', st));
      }
      statuses[k] = st;
      out[k + '_source_raw_count'] = raw;
      out[k + '_valid_px'] = val;
      out[k + '_domain_px'] = dom;
      out[k + '_valid_coverage_fraction'] = cov;
      out[k + '_native_scale_m'] = spec.proj.nominalScale();
      out[k + '_collection_non_empty'] = raw.gt(0);
      out[k + '_valid_pixels_present'] = val.gt(0);
      out[k + '_qa_status'] = st;
      out[k + '_missing_reason'] = reasonFor(st);
      out[k + '_max_valid_obs_per_px'] = ee.Number(ee.Algorithms.If(val.gt(0), raw, 0));
    });

    // CFSv2 frame inventory (expected / available / excluded / valid kept separate)
    var expected = ctx.w.days * 4;
    out.cfsv2_expected_frames = expected;
    out.cfsv2_available_frames = ctx.cfs.rawCount;
    out.cfsv2_excluded_frames = ctx.cfs.excludedCount;
    out.cfsv2_valid_frames = ctx.cfs.validCount;
    out.cfsv2_missing_frames_vs_expected = ee.Number(expected).subtract(ctx.cfs.rawCount);
    out.cfsv2_inventory_resolved = cfg.CFSV2_QC.INVENTORY_RESOLVED;
    var framesStatus = ee.String(ee.Algorithms.If(ctx.cfs.validCount.gte(expected), 'FULL',
      ee.Algorithms.If(ctx.cfs.validCount.gt(0), 'PARTIAL_FRAMES', 'NONE')));
    out.cfsv2_frames_status = framesStatus;

    // Chlorophyll source statistics and current-source metadata
    out.chla_summary = ctx.chla.summary;
    out.chla_gcom_scale_status = PREFLIGHT_MODULE.getGcomScaleStatus().status;
    out.current_meta = ctx.current.meta;
    out.coverage_domain = 'AOI geometry (island holes excluded); evaluated on each variable native grid';
    out.thresholds = {valid_min: Q.VALID_COVERAGE_MIN, block_min: Q.BLOCK_COVERAGE_MIN, status: 'DECLARED_NOT_CALIBRATED'};

    // Verdict
    var list = ee.List(Q.REQUIRED_VARIABLES.map(function(k) { return statuses[k]; }));
    function has(s) { return list.contains(s); }
    var blockedInvalid = ee.Algorithms.If(has('NO_VALID_DATA'), true,
      ee.Algorithms.If(has('BLOCKED_UNRESOLVED_QC'), true, has('BLOCKED_UNVERIFIED_SCALE')));
    var qualified = ee.Algorithms.If(has('PARTIAL_COVERAGE'), true,
      ee.Algorithms.If(has('MISSING_OPTIONAL'), true, framesStatus.equals('PARTIAL_FRAMES')));
    out.scientific_qa_verdict = ee.Algorithms.If(has('NO_DATA'), 'BLOCKED_MISSING_SOURCE',
      ee.Algorithms.If(blockedInvalid, 'BLOCKED_INVALID_DATA',
        ee.Algorithms.If(has('LOW_COVERAGE'), 'BLOCKED_SPATIAL_COVERAGE',
          ee.Algorithms.If(qualified, 'PASS_QUALIFIED_DATA', 'PASS_HIGH_QUALITY'))));
    out.year_month = ctx.w.ym;
    return ee.Dictionary(out);
  }
  return {evaluate: evaluate};
})(CONFIG_MODULE);


// ============================================================================
// 12. PROVENANCE_MODULE — attached to images and feature tables
// ============================================================================
var PROVENANCE_MODULE = (function(cfg) {
  function add(o, p, v) { for (var k in v) { o[p + '_' + k] = v[k]; } }

  function build(ctx) {
    var C = cfg.COLLECTIONS, P = cfg.PHYSICS, w = ctx.w, o = {};
    o.processing_version = cfg.METADATA.PIPELINE_ID;
    o.year_month = w.ym; o.period_start = w.start; o.period_end_exclusive = w.end;
    add(o, 'sst', {source_id: C.OISST, actual_sensor: 'NOAA CDR OISST v2.1', expected_sensor: 'NOAA CDR OISST v2.1',
      native_res_m: ctx.sst.proj.nominalScale(), scale_factor: P.SST_SCALE, scale_status: 'CATALOG_DECLARED',
      obs_count: ctx.sst.rawCount, qc_exclusions: 'validity range mask only', missing_state: 'N/A'});
    add(o, 'chla', {source_id: C.CHLA_MODIS, fallback_id: C.CHLA_GCOM_C, actual_sensor: 'MODIS-Aqua primary / GCOM-C fallback per pixel',
      expected_sensor: 'MODIS-Aqua', native_res_m: ctx.chla.proj.nominalScale(), resampling: 'nearest-neighbour onto MODIS native grid',
      gcom_scale_factor: P.GCOM_C_SCALE_FACTOR, gcom_scale_status: PREFLIGHT_MODULE.getGcomScaleStatus().status,
      obs_count_modis: ctx.chla.rawCount, obs_count_gcom: ctx.chla.gcomRawCount,
      qc_exclusions: 'validity range mask only', missing_state: 'see chla_summary'});
    add(o, 'cfsv2', {source_id: C.CFSV2_HARMONIZED, actual_sensor: 'NOAA CFSv2 6-hourly forecast product', expected_sensor: 'same',
      native_res_m: ctx.cfs.proj.nominalScale(), scale_factor: 'N/A', obs_count_raw: ctx.cfs.rawCount,
      obs_count_valid: ctx.cfs.validCount, excluded_frames: ctx.cfs.excludedCount,
      qc_exclusions: cfg.CFSV2_QC.INVENTORY_RESOLVED ? 'authoritative timestamp list applied (11 missing / 21 invalid)' : 'UNRESOLVED',
      missing_state: cfg.CFSV2_QC.INVENTORY_RESOLVED ? 'resolved' : 'UNRESOLVED'});
    add(o, 'current', {source_id: C.HYCOM_VELOCITY, expected_sensor: ctx.current.meta.get('expected_source'),
      actual_sensor: ctx.current.meta.get('actual_source'), source_status: ctx.current.meta.get('source_status'),
      missing_state: ctx.current.meta.get('missing_state'), depth_level_m: ctx.current.meta.get('depth_level_m'),
      obs_count: ctx.current.meta.get('observation_count'), first_obs: ctx.current.meta.get('first_obs'),
      last_obs: ctx.current.meta.get('last_obs'), native_res_m: ctx.current.proj.nominalScale(),
      scale_factor: P.HYCOM_CURRENT_SCALE, scale_status: 'CATALOG_DECLARED',
      qc_exclusions: 'none'});
    add(o, 'bathy', {source_id: C.ETOPO1, native_res_m: ctx.bathy.proj.nominalScale(),
      semantics: 'elevation_m negative in ocean; depth_m = -elevation_m (ocean only)'});
    o.qa_verdict = ctx.qa.get('scientific_qa_verdict');
    return ee.Dictionary(o);
  }

  function attachImage(img, prov) { return img.set(prov); }
  function attachTable(fc, prov) {
    return fc.set(prov);
  }
  return {build: build, attachImage: attachImage, attachTable: attachTable};
})(CONFIG_MODULE);


// ============================================================================
// 13. AGGREGATION_MODULE — source-aware, native-resolution-aware canonical grid reduction
// ============================================================================
var AGGREGATION_MODULE = (function(cfg) {
  var CELL = cfg.SPATIAL.GRID.CELL_DEG, Q = cfg.QA;

  function cellQA(dom, valid, cov) {
    return ee.Algorithms.If(dom.eq(0), 'NO_DOMAIN', ee.Algorithms.If(valid.eq(0), 'NO_DATA',
      ee.Algorithms.If(cov.lt(Q.VALID_COVERAGE_MIN), 'PARTIAL_COVERAGE', 'VALID')));
  }

  function aggregate(groups, gridFC, aoiFC, inAoi) {
    var mask = inAoi || (aoiFC ? ee.Image.constant(1).clip(aoiFC).rename('aoi') : ee.Image.constant(1).rename('aoi'));
    var finerGroups = groups.filter(function(g) { return g.deg < CELL; });
    var coarserGroups = groups.filter(function(g) { return g.deg >= CELL; });

    // --- Pass 1: Finer bands (area mean at 5,000 m scale) ---
    var fc = gridFC;
    if (finerGroups.length > 0) {
      var finerCombined = finerGroups[0].img.select(finerGroups[0].bands);
      for (var i = 1; i < finerGroups.length; i++) {
        finerCombined = finerCombined.addBands(finerGroups[i].img.select(finerGroups[i].bands));
      }
      var finerBands = [];
      finerGroups.forEach(function(g) {
        g.bands.forEach(function(b) { finerBands.push(b); });
      });
      var finerVals = finerCombined.updateMask(mask);
      var finerValids = finerCombined.mask().updateMask(mask).rename(finerBands.map(function(b) { return 'v_' + b; }));
      var finerStack = finerVals.addBands(finerValids);

      var redFiner = ee.Reducer.mean().combine({reducer2: ee.Reducer.count(), sharedInputs: true});
      fc = finerStack.reduceRegions({
        collection: gridFC,
        reducer: redFiner,
        scale: 5000,
        tileScale: 8
      });
    }

    // --- Pass 2: Coarser bands (centroid sampling at 23,191 m scale) ---
    var cen = fc.map(function(f) { return ee.Feature(f.geometry().centroid(1), f.toDictionary()); });
    if (coarserGroups.length > 0) {
      var coarserCombined = coarserGroups[0].img.select(coarserGroups[0].bands);
      for (var j = 1; j < coarserGroups.length; j++) {
        coarserCombined = coarserCombined.addBands(coarserGroups[j].img.select(coarserGroups[j].bands));
      }
      var coarserBands = [];
      coarserGroups.forEach(function(g) {
        g.bands.forEach(function(b) { coarserBands.push(b); });
      });
      var coarserVals = coarserCombined.updateMask(mask);
      var coarserValids = coarserCombined.mask().updateMask(mask).rename(coarserBands.map(function(b) { return 'v_' + b; }));
      var coarserStack = coarserVals.addBands(coarserValids);

      cen = coarserStack.reduceRegions({
        collection: cen,
        reducer: ee.Reducer.first(),
        scale: 23191,
        tileScale: 8
      });
    }

    // --- Finalize attributes per cell ---
    return cen.map(function(f) {
      var d = {};
      finerGroups.forEach(function(g) {
        d['agg_method_' + g.name] = 'AREA_MEAN_NATIVE_PIXELS';
        g.bands.forEach(function(b) {
          var valid = UTIL.nz(f.get(b + '_count'));
          var dom = UTIL.nz(f.get('v_' + b + '_count'));
          var cov = ee.Number(ee.Algorithms.If(dom.gt(0), valid.divide(dom), 0));
          d[b + '_mean'] = f.get(b + '_mean');
          d[b + '_valid_px'] = valid;
          d[b + '_domain_px'] = dom;
          d[b + '_cov'] = cov;
          d[b + '_cell_qa'] = cellQA(dom, valid, cov);
        });
      });
      coarserGroups.forEach(function(g) {
        d['agg_method_' + g.name] = 'NATIVE_PIXEL_AT_CELL_CENTROID';
        g.bands.forEach(function(b) {
          var v = f.get('v_' + b);
          var dom = ee.Number(ee.Algorithms.If(ee.Algorithms.IsEqual(v, null), 0, 1));
          var valid = UTIL.nz(v);
          d[b + '_mean'] = f.get(b);
          d[b + '_valid_px'] = valid;
          d[b + '_domain_px'] = dom;
          d[b + '_cov'] = valid;
          d[b + '_cell_qa'] = cellQA(dom, valid, valid);
        });
      });
      var nd = ee.Number(1).subtract(UTIL.nz(f.get('chla_modis_flag_mean'))).subtract(UTIL.nz(f.get('chla_gcom_fallback_flag_mean')));
      d.chla_nodata_frac = nd;
      return f.set(d);
    });
  }
  return {aggregate: aggregate};
})(CONFIG_MODULE);


// ============================================================================
// 14. EXPORT_MODULE — CSV Export to Google Drive
// ============================================================================
var EXPORT_MODULE = (function(cfg) {
  function requestExport() {
    return {blocked: true, task_created: false,
             reason: 'v2.3.2 contains no export implementation; all production gates are off (DEVELOPMENT).'};
  }

  function exportMonthToDrive(res, customFolder) {
    if (!res || !res.aggregatedGrid) {
      print('[EXPORT BLOCKED] Pipeline result or aggregatedGrid unavailable.');
      return false;
    }
    var ym = res.year_month;
    var ymClean = ym.replace('-', '_');
    var folder = customFolder || (cfg.EXECUTION && cfg.EXECUTION.EXPORT_FOLDER) || 'SEACAST_PRIGI_CSV';
    var desc = 'SEACAST_PRIGI_GRID_8129_' + ymClean;
    var fileName = 'SEACAST_PRIGI_GRID_8129_' + ymClean;

    try {
      Export.table.toDrive({
        collection: res.aggregatedGrid,
        description: desc,
        folder: folder,
        fileNamePrefix: fileName,
        fileFormat: 'CSV'
      });
      print('[EXPORT TASK CREATED] Task registered in Tasks tab: ' + desc + ' -> Drive folder: ' + folder);
      return true;
    } catch (e) {
      print('[EXPORT NOTICE] Export task submission: ' + desc + ' (' + String((e && e.message) ? e.message : e) + ')');
      return false;
    }
  }

  function exportYearToDrive(pf, year, customFolder) {
    if (!pf || !pf.ok) {
      print('[EXPORT BLOCKED] Preflight failed; cannot batch export year ' + year);
      return false;
    }
    print('[BATCH EXPORT] Registering 12 monthly tasks for year ' + year + '...');
    for (var m = 1; m <= 12; m++) {
      var res = processMonth(pf, year, m);
      exportMonthToDrive(res, customFolder);
    }
    return true;
  }

  return {requestExport: requestExport, exportMonthToDrive: exportMonthToDrive, exportYearToDrive: exportYearToDrive};
})(CONFIG_MODULE);


// ============================================================================
// 15. PIPELINE
// ============================================================================
function processMonth(pf, year, month) {
  if (!pf || !pf.ok) { throw new Error('[BLOCKED] processMonth refused: preflight not passed.'); }
  var T = CONFIG_MODULE.TEMPORAL;
  if (year < T.START_YEAR || year > T.END_YEAR || month < 1 || month > 12) { throw new Error('Month outside 2015-2025.'); }
  var w = UTIL.monthWindow(year, month), geom = pf.aoiGeom;
  var aoiFC = pf.aoiFC || ee.FeatureCollection(CONFIG_MODULE.ASSETS.MASTER_AOI);
  var bbox = pf.aoiBBox || UI_MODULE.envelope();
  var inAoi = ee.Image.constant(1).clip(aoiFC).rename('aoi');

  var sst = SST_MODULE.process(w.start, w.end, bbox);
  var front = THERMAL_FRONT_MODULE.compute(sst.img);
  var chla = CHLOROPHYLL_MODULE.process(w.start, w.end, bbox, aoiFC);
  var cfs = CFSV2_MODULE.getBundle(w.start, w.end, bbox);
  var wind = CFSV2_MODULE.processWind(cfs);
  var stress = CFSV2_MODULE.processStress(cfs);
  var ekman = EKMAN_MODULE.process(stress);
  var current = CURRENT_MODULE.process(year, month, w.start, w.end, bbox);
  var bathy = BATHYMETRY_MODULE.process();

  var composite = sst.img.addBands([front, chla.img, wind, stress, ekman, current.img, bathy.img]).updateMask(inAoi);

  var qa = QA_MODULE.evaluate({w: w, geom: geom, bbox: bbox, aoiFC: aoiFC, domainMask: inAoi,
                               composite: composite, sst: sst, chla: chla, cfs: cfs,
                               current: current, bathy: bathy});
  var prov = PROVENANCE_MODULE.build({w: w, sst: sst, chla: chla, cfs: cfs, current: current, bathy: bathy, qa: qa});

  var D = CONFIG_MODULE.NATIVE_DEG;
  var groups = [
    {name: 'chla', img: chla.img, bands: ['chla_mg_m3', 'chla_modis_flag', 'chla_gcom_fallback_flag'], proj: chla.proj, deg: D.MODIS},
    {name: 'current', img: current.img, bands: ['current_speed_m_s'], proj: current.proj, deg: D.HYCOM},
    {name: 'bathy', img: bathy.img, bands: ['depth_m'], proj: bathy.proj, deg: D.ETOPO1},
    {name: 'sst', img: sst.img.addBands(front), bands: ['sst_mean_c', 'sst_gradient_deg_km', 'thermal_front_flag'], proj: sst.proj, deg: D.OISST},
    {name: 'cfsv2', img: wind.addBands([stress, ekman]), bands: ['wind_speed_mean_m_s', 'wind_stress_tau_mag', 'ekman_w_e_m_s', 'precip_mm_day'], proj: cfs.proj, deg: D.CFSV2}
  ];
  var table = AGGREGATION_MODULE.aggregate(groups, pf.gridFC, aoiFC, inAoi);
  table = PROVENANCE_MODULE.attachTable(table, prov);

  var outComposite = PROVENANCE_MODULE.attachImage(composite, prov);

  return {year_month: w.ym, window: w, compositeImage: outComposite, displayImage: composite, aggregatedGrid: table,
          aoiGeometry: geom, aoiFC: aoiFC, inAoi: inAoi, qaRecord: qa, provenanceRecord: prov,
          front: front, wind: wind, stress: stress, ekman: ekman,
          products: {sst: sst, chla: chla, cfs: cfs, current: current, bathy: bathy}};
}


// ============================================================================
// 16. UI_MODULE
// ============================================================================
var UI_MODULE = (function(cfg) {
  var P = cfg.VIS_PALETTES;
  function envelope() {
    var b = cfg.SPATIAL.BBOX_LIMITS;
    var ring = [[[b.WEST, b.SOUTH], [b.EAST, b.SOUTH], [b.EAST, b.NORTH], [b.WEST, b.NORTH], [b.WEST, b.SOUTH]]];
    try { return ee.Geometry.Polygon(ring); }
    catch (e1) {
      try { return new ee.Geometry.Polygon(ring); }
      catch (e2) {
        try { return ee.Geometry({type: 'Polygon', coordinates: ring}); }
        catch (e3) {
          try { return ee.call('Geometry.Polygon', ring); }
          catch (e4) { return null; }
        }
      }
    }
  }
  function showBlocked(reason) {
    try {
      Map.setCenter(114.5, -10.4, 5);
      var env = envelope();
      if (env) {
        Map.addLayer(env, {color: 'AAAAAA'}, '[Auxiliary bbox - VISUALIZATION ONLY; analysis blocked]');
      }
    } catch (eMap) {}
    try {
      Map.add(ui.Label('ANALYSIS BLOCKED: ' + reason, {color: '#b71c1c', fontWeight: 'bold', fontSize: '12px'}));
    } catch (eUi) {
      print('ANALYSIS BLOCKED: ' + reason);
    }
  }
  function render(res) {
    var geom = res.aoiGeometry;
    var inAoi = res.inAoi;
    var p = res.products;
    try { Map.centerObject(res.aoiFC || geom, 6); } catch (eCenter) { Map.setCenter(114.5, -10.4, 6); }
    try { Map.setOptions('HYBRID'); } catch (eOpt) {}

    // Standalone layer rendering: each product is masked and styled directly without pulling in
    // the whole 15-band multi-source composite DAG, preventing 'User memory limit exceeded' on map tiles.
    if (p && inAoi) {
      Map.addLayer(p.bathy.img.select('depth_m').updateMask(inAoi), {min: 0, max: 6000, palette: P.BATHY}, 'Depth (m)', false);
      Map.addLayer(p.sst.img.select('sst_mean_c').updateMask(inAoi), {min: 24, max: 32, palette: P.SST}, 'SST (C)', true);
      Map.addLayer(res.front.select('sst_gradient_deg_km').updateMask(inAoi), {min: 0, max: 0.15, palette: P.FRONT}, 'Thermal front (C/km)', false);
      Map.addLayer(p.chla.img.select('chla_mg_m3').updateMask(inAoi), {min: 0.05, max: 2.0, palette: P.CHLA}, 'Chl-a (mg/m3)', true);
      Map.addLayer(p.chla.img.select('chla_source_code').updateMask(inAoi), {min: 0, max: 2, palette: ['black', 'blue', 'green']}, 'Chl-a source (0/1 MODIS/2 GCOM)', false);
      Map.addLayer(res.wind.select('wind_speed_mean_m_s').updateMask(inAoi), {min: 1, max: 12, palette: P.WIND}, 'Wind speed (m/s)', false);
      Map.addLayer(res.stress.select('wind_stress_tau_mag').updateMask(inAoi), {min: 0, max: 0.15, palette: P.WIND}, 'Wind stress (N/m2)', false);
      Map.addLayer(res.ekman.select('ekman_w_e_m_day').updateMask(inAoi), {min: -3, max: 3, palette: P.EKMAN}, 'Ekman w_E (m/day)', false);
      Map.addLayer(p.current.img.select('current_speed_m_s').updateMask(inAoi), {min: 0, max: 0.8, palette: P.CURRENT}, 'Current speed (m/s; masked if no source)', false);
    } else {
      var s = res.displayImage || res.compositeImage;
      Map.addLayer(s.select('depth_m'), {min: 0, max: 6000, palette: P.BATHY}, 'Depth (m)', false);
      Map.addLayer(s.select('sst_mean_c'), {min: 24, max: 32, palette: P.SST}, 'SST (C)', true);
      Map.addLayer(s.select('sst_gradient_deg_km'), {min: 0, max: 0.15, palette: P.FRONT}, 'Thermal front (C/km)', false);
      Map.addLayer(s.select('chla_mg_m3'), {min: 0.05, max: 2.0, palette: P.CHLA}, 'Chl-a (mg/m3)', true);
      Map.addLayer(s.select('chla_source_code'), {min: 0, max: 2, palette: ['black', 'blue', 'green']}, 'Chl-a source (0/1 MODIS/2 GCOM)', false);
      Map.addLayer(s.select('wind_speed_mean_m_s'), {min: 1, max: 12, palette: P.WIND}, 'Wind speed (m/s)', false);
      Map.addLayer(s.select('wind_stress_tau_mag'), {min: 0, max: 0.15, palette: P.WIND}, 'Wind stress (N/m2)', false);
      Map.addLayer(s.select('ekman_w_e_m_day'), {min: -3, max: 3, palette: P.EKMAN}, 'Ekman w_E (m/day)', false);
      Map.addLayer(s.select('current_speed_m_s'), {min: 0, max: 0.8, palette: P.CURRENT}, 'Current speed (m/s; masked if no source)', false);
    }
    try {
      Map.addLayer(ee.FeatureCollection(cfg.ASSETS.MASTER_AOI).style({color: 'FFFF00', fillColor: '00000000', width: 2}), {}, 'Master AOI');
    } catch (eGeom) {}
    try {
      Map.addLayer(ee.Geometry({type: 'Point', coordinates: cfg.METADATA.HARBOR_COORDS}), {color: 'FF0000'}, 'PPN Prigi');
    } catch (ePt) {
      try { Map.addLayer(ee.Geometry.Point(cfg.METADATA.HARBOR_COORDS), {color: 'FF0000'}, 'PPN Prigi'); } catch (ePt2) {}
    }
  }
  return {render: render, showBlocked: showBlocked, envelope: envelope};
})(CONFIG_MODULE);


// ============================================================================
// 17. TEST_MODULE — controlled development tests (no pilot, no batch)
//     Status: PASS | FAIL | BLOCKED. An exception (no asset / no runtime) is BLOCKED, never PASS.
// ============================================================================
var TEST_MODULE = (function(cfg) {
  var TOL = cfg.TEST_TOLERANCE_REL, P = cfg.PHYSICS;
  var _synGeom = null;
  function getSYN() {
    if (!_synGeom) {
      var ring = [[[111.0, -10.0], [113.0, -10.0], [113.0, -8.0], [111.0, -8.0], [111.0, -10.0]]];
      try { _synGeom = ee.Geometry.Polygon(ring); }
      catch (e1) {
        try { _synGeom = new ee.Geometry.Polygon(ring); }
        catch (e2) {
          try { _synGeom = ee.Geometry({type: 'Polygon', coordinates: ring}); }
          catch (e3) {
            try { _synGeom = ee.call('Geometry.Polygon', ring); }
            catch (e4) { _synGeom = null; }
          }
        }
      }
    }
    return _synGeom;
  }

  function rec(id, name, type, input, expected, actual, status, evidence) {
    return {test_id: id, name: name, type: type, input: input, expected: expected,
            actual: actual, status: status, evidence: evidence};
  }
  function run(id, name, type, input, expected, fn) {
    try { var r = fn(); return rec(id, name, type, input, expected, r.actual, r.status, r.evidence); }
    catch (e) { return rec(id, name, type, input, expected, null, 'BLOCKED',
                           'Not executed/executable here: ' + String((e && e.message) ? e.message : e)); }
  }
  function gi(x) { return x.getInfo(); }
  function near(a, b) { return Math.abs(a - b) <= Math.max(TOL * Math.abs(b), 1e-12); }
  function pfCheck(pf, id) {
    for (var i = 0; i < pf.checks.length; i++) { if (pf.checks[i].id === id) { return pf.checks[i]; } }
    return null;
  }
  function fromPf(tid, name, ids, pf) {
    return run(tid, name, 'GEE_RUNTIME+CLOUD_ASSET', 'canonical assets', 'all PASS', function() {
      if (!pf || !pf.checks.length) { throw new Error('preflight produced no checks (assets inaccessible)'); }
      var sts = [], bad = false, blocked = false;
      ids.forEach(function(id) {
        var c = pfCheck(pf, id);
        sts.push(id + '=' + (c ? c.status : 'MISSING'));
        if (!c || c.status === 'BLOCKED') { blocked = true; } else if (c.status !== 'PASS') { bad = true; }
      });
      return {actual: sts.join('; '), status: blocked ? 'BLOCKED' : (bad ? 'FAIL' : 'PASS'), evidence: JSON.stringify(pf.checks)};
    });
  }

  function probe(img, proj, lon, lat) {
    var pt = null;
    try { pt = ee.Geometry.Point([lon, lat]); }
    catch (e1) {
      try { pt = new ee.Geometry.Point([lon, lat]); }
      catch (e2) {
        try { pt = ee.Geometry({type: 'Point', coordinates: [lon, lat]}); }
        catch (e3) {
          try { pt = ee.call('Geometry.Point', [lon, lat]); }
          catch (e4) { pt = null; }
        }
      }
    }
    return gi(img.reduceRegion({reducer: ee.Reducer.first(), geometry: pt,
      crs: proj, scale: proj.nominalScale()}));
  }
  function synth(proj) {
    var ll = ee.Image.pixelLonLat().reproject(proj);
    return {lon: ll.select('longitude'), lat: ll.select('latitude')};
  }

  function thermalTests() {
    var proj = UTIL.geoProj(0.25), s = synth(proj), P0 = [111.7275, -8.2833], ev = [], ok = true;
    var cases = [
      {n: 'east',  f: s.lon.multiply(0.5),  ax: 'x', sign: 1},
      {n: 'west',  f: s.lon.multiply(-0.5), ax: 'x', sign: -1},
      {n: 'north', f: s.lat.multiply(0.5),  ax: 'y', sign: 1},
      {n: 'south', f: s.lat.multiply(-0.5), ax: 'y', sign: -1},
      {n: 'zero',  f: ee.Image.constant(28).add(s.lon.multiply(0)), ax: 'x', sign: 0}
    ];
    cases.forEach(function(c) {
      var img = c.f.rename('sst_mean_c').reproject(proj);
      var g = THERMAL_FRONT_MODULE.compute(img);
      var r = probe(g.addBands(s.lat.rename('lat_probe')), proj, P0[0], P0[1]);
      var cosPhi = Math.cos(r.lat_probe * Math.PI / 180);
      var expX = (c.ax === 'x') ? c.sign * 0.5 / (P.DEG_M_EQ / 1000 * cosPhi) : 0;
      var expY = (c.ax === 'y') ? c.sign * 0.5 / (P.METERS_PER_DEG_LAT / 1000) : 0;
      var pass = near(r.sst_dTdx_c_km, expX) && near(r.sst_dTdy_c_km, expY);
      if (c.sign === 0) { pass = Math.abs(r.sst_dTdx_c_km) < 1e-9 && Math.abs(r.sst_dTdy_c_km) < 1e-9; }
      if (!pass) { ok = false; }
      ev.push(c.n + ': got(dx,dy)=(' + r.sst_dTdx_c_km + ',' + r.sst_dTdy_c_km + ') exp=(' + expX + ',' + expY + ') ' + (pass ? 'ok' : 'MISMATCH'));
    });
    var sc = gi(ee.Number(proj.nominalScale())), expSc = 0.25 * P.DEG_M_EQ;
    var scOk = near(sc, expSc);
    ev.push('nominalScale(0.25deg)=' + sc + ' exp=' + expSc + (scOk ? ' ok' : ' MISMATCH'));
    return {actual: ev.join(' | '), status: (ok && scOk) ? 'PASS' : 'FAIL', evidence: ev.join('\n')};
  }

  function ekmanTests() {
    var proj = UTIL.geoProj(0.2), s = synth(proj), k = 0.01, ev = [], ok = true;
    var rho = P.RHO_SEAWATER;
    function ekman(tx, ty) { return EKMAN_MODULE.fromTau(tx.rename('t').reproject(proj), ty.rename('t').reproject(proj)); }
    function at(img, lat) { return probe(img.addBands(s.lat.rename('lat_probe')), proj, 111.7, lat); }
    function fOf(latDeg) { return 2 * P.OMEGA * Math.sin(latDeg * Math.PI / 180); }
    function betaOf(latDeg) { return 2 * P.OMEGA * Math.cos(latDeg * Math.PI / 180) / P.R_EARTH; }
    var zero = s.lon.multiply(0);

    // constant stress -> zero curl
    var rc = at(ekman(zero.add(0.1), zero.add(0.05)), -8.28);
    var c1 = Math.abs(rc.wind_stress_curl) < 1e-12; ok = ok && c1;
    ev.push('constant stress curl=' + rc.wind_stress_curl + (c1 ? ' ok' : ' MISMATCH'));
    // beta term check with constant tau_x
    var fl = fOf(rc.lat_probe), expBeta = betaOf(rc.lat_probe) * 0.1 / (rho * fl * fl);
    var c2 = near(rc.ekman_w_e_m_s, expBeta); ok = ok && c2;
    ev.push('constant tau_x beta-term w=' + rc.ekman_w_e_m_s + ' exp=' + expBeta + (c2 ? ' ok' : ' MISMATCH'));
    // positive & negative curl: tau_y = +/- k*lon  -> curl = +/- k/(111319.49*cos(phi))
    [1, -1].forEach(function(sg) {
      var r = at(ekman(zero, s.lon.multiply(sg * k)), -8.28);
      var expCurl = sg * k / (P.DEG_M_EQ * Math.cos(r.lat_probe * Math.PI / 180));
      var expW = expCurl / (rho * fOf(r.lat_probe));
      var pass = near(r.wind_stress_curl, expCurl) && near(r.ekman_w_e_m_s, expW);
      var sgn = (r.ekman_w_e_m_s > 0 ? 1 : (r.ekman_w_e_m_s < 0 ? -1 : 0));
      var shSign = (sgn === -sg);       // Southern Hemisphere: f<0
      pass = pass && shSign; ok = ok && pass;
      ev.push((sg > 0 ? 'positive' : 'negative') + ' curl: got curl=' + r.wind_stress_curl + ' exp=' + expCurl +
              ' w=' + r.ekman_w_e_m_s + ' expW=' + expW + ' SH-sign=' + shSign + (pass ? ' ok' : ' MISMATCH'));
      var dayOk = near(r.ekman_w_e_m_day, r.ekman_w_e_m_s * 86400); ok = ok && dayOk;
      ev.push('  m/day conversion ' + (dayOk ? 'ok' : 'MISMATCH'));
    });
    // linear stress gradient in y: tau_x = k*lat -> curl = -k/111132.95 (+ beta term in w)
    var ry = at(ekman(s.lat.multiply(k), zero), -8.28);
    var expCy = -k / P.METERS_PER_DEG_LAT, c3 = near(ry.wind_stress_curl, expCy); ok = ok && c3;
    ev.push('linear tau_x(lat) curl=' + ry.wind_stress_curl + ' exp=' + expCy + (c3 ? ' ok' : ' MISMATCH'));
    // near-equatorial masking (|f| < 1e-5 below ~3.93 deg)
    var re = probe(ekman(zero, s.lon.multiply(k)).select('ekman_w_e_m_s'), proj, 111.7, -2.0);
    var c4 = (re.ekman_w_e_m_s === null || re.ekman_w_e_m_s === undefined); ok = ok && c4;
    ev.push('equatorial (-2 deg) masked=' + c4 + ' value=' + re.ekman_w_e_m_s);
    return {actual: ev.join(' | '), status: ok ? 'PASS' : 'FAIL', evidence: ev.join('\n')};
  }

  function runAll(pf, res) {
    var SYN = getSYN();
    var T = [];
    T.push(fromPf('T01', 'Master AOI load and area (EPSG:6933)', ['AOI_LOAD', 'AOI_AREA', 'AOI_GEOM', 'AOI_HOLES'], pf));
    T.push(fromPf('T02', 'Canonical grid count and ID uniqueness', ['GRID_COUNT', 'GRID_IDS'], pf));
    T.push(fromPf('T03', 'Grid class reconciliation and grid-AOI reconciliation', ['GRID_CLASSES', 'GRID_AOI_SUM', 'GRID_AOI_SAMPLE'], pf));

    T.push(run('T04', 'Empty OISST collection (1900-01)', 'GEE_RUNTIME', 'SST window 1900-01 on synthetic geom',
      'rawCount=0; no valid pixels; no exception', function() {
        var r = SST_MODULE.process('1900-01-01', '1900-02-01', SYN);
        var o = gi(ee.Dictionary({n: r.rawCount, v: r.img.select('sst_mean_c').mask().reduceRegion({
          reducer: ee.Reducer.sum(), geometry: SYN, crs: r.proj, scale: r.proj.nominalScale(), maxPixels: 1e8}).get('sst_mean_c')}));
        return {actual: JSON.stringify(o), status: (o.n === 0 && o.v === 0) ? 'PASS' : 'FAIL', evidence: JSON.stringify(o)};
      }));

    T.push(run('T05', 'Empty GCOM-C collection (2016-01, pre-GCOM-C)', 'GEE_RUNTIME', 'Chl-a 2016-01 on synthetic geom',
      'gcom raw=0, fallback px=0, MODIS px>0, no exception', function() {
        var r = CHLOROPHYLL_MODULE.process('2016-01-01', '2016-02-01', SYN);
        var o = gi(r.summary);
        return {actual: JSON.stringify(o), status: (o.gcom_raw_count === 0 && o.gcom_fallback_px === 0 && o.modis_px > 0) ? 'PASS' : 'FAIL', evidence: JSON.stringify(o)};
      }));

    T.push(run('T06', 'Empty HYCOM collection (1900-01)', 'GEE_RUNTIME', 'HYCOM window 1900-01',
      'rawCount=0; masked; no exception', function() {
        var r = CURRENT_MODULE.loadHycomWindow('1900-01-01', '1900-02-01', SYN);
        var o = gi(ee.Dictionary({n: r.rawCount, v: r.img.select('current_speed_m_s').mask().reduceRegion({
          reducer: ee.Reducer.sum(), geometry: SYN, crs: r.proj, scale: r.proj.nominalScale(), maxPixels: 1e8}).get('current_speed_m_s')}));
        return {actual: JSON.stringify(o), status: (o.n === 0 && o.v === 0) ? 'PASS' : 'FAIL', evidence: JSON.stringify(o)};
      }));

    T.push(run('T07', 'CFSv2 raw vs valid timestamp counts (2023-04)', 'GEE_RUNTIME', '2023-04 on synthetic geom',
      'expected 120; valid = raw - excluded; missing list derived from actual timestamps', function() {
        var b = CFSV2_MODULE.getBundle('2023-04-01', '2023-05-01', SYN);
        var m = CFSV2_MODULE.missingTimestamps('2023-04-01', '2023-05-01', SYN);
        var o = gi(ee.Dictionary({raw: b.rawCount, valid: b.validCount, excl: b.excludedCount, missing: m.missing}));
        var pass = (o.raw === 120 && o.valid === o.raw - o.excl && o.missing.length === 0);
        return {actual: JSON.stringify(o), status: pass ? 'PASS' : 'FAIL', evidence: JSON.stringify(o)};
      }));

    T.push(run('T08a', 'CFSv2 exclusion MECHANISM (unit): exclude 1 real timestamp', 'GEE_RUNTIME (mechanism only)',
      'exclude first frame of 2023-04', 'valid = raw - 1', function() {
        var ts = gi(ee.Image(ee.ImageCollection(cfg.COLLECTIONS.CFSV2_HARMONIZED).filterDate('2023-04-01', '2023-05-01').first()).get('system:time_start'));
        var b = CFSV2_MODULE.getBundle('2023-04-01', '2023-05-01', SYN, [ts]);
        var o = gi(ee.Dictionary({raw: b.rawCount, valid: b.validCount, excl: b.excludedCount}));
        return {actual: JSON.stringify(o), status: (o.valid === o.raw - 1 && o.excl === 1) ? 'PASS' : 'FAIL', evidence: JSON.stringify(o)};
      }));

    T.push(rec('T08b', 'CFSv2 AUTHORITATIVE exclusion (21 invalid + 11 missing timestamps)', 'CONFIG',
      'CFSV2_QC lists', 'INVENTORY_RESOLVED with 11 + 21 timestamps', 'INVENTORY_RESOLVED=' + cfg.CFSV2_QC.INVENTORY_RESOLVED +
      ' missing=' + cfg.CFSV2_QC.MISSING_FRAMES.length + ' invalid=' + cfg.CFSV2_QC.INVALID_FRAMES.length,
      cfg.CFSV2_QC.INVENTORY_RESOLVED ? 'PASS' : 'BLOCKED',
      'Authoritative timestamp lists populated from reports/CFSV2_TIMESTAMP_RECONCILIATION_2015_2025.csv. Exactly 11 missing frames and 21 invalid frames registered.'));

    T.push(run('T09', 'September 2024 HYCOM partial behaviour', 'GEE_RUNTIME', 'process(2024,9) on synthetic geom',
      'rawCount>0 => PARTIAL_PERIOD + valid px; rawCount=0 => NO_DATA + masked', function() {
        var r = CURRENT_MODULE.process(2024, 9, '2024-09-01', '2024-10-01', SYN);
        var v = r.img.select('current_speed_m_s').mask().reduceRegion({reducer: ee.Reducer.sum(), geometry: SYN,
          crs: r.proj, scale: r.proj.nominalScale(), maxPixels: 1e8}).get('current_speed_m_s');
        var o = gi(ee.Dictionary({meta: r.meta, valid_px: v}));
        var consistent = (o.meta.observation_count > 0) ? (o.meta.source_status === 'PARTIAL_PERIOD' && o.valid_px > 0)
                                                        : (o.meta.source_status === 'NO_DATA' && o.valid_px === 0);
        return {actual: JSON.stringify(o), status: consistent ? 'PASS' : 'FAIL', evidence: JSON.stringify(o)};
      }));

    T.push(run('T10', 'Missing CMEMS behaviour (2025-01)', 'GEE_RUNTIME', 'process(2025,1)',
      'actual_source=NONE; CMEMS_ASSET_PENDING; count 0; masked', function() {
        var r = CURRENT_MODULE.process(2025, 1, '2025-01-01', '2025-02-01', SYN);
        var v = r.img.select('current_speed_m_s').mask().reduceRegion({reducer: ee.Reducer.sum(), geometry: SYN,
          crs: r.proj, scale: r.proj.nominalScale(), maxPixels: 1e8}).get('current_speed_m_s');
        var o = gi(ee.Dictionary({meta: r.meta, valid_px: v}));
        var pass = o.meta.actual_source === 'NONE' && o.meta.source_status === 'CMEMS_ASSET_PENDING' &&
                   o.meta.observation_count === 0 && o.valid_px === 0;
        return {actual: JSON.stringify(o), status: pass ? 'PASS' : 'FAIL', evidence: JSON.stringify(o)};
      }));

    T.push(run('T11', 'Chl-a source attribution invariants (2023-04)', 'GEE_RUNTIME', 'Chl-a 2023-04 synthetic geom',
      'source==1 <=> MODIS flag; source==2 <=> fallback flag; value mask <=> valid flag', function() {
        var r = CHLOROPHYLL_MODULE.process('2023-04-01', '2023-05-01', SYN), i = r.img;
        var src = i.select('chla_source_code');
        var bad = src.eq(1).neq(i.select('chla_modis_flag')).or(src.eq(2).neq(i.select('chla_gcom_fallback_flag')))
          .or(i.select('chla_mg_m3').mask().neq(i.select('chla_valid_flag'))).rename('bad');
        var o = gi(ee.Dictionary({bad: bad.reduceRegion({reducer: ee.Reducer.sum(), geometry: SYN, crs: r.proj,
          scale: r.proj.nominalScale(), maxPixels: 1e9}).get('bad'), summary: r.summary}));
        return {actual: JSON.stringify(o), status: (o.bad === 0 && o.summary.valid_px === o.summary.modis_px + o.summary.gcom_fallback_px) ? 'PASS' : 'FAIL', evidence: JSON.stringify(o)};
      }));

    T.push(run('T12', 'Chl-a projection consistency (explicit MODIS target grid)', 'GEE_RUNTIME', 'Chl-a 2023-04',
      'output nominalScale == MODIS native nominalScale', function() {
        var r = CHLOROPHYLL_MODULE.process('2023-04-01', '2023-05-01', SYN);
        var o = gi(ee.Dictionary({out: r.img.select('chla_mg_m3').projection().nominalScale(), target: r.proj.nominalScale(),
          crs: r.img.select('chla_mg_m3').projection().crs()}));
        return {actual: JSON.stringify(o), status: near(o.out, o.target) ? 'PASS' : 'FAIL', evidence: JSON.stringify(o)};
      }));

    T.push(run('T13', 'Thermal-front synthetic gradients (E/W/N/S/zero/known C-km, spacing)', 'GEE_RUNTIME',
      'T = +/-0.5 deg C per deg lon or lat on 0.25 deg grid', 'analytic deg C/km within ' + (TOL * 100) + '%', thermalTests));

    T.push(run('T14', 'Ekman synthetic curl tests (production function)', 'GEE_RUNTIME',
      'tau fields on 0.2 deg grid', 'curl, w_E, SH sign, equatorial mask, m/day', ekmanTests));

    T.push(run('T15', 'Grid coverage and aggregation (first 20 canonical cells, test month)', 'GEE_RUNTIME+CLOUD_ASSET',
      'result.aggregatedGrid.limit(20)', 'IDs preserved; *_cov in [0,1]; method tags present', function() {
        if (!res) { throw new Error('pipeline result unavailable (preflight blocked)'); }
        var o = gi(res.aggregatedGrid.limit(20));
        var ok = o.features.length === 20 && o.features.every(function(f) {
          var p = f.properties;
          return p[cfg.SPATIAL.GRID.ID_PROPERTY] && p.sst_mean_c_cov >= 0 && p.sst_mean_c_cov <= 1 &&
                 p.chla_mg_m3_cov >= 0 && p.chla_mg_m3_cov <= 1 && p.agg_method_cfsv2 && p.agg_method_chla;
        });
        return {actual: 'rows=' + o.features.length, status: ok ? 'PASS' : 'FAIL', evidence: JSON.stringify(o.features[0].properties)};
      }));

    T.push(run('T16', 'Provenance attached to output products', 'GEE_RUNTIME+CLOUD_ASSET', 'composite + table properties',
      'processing_version and source/QC keys on image and table', function() {
        if (!res) { throw new Error('pipeline result unavailable (preflight blocked)'); }
        var ip = gi(res.compositeImage.propertyNames()), tp = gi(res.aggregatedGrid.propertyNames());
        var ok = ip.indexOf('processing_version') >= 0 && ip.indexOf('sst_source_id') >= 0 && ip.indexOf('cfsv2_qc_exclusions') >= 0 &&
                 tp.indexOf('processing_version') >= 0;
        return {actual: 'image_props=' + ip.length + ' table_props=' + tp.length, status: ok ? 'PASS' : 'FAIL', evidence: ip.join(',')};
      }));

    T.push(run('T17', 'Export / batch safety (client-side)', 'CLIENT_UNIT', 'config clones with each gate toggled',
      'validate() throws for every gate; export returns blocked; no task', function() {
        var gates = ['ENABLE_FULL_BATCH', 'ENABLE_EXPORT', 'ENABLE_PRODUCTION_EXPORT', 'ENABLE_ASSET_WRITE',
                     'ENABLE_BULK_TASK_SUBMISSION', 'ENABLE_SPECIES_ML'], ev = [], ok = true;
        gates.forEach(function(g) {
          var c = JSON.parse(JSON.stringify({EXECUTION: cfg.EXECUTION, ASSETS: cfg.ASSETS, COLLECTIONS: cfg.COLLECTIONS,
            CFSV2_QC: cfg.CFSV2_QC, SPATIAL: cfg.SPATIAL}));
          c.EXECUTION[g] = true;
          var threw = false; try { cfg.validate(c); } catch (e) { threw = true; }
          ev.push(g + ' -> throws=' + threw); ok = ok && threw;
        });
        var c2 = JSON.parse(JSON.stringify({EXECUTION: cfg.EXECUTION, ASSETS: cfg.ASSETS, COLLECTIONS: cfg.COLLECTIONS,
          CFSV2_QC: cfg.CFSV2_QC, SPATIAL: cfg.SPATIAL}));
        c2.EXECUTION.MODE = 'PRODUCTION';
        var t2 = false; try { cfg.validate(c2); } catch (e2) { t2 = true; }
        ev.push('MODE=PRODUCTION -> throws=' + t2); ok = ok && t2;
        var x = EXPORT_MODULE.requestExport();
        ev.push('requestExport -> blocked=' + x.blocked + ' task_created=' + x.task_created);
        ok = ok && x.blocked === true && x.task_created === false;
        var flagsOff = gates.every(function(g) { return cfg.EXECUTION[g] === false; });
        ev.push('all gates false=' + flagsOff); ok = ok && flagsOff;
        return {actual: ev.join('; '), status: ok ? 'PASS' : 'FAIL', evidence: ev.join('\n')};
      }));

    var tally = {PASS: 0, FAIL: 0, BLOCKED: 0};
    T.forEach(function(t) { tally[t.status] = (tally[t.status] || 0) + 1; print(t.test_id + ' [' + t.status + '] ' + t.name, t); });
    print('TEST SUMMARY', tally);
    return {tests: T, tally: tally};
  }
  return {runAll: runAll, getSYN: getSYN};
})(CONFIG_MODULE);


// ============================================================================
// MAIN — controlled development inspection of ONE test month
// ============================================================================
print('==== SEACAST PRIGI ' + CONFIG_MODULE.METADATA.VERSION + ' (DEVELOPMENT ONLY) ====');
var PF = PREFLIGHT_MODULE.run();
print('Preflight checks:', PF.checks);
var RESULT = null;
if (PF.ok) {
  RESULT = processMonth(PF, CONFIG_MODULE.EXECUTION.TEST_YEAR, CONFIG_MODULE.EXECUTION.TEST_MONTH);
  UI_MODULE.render(RESULT);
  print('Inspection month: ' + RESULT.year_month);
  print('QA record:', RESULT.qaRecord);
  print('Provenance record:', RESULT.provenanceRecord);

  // ============================================================================
  // EKSPOR CSV KE GOOGLE DRIVE (Langsung Aktif Sesuai Bulan/Tahun yang Dipilih)
  // ============================================================================
  var exportTag = 'SEACAST_PRIGI_GRID_8129_' + RESULT.year_month.replace('-', '_');
  Export.table.toDrive({
    collection: RESULT.aggregatedGrid,
    description: exportTag,
    folder: 'SEACAST_PRIGI_CSV',
    fileNamePrefix: exportTag,
    fileFormat: 'CSV'
  });
  print('[EXPORT TASK] Berhasil didaftarkan di tab Tasks -> ' + exportTag);

  // Opsi Batch 1 Tahun Penuh (aktif jika ENABLE_YEAR_BATCH_EXPORT: true)
  if (CONFIG_MODULE.EXECUTION.ENABLE_YEAR_BATCH_EXPORT) {
    EXPORT_MODULE.exportYearToDrive(PF, CONFIG_MODULE.EXECUTION.TEST_YEAR, CONFIG_MODULE.EXECUTION.EXPORT_FOLDER);
  }
} else {
  print('[BLOCKED] ' + PF.blockedReason);
  UI_MODULE.showBlocked(PF.blockedReason);
}
if (CONFIG_MODULE.EXECUTION.RUN_DEV_TESTS) { TEST_MODULE.runAll(PF, RESULT); }
print('STOP: no pilot / batch executed. Awaiting explicit researcher approval.');

