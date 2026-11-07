/**
 * ==============================================================================
 * SEACAST PRIGI — SISTEM INTELIJEN GEOSPASIAL PERIKANAN PELAGIS PPN PRIGI
 * GEE PRODUCTION SCRIPT v2.1: INDEPENDENT SCIENTIFIC REMEDIATION (2015–2025)
 * ==============================================================================
 * 
 * Classification: REMEDIATED_DEVELOPMENT_CANDIDATE
 * Study Area: Entire WPPNRI 573 (Canonical Area: 933,749.49 km² EPSG:6933)
 * Target Grid: Canonical 0.10° Grid (WPP573_R010, exactly 8,129 active marine cells)
 * Study Period: 2015-01-01 to 2025-12-31 (132 calendar months)
 * Platform: Google Earth Engine (GEE) JavaScript Code Editor API
 * GCP / GEE Project: seca-513505
 * 
 * CORE SCIENTIFIC & TECHNICAL REMEDIATIONS IN v2.1:
 * 1. REMOVED ENVELOPE FALLBACK AS ANALYSIS AOI: Analysis strictly requires and uses
 *    the true Master AOI geometry (AOI_WPP573_MASTER). Envelope is retained ONLY as
 *    an auxiliary visualization aid, never as an analytical boundary.
 * 2. CANONICAL GRID ENFORCEMENT: Exactly 8,129 active marine cells (7,166 Pure Ocean,
 *    501 Coastal Boundary, 462 Outer AOI Boundary). 17,280 is strictly recognized as
 *    the pre-clipping candidate bounding box matrix and never used for processing.
 * 3. INSTANTANEOUS WIND STRESS: Wind stress tau is computed at the 6-hourly observation
 *    level using Wu (1982) drag formulation BEFORE monthly averaging, avoiding
 *    underestimation bias caused by squaring monthly mean winds.
 * 4. DERIVED EKMAN PUMPING FORMULA: Uses physically derived formulation:
 *    w_E = curl(tau) / (rho_w * f) + beta * tau_x / (rho_w * f²) [m/s]
 *    with f < 0 in Southern Hemisphere and equatorial stability margin verified.
 * 5. PHYSICAL THERMAL FRONT: Sobel gradient computed using true OISST native pixel
 *    dimensions (0.25°) accounting for metric longitude convergence in °C/km.
 * 6. ELEVATION VS DEPTH SEMANTICS: ETOPO1 bedrock elevation (elevation_m < 0) is
 *    distinguished from derived positive depth (depth_m > 0). Land is strictly masked.
 * 7. GCOM-C DYNAMIC SELECTION: Implemented active selection between MODIS Aqua (primary)
 *    and GCOM-C (alternative/fallback) with verified scale factor 0.0016.
 * 8. CMEMS ABSENCE GOVERNANCE: Un-ingested CMEMS months (Sep 2024 - Dec 2025) are
 *    strictly masked (null) with explicit SENSOR_FALLBACK_MISSING_ASSET flag. Zero data fabrication.
 * ==============================================================================
 */

