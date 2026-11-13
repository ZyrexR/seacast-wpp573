# 06 — TEMPORAL ANALYSIS DESIGN & 132-MONTH CALENDAR
## SEACAST PRIGI — Wilayah Pengelolaan Perikanan Negara Republik Indonesia (WPPNRI) 573

- **Document ID:** `TEMP-DES-06`
- **Periode Analisis:** 2015-01 s/d 2025-12 (`11` Tahun = `132` Bulan)
- **Unit Waktu Utama:** Bulanan (Monthly Aggregations)

---

## 1. Kalender Temporal Tetap (Fixed Calendar Protocol)

Setiap sel grid memiliki tepat 132 entri waktu bulanan. Tidak ada bulan yang dihilangkan secara diam-diam.
Struktur primary key komposit:
```text
PRIMARY KEY (grid_id, year, month)
```

Jika data satelit mengalami tutupan awan atau keterbatasan sensor pada suatu bulan:
- Data **TIDAK** diisi dengan nilai nol (`0`).
- Diberikan atribut `data_coverage` (proporsi piksel valid `0.0–1.0`).
- Diberikan bendera kualitas resmi: `OBSERVED_RAW`, `SENSOR_FUSED`, `GAP_FILLED`, atau `MASKED_LAND`.
