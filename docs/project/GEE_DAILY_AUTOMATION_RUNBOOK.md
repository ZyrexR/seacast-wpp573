# SEACAST — Runbook Automasi GEE Harian (Produksi Linux / CloudPanel)

**Status:** RANCANGAN — implementasi skrip/command dikerjakan Codex; dokumen ini adalah kontrak & panduan operasional.

## Alur

```
cron (setiap menit) -> php artisan schedule:run
  04:00 WIB  seacast:sync-live   -> scripts/gee_daily_extractor.py --run-inference --persist-db
  04:30 WIB  seacast:infer-daily -> inferensi + cache warming
```

Hasil akhir wajib menulis `storage/app/gee_ingestion/ingestion_manifest.json`.
Jika GEE gagal, manifest TIDAK boleh ditandai live/valid (status jujur: `UNVERIFIED` / `DEGRADED`).

## Kontrak environment variable (`.env` server)

| Variable | Fungsi |
|---|---|
| `GEE_PROJECT` | Project ID Google Cloud untuk Earth Engine |
| `GEE_SERVICE_ACCOUNT_EMAIL` | Email service account (`...@<project>.iam.gserviceaccount.com`) |
| `GEE_SERVICE_ACCOUNT_KEY_PATH` | Path absolut file JSON key (di luar webroot, `chmod 600`) |
| `PYTHON_BINARY` | Path python venv server (mis. `/home/<user>/seacast/.venv/bin/python`) |
| `GEE_SYNC_TIMEOUT_SECONDS` | Timeout proses ekstraksi (usulan default `1800`) |
| `GEE_SYNC_MAX_RETRIES` | Jumlah retry saat error sementara (usulan `3`, backoff eksponensial) |

Prioritas autentikasi di Python: service account (jika `GEE_SERVICE_ACCOUNT_KEY_PATH` ada) -> kredensial OAuth lokal (hanya untuk laptop/dev).

## Langkah setup server

1. **Google Cloud:** aktifkan Earth Engine API, buat Service Account, buat key JSON, lalu daftarkan service account di https://code.earthengine.google.com/register (untuk project non-komersial).
2. **Simpan key** di luar webroot: `/home/<user>/secrets/gee-sa.json`, `chmod 600`. Jangan commit ke Git.
3. **Python venv:** `python3 -m venv .venv && .venv/bin/pip install earthengine-api scikit-learn joblib pandas numpy psycopg2-binary` (pin `scikit-learn==1.6.1` agar sama dengan artifact model Cakalang/YFT).
4. **Isi `.env`** sesuai tabel di atas.
5. **Cron (CloudPanel -> Cron Jobs):**
   `* * * * * cd /home/<user>/htdocs/<site>/seacast-prigi-laravel && php artisan schedule:run >> /dev/null 2>&1`
6. **Queue worker:** jalankan `php artisan queue:work --sleep=3 --tries=3` di bawah Supervisor.
7. **Uji manual sebelum dijadwalkan:** `php artisan seacast:sync-live --date=YYYY-MM-DD` lalu cek `ingestion_manifest.json` dan `/dashboard/system-status`.

## Verifikasi harian

- `storage/logs/seacast_sync_live.log` dan `storage/logs/daily_inference.log` terisi tanpa error.
- `ingestion_manifest.json` memiliki `generated_at` hari ini dan belum kedaluwarsa.
- `/dashboard/system-status` menampilkan `OPERATIONAL` (bukan `UNVERIFIED` / `DEGRADED`).
- `/api/predictions?summary_only=1` memuat `prediction_date` terbaru.

## Risiko yang diketahui

- Kuota/rate limit GEE: gunakan retry + backoff, jangan menarik ulang tanggal yang sudah valid.
- Data satelit terbaru sering tertunda 1–3 hari; pipeline harus memakai tanggal observasi terakhir yang tersedia dan mencatatnya di manifest (bukan mengklaim tanggal hari ini).
- Gate ilmiah tetap `HOLD_FOR_SCIENTIFIC_REVIEW`: automasi data tidak mengubah status kalibrasi temporal.
