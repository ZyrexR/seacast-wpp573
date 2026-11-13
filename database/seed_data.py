"""
SEACAST PRIGI v2.3.2 — Production Database Seeder
Migrates Canonical Grids, Daily Predictions, Monthly Archive, and Port Landings
into PostgreSQL 17/18 with PostGIS Spatial Extension.
"""

import os
import sys
import json
import psycopg2
from psycopg2 import sql
from psycopg2.extras import execute_batch, execute_values
import pandas as pd
import numpy as np

# Database Configuration
PG_HOST = os.environ.get("PGHOST", "localhost")
PG_PORT = int(os.environ.get("PGPORT", 5432))
PG_USER = os.environ.get("PGUSER", "postgres")
PG_PASSWORD = os.environ.get("PGPASSWORD", "postgres")
PG_DATABASE = os.environ.get("PGDATABASE", "seacast_prigi")

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
PROJECT_ROOT = os.path.dirname(SCRIPT_DIR)
SCHEMA_SQL_PATH = os.path.join(SCRIPT_DIR, "schema.sql")
GEOJSON_PATH = os.path.join(PROJECT_ROOT, "data", "geojson", "canonical_grid_8129_cells.geojson")
CSV_2025_PATH = os.path.join(PROJECT_ROOT, "data", "predictions", "SEACAST_PRIGI_PREDICTIONS_2025_SAMPLE.csv")
MASTER_CSV_PATH = os.path.join(PROJECT_ROOT, "master.csv")


def ensure_database():
    """Connects to default postgres database to create target database if needed."""
    print(f"[*] Connecting to postgres server at {PG_HOST}:{PG_PORT} as {PG_USER}...")
    conn = psycopg2.connect(
        host=PG_HOST,
        port=PG_PORT,
        user=PG_USER,
        password=PG_PASSWORD,
        database="postgres"
    )
    conn.autocommit = True
    cur = conn.cursor()

    cur.execute("SELECT 1 FROM pg_database WHERE datname = %s", (PG_DATABASE,))
    exists = cur.fetchone()
    if not exists:
        print(f"[+] Creating database '{PG_DATABASE}'...")
        cur.execute(sql.SQL("CREATE DATABASE {}").format(sql.Identifier(PG_DATABASE)))
        print(f"[OK] Database '{PG_DATABASE}' created successfully.")
    else:
        print(f"[OK] Database '{PG_DATABASE}' already exists.")

    cur.close()
    conn.close()


def apply_schema(conn):
    """Executes schema.sql on target database to create PostGIS extension & tables."""
    print(f"[*] Applying PostGIS schema from {SCHEMA_SQL_PATH}...")
    with open(SCHEMA_SQL_PATH, "r", encoding="utf-8") as f:
        schema_sql = f.read()

    cur = conn.cursor()
    cur.execute(schema_sql)
    conn.commit()
    cur.close()
    print("[OK] PostGIS extension and table structures applied.")