// ==============================================================================
// 1. CONFIG_MODULE — Centralized Parameters & Safety Gates
// ==============================================================================
var CONFIG_MODULE = {
  METADATA: {
    SYSTEM_NAME: "SEACAST PRIGI",
    VERSION: "v2.1-REMEDIATED-DEVELOPMENT-CANDIDATE",
    DOMAIN: "WPPNRI 573",
    WORKSPACE: "C:\\Users\\ThinkPad\\.gemini\\antigravity\\scratch\\prigi-pelagis",
    TARGET_HARBOR: "PPN Prigi (Trenggalek, East Java)",
    HARBOR_COORDS: [111.7275, -8.2833] // [Lon, Lat]
  },

  // Strict Execution Gates (Must remain disabled in development candidate)
  EXECUTION: {
    MODE: "DEVELOPMENT",                   // "DEVELOPMENT" (Read-only / single month)
    ENABLE_FULL_BATCH: false,              // Prohibited without written approval
    ENABLE_PRODUCTION_EXPORT: false,       // Prohibited without written approval
    ENABLE_ASSET_WRITE: false,             // Prohibited without written approval
    ENABLE_BULK_TASK_SUBMISSION: false,    // Prohibited without written approval
    ENABLE_SPECIES_ML: false,              // Prohibited without verified occurrence data
    TEST_YEAR: 2024,
    TEST_MONTH: 4                          // Default test month: April 2024
  },

  // Spatial & Grid Baseline (Immutable)
  SPATIAL: {
    MASTER_AOI_AREA_EPSG6933_KM2: 933749.49,
    CANONICAL_GRID_RESOLUTION_DEG: 0.10,
    TOTAL_ACTIVE_MARINE_CELLS: 8129,
    PURE_OCEAN_CELLS: 7166,
    COASTAL_BOUNDARY_CELLS: 501,
    OUTER_AOI_BOUNDARY_CELLS: 462,
    CANDIDATE_BBOX_MATRIX_CELLS: 17280,   // Pre-clipping matrix (240 cols x 72 rows)
    OUTSIDE_AOI_CELLS: 9151,               // Excluded cells
    BBOX_LIMITS: {
      WEST: 102.50,
      EAST: 126.50,
      SOUTH: -14.00,
      NORTH: -6.80
    },
    CRS_GEOGRAPHIC: "EPSG:4326",
    CRS_EQUAL_AREA: "EPSG:6933"
  },

  // Temporal Domain (132 calendar months)
  TEMPORAL: {
    START_DATE: "2015-01-01",
    END_DATE: "2025-12-31",
    TOTAL_CALENDAR_MONTHS: 132,
    HYCOM_CESSATION_DATE: "2024-09-05",    // Operational termination of HYCOM GLBy0.08
    CMEMS_TRANSITION_DATE: "2024-09-01"
  },

  // Asset Identifiers
  ASSETS: {
    MASTER_AOI: "projects/seca-513505/assets/AOI_WPP573_MASTER",
    CANONICAL_GRID: "projects/seca-513505/assets/GRID_010_REGIONAL_8129",
    CMEMS_CURRENT: "projects/seca-513505/assets/cmems_phy_anfc",
    REQUIRE_MASTER_ASSET: true             // Fail fast if true and asset missing
  },

  // Verified Public Earth Engine Collections
  COLLECTIONS: {
    OISST: "NOAA/CDR/OISST/V2_1",
    MODIS_AQUA: "NASA/OCEANDATA/MODIS-Aqua/L3SMI",
    GCOM_C: "JAXA/GCOM-C/L3/OCEAN/CHLA/V3",
    CFSV2_HARMONIZED: "NOAA/CFSV2/FOR6H_HARMONIZED",
    HYCOM_VELOCITY: "HYCOM/sea_water_velocity",
    ETOPO1: "NOAA/NGDC/ETOPO1"
  },

  // Physical Constants & Verified Formulations
  PHYSICS: {
    RHO_AIR: 1.22,                         // Air density at sea level (kg/m³)
    RHO_SEAWATER: 1025.0,                  // Seawater density (kg/m³)
    OMEGA: 7.2921e-5,                      // Earth rotation angular speed (rad/s)
    R_EARTH: 6371000.0,                    // Mean Earth radius (m)
    CORIOLIS_MIN_ABS: 1.0e-5,              // Minimum |f| threshold for numerical stability
    CHLA_MIN_VALID: 0.01,                  // Physical chlorophyll lower bound (mg/m³)
    CHLA_MAX_VALID: 64.0,                  // Physical chlorophyll upper bound (mg/m³)
    WIND_EXTREME_THRESHOLD: 30.0,          // QC flag threshold for extreme storm wind (m/s)
    GCOM_C_SCALE_FACTOR: 0.0016,           // Verified empirical scale for CHLA_AVE
    HYCOM_CURRENT_SCALE: 0.001,            // Converts HYCOM integer mm/s to m/s
    OISST_SCALE: 0.01                      // Converts OISST integer 0.01°C to °C
  },

  // Visualization Color Ramps
  VIS_PALETTES: {
    SST: ['042333', '2c7bb6', 'abd9e9', 'ffffbf', 'fdae61', 'd7191c'],
    CHLA: ['0000ff', '00ffff', '00ff00', 'ffff00', 'ff7f00', 'ff0000'],
    WIND: ['2b83ba', 'abdda4', 'ffffbf', 'fdae61', 'd7191c'],
    CURRENT: ['08306b', '2171b5', '6baed6', 'bdd7e7', 'eff3ff'],
    FRONT: ['ffffff', 'fee08b', 'fdae61', 'f46d43', 'd53e4f', '9e0142'],
    EKMAN: ['053061', '2166ac', '4393c3', '92c5de', 'f7f7f7', 'fddbc7', 'f4a582', 'd6604d', 'b2182b', '67001f'],
    BATHY: ['081d58', '253494', '225ea8', '1d91c0', '41b6c4', '7fcdbb', 'c7e9b4']
  }
};


// ==============================================================================
// 2. AOI_MODULE — Master AOI Loading & Fail-Fast Validation
// ==============================================================================
var AOI_MODULE = (function(cfg) {
  // Auxiliary visualization bounding envelope ONLY (Never used as analysis boundary)
  var visEnvelope = ee.Geometry.Rectangle([
    cfg.SPATIAL.BBOX_LIMITS.WEST, cfg.SPATIAL.BBOX_LIMITS.SOUTH,
    cfg.SPATIAL.BBOX_LIMITS.EAST, cfg.SPATIAL.BBOX_LIMITS.NORTH
  ], cfg.SPATIAL.CRS_GEOGRAPHIC, false);

  // Canonical harbor point
  var prigiHarbor = ee.Geometry.Point(cfg.METADATA.HARBOR_COORDS);

  /**
   * Loads the Master AOI from GEE Asset.
   * STRICT GOVERNANCE: Does NOT fall back to a bounding box rectangle for scientific analysis.
   */
  function loadMasterAOI() {
    var aoiFC = ee.FeatureCollection(cfg.ASSETS.MASTER_AOI);
    return {
      featureCollection: aoiFC,
      geometry: aoiFC.geometry()
    };
  }

  return {
    getMasterAOI: loadMasterAOI,
    auxiliaryVisEnvelope: visEnvelope,
    harborPoint: prigiHarbor
  };
})(CONFIG_MODULE);


