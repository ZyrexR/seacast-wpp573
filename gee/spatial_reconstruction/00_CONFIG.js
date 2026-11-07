/**
 * SEACAST PRIGI — WPPNRI 573 SPATIAL RECONSTRUCTION
 * Module 00: Centralized Configuration
 * File: gee/spatial_reconstruction/00_CONFIG.js
 */
var CONFIG = {
  PROJECT_ID: 'seca-513505',
  // Official WPPNRI 573 Bounding Coordinates for spatial queries
  AOI_BOUNDS: {
    WEST: 102.5778,
    EAST: 126.4996,
    SOUTH: -13.9382,
    NORTH: -6.8018
  },
  // Sub-AOI Prigi (Fine-scale focal study area for PPN Prigi)
  SUB_AOI_PRIGI: {
    WEST: 111.00,
    EAST: 113.00,
    SOUTH: -10.45,
    NORTH: -8.00,
    HARBOR_LON: 111.7314,
    HARBOR_LAT: -8.2818
  },
  TEMPORAL: {
    START_DATE: '2015-01-01',
    END_DATE: '2025-12-31',
    TOTAL_MONTHS: 132
  },
  COLLECTIONS: {
    OISST: 'NOAA/CDR/OISST/V2_1',
    MODIS_AQUA: 'NASA/OCEANDATA/MODIS-Aqua/L3SMI',
    CFSV2_WIND: 'NOAA/CFSV2/FOR6H_HARMONIZED',
    HYCOM_VELOCITY: 'HYCOM/sea_water_velocity',
    ETOPO1: 'NOAA/NGDC/ETOPO1'
  },
  GRID_RESOLUTIONS: {
    REGIONAL_WPP: 0.25,    // Matches OISST & Altimetry native scale
    INTERMEDIATE: 0.10,    // High-level modeling scale (~7,500 ocean cells)
    PRIGI_LOCAL: 0.05      // High-resolution screening grid (2,050 cells)
  },
  CRS: {
    GEOGRAPHIC: 'EPSG:4326',
    EQUAL_AREA: 'EPSG:6933'
  }
};

exports.CONFIG = CONFIG;