def seed_canonical_grids(conn):
    """Ingests all 8,129 polygon cells from GeoJSON into canonical_grids."""
    cur = conn.cursor()
    cur.execute("SELECT COUNT(*) FROM canonical_grids")
    count = cur.fetchone()[0]
    if count >= 8129:
        print(f"[OK] canonical_grids already populated ({count} records). Skipping.")
        cur.close()
        return

    print(f"[*] Reading canonical GeoJSON from {GEOJSON_PATH}...")
    with open(GEOJSON_PATH, "r", encoding="utf-8") as f:
        gj = json.load(f)

    features = gj.get("features", [])
    print(f"[*] Found {len(features)} spatial grid features. Preparing batch insert...")

    records = []
    for feat in features:
        props = feat.get("properties", {})
        geom = feat.get("geometry", {})
        cell_id = props.get("cell_id")
        cell_class = props.get("cell_class", "REGIONAL_OCEANIC")
        depth_m = float(props.get("depth_m", 1500.0))
        dist_port_nm = float(props.get("dist_nm", 100.0))
        dist_port_km = dist_port_nm * 1.852

        # Extract centroid approx from polygon coordinates
        coords = geom.get("coordinates", [[]])[0]
        lons = [c[0] for c in coords]
        lats = [c[1] for c in coords]
        center_lon = sum(lons) / len(lons) if lons else 111.7
        center_lat = sum(lats) / len(lats) if lats else -8.3

        # Sector classification
        if center_lon < 110.5:
            sector = "JABAR_JATENG"
        elif center_lon <= 114.5:
            sector = "JATIM"
        else:
            sector = "BALI_NUSA_TENGGARA"

        is_shelf_break = (depth_m >= 150.0 and depth_m <= 1200.0)
        is_prigi_inshore = (dist_port_nm <= 12.0)
        is_prigi_offshore = (dist_port_nm > 12.0 and dist_port_nm <= 60.0)

        geom_json_str = json.dumps(geom)

        records.append((
            cell_id, cell_class, center_lon, center_lat, depth_m,
            dist_port_km, dist_port_nm, is_shelf_break,
            is_prigi_inshore, is_prigi_offshore, sector, geom_json_str
        ))

    insert_sql = """
    INSERT INTO canonical_grids (
        cell_id, cell_class, center_lon, center_lat, depth_m,
        dist_port_km, dist_port_nm, is_shelf_break,
        is_prigi_inshore, is_prigi_offshore, sector, geom
    ) VALUES (
        %s, %s, %s, %s, %s,
        %s, %s, %s,
        %s, %s, %s, ST_SetSRID(ST_GeomFromGeoJSON(%s), 4326)
    ) ON CONFLICT (cell_id) DO NOTHING;
    """

    execute_batch(cur, insert_sql, records, page_size=1000)
    conn.commit()
    cur.close()
    print(f"[OK] Successfully ingested {len(records)} canonical cells into canonical_grids.")


def seed_daily_predictions(conn):
    """Populates daily_predictions from 2025 sample CSV."""
    cur = conn.cursor()
    cur.execute("SELECT COUNT(*) FROM daily_predictions WHERE prediction_date = '2026-11-03'")
    count = cur.fetchone()[0]
    if count >= 8000:
        print(f"[OK] daily_predictions already populated ({count} records). Skipping.")
        cur.close()
        return

    print(f"[*] Reading predictions from {CSV_2025_PATH}...")
    df = pd.read_csv(CSV_2025_PATH)
    # Filter to one month as the operational daily snapshot
    df_snapshot = df[df['month'] == 12].copy()
    if len(df_snapshot) == 0:
        df_snapshot = df.head(8129).copy()

    records = []
    for _, row in df_snapshot.iterrows():
        cell_id = str(row['cell_id'])
        hsi_cak = float(row.get('hsi_cakalang', 0.5))
        hsi_mad = float(row.get('hsi_madidihang', 0.5))
        hsi_tongkol = float(row.get('hsi_tongkol', 0.3))
        is_hotspot = bool(row.get('is_hotspot', False))
        dom_zone = str(row.get('dom_zone', 'GENERAL_OCEANIC'))
        bycatch = str(row.get('bycatch_traffic_light', 'GREEN'))
        sst = float(row.get('sst', 28.0))
        chla = float(row.get('chla', 0.3))
        curr = float(row.get('current_velocity', 0.2))
        wind = float(row.get('wind_speed', 5.0))
        dist_nm = float(row.get('dist_port_nm', 50.0))

        # Simulated wave height
        wave_h = round(1.2 + (dist_nm / 180.0), 1)
        safety = "AMAN" if wave_h < 1.8 else ("WASPADA_KAPAL_KECIL" if wave_h < 2.5 else "BAHAYA_GELOMBANG_TINGGI")

        records.append((
            "2026-11-03", cell_id, hsi_cak, hsi_mad, hsi_tongkol, is_hotspot,
            dom_zone, bycatch, sst, chla, curr, wave_h, wind, safety
        ))

    insert_sql = """
    INSERT INTO daily_predictions (
        prediction_date, cell_id, hsi_cakalang, hsi_madidihang, hsi_tongkol,
        is_hotspot, dom_zone, bycatch_traffic_light, sst_c, chla_mg_m3,
        current_velocity_ms, wave_height_m, wind_speed_ms, safety_advisory
    ) VALUES (
        %s, %s, %s, %s, %s,
        %s, %s, %s, %s, %s,
        %s, %s, %s, %s
    ) ON CONFLICT (prediction_date, cell_id) DO NOTHING;
    """

    execute_batch(cur, insert_sql, records, page_size=1000)
    conn.commit()
    cur.close()
    print(f"[OK] Successfully ingested {len(records)} daily records into daily_predictions.")