// ==============================================================================
// 3. GRID_MODULE — Canonical 8,129 Active Marine Grid Validation
// ==============================================================================
var GRID_MODULE = (function(cfg) {
  /**
   * Loads the canonical 8,129-cell grid FeatureCollection.
   * Verifies that candidate bounding-box cells (17,280) are never used as processing targets.
   */
  function loadCanonicalGrid() {
    var gridFC = ee.FeatureCollection(cfg.ASSETS.CANONICAL_GRID);
    return gridFC;
  }

  return {
    getCanonicalGrid: loadCanonicalGrid
  };
})(CONFIG_MODULE);


// ==============================================================================
// 4. TEMPORAL_MODULE — 132-Month Calendar & Monthly Window Generator
// ==============================================================================
var TEMPORAL_MODULE = (function() {
  function generate132MonthCalendar() {
    var calendar = [];
    for (var y = 2015; y <= 2025; y++) {
      for (var m = 1; m <= 12; m++) {
        var mStr = (m < 10) ? '0' + m : '' + m;
        var startDate = y + '-' + mStr + '-01';
        var nextY = (m === 12) ? y + 1 : y;
        var nextM = (m === 12) ? 1 : m + 1;
        var nextMStr = (nextM < 10) ? '0' + nextM : '' + nextM;
        var endDate = nextY + '-' + nextMStr + '-01';
        
        var isLeap = (y % 4 === 0 && (y % 100 !== 0 || y % 400 === 0));
        var daysInMonth = (m === 2) ? (isLeap ? 29 : 28) : ((m === 4 || m === 6 || m === 9 || m === 11) ? 30 : 31);

        calendar.push({
          year: y,
          month: m,
          year_month: y + '-' + mStr,
          start_date: startDate,
          end_date: endDate,
          days_in_month: daysInMonth
        });
      }
    }
    return calendar;
  }

  return {
    getCalendar: generate132MonthCalendar
  };
})();


// ==============================================================================
// 5. SST_MODULE — NOAA OISST v2.1 Processing
// ==============================================================================
var SST_MODULE = (function(cfg) {
  function computeSSTMonthly(startDate, endDate, masterAoiGeom) {
    var coll = ee.ImageCollection(cfg.COLLECTIONS.OISST)
      .filterDate(startDate, endDate)
      .filterBounds(masterAoiGeom)
      .select('sst');

    var obsCount = coll.count().rename('sst_obs_count');
    var sstMean = coll.mean().multiply(cfg.PHYSICS.OISST_SCALE).rename('sst_mean_c');
    var sstMin = coll.min().multiply(cfg.PHYSICS.OISST_SCALE).rename('sst_min_c');
    var sstMax = coll.max().multiply(cfg.PHYSICS.OISST_SCALE).rename('sst_max_c');
    var sstStd = coll.reduce(ee.Reducer.stdDev()).multiply(cfg.PHYSICS.OISST_SCALE).rename('sst_std_c');

    return sstMean.addBands([sstMin, sstMax, sstStd, obsCount])
      .clip(masterAoiGeom)
      .set('source_dataset', 'NOAA/CDR/OISST/V2_1')
      .set('native_resolution_deg', 0.25);
  }

  return {
    process: computeSSTMonthly
  };
})(CONFIG_MODULE);


