# Dasbor Ikan Pelagis Besar PPN Prigi (kerangka)

Jalankan: `pip install -r requirements.txt` lalu `streamlit run app.py`.

Tanpa `data/master.csv`, aplikasi memakai DATA DUMMY (ada peringatan di setiap halaman).

## Kolom data/master.csv
`year_month` (YYYY-MM-01), `total_pelagis_besar_kg`, `cakalang_kg`, `tuna_kg`, `tongkol_kg`,
`tenggiri_kg`, `sst_mean`, `chl_mean`, `wind_speed`, `hs_mean`, `dmi`, `oni`.
Kolom lag dan musim dibuat otomatis di `utils.py`. Sesuaikan `SPECIES` dan `CUTOFF` dengan data nyata.

Opsional: `data/species_traits.csv` (species, Linf_cm, K, Lm_cm, iucn_status, source, accessed).
