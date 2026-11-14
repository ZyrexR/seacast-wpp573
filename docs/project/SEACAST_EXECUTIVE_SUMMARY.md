# SEACAST — Executive Summary

**Status snapshot:** 14 November 2026
**Scope:** WPPNRI 573; komoditas TCT — Tuna Sirip Kuning, Cakalang, dan Tongkol Krai.

## Keputusan arsitektur

SEACAST memiliki dua keluaran yang sengaja dipisahkan:

1. **Spatial HSI/DOM map** — memetakan kesesuaian relatif sel grid untuk tiga komoditas TCT dan menurunkan zona potensial fishery ground, feeding ground, serta nursery/protection proxy. Ini bukan klaim lokasi ikan aktual, biomassa, atau jaminan tangkapan.
2. **Trip Planner** — menghitung skenario jarak, BBM, biaya, kebutuhan GT/alat tangkap, risiko cuaca, dan estimasi tangkapan berbasis input pelabuhan serta sel terpilih. Hasilnya adalah skenario keputusan, bukan jaminan hasil ekonomi.

Model temporal CPUE berada di bawah keluaran kedua sebagai lapisan kalibrasi opsional. Ia tidak boleh menimpa HSI sel atau mengubah prediksi pelabuhan menjadi lokasi ikan.

## Data dan batasan yang terukur

- Grid kanonikal: 8.129 sel WPPNRI 573.
- Data oseanografi GEE: 132 bulan, 2015–2025.
- Data EOG VBD: 1.073.028 baris panel bulanan/grid; dipakai sebagai proxy aktivitas kapal, bukan identifikasi spesies.
- Panel operasional notebook: dua hub Prigi–Cilacap, 44.616 baris lokal dan 338 sel operasional.
- Statistik pendaratan/upaya: agregat bulanan; belum memiliki koordinat tangkapan per spesies.
- Data occurrence literatur: 31 titik Tuna Sirip Kuning.

## Status validasi spasial

| Cakupan audit | Hasil | Status | Makna |
|---|---:|---|---|
| Full grid 8.129 sel | 31/31, 100% | `EMPIRICALLY_VALIDATED` | Titik occurrence dekat grid kanonikal; belum berarti validasi zona armada |
| Subset operasional ≤60 NM | 3/31, 9,68% | `PARTIAL_SPATIAL_VALIDATION` | Belum cukup untuk klaim lokasi ikan operasional Prigi–Cilacap |

Karena keluaran web ditujukan untuk penggunaan operasional, status spasial yang berlaku adalah `PARTIAL_SPATIAL_VALIDATION`. MaxEnt belum menjadi bagian ensemble produksi; ia hanya dapat dijalankan terpisah setelah tersedia occurrence berkoordinat yang relevan dan spatial-block validation.

## Holdout temporal 2025

Model dilatih pada 2015–2024 dan diuji pada 2025. Nilai berikut berasal dari run notebook terakhir, bukan target yang dipaksakan:

| Komoditas | RMSE model | RMSE baseline musiman | R² riil | R² log | Kalibrasi temporal | Keputusan |
|---|---:|---:|---:|---:|---|---|
| Tuna Sirip Kuning | 274,103 | 305,572 | -0,282 | 0,089 | `ENABLED` | Kandidat riset; belum release |
| Cakalang | 178,809 | 178,809 | 0,146 | 0,509 | `BASELINE_ONLY` | Baseline dipertahankan |
| Tongkol Krai | 13,537 | 13,537 | -0,077 | -1,212 | `BASELINE_ONLY` | Baseline dipertahankan |

R² log Cakalang yang melewati 0,50 tidak cukup untuk mengaktifkan koreksi produksi karena RMSE skala riil tidak mengalahkan baseline musiman. Scientific gate saat ini menghasilkan `HOLD_FOR_SCIENTIFIC_REVIEW` untuk seluruh komoditas.

## Integrasi aplikasi

API, export, tile, dan Trip Planner mempertahankan field legacy selama migrasi, sekaligus menambahkan:

- nama publik `tuna_sirip_kuning`, `cakalang`, `tongkol_krai`;
- provenance `evidence_level`, `source_mode`, `model_version`, `prediction_timestamp`, `validation_status`;
- `cell_id`, `target_source`, `estimate_source`, dan label fallback pada Trip Planner;
- cache/tile schema version `v2` setelah properti publik berubah.

Kesimpulan rilis: peta HSI boleh ditampilkan sebagai **proxy ilmiah berlabel**, Trip Planner boleh dipakai sebagai **skenario dengan label sumber data**, tetapi sistem belum boleh mempromosikan titik sebagai lokasi ikan terverifikasi.