def seed_fleet_and_landings(conn):
    """Seeds reference fleet breakdown and historical landings archive."""
    cur = conn.cursor()

    # Fleet breakdown
    fleet_rows = [
        (2026, "< 10 GT", 850, 67.0, 12.0),
        (2026, "10 - 20 GT", 165, 13.0, 30.0),
        (2026, "20 - 30 GT", 254, 20.0, 60.0),
    ]
    cur.executemany("""
    INSERT INTO prigi_fleet_by_gt (year, gt_class, vessel_count, pct_fleet, max_range_nm)
    VALUES (%s, %s, %s, %s, %s)
    ON CONFLICT (year, gt_class) DO UPDATE SET
        vessel_count = EXCLUDED.vessel_count,
        pct_fleet = EXCLUDED.pct_fleet,
        max_range_nm = EXCLUDED.max_range_nm;
    """, fleet_rows)

    # Master Landings if available
    if os.path.exists(MASTER_CSV_PATH):
        print(f"[*] Reading master landings from {MASTER_CSV_PATH}...")
        df_m = pd.read_csv(MASTER_CSV_PATH)
        landing_rows = []
        for _, row in df_m.iterrows():
            ym = str(row.get('year_month', '2020_01'))
            yr = int(row.get('year', 2020))
            mo = int(row.get('month', 1))
            cak = float(row.get('cakalang_tons', row.get('cakalang', 0))) if pd.notnull(row.get('cakalang_tons', row.get('cakalang', None))) else 0
            mad = float(row.get('madidihang_tons', row.get('madidihang', 0))) if pd.notnull(row.get('madidihang_tons', row.get('madidihang', None))) else 0
            tong = float(row.get('tongkol_tons', row.get('tongkol', 0))) if pd.notnull(row.get('tongkol_tons', row.get('tongkol', None))) else 0
            tot = float(row.get('total_pelagic_tons', cak + mad + tong))
            trips = int(row.get('trips_count', 120)) if pd.notnull(row.get('trips_count')) else 120
            cpue = tot / trips if trips > 0 else 0
            sst = float(row.get('sst', 28.0)) if pd.notnull(row.get('sst')) else 28.0
            chla = float(row.get('chla', 0.4)) if pd.notnull(row.get('chla')) else 0.4

            landing_rows.append((ym, yr, mo, cak, mad, tong, tot, trips, cpue, sst, chla))

        execute_batch(cur, """
        INSERT INTO prigi_port_landings (
            year_month, year, month, cakalang_tons, madidihang_tons, tongkol_tons,
            total_pelagic_tons, trips_count, cpue_proxy, mean_sst, mean_chla
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        ON CONFLICT (year_month) DO NOTHING;
        """, landing_rows, page_size=500)
        print(f"[OK] Ingested {len(landing_rows)} monthly port landing records into prigi_port_landings.")

    conn.commit()
    cur.close()


def main():
    print("==============================================================")
    print("SEACAST PRIGI v2.3.2 — PostgreSQL PostGIS Database Ingestion")
    print("==============================================================")
    try:
        ensure_database()
        conn = psycopg2.connect(
            host=PG_HOST,
            port=PG_PORT,
            user=PG_USER,
            password=PG_PASSWORD,
            database=PG_DATABASE
        )
        apply_schema(conn)
        seed_canonical_grids(conn)
        seed_daily_predictions(conn)
        seed_fleet_and_landings(conn)

        print("==============================================================")
        print("[SUCCESS] DATABASE INGESTION COMPLETED SUCCESSFULLY!")
        print(f"• Database Name:   {PG_DATABASE}")
        print(f"• Host:            {PG_HOST}:{PG_PORT}")
        print(f"• Spatial Table:   canonical_grids (8,129 cells, GiST geom)")
        print(f"• Daily Table:     daily_predictions")
        print(f"• Port Landings:   prigi_port_landings")
        print("==============================================================")
        conn.close()
    except Exception as e:
        print(f"\n[!] Error during database setup: {e}")
        sys.exit(1)


if __name__ == "__main__":
    main()

