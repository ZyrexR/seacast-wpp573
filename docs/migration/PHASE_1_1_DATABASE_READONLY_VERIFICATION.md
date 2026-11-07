# SEACAST PRIGI V3 — VERIFIKASI KEAMANAN READ-ONLY DATABASE (PHASE 1.1)
## LEAST-PRIVILEGE POSTGRESQL 17.11 & POSTGIS 3.6.2 VERIFICATION

**Tanggal Verifikasi:** 4 November 2026  
**Target Basis Data:** PostgreSQL 17.11 / PostGIS 3.6.2 (`seacast_prigi`)  
**Pengguna Database Target:** `seacast_readonly`  
**Status Evaluasi:** **PASS (VERIFIED & ENFORCED)**  

---

## 1. TEMUAN AWAL (FORENSIK SEBELUM REMEDIASI)

Pada audit Phase 1 awal:
- Berkas `.env` mengonfigurasi `DB_USERNAME=postgres` dengan hak `SUPERUSER`.
- Berkas `config/database.php` mengonfigurasi koneksi `pgsql` dan `pgsql_readonly` dengan fallback ke akun `postgres`.
- Meskipun model Eloquent dikunci dengan `protected $guarded = ['*']`, proteksi tersebut hanya berada di level aplikasi. Di level basis data, koneksi memiliki hak penuh untuk melakukan `INSERT`, `UPDATE`, `DELETE`, `DROP`, dan `TRUNCATE`.

---

## 2. PROVISIONING AKUN DEDIKASI `seacast_readonly`

Akun khusus dengan prinsip hak akses terendah (*least privilege*) telah dibuat dan diterapkan:

### A. Perintah SQL Provisioning yang Dijalankan
```sql
-- 1. Buat role khusus tanpa hak administratif
CREATE ROLE seacast_readonly WITH LOGIN PASSWORD '<ENCRYPTED_SECRET>' NOSUPERUSER NOCREATEDB NOCREATEROLE NOINHERIT;

-- 2. Hak koneksi dan navigasi skema
GRANT CONNECT ON DATABASE seacast_prigi TO seacast_readonly;
REVOKE ALL ON SCHEMA public FROM seacast_readonly;
GRANT USAGE ON SCHEMA public TO seacast_readonly;

-- 3. Batasi hanya operasi SELECT pada seluruh tabel data ilmiah
REVOKE ALL ON ALL TABLES IN SCHEMA public FROM seacast_readonly;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO seacast_readonly;

-- 4. Cabut hak sequence dan DDL
REVOKE ALL ON ALL SEQUENCES IN SCHEMA public FROM seacast_readonly;
REVOKE CREATE ON SCHEMA public FROM seacast_readonly;

-- 5. Kunci default privilege untuk tabel masa depan
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO seacast_readonly;
```

### B. Konfigurasi Proyek Laravel Target
Berkas `.env` dan `config/database.php` diperbarui untuk secara ketat menggunakan akun ini:
- `.env`:
  ```ini
  DB_PGSQL_USERNAME=seacast_readonly
  DB_PGSQL_PASSWORD=<ENCRYPTED_SECRET>
  ```
- `config/database.php`:
  ```php
  'pgsql' => [
      'driver' => 'pgsql',
      ...
      'username' => env('DB_PGSQL_USERNAME', 'seacast_readonly'),
      'password' => env('DB_PGSQL_PASSWORD', env('DB_PASSWORD', '')),
  ],
  'pgsql_readonly' => [
      'driver' => 'pgsql',
      ...
      'username' => env('DB_PGSQL_USERNAME', 'seacast_readonly'),
      'password' => env('DB_PGSQL_PASSWORD', env('DB_PASSWORD', '')),
  ],
  ```

---

## 3. HASIL VERIFIKASI HAK AKSES POSTGRESQL AKTUAL

Pemeriksaan privilege langsung terhadap katalog PostgreSQL `pg_roles`:

