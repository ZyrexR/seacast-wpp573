-- ==============================================================================
-- SEACAST PRIGI v2.3.2 — PostgreSQL 18 + PostGIS Spatial Schema
-- WPPNRI 573 Canonical Grid & Dynamic Ocean Management Database
-- ==============================================================================

-- 1. Enable PostGIS Extension
CREATE EXTENSION IF NOT EXISTS postgis;

-- 2. Master Table: Canonical 8,129 Spatial Grid Cells
-- Covers entire WPPNRI 573 from Sunda Strait (102.5°E) to Sawu Sea / NTT (126.4°E)
CREATE TABLE IF NOT EXISTS canonical_grids (
    cell_id VARCHAR(32) PRIMARY KEY,
    cell_class VARCHAR(24) NOT NULL,
    center_lon DOUBLE PRECISION NOT NULL,
    center_lat DOUBLE PRECISION NOT NULL,
    depth_m DOUBLE PRECISION NOT NULL,
    dist_port_km DOUBLE PRECISION NOT NULL,
    dist_port_nm DOUBLE PRECISION NOT NULL,
    is_shelf_break BOOLEAN DEFAULT FALSE,
    is_prigi_inshore BOOLEAN DEFAULT FALSE,     -- <= 12 NM
    is_prigi_offshore BOOLEAN DEFAULT FALSE,    -- 12 - 60 NM
    sector VARCHAR(32) NOT NULL,                -- JATIM, JABAR_JATENG, BALI_NUSA_TENGGARA
    geom GEOMETRY(Polygon, 4326) NOT NULL
);

-- Spatial GiST Index for sub-millisecond bounding box and distance queries
CREATE INDEX IF NOT EXISTS idx_canonical_grids_geom ON canonical_grids USING GIST (geom);
CREATE INDEX IF NOT EXISTS idx_canonical_grids_dist ON canonical_grids (dist_port_nm);
CREATE INDEX IF NOT EXISTS idx_canonical_grids_sector ON canonical_grids (sector);

-- 3. Dynamic Daily Predictions (Automated Ingest at 04:00 WIB)
CREATE TABLE IF NOT EXISTS daily_predictions (
    prediction_date DATE NOT NULL,
    cell_id VARCHAR(32) NOT NULL REFERENCES canonical_grids(cell_id) ON DELETE CASCADE,
    hsi_cakalang NUMERIC(4,3) NOT NULL,
    hsi_madidihang NUMERIC(4,3) NOT NULL,
    hsi_tongkol NUMERIC(4,3) NOT NULL,
    is_hotspot BOOLEAN NOT NULL,
    dom_zone VARCHAR(32) NOT NULL,              -- Z1_PFG_INSHORE, Z2_PFG_OFFSHORE, Z3_FEEDING_CORE, Z4_NURSERY_GROUND, Z5_BYCATCH_SCREENING, GENERAL_OCEANIC
    bycatch_traffic_light VARCHAR(10) NOT NULL, -- GREEN, YELLOW, RED
    sst_c NUMERIC(4,2),
    chla_mg_m3 NUMERIC(5,3),
    current_velocity_ms NUMERIC(4,2),
    wave_height_m NUMERIC(3,1),
    wind_speed_ms NUMERIC(4,1),
    safety_advisory VARCHAR(64),                -- AMAN, WASPADA_KAPAL_KECIL, BAHAYA_GELOMBANG_TINGGI
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (prediction_date, cell_id)
);

CREATE INDEX IF NOT EXISTS idx_daily_predictions_date ON daily_predictions (prediction_date);
CREATE INDEX IF NOT EXISTS idx_daily_predictions_hotspot ON daily_predictions (is_hotspot);
CREATE INDEX IF NOT EXISTS idx_daily_predictions_dom ON daily_predictions (dom_zone);

-- 4. Historical Monthly Climate Archive (11 Years: 2015 - 2025 / 1,073,028 Rows)
CREATE TABLE IF NOT EXISTS grid_monthly_predictions (
    year_month VARCHAR(7) NOT NULL,             -- YYYY_MM
    year INT NOT NULL,
    month INT NOT NULL,
    cell_id VARCHAR(32) NOT NULL REFERENCES canonical_grids(cell_id) ON DELETE CASCADE,
    hsi_cakalang NUMERIC(4,3) NOT NULL,
    hsi_madidihang NUMERIC(4,3) NOT NULL,
    hsi_tongkol NUMERIC(4,3) NOT NULL,
    is_hotspot BOOLEAN NOT NULL,
    dom_zone VARCHAR(32) NOT NULL,
    bycatch_traffic_light VARCHAR(10) NOT NULL,
    sst NUMERIC(5,2),
    grad_sst NUMERIC(6,4),
    front_indicator NUMERIC(3,2),
    chla NUMERIC(6,3),
    current_velocity NUMERIC(5,3),
    wind_speed NUMERIC(5,2),
    wind_stress NUMERIC(6,4),
    ekman_upwelling_velocity NUMERIC(10,8),
    depth NUMERIC(7,1),
    dist_port_nm NUMERIC(6,1),
    PRIMARY KEY (year_month, cell_id)
);

CREATE INDEX IF NOT EXISTS idx_grid_monthly_ym ON grid_monthly_predictions (year_month);
CREATE INDEX IF NOT EXISTS idx_grid_monthly_dom ON grid_monthly_predictions (dom_zone);

-- 5. Reference: Prigi Port Landings & CPUE Validation Archive (2012 - 2025)
CREATE TABLE IF NOT EXISTS prigi_port_landings (
    year_month VARCHAR(7) PRIMARY KEY,
    year INT NOT NULL,
    month INT NOT NULL,
    cakalang_tons NUMERIC(10,2),
    madidihang_tons NUMERIC(10,2),
    tongkol_tons NUMERIC(10,2),
    total_pelagic_tons NUMERIC(10,2),
    trips_count INT,
    cpue_proxy NUMERIC(10,4),
    mean_sst NUMERIC(4,2),
    mean_chla NUMERIC(5,3)
);

-- 6. Reference: Prigi Fleet Capacity by Gross Tonnage (GT)
CREATE TABLE IF NOT EXISTS prigi_fleet_by_gt (
    year INT NOT NULL,
    gt_class VARCHAR(32) NOT NULL,              -- < 10 GT, 10 - 20 GT, 20 - 30 GT, > 30 GT
    vessel_count INT NOT NULL,
    pct_fleet NUMERIC(5,2) NOT NULL,
    max_range_nm NUMERIC(5,1) NOT NULL,
    PRIMARY KEY (year, gt_class)
);

