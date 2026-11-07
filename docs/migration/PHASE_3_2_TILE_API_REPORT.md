# PHASE 3.2 TILE API SPECIFICATION & INTEGRATION REPORT
## SEACAST PRIGI V3 — Vector Tile Endpoints, Route Validation & Security Bounds

**Tanggal:** 2026-11-04  
**Proyek:** SEACAST PRIGI V3  
**Endpoint:** `/api/tiles/{layer}/{z}/{x}/{y}.pbf`  
**Status:** **ACTIVE, TESTED & DOCUMENTED**  

---

### 1. Spesifikasi Kontrak API Ubin Vektor

Endpoint ubin vektor melayani data spasial biner untuk integrasi langsung dengan map rendering engine MapLibre GL JS:

```http
GET /api/tiles/{layer}/{z}/{x}/{y}.pbf?date=YYYY-MM-DD HTTP/1.1
Host: 127.0.0.1:8000
Accept: application/x-protobuf, */*
```

#### 1.1. Parameter URL
- `{layer}` (string, wajib): Nama lapisan vektor yang diizinkan (`predictions`, `canonical_grids`, `hotspots`).
- `{z}` (integer, wajib): Tingkat perbesaran zoom level ($4 \le z \le 16$).
- `{x}` (integer, wajib): Indeks kolom ubin horizontal ($0 \le x \le 2^z - 1$).
- `{y}` (integer, wajib): Indeks baris ubin vertikal ($0 \le y \le 2^z - 1$).

#### 1.2. Parameter Kueri
- `date` (string, opsional, default: `2026-11-03`): Tanggal snapshot prediksi dengan format valid `YYYY-MM-DD`.

---

### 2. Validasi Keamanan & Sanitasi Input

Untuk mencegah *SQL Injection*, *Path Traversal*, dan *Denial of Service (DoS)*:
1. **Layer Whitelist Enforcement:** Request untuk nama layer di luar daftar yang diizinkan langsung ditolak dengan status **HTTP 404 Not Found**. Tidak ada nama tabel atau kolom yang diterima dari klien.
2. **Strict Coordinate Bounds:** Nilai zoom di luar rentang $4-16$ atau koordinat $(x, y)$ di luar batas piramida ubin ditolak dengan status **HTTP 400 Bad Request**.
3. **Prepared Parameterized SQL:** Semua kueri PostGIS menggunakan prepared statements dengan PDO binding `[$z, $x, $y, $z, $x, $y, $date]`.
4. **Dedicated Rate Limiting:** Diberikan batas throttling khusus sebesar **600 request / menit** (`throttle:600,1`) untuk mendukung permintaan ubin paralel tanpa terputus saat panning peta.

---

### 3. Status Respons HTTP & Format Header

#### 3.1. Respons Sukses Berisi Data (HTTP 200 OK)
```http
HTTP/1.1 200 OK
Content-Type: application/x-protobuf
Content-Length: 15420
ETag: "d41d8cd98f00b204e9800998ecf8427e"
X-Cache: HIT
Cache-Control: public, max-age=86400, stale-while-revalidate=604800
```

#### 3.2. Respons Ubin Samudera Kosong (HTTP 204 No Content)
Jika ubin berada di area perairan di luar batas grid 8.129 sel kanonikal:
```http
HTTP/1.1 204 No Content
ETag: "e3b0c44298fc1c149afbf4c8996fb924"
X-Cache: HIT-EMPTY
Cache-Control: public, max-age=86400, stale-while-revalidate=604800
```
Browser dan MapLibre mengabaikan ubin kosong tanpa menimbulkan pesan kesalahan rendering (*silent pass*).

#### 3.3. Conditional Revalidation (HTTP 304 Not Modified)
Jika klien mengirim header `If-None-Match` yang cocok dengan ETag:
```http
HTTP/1.1 304 Not Modified
ETag: "d41d8cd98f00b204e9800998ecf8427e"
X-Cache: HIT-REVALIDATED
```