```sql
SELECT rolname, rolsuper, rolcreaterole, rolcreatedb, rolcanlogin 
FROM pg_roles 
WHERE rolname = 'seacast_readonly';
```

| Atribut Role | Nilai Aktual | Status Evaluasi |
| :--- | :---: | :---: |
| `rolname` | `seacast_readonly` | **VERIFIED** |
| `rolsuper` (Superuser) | `FALSE` | **VERIFIED (Least Privilege)** |
| `rolcreaterole` (Beri Hak Pengguna Lain) | `FALSE` | **VERIFIED (Least Privilege)** |
| `rolcreatedb` (Buat Basis Data) | `FALSE` | **VERIFIED (Least Privilege)** |
| `rolcanlogin` (Izin Masuk Sesi) | `TRUE` | **VERIFIED** |

---

## 4. PENGUJIAN OPERASI MANIPULASI DATA & SKEMA (PROBING TESTS)

Pengujian dilakukan langsung menggunakan koneksi aktif Laravel sebagai `seacast_readonly`:

### Uji 1: Operasi Pembacaan (SELECT)
- **Query**: `SELECT COUNT(*) FROM canonical_grids;`
- **Hasil**: **SUKSES** (Mengembalikan tepat `8.129` sel poligon).

### Uji 2: Penolakan Penyisipan Data (INSERT)
- **Query**: `INSERT INTO canonical_grids (cell_id) VALUES ('SECURITY_TEST_PROBE_CELL');`
- **Respon Database**: `SQLSTATE[42501]: Insufficient privilege: 7 ERROR: permission denied for table canonical_grids`
- **Hasil**: **DITOLAK SECARA KERAS OLEH POSTGRESQL (PASS)**.

### Uji 3: Penolakan Pengubahan Data (UPDATE)
- **Query**: `UPDATE canonical_grids SET cell_id = 'MUTATION_PROBE' WHERE cell_id = 'CELL_0001';`
- **Respon Database**: `SQLSTATE[42501]: Insufficient privilege: 7 ERROR: permission denied for table canonical_grids`
- **Hasil**: **DITOLAK SECARA KERAS OLEH POSTGRESQL (PASS)**.

### Uji 4: Penolakan Penghapusan Data (DELETE)
- **Query**: `DELETE FROM canonical_grids WHERE cell_id = 'CELL_0001';`
- **Respon Database**: `SQLSTATE[42501]: Insufficient privilege: 7 ERROR: permission denied for table canonical_grids`
- **Hasil**: **DITOLAK SECARA KERAS OLEH POSTGRESQL (PASS)**.

### Uji 5: Penolakan Perubahan Skema (DDL CREATE TABLE)
- **Query**: `CREATE TABLE security_probe_table (id int);`
- **Respon Database**: `SQLSTATE[42501]: Insufficient privilege: 7 ERROR: permission denied for schema public`
- **Hasil**: **DITOLAK SECARA KERAS OLEH POSTGRESQL (PASS)**.

---

## 5. VERIFIKASI ZERO DATA DRIFT

Pemeriksaan integritas jumlah baris setelah seluruh pengujian probing:

| Tabel Ilmiah | Jumlah Baris Target | Jumlah Baris Aktual | Integritas Data |
| :--- | :---: | :---: | :---: |
| `canonical_grids` | 8.129 | 8.129 | **100% UNMODIFIED** |
| `daily_predictions` | 8.129 | 8.129 | **100% UNMODIFIED** |
| `grid_monthly_predictions` | 97.548 | 97.548 | **100% UNMODIFIED** |
| `prigi_port_landings` | 132 | 132 | **100% UNMODIFIED** |
| `prigi_fleet_by_gt` | 3 | 3 | **100% UNMODIFIED** |

**Kesimpulan:** Akun database Laravel telah sepenuhnya menerapkan prinsip least-privilege read-only dengan bukti penolakan langsung dari engine PostgreSQL 17.

