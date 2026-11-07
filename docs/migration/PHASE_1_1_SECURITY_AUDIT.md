# SEACAST PRIGI V3 — AUDIT KEAMANAN SISTEM & AUTORISASI (PHASE 1.1)
## APPLICATION SECURITY, AUTHENTICATION, AUTHORIZATION & API HARDENING

**Tanggal Audit:** 4 November 2026  
**Cakupan Audit:** Sesi Web, Autentikasi Persona, Akun Demo, Kontrol Akses Berbasis Peran (RBAC), Endpoint Monitoring API  
**Status Evaluasi:** **PASS (FIXED & VERIFIED)**  

---

## 1. AUDIT SISTEM AUTENTIKASI & SESI

Pemeriksaan menyeluruh terhadap fondasi autentikasi di `app/Http/Controllers/Web/AuthController.php`:

| Parameter Keamanan | Implementasi Aktual | Status Evaluasi |
| :--- | :--- | :---: |
| **Sistem Autentikasi** | Session-based via Cookie HTTP-Only & SameSite=Lax | **VERIFIED** |
| **Password Hashing** | Bcrypt (Blowfish) via `Illuminate\Support\Facades\Hash` | **VERIFIED** |
| **Proteksi CSRF** | Token CSRF pada setiap form Blade (`@csrf` / `<meta name="csrf-token">`) | **VERIFIED** |
| **Regenerasi Sesi Login** | `$request->session()->regenerate()` dipanggil saat login berhasil | **VERIFIED** |
| **Invalidasi Sesi Logout** | `Auth::logout()`, `$request->session()->invalidate()`, `regenerateToken()` | **VERIFIED** |
| **Login Rate Limiting** | `RateLimiter::tooManyAttempts('login-attempt:...', 5)` dengan lockout 60 detik | **VERIFIED** |
| **Route Protection** | Middleware `auth` pada seluruh grup `/dashboard/*` (Redirect 302 jika belum login) | **VERIFIED** |

---

## 2. REMEDIASI PENGAMAN AKUN DEMO PRODUKSI

### A. Risiko Awal
Empat akun persona (`nelayan@seacast.id`, `syahbandar@seacast.id`, `peneliti@seacast.id`, `admin@seacast.id`) memiliki kata sandi bawaan `seacast2026`. Tanpa pengamanan, kredensial demo ini berpotensi dieksploitasi di lingkungan produksi.

### B. Tindakan Remediasi yang Diterapkan
1. **Pengaman Seeder (`DatabaseSeeder.php`)**:
   ```php
   if (app()->environment('production')) {
       throw new RuntimeException('DatabaseSeeder dengan akun demo dilarang dijalankan pada lingkungan produksi.');
   }
   ```
2. **Pengaman Login Controller (`AuthController.php`)**:
   ```php
   $knownDemoEmails = ['nelayan@seacast.id', 'syahbandar@seacast.id', 'peneliti@seacast.id', 'admin@seacast.id'];
   $isProduction = app()->environment('production') || config('app.env') === 'production';
   if ($isProduction && in_array(strtolower($credentials['email']), $knownDemoEmails, true) && $credentials['password'] === 'seacast2026') {
       throw ValidationException::withMessages([
           'email' => ['Kredensial demo bawaan dinonaktifkan secara otomatis pada lingkungan produksi demi kepatuhan keamanan.'],
       ]);
   }
   ```
3. **Hasil Verifikasi**:
   Uji coba otomatis (`SecurityRemediationTest::test_demo_credentials_blocked_when_environment_is_production`) membuktikan bahwa saat `app.env = production`, login dengan kredensial demo langsung ditolak dengan `ValidationException`.

---

## 3. IMPLEMENTASI KONTROL AKSES BERBASIS PERAN (RBAC)

### A. Penambahan Kolom Peran pada Model Pengguna
- Migration `2026_11_04_000003_add_role_to_users_table.php` menambahkan kolom `role` pada tabel internal `users` di SQLite.
- Model `app/Models/User.php` dilengkapi fungsi pemeriksa wewenang:
  * `hasRole(string|array $roles): bool`
  * `isAdmin(): bool`
  * `isNelayan(): bool`
  * `isSyahbandar(): bool`
  * `isPeneliti(): bool`

### B. Middleware Autorisasi (`App\Http\Middleware\EnsureUserHasRole`)
- Didaftarkan sebagai alias `'role'` pada `bootstrap/app.php`.
- Memeriksa kesesuaian peran pengguna yang sedang aktif terhadap parameter yang diizinkan. Jika tidak sesuai, request dihentikan dengan status `403 Forbidden`.

### C. Pemisahan Hak Akses Modul
- Modul sensitif `/dashboard/system-status` (diagnostik infrastruktur dan basis data PostGIS) dikunci dengan middleware `role:admin`.
- **Hasil Uji**:
  * Pengguna peran `nelayan` mengakses `/dashboard` -> **200 OK**.
  * Pengguna peran `nelayan` mengakses `/dashboard/system-status` -> **403 Forbidden** (Akses Ditolak).
  * Pengguna peran `admin` mengakses `/dashboard/system-status` -> **200 OK**.

---

## 4. PENGERASAN ENDPOINT PEMANTAUAN (`/api/monitoring`)

### A. Sanitasi Path dan Penyembunyian Rahasia Server
Endpoint publik `GET /api/monitoring` telah diaudit untuk memastikan tidak ada kebocoran path direktori absolut Windows host atau rahasia server:
- Path penyimpanan internal (`storage_path()`) disanitasi menjadi referensi relatif: `'path' => 'storage/'`.
- Path eksekusi Python (`C:\Users\...` atau `C:\Python314\...`) disanitasi menjadi deskripsi aman: `'runtime' => 'Python 3 (Host CLI)'`.
- Path direktori model disanitasi menjadi: `'model_fixtures' => 'models/'`.
- Variabel lingkungan sensitif (`APP_KEY`, `DB_PASSWORD`, `DB_USERNAME`) tidak pernah dimuat ke dalam respon JSON.

### B. Pemisahan Endpoint Diagnostik Administrator
- Dibuat route terpisah: `GET /api/monitoring/diagnostics`.
- Akses ke endpoint ini mewajibkan sesi autentikasi dan peran `admin`.
- Pengguna tanpa login dialihkan/ditolak (`401 Unauthorized`), pengguna non-admin ditolak (`403 Forbidden`), dan admin menerima metrik memori, SAPI, dan opcache yang aman.

### C. Pembatasan Laju Request (Rate Limiting)
- Diterapkan middleware `throttle:60,1` pada grup `routes/api.php` untuk mencegah serangan DoS atau brute-force probing pada endpoint pemantauan.

**Status Akhir Pekerjaan B & C:** **PASS (FIXED & VERIFIED)**

