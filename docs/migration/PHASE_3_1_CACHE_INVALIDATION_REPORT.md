# PHASE 3.1 CACHE INVALIDATION & DATA CONSISTENCY REPORT
## SEACAST PRIGI V3 — Invalidation Triggers, Tag Eviction & Stale Data Prevention

**Tanggal:** 2026-11-04  
**Proyek:** SEACAST PRIGI V3  
**Framework:** Laravel 13.40.0 + PHP 8.3.36  
**Auditor:** Senior Laravel Architect & Software Reliability Engineer  
**Status:** **AUDITED, TESTED & VERIFIED**  

---

### 1. Eksekutif Ringkasan Strategi Invalidasi

Dalam sistem kecerdasan oseanografi seperti SEACAST PRIGI V3, keusangan data (*data staleness*) atau penyajian data basi dapat berdampak pada keselamatan pelaut dan keputusan operasional armada penangkapan ikan. Oleh karena itu, arsitektur caching wajib memiliki mekanisme invalidasi yang deterministik, terarah (*targeted*), dan terikat langsung pada siklus komputasi ilmiah.

Subfase 3.1 menerapkan strategi invalidasi berbasis kejadian (*event-driven*) dan berbasis domain melalui [`App\Services\GeospatialCacheManager`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Services/GeospatialCacheManager.php).

---

### 2. Pemicu Invalidasi (*Invalidation Triggers*) & Penanganan

| Pemicu (*Trigger*) | Domain Terdampak | Metode Eksekusi | Dampak Pembersihan | Tindakan Pasca-Invalidasi |
|---|---|---|---|---|
| **Eksekusi Inferensi Harian (04:00 WIB)** | `daily_predictions` | `GeospatialCacheManager::invalidateDate($date)` | Menghapus seluruh ringkasan (`summary`) dan variasi filter GeoJSON untuk tanggal tersebut. Domain lain tetap aman. | Memanggil `php artisan seacast:cache:warm --date={date}` untuk pre-warming. |
| **Pembaruan Grid Kanonikal / Batimetri** | `canonical_grids` | `GeospatialCacheManager::invalidateCanonicalGrids()` | Menghapus seluruh cache geometri grid kanonikal. | Cache dibuat ulang saat request pertama (MISS). |
| **Pemeliharaan Sistem / Deploy Baru** | Seluruh Domain | `GeospatialCacheManager::flushAll()` | Menghapus seluruh entri pada namespace `seacast:geo:*`. | `php artisan seacast:cache:warm` |
| **Manual Admin via CLI** | Selektif / Penuh | `php artisan seacast:cache:clear` | Sesuai opsi `--date` atau `--domain`. | Menampilkan ringkasan jumlah entri yang terhapus. |

---

### 3. Protokol Pencegahan Keracunan Cache (*Cache Poisoning & Leakage Prevention*)

1. **Role-Based Isolation:**
   Endpoint diagnostik internal (`/api/monitoring/diagnostics`) yang membutuhkan autentikasi role admin menggunakan header `Cache-Control: no-cache, private` dan **tidak pernah disimpan** dalam geospatial public cache. Hanya data publik dan data prediksi agregat yang dapat masuk ke `GeospatialCacheManager`.
2. **Determinisme Kunci & Sanitasi Input:**
   Semua parameter input kueri divalidasi dengan ketat pada form request controller (skema tanggal `Y-m-d`, numeric range bounding box, enum spesies). Parameter asing atau berbahaya secara otomatis diabaikan dari pembentukan hash kunci cache.
3. **Atomic Tag Registry Updates:**
   Pembaruan daftar kunci pada indeks tag dijalankan secara aman untuk mencegah inkonsistensi konkurensi.

---

### 4. Verifikasi Automated Test untuk Invalidasi

Fungsionalitas invalidasi diverifikasi secara otomatis melalui test suite:
- `Tests\Unit\GeospatialCacheManagerTest::test_it_selectively_invalidates_by_tag`: Memastikan invalidasi tanggal $T_1$ menghapus entri $T_1$ tanpa menyentuh entri $T_2$.
- `Tests\Feature\GeospatialPerformanceFeatureTest::test_canonical_grid_cache_hit_and_invalidation`: Memastikan bahwa pemanggilan `invalidateCanonicalGrids()` mengubah status kueri berikutnya dari `X-Cache: HIT` menjadi `X-Cache: MISS`.