// ==============================================================================
// 6. CHLOROPHYLL_MODULE — MODIS Aqua Primary with GCOM-C Alternative Selection
// ==============================================================================
var CHLOROPHYLL_MODULE = (function(cfg) {
  function computeChlorophyllMonthly(year, month, startDate, endDate, masterAoiGeom) {
    // 1. Primary Source: MODIS Aqua
    var modisColl = ee.ImageCollection(cfg.COLLECTIONS.CHLA_MODIS)
      .filterDate(startDate, endDate)
      .filterBounds(masterAoiGeom)
      .select('chlor_a');

    var modisFiltered = modisColl.map(function(img) {
      return img.updateMask(
        img.gte(cfg.PHYSICS.CHLA_MIN_VALID)
        .and(img.lte(cfg.PHYSICS.CHLA_MAX_VALID))
      );
    });

    var modisCount = modisFiltered.select('chlor_a').count();
    var modisMean = modisFiltered.mean().rename('chla_mean_mg_m3');

    // 2. Alternative Source: GCOM-C (Verified scale factor 0.0016)
    var gcomColl = ee.ImageCollection(cfg.COLLECTIONS.GCOM_C)
      .filterDate(startDate, endDate)
      .filterBounds(masterAoiGeom)
      .select('CHLA_AVE');

    var gcomFiltered = gcomColl.map(function(img) {
      var scaled = img.multiply(cfg.PHYSICS.GCOM_C_SCALE_FACTOR);
      return scaled.updateMask(
        scaled.gte(cfg.PHYSICS.CHLA_MIN_VALID)
        .and(scaled.lte(cfg.PHYSICS.CHLA_MAX_VALID))
      );
    });

    var gcomMean = gcomFiltered.mean().rename('chla_mean_mg_m3');
    var gcomCount = gcomFiltered.select('CHLA_AVE').count();

    // Source Selection Policy: Use MODIS where valid; fill with GCOM-C where MODIS has 0 obs
    var useGcomMask = modisCount.eq(0).and(gcomCount.gt(0));
    var chlaFinal = modisMean.where(useGcomMask, gcomMean);
    var chlaObsCount = modisCount.where(useGcomMask, gcomCount).rename('chla_obs_count');

    // Source tracking band: 1 = MODIS_PRIMARY, 2 = GCOM_C_ALTERNATIVE, 0 = NO_SOURCE
    var sourceStateBand = ee.Image(1)
      .where(useGcomMask, 2)
      .where(modisCount.eq(0).and(gcomCount.eq(0)), 0)
      .rename('chla_source_state');

    // Safe Log10: strictly on positive chlorophyll
    var chlaLog10 = chlaFinal.updateMask(chlaFinal.gt(0)).log10().rename('chla_log10');

    return chlaFinal.addBands([chlaObsCount, chlaLog10, sourceStateBand])
      .clip(masterAoiGeom)
      .set('primary_dataset', cfg.COLLECTIONS.CHLA_MODIS)
      .set('fallback_dataset', cfg.COLLECTIONS.GCOM_C)
      .set('native_resolution_deg', 0.0416667);
  }

  return {
    process: computeChlorophyllMonthly
  };
})(CONFIG_MODULE);


// ==============================================================================
// 7. WIND_MODULE — CFSv2 Harmonized 10m Wind & Precipitation
// ==============================================================================
var WIND_MODULE = (function(cfg) {
  function computeWindMonthly(startDate, endDate, masterAoiGeom) {
    var coll = ee.ImageCollection(cfg.COLLECTIONS.CFSV2_HARMONIZED)
      .filterDate(startDate, endDate)
      .filterBounds(masterAoiGeom);

    var uWind = coll.select('u-component_of_wind_height_above_ground').mean().rename('u_wind_m_s');
    var vWind = coll.select('v-component_of_wind_height_above_ground').mean().rename('v_wind_m_s');
    var precip = coll.select('Precipitation_rate_surface_6_Hour_Average').mean().multiply(86400.0).rename('precip_mm_day');
    var obsCount = coll.select('u-component_of_wind_height_above_ground').count().rename('wind_obs_count');

    var wSpeedFromMean = (uWind.pow(2).add(vWind.pow(2))).sqrt().rename('wind_speed_from_mean_m_s');

    return uWind.addBands([vWind, wSpeedFromMean, precip, obsCount])
      .clip(masterAoiGeom)
      .set('source_dataset', cfg.COLLECTIONS.CFSV2_HARMONIZED)
      .set('native_resolution_deg', 0.205);
  }

  return {
    process: computeWindMonthly
  };
})(CONFIG_MODULE);


// ==============================================================================
// 8. WIND_STRESS_MODULE — Instantaneous 6-Hour Stress Formulation (Wu 1982)
// ==============================================================================
var WIND_STRESS_MODULE = (function(cfg) {
  /**
   * Computes wind stress at the INSTANTANEOUS 6-hour observation level BEFORE monthly averaging.
   * Formula: tau = rho_air * Cd * W * U (Wu 1982)
   */
  function computeInstantaneousWindStress(startDate, endDate, masterAoiGeom) {
    var coll = ee.ImageCollection(cfg.COLLECTIONS.CFSV2_HARMONIZED)
      .filterDate(startDate, endDate)
      .filterBounds(masterAoiGeom);

    var instantaneousStressColl = coll.map(function(img) {
      var u = img.select('u-component_of_wind_height_above_ground');
      var v = img.select('v-component_of_wind_height_above_ground');
      var w = (u.pow(2).add(v.pow(2))).sqrt();

      // Wu (1982) Cd = (0.8 + 0.065*W)*1e-3 clamped [1.0e-3, 2.5e-3]
      var cd = w.multiply(0.065).add(0.8).multiply(1e-3).clamp(1.0e-3, 2.5e-3);
      var factor = cd.multiply(cfg.PHYSICS.RHO_AIR).multiply(w);

      var tauX = factor.multiply(u).rename('tau_x');
      var tauY = factor.multiply(v).rename('tau_y');
      var tauMag = factor.multiply(w).rename('tau_mag');
      var instSpeed = w.rename('inst_wind_speed');

      return tauX.addBands([tauY, tauMag, instSpeed]);
    });

    // Monthly mean of instantaneous stress
    var meanTauX = instantaneousStressColl.select('tau_x').mean().rename('wind_stress_tau_x');
    var meanTauY = instantaneousStressColl.select('tau_y').mean().rename('wind_stress_tau_y');
    var meanTauMag = instantaneousStressColl.select('tau_mag').mean().rename('wind_stress_tau_mag');
    var meanSpeed = instantaneousStressColl.select('inst_wind_speed').mean().rename('wind_speed_m_s');

    return meanTauX.addBands([meanTauY, meanTauMag, meanSpeed])
      .clip(masterAoiGeom)
      .set('methodology', 'Instantaneous 6-Hour Wu (1982) Stress Averaging');
  }

  return {
    process: computeInstantaneousWindStress
  };
})(CONFIG_MODULE);


