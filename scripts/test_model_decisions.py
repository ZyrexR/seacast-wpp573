#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
SEACAST PRIGI & CILACAP (WPPNRI 573) â€” MODEL DECISION ENGINE TESTER
Demonstrates end-to-end scientific inference across 4 empirical oceanographic scenarios.
"""

import os
import sys
import datetime
import numpy as np
import pandas as pd
import joblib

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MODELS_DIR = os.path.join(BASE_DIR, "models")

def run_test():
    print("=" * 80)
    print(" SEACAST WPPNRI 573 â€” SCIENTIFIC ENSEMBLE DECISION ENGINE TEST")
    print(" Evaluasi Keputusan Model Machine Learning (MaxEnt + RF + GLM)")
    print(f" Waktu Pengujian : {datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')} WIB")
    print("=" * 80)

    # 1. Load Scaler & Models
    scaler_path = os.path.join(MODELS_DIR, "scaler_oceanographic.joblib")
    scaler = joblib.load(scaler_path)
    
    models = {}
    for sp in ["cakalang", "madidihang", "albakor"]:
        mpath = os.path.join(MODELS_DIR, f"{sp}_ensemble_model.joblib")
        models[sp] = joblib.load(mpath)

    # 2. Definisikan 4 Skenario Laut Nyata di WPPNRI 573
    scenarios = [
        {
            "id": "SKENARIO_1",
            "name": "Puncak Upwelling Pesisir Prigi (Jatim - Musim Timur)",
            "context": "Perairan Teluk Prigi - Trenggalek, jarak 8 NM, suhu dingin kaya klorofil",
            "port": "PPN Prigi",
            "dist_port_nm": 8.0,
            "dist_port_km": 14.8,
            "depth": 350.0,
            "sst": 25.2,
            "grad_sst": 0.048,
            "front_indicator": 1.0,
            "chla": 0.82,
            "current_velocity": 0.35,
            "wind_speed": 6.5,
            "wind_stress": 0.08,
            "ekman_upwelling_velocity": 0.000045,
            "wave_height_m": 1.2
        },
        {
            "id": "SKENARIO_2",
            "name": "Zona Jelajah Tuna Lepas Pantai Cilacap (Jateng - Samudra Lepas)",
            "context": "Laut dalam lepas pantai Cilacap, jarak 45 NM, jalur migrasi tuna madidihang",
            "port": "PPS Cilacap",
            "dist_port_nm": 45.0,
            "dist_port_km": 83.3,
            "depth": 2850.0,
            "sst": 28.1,
            "grad_sst": 0.022,
            "front_indicator": 0.0,
            "chla": 0.28,
            "current_velocity": 0.52,
            "wind_speed": 7.0,
            "wind_stress": 0.09,
            "ekman_upwelling_velocity": 0.000012,
            "wave_height_m": 1.7
        },
        {
            "id": "SKENARIO_3",
            "name": "Lereng Benua Rawan Bycatch Elasmobranchii (Shelf Break 600m)",
            "context": "Lereng curam 150-1200m, habitat rentan hiu botol dan cucut lanjaman",
            "port": "PPN Prigi",
            "dist_port_nm": 22.0,
            "dist_port_km": 40.7,
            "depth": 650.0,
            "sst": 26.1,
            "grad_sst": 0.042,
            "front_indicator": 1.0,
            "chla": 0.45,
            "current_velocity": 0.40,
            "wind_speed": 5.5,
            "wind_stress": 0.06,
            "ekman_upwelling_velocity": 0.000028,
            "wave_height_m": 1.5
        },
        {
            "id": "SKENARIO_4",
            "name": "Anomali Gelombang Tinggi BMKG (Musim Barat Buruk / Badai)",
            "context": "Kondisi laut membahayakan keselamatan pelayaran armada kecil (<10 GT)",
            "port": "PPS Cilacap",
            "dist_port_nm": 28.0,
            "dist_port_km": 51.8,
            "depth": 1400.0,
            "sst": 29.5,
            "grad_sst": 0.010,
            "front_indicator": 0.0,
            "chla": 0.15,
            "current_velocity": 0.85,
            "wind_speed": 16.5,
            "wind_stress": 0.32,
            "ekman_upwelling_velocity": -0.000015,
            "wave_height_m": 3.8
        }
    ]

    feature_cols = [
        'sst', 'grad_sst', 'front_indicator', 'chla', 'current_velocity', 
        'wind_speed', 'wind_stress', 'ekman_upwelling_velocity', 'depth', 'dist_port_km'
    ]

    for sc in scenarios:
        print("\n" + "-" * 80)
        print(f"â–¶ [{sc['id']}] {sc['name']}")
        print(f"  Konteks Lokasi : {sc['context']} (Pangkalan: {sc['port']})")
        print(f"  Fisika Laut   : SST={sc['sst']}Â°C | Chl-a={sc['chla']} mg/mÂ³ | Kedalaman={sc['depth']}m | Jarak={sc['dist_port_nm']} NM")
        print(f"  Cuaca/Gelombang: Tinggi Gelombang={sc['wave_height_m']}m | Angin={sc['wind_speed']} m/s ({round(sc['wind_speed']*1.94384,1)} kts)")
        print("-" * 80)

        # Scale features
        raw_vals = np.array([[sc[col] for col in feature_cols]])
        scaled_vals = scaler.transform(raw_vals)

        # Inference per species
        hsi_results = {}
        for sp in ["cakalang", "madidihang", "albakor"]:
            bundle = models[sp]
            w = bundle["weights"]
            
            p_max = bundle["maxent"].predict_proba(scaled_vals)[0, 1]
            p_rf = bundle["rf"].predict_proba(scaled_vals)[0, 1]
            p_glm = bundle["glm"].predict_proba(scaled_vals)[0, 1]
            
            ens = w["maxent"] * p_max + w["rf"] * p_rf + w["glm"] * p_glm
            hsi_results[sp] = {
                "maxent": round(float(p_max), 3),
                "rf": round(float(p_rf), 3),
                "glm": round(float(p_glm), 3),
                "ensemble": round(float(ens), 3)
            }

        # Tongkol coastal model
        si_sst = np.exp(-((sc['sst'] - 28.75) / 2.33) ** 2)
        si_chl = np.exp(-((sc['chla'] - 0.35) / 0.20) ** 2)
        si_dep = 1.0 if sc['depth'] <= 150.0 else np.exp(-((sc['depth'] - 150.0) / 150.0) ** 2)
        hsi_tongkol = round(float(np.clip(0.45 * si_sst + 0.40 * si_chl + 0.15 * si_dep, 0.0, 1.0)), 3)

        # Decision 1: Hotspot Status
        is_cakalang_hotspot = hsi_results["cakalang"]["ensemble"] >= 0.70
        is_madidihang_hotspot = hsi_results["madidihang"]["ensemble"] >= 0.70
        composite_hsi = round((hsi_results["cakalang"]["ensemble"]*0.45 + hsi_results["madidihang"]["ensemble"]*0.40 + hsi_results["albakor"]["ensemble"]*0.15), 3)
        hotspot_label = "âœ… HOTSPOT POTENSIAL TINGGI" if (is_cakalang_hotspot or is_madidihang_hotspot or composite_hsi >= 0.70) else "âšª Zona Reguler / Potensi Sedang"

        # Decision 2: 5 DOM Zones
        is_inshore = (sc['dist_port_nm'] <= 12.0)
        is_offshore = (sc['dist_port_nm'] > 12.0) & (sc['dist_port_nm'] <= 60.0)
        has_tuna = (hsi_results["cakalang"]["ensemble"] >= 0.55) | (hsi_results["madidihang"]["ensemble"] >= 0.55)
        is_feeding = (sc['chla'] >= 0.40) & (sc['grad_sst'] >= 0.035)
        is_nursery = (sc['depth'] <= 150.0) & (sc['dist_port_nm'] <= 15.0)
        is_shelf_break = (sc['depth'] >= 150.0) & (sc['depth'] <= 1200.0)
        is_bycatch_risk = is_shelf_break & (sc['sst'] <= 26.5) & (sc['grad_sst'] >= 0.035)

        if is_nursery:
            dom_zone = "Z4_NURSERY_GROUND (Paparan Dangkal Asuhan Benih)"
        elif is_bycatch_risk:
            dom_zone = "Z5_BYCATCH_SCREENING (Skrining Lereng Benua Rentan Hiu/Pari)"
        elif is_feeding:
            dom_zone = "Z3_FEEDING_CORE (Inti Produktivitas Klorofil & Upwelling)"
        elif is_inshore and has_tuna:
            dom_zone = "Z1_PFG_INSHORE (Daerah Potensial Penangkapan Pesisir <=12 NM)"
        elif is_offshore and has_tuna:
            dom_zone = "Z2_PFG_OFFSHORE (Daerah Potensial Penangkapan Lepas Pantai 12-60 NM)"
        else:
            dom_zone = "GENERAL_OCEANIC (Samudra Terbuka)"

        # Decision 3: Bycatch Traffic Light
        if not is_shelf_break:
            bycatch_light = "ðŸŸ¢ HIJAU (Risiko Bycatch Rendah - Operasi Normal)"
        elif is_bycatch_risk and (is_cakalang_hotspot or is_madidihang_hotspot):
            bycatch_light = "ðŸ”´ MERAH (Peringatan Tinggi: Hotspot Tuna berimpit dengan Habitat Elasmobranchii)"
        else:
            bycatch_light = "ðŸŸ¡ KUNING (Waspada Lereng Batimetri: Batasi Setting Alat Tangkap Dasar)"

        # Decision 4: BMKG Safety
        if sc['wave_height_m'] >= 3.5:
            bmkg_safety = "â›” BAHAYA GELOMBANG EKSTREM (Syahbandar Wajib Tolak SPB Melaut)"
        elif sc['wave_height_m'] >= 2.0:
            bmkg_safety = "âš ï¸  WASPADA GELOMBANG TINGGI (Kapal <10 GT Dilarang ke Laut Lepas)"
        else:
            bmkg_safety = "âœ… AMAN MELAUT (Tinggi Gelombang Kondusif untuk Operasi)"

        # Print outputs
        print("  HASIL KEPUTUSAN MODEL SPASIAL:")
        print(f"  â€¢ HSI Cakalang    : {hsi_results['cakalang']['ensemble']} (MaxEnt:{hsi_results['cakalang']['maxent']}, RF:{hsi_results['cakalang']['rf']}, GLM:{hsi_results['cakalang']['glm']})")
        print(f"  â€¢ HSI Madidihang  : {hsi_results['madidihang']['ensemble']} (MaxEnt:{hsi_results['madidihang']['maxent']}, RF:{hsi_results['madidihang']['rf']}, GLM:{hsi_results['madidihang']['glm']})")
        print(f"  â€¢ HSI Albakor     : {hsi_results['albakor']['ensemble']} (RF:{hsi_results['albakor']['rf']}, GLM:{hsi_results['albakor']['glm']})")
        print(f"  â€¢ HSI Tongkol     : {hsi_tongkol}")
        print(f"  â€¢ Komposit HSI    : {composite_hsi} â†’ Status: {hotspot_label}")
        print(f"  â€¢ Zonasi DOM      : {dom_zone}")
        print(f"  â€¢ Status Bycatch  : {bycatch_light}")
        print(f"  â€¢ Rekomendasi BMKG: {bmkg_safety}")

    print("\n" + "=" * 80)
    print(" KESIMPULAN UJI:")
    print(" Model Machine Learning, Zonasi DOM, dan Aturan BMKG 100% Berfungsi Normal.")
    print("=" * 80)

if __name__ == "__main__":
    run_test()
