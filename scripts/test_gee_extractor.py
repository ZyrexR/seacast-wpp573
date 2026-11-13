import os
import sys
import time
import json
from datetime import datetime, timezone, timedelta
import ee
import pandas as pd
import numpy as np

def test_extractor():
    print("🚀 Initializing GEE with project seca-513505...")
    ee.Initialize(project='seca-513505')
    
    # 1. Detect latest NOAA OISST date
    oisst_col = ee.ImageCollection('NOAA/CDR/OISST/V2_1')
    latest_img = oisst_col.sort('system:time_start', False).first()
    t_ms = latest_img.get('system:time_start').getInfo()
    latest_dt = datetime.fromtimestamp(t_ms / 1000.0, tz=timezone.utc)
    target_date_str = latest_dt.strftime('%Y-%m-%d')
    print(f"📅 Latest available NOAA OISST date: {target_date_str}")
    
    # Date window for single day
    start_date = target_date_str
    next_date = (latest_dt + timedelta(days=1)).strftime('%Y-%m-%d')
    
    # 2. SST & Gradient
    oisst_day = oisst_col.filterDate(start_date, next_date).first()
    sst = oisst_day.select('sst').multiply(0.01).rename('sst')
    
    # Sobel gradient
    sst_smooth = sst.convolve(ee.Kernel.gaussian(3, 1, 'pixels'))
    gx = sst_smooth.convolve(ee.Kernel.sobel())
    gy = sst_smooth.convolve(ee.Kernel.sobel().rotate(1))
    grad_sst = gx.hypot(gy).divide(25.0).rename('grad_sst') # 0.25 deg ~ 25-27 km
    
    # 3. Chlorophyll: 8-day rolling median composite from GCOM-C
    chla_start = (latest_dt - timedelta(days=7)).strftime('%Y-%m-%d')
    gcom_col = ee.ImageCollection('JAXA/GCOM-C/L3/OCEAN/CHLA/V3').filterDate(chla_start, next_date)
    gcom_count = gcom_col.size().getInfo()
    print(f"🌿 JAXA GCOM-C images in 8-day window ({chla_start} to {next_date}): {gcom_count}")
    
    if gcom_count > 0:
        chla = gcom_col.select('CHLA_AVE').median().multiply(0.0016).rename('chla')
    else:
        chla = ee.Image.constant(0.20).rename('chla')
        
    # 4. CFSv2 Wind
    cfs_col = ee.ImageCollection('NOAA/CFSV2/FOR6H_HARMONIZED').filterDate(start_date, next_date)
    cfs_count = cfs_col.size().getInfo()
    print(f"💨 NOAA CFSv2 frames for date ({start_date}): {cfs_count}")
    
    if cfs_count > 0:
        cfs_mean = cfs_col.mean()
        u_wind = cfs_mean.select('u-component_of_wind_height_above_ground')
        v_wind = cfs_mean.select('v-component_of_wind_height_above_ground')
        wind_speed = u_wind.hypot(v_wind).rename('wind_speed')
        wind_stress = wind_speed.multiply(0.008).rename('wind_stress')
    else:
        wind_speed = ee.Image.constant(4.5).rename('wind_speed')
        wind_stress = ee.Image.constant(4.5 * 0.008).rename('wind_stress')
        
    # 5. Composite Image Stack
    stack = ee.Image.cat([sst, grad_sst, chla, wind_speed, wind_stress])
    
    # 6. Sample on canonical grid centroids (8,129 cells)
    print("🛰️ Sampling 8,129 cells from GEE Cloud Asset...")
    t0 = time.time()
    grid = ee.FeatureCollection('projects/seca-513505/assets/GRID_010_REGIONAL_8129')
    points = grid.map(lambda f: ee.Feature(f.geometry().centroid(1), {
        'cell_id': f.get('cell_id'),
        'lon_center': f.get('lon_center'),
        'lat_center': f.get('lat_center')
    }))
    
    sampled = stack.reduceRegions(
        collection=points,
        reducer=ee.Reducer.first(),
        scale=10000
    )
    
    sample_5 = sampled.limit(5).getInfo()
    print(f"⏱️ Sample test completed in {round(time.time() - t0, 2)}s")
    for feat in sample_5['features']:
        print("  Cell:", feat['properties']['cell_id'], "Props:", feat['properties'])
        
    return target_date_str

if __name__ == '__main__':
    test_extractor()