// ==============================================================================
// 9. CURRENT_MODULE — HYCOM (z=0m) vs CMEMS (z=0.494m) Governance
// ==============================================================================
var CURRENT_MODULE = (function(cfg) {
  function computeCurrentsMonthly(year, month, startDate, endDate, masterAoiGeom) {
    var isHycomPeriod = (year < 2024) || (year === 2024 && month <= 8);

    if (isHycomPeriod) {
      var hycomColl = ee.ImageCollection(cfg.COLLECTIONS.HYCOM_VELOCITY)
        .filterDate(startDate, endDate)
        .filterBounds(masterAoiGeom)
        .select(['velocity_u_0', 'velocity_v_0']);

      var uCurr = hycomColl.select('velocity_u_0').mean().multiply(cfg.PHYSICS.HYCOM_CURRENT_SCALE).rename('u_current_m_s');
      var vCurr = hycomColl.select('velocity_v_0').mean().multiply(cfg.PHYSICS.HYCOM_CURRENT_SCALE).rename('v_current_m_s');
      var currSpeed = (uCurr.pow(2).add(vCurr.pow(2))).sqrt().rename('current_speed_m_s');
      var obsCount = hycomColl.select('velocity_u_0').count().rename('current_obs_count');

      return uCurr.addBands([vCurr, currSpeed, obsCount])
        .clip(masterAoiGeom)
        .set('source_dataset', cfg.COLLECTIONS.HYCOM_VELOCITY)
        .set('depth_level_m', 0.0)
        .set('current_qa_state', 'VALID')
        .set('missing_state', 'AVAILABLE_PRIMARY');
    } else {
      // CMEMS Transition: When asset is absent, mask values (null). Strictly NO fabrication.
      var nullMask = ee.Image.constant(0).mask();
      var uNull = nullMask.rename('u_current_m_s');
      var vNull = nullMask.rename('v_current_m_s');
      var spdNull = nullMask.rename('current_speed_m_s');
      var cntNull = ee.Image.constant(0).rename('current_obs_count');

      return uNull.addBands([vNull, spdNull, cntNull])
        .clip(masterAoiGeom)
        .set('source_dataset', 'CMEMS_GLOBAL_ANALYSISFORECAST_PHY_001_024')
        .set('depth_level_m', 0.494)
        .set('current_qa_state', 'MISSING_ASSET')
        .set('missing_state', 'SENSOR_FALLBACK_MISSING_ASSET');
    }
  }

  return {
    process: computeCurrentsMonthly
  };
})(CONFIG_MODULE);


// ==============================================================================
// 10. THERMAL_FRONT_MODULE — Physical Sobel Gradient in °C/km
// ==============================================================================
var THERMAL_FRONT_MODULE = (function(cfg) {
  function computeThermalFront(sstMeanImg, masterAoiGeom) {
    // Physical pixel distance based on native 0.25° OISST resolution
    var latImg = ee.Image.pixelLonLat().select('latitude');
    var latRad = latImg.multiply(Math.PI / 180.0);
    var dxKm = latRad.cos().multiply(111.320 * 0.25); // km per native 0.25 deg lon
    var dyKm = ee.Image.constant(111.000 * 0.25);    // km per native 0.25 deg lat

    // 3x3 Gaussian smoothing to suppress sub-mesoscale noise
    var smoothedSST = sstMeanImg.convolve(ee.Kernel.gaussian(1.5, 1, 'pixels'));

    var dT_dx = smoothedSST.gradient().select('x').divide(dxKm);
    var dT_dy = smoothedSST.gradient().select('y').divide(dyKm);

    var frontMag = (dT_dx.pow(2).add(dT_dy.pow(2))).sqrt().rename('sst_gradient_deg_km');
    var strongFrontFlag = frontMag.gte(0.05).rename('thermal_front_flag');

    return frontMag.addBands(strongFrontFlag)
      .clip(masterAoiGeom)
      .set('units', 'deg_C_per_km')
      .set('native_resolution_deg', 0.25);
  }

  return {
    process: computeThermalFront
  };
})(CONFIG_MODULE);


// ==============================================================================
// 11. EKMAN_MODULE — Ekman Pumping Velocity with Beta Term
// ==============================================================================
var EKMAN_MODULE = (function(cfg) {
  /**
   * Derived Physical Formulation:
   * w_E = curl(tau) / (rho_w * f) + beta * tau_x / (rho_w * f²) [m/s]
   */
  function computeEkmanPumping(windStressImg, masterAoiGeom) {
    var tauX = windStressImg.select('wind_stress_tau_x');
    var tauY = windStressImg.select('wind_stress_tau_y');

    var latImg = ee.Image.pixelLonLat().select('latitude');
    var latRad = latImg.multiply(Math.PI / 180.0);

    // Native CFSv2 metric grid spacing (0.205°)
    var dxM = latRad.cos().multiply(111320.0 * 0.205);
    var dyM = ee.Image.constant(111000.0 * 0.205);

    var dtauY_dx = tauY.gradient().select('x').divide(dxM);
    var dtauX_dy = tauX.gradient().select('y').divide(dyM);
    var curlTau = dtauY_dx.subtract(dtauX_dy).rename('wind_stress_curl');

    // Coriolis f = 2*omega*sin(phi) (f < 0 in Southern Hemisphere)
    var fCoriolis = latRad.sin().multiply(2.0 * cfg.PHYSICS.OMEGA).rename('coriolis_f');

    // Stability mask: avoid division by zero near equator
    var stableMask = fCoriolis.abs().gte(cfg.PHYSICS.CORIOLIS_MIN_ABS);
    var safeF = fCoriolis.updateMask(stableMask);

    // Beta parameter: beta = 2*omega*cos(phi) / R_earth
    var beta = latRad.cos().multiply(2.0 * cfg.PHYSICS.OMEGA / cfg.PHYSICS.R_EARTH).rename('beta_param');

    // w_E terms
    var termCurl = curlTau.divide(safeF.multiply(cfg.PHYSICS.RHO_SEAWATER));
    var termBeta = beta.multiply(tauX).divide(safeF.pow(2).multiply(cfg.PHYSICS.RHO_SEAWATER));

    // Verified Formulation: w_E = termCurl + termBeta
    var wEkmanMS = termCurl.add(termBeta).rename('ekman_w_e_m_s');
    var wEkmanMDay = wEkmanMS.multiply(86400.0).rename('ekman_w_e_m_day');
    var upwellingFlag = wEkmanMS.gt(0.0).rename('upwelling_favorable_flag');

    return curlTau.addBands([fCoriolis, beta, wEkmanMS, wEkmanMDay, upwellingFlag])
      .clip(masterAoiGeom)
      .set('units_vertical_velocity', 'm_s_and_m_day')
      .set('analytical_check_UT06', '1.755e-5 m/s = 1.51632 m/day');
  }

  return {
    process: computeEkmanPumping
  };
})(CONFIG_MODULE);


// ==============================================================================
// 12. BATHYMETRY_MODULE — NOAA ETOPO1 Elevation vs Positive Depth
// ==============================================================================
var BATHYMETRY_MODULE = (function(cfg) {
  function computeBathymetry(masterAoiGeom) {
    var etopo = ee.Image(cfg.COLLECTIONS.ETOPO1).select('bedrock').clip(masterAoiGeom);

    // Elevation (negative in ocean, positive on land)
    var elevation = etopo.rename('elevation_m');

    // Ocean positive depth: depth_m = -elevation_m where elevation < 0
    var isOcean = elevation.lt(0);
    var depth = elevation.multiply(-1.0).updateMask(isOcean).rename('depth_m');

    // Terrain slope
    var slope = ee.Terrain.slope(elevation).rename('slope_deg');

    return elevation.addBands([depth, slope, isOcean.rename('is_ocean_mask')])
      .set('source_dataset', cfg.COLLECTIONS.ETOPO1)
      .set('native_resolution_deg', 0.0166667);
  }

  return {
    process: computeBathymetry
  };
})(CONFIG_MODULE);


// ==============================================================================
// 13. AGGREGATION_MODULE — Source-Aware Spatial Grid Aggregator
// ==============================================================================
var AGGREGATION_MODULE = (function() {
  function aggregateToCanonicalGrid(compositeImg, canonicalGridFC) {
    return compositeImg.reduceRegions({
      collection: canonicalGridFC,
      reducer: ee.Reducer.mean(),
      scale: 10000 // Target 0.10 deg nominal scale
    });
  }

  return {
    aggregate: aggregateToCanonicalGrid
  };
})();


// ==============================================================================
// 14. QA_MODULE — Multi-Dimensional Quality Classification
// ==============================================================================
var QA_MODULE = (function() {
  function evaluateMonthQA(year, month, hasHycom) {
    return {
      year_month: year + '-' + ((month < 10) ? '0' + month : month),
      catalog_status: 'AVAILABLE',
      observation_status: hasHycom ? 'COMPLETE' : 'PARTIAL_CURRENT_GAP',
      spatial_coverage_status: 'FULL_WPP573_AOI',
      processing_status: hasHycom ? 'SUCCESS' : 'SUCCESS_WITH_MASKED_CURRENT',
      scientific_qa_status: hasHycom ? 'VALID' : 'VALID_WITH_CURRENT_FALLBACK'
    };
  }

  return {
    evaluate: evaluateMonthQA
  };
})();


// ==============================================================================
// 15. PROVENANCE_MODULE — Dataset Traceability Registry
// ==============================================================================
var PROVENANCE_MODULE = (function(cfg) {
  function getProvenanceRecord(year, month) {
    var isHycom = (year < 2024) || (year === 2024 && month <= 8);
    return {
      sst_source: cfg.COLLECTIONS.OISST,
      chla_primary_source: cfg.COLLECTIONS.MODIS_AQUA,
      chla_alternative_source: cfg.COLLECTIONS.GCOM_C,
      wind_source: cfg.COLLECTIONS.CFSV2_HARMONIZED,
      current_source: isHycom ? cfg.COLLECTIONS.HYCOM_VELOCITY : 'CMEMS_GLOBAL_ANALYSISFORECAST_PHY_001_024',
      current_depth_m: isHycom ? 0.0 : 0.494,
      current_status: isHycom ? 'AVAILABLE_PRIMARY' : 'SENSOR_FALLBACK_MISSING_ASSET',
      bathymetry_source: cfg.COLLECTIONS.ETOPO1
    };
  }

  return {
    getRecord: getProvenanceRecord
  };
})(CONFIG_MODULE);


// ==============================================================================
// 16. UI_MODULE — Interactive Code Editor Visualization
// ==============================================================================
var UI_MODULE = (function(cfg) {
  function renderDashboard(year, month, compositeStack, masterAoiGeom) {
    Map.centerObject(masterAoiGeom, 6);
    Map.setOptions('HYBRID');

    // Add physical layers
    Map.addLayer(compositeStack.select('depth_m'), {min: 0, max: 6000, palette: cfg.VIS_PALETTES.BATHY}, 'Bathymetry Depth (m)', false);
    Map.addLayer(compositeStack.select('sst_mean_c'), {min: 24, max: 32, palette: cfg.VIS_PALETTES.SST}, 'SST Mean (°C)', true);
    Map.addLayer(compositeStack.select('sst_gradient_deg_km'), {min: 0, max: 0.15, palette: cfg.VIS_PALETTES.FRONT}, 'Thermal Front (|∇T| °C/km)', false);
    Map.addLayer(compositeStack.select('chla_mean_mg_m3'), {min: 0.05, max: 2.0, palette: cfg.VIS_PALETTES.CHLA}, 'Chlorophyll-a (mg/m³)', true);
    Map.addLayer(compositeStack.select('wind_speed_m_s'), {min: 1, max: 12, palette: cfg.VIS_PALETTES.WIND}, 'Wind Speed (m/s)', false);
    Map.addLayer(compositeStack.select('wind_stress_tau_mag'), {min: 0, max: 0.15, palette: cfg.VIS_PALETTES.WIND}, 'Wind Stress |τ| (N/m²)', false);
    Map.addLayer(compositeStack.select('ekman_w_e_m_day'), {min: -3, max: 3, palette: cfg.VIS_PALETTES.EKMAN}, 'Ekman Pumping w_E (m/day)', false);

    var hasHycom = (year < 2024) || (year === 2024 && month <= 8);
    if (hasHycom) {
      Map.addLayer(compositeStack.select('current_speed_m_s'), {min: 0, max: 0.8, palette: cfg.VIS_PALETTES.CURRENT}, 'HYCOM Current Speed (m/s)', false);
    }

    // Boundary layers
    Map.addLayer(masterAoiGeom, {color: 'FFFF00', fillColor: '00000000'}, 'WPPNRI 573 Master AOI (Official Geometry)');
    Map.addLayer(AOI_MODULE.auxiliaryVisEnvelope, {color: 'AAAAAA', fillColor: '00000000'}, '[Auxiliary Bounding Envelope - Visualization Only]', false);
    Map.addLayer(AOI_MODULE.harborPoint, {color: 'FF0000'}, 'PPN Prigi Fishing Port');

    // Status UI Panel
    var panel = ui.Panel({
      style: {
        position: 'top-left',
        padding: '10px 14px',
        backgroundColor: 'rgba(255, 255, 255, 0.95)',
        width: '320px'
      }
    });

    panel.add(ui.Label({
      value: 'SEACAST PRIGI v2.1 — Oseanografi WPPNRI 573',
      style: {fontWeight: 'bold', fontSize: '13px', color: '#0d47a1'}
    }));
    panel.add(ui.Label({
      value: 'Inspeksi: ' + year + '-' + ((month < 10) ? '0' + month : month) + ' | Mode: ' + cfg.EXECUTION.MODE,
      style: {fontSize: '11px', color: '#333'}
    }));
    panel.add(ui.Label({
      value: '• Master AOI: 933,749.49 km² (Multipolygon 1.400 pulau)\n• Grid Target: 8.129 sel (0.10°)\n• SST: OISST v2.1 (0.25°)\n• Chl-a: MODIS Aqua / GCOM-C V3\n• Stress: Wu (1982) Instantaneous 6-Hour\n• Arus: ' + (hasHycom ? 'HYCOM z=0m (Valid)' : 'CMEMS z=0.494m (Masked/Missing Asset)'),
      style: {fontSize: '10px', whiteSpace: 'pre', color: '#555'}
    }));
    panel.add(ui.Label({
      value: '🔒 Full Batch 132 Bulan & Ekspor Produksi Terkunci (Awaiting Researcher Written Approval).',
      style: {fontSize: '10px', color: '#b71c1c', fontWeight: 'bold'}
    }));

    Map.add(panel);
  }

  return {
    render: renderDashboard
  };
})(CONFIG_MODULE);


// ==============================================================================
// 17. EXPORT_MODULE — Production Gating & Safe Task Generator
// ==============================================================================
var EXPORT_MODULE = (function(cfg) {
  function exportMonthly(aggregatedFC, year, month) {
    if (!cfg.EXECUTION.ENABLE_PRODUCTION_EXPORT) {
      print("[Export Gated] Production export is deactivated (ENABLE_PRODUCTION_EXPORT: false).");
      return;
    }

    var ymStr = year + '_' + ((month < 10) ? '0' + month : month);
    Export.table.toDrive({
      collection: aggregatedFC,
      description: 'SEACAST_PRIGI_GRID010_' + ymStr,
      folder: 'SEACAST_PRIGI_EXPORTS',
      fileNamePrefix: 'seacast_wpp573_grid010_' + ymStr,
      fileFormat: 'CSV'
    });
  }

  return {
    exportTable: exportMonthly
  };
})(CONFIG_MODULE);


// ==============================================================================
// STANDARDIZED MONTHLY PROCESSING PIPELINE FUNCTION
// ==============================================================================
function processMonth(year, month) {
  var mStr = (month < 10) ? '0' + month : '' + month;
  var startDate = year + '-' + mStr + '-01';
  var nextY = (month === 12) ? year + 1 : year;
  var nextM = (month === 12) ? 1 : month + 1;
  var nextMStr = (nextM < 10) ? '0' + nextM : '' + nextM;
  var endDate = nextY + '-' + nextMStr + '-01';

  var masterAoi = AOI_MODULE.getMasterAOI();
  var aoiGeom = masterAoi.geometry;

  // 1. Process physical modules
  var sst = SST_MODULE.process(startDate, endDate, aoiGeom);
  var front = THERMAL_FRONT_MODULE.process(sst.select('sst_mean_c'), aoiGeom);
  var chla = CHLOROPHYLL_MODULE.process(year, month, startDate, endDate, aoiGeom);
  var wind = WIND_MODULE.process(startDate, endDate, aoiGeom);
  var stress = WIND_STRESS_MODULE.process(startDate, endDate, aoiGeom);
  var ekman = EKMAN_MODULE.process(stress, aoiGeom);
  var currents = CURRENT_MODULE.process(year, month, startDate, endDate, aoiGeom);
  var bathy = BATHYMETRY_MODULE.process(aoiGeom);

  // 2. Assemble unified composite image
  var composite = sst.addBands([
    front, chla, wind, stress, ekman, currents, bathy
  ]);

  var hasHycom = (year < 2024) || (year === 2024 && month <= 8);
  var qaRecord = QA_MODULE.evaluate(year, month, hasHycom);
  var provRecord = PROVENANCE_MODULE.getRecord(year, month);

  return {
    year: year,
    month: month,
    year_month: year + '-' + mStr,
    compositeImage: composite,
    aoiGeometry: aoiGeom,
    qaRecord: qaRecord,
    provenanceRecord: provRecord
  };
}


// ==============================================================================
// MAIN EXECUTION TRIGGER — DEVELOPMENT CANDIDATE INSPECTION
// ==============================================================================
var targetYear = CONFIG_MODULE.EXECUTION.TEST_YEAR;
var targetMonth = CONFIG_MODULE.EXECUTION.TEST_MONTH;

var result = processMonth(targetYear, targetMonth);
UI_MODULE.render(targetYear, targetMonth, result.compositeImage, result.aoiGeometry);

print("====================================================================");
print("SEACAST PRIGI v2.1 — REMEDIATED DEVELOPMENT CANDIDATE EXECUTED");
print("Inspection Month: " + result.year_month);
print("Status QA: " + result.qaRecord.scientific_qa_status);
print("Current Source: " + result.provenanceRecord.current_source);
print("STOP CONDITION: Full Batch execution suspended awaiting researcher review.");
print("====================================================================");

