# PHASE 3 SKILL USAGE REGISTER
## SEACAST PRIGI V3 — Repository Cleanup, Geospatial Performance, Mapcn Integration & Analytics Expansion

**Tanggal:** 2026-11-04  
**Proyek:** SEACAST PRIGI V3  
**Framework Target:** Laravel 13.40.0 + PHP 8.3.36  
**Spatial Engine:** PostgreSQL 17.11 + PostGIS 3.6.2 (`seacast_prigi`)  
**Scientific Engine:** Python 3.14.7 + scikit-learn 1.9.1  
**Frontend Stack:** Laravel Blade + Livewire 4 + Alpine.js + Tailwind CSS  
**Map Engine:** MapLibre GL JS (mapcn architecture)  
**Status:** AUDITED & REGISTERED FOR PHASE 3.0  

---

### 1. Inventarisasi Skill & Registrasi Penggunaan

Berdasarkan mandat pemeriksaan keterampilan (*Skill Discovery*) pada workspace `.agent/`, `.agents/`, plugins internal, dan global builtin Antigravity, berikut adalah tabel registrasi pemanfaatan skill untuk seluruh subfase implementasi Phase 3 (dimulai dari Subfase 3.0: Repository Cleanup & Technical Debt Reduction):

| Skill | Lokasi | Relevansi Teknis | Subphase Penggunaan | Status |
|---|---|---|---|---|
| `using-superpowers` | `C:\Users\ThinkPad\.gemini\config\plugins\superpowers\skills\using-superpowers\SKILL.md` | Protokol tata kelola eksekusi agen, pemanggilan skill terstruktur, dan penegakan disiplin engineering | Seluruh Tahap Phase 3 (Global Meta-Governance) | **Active / Used** |
| `verification-before-completion` | `C:\Users\ThinkPad\.gemini\config\plugins\superpowers\skills\verification-before-completion\SKILL.md` | Penegakan bukti empiris mutlak (*fresh verification evidence*) sebelum membuat klaim status PASS, file clean, atau test regression clean | Phase 3.0 s.d. Phase 3.4, Final Sign-Off | **Active / Used** |
| `test-driven-development` | `C:\Users\ThinkPad\.gemini\config\plugins\superpowers\skills\test-driven-development\SKILL.md` | Metodologi Red-Green-Refactor untuk modul caching, PostGIS MVT controller, Fleet Analytics, dan GeoJSON/CSV export engine | Phase 3.1, 3.2, 3.3, 3.4 | **Planned** |
| `systematic-debugging` | `C:\Users\ThinkPad\.gemini\config\plugins\superpowers\skills\systematic-debugging\SKILL.md` | Prosedur investigasi terstruktur apabila terjadi cache poisoning, query slow down, atau rendering artifact MapLibre | Phase 3.1, 3.2, 3.3 | **Standby** |
| `executing-plans` | `C:\Users\ThinkPad\.gemini\config\plugins\superpowers\skills\executing-plans\SKILL.md` | Disiplin eksekusi berurutan per subphase (3.0 hingga 3.4) tanpa melompati tahap atau mengabaikan acceptance criteria | Seluruh Tahap Phase 3 | **Active / Used** |
| `writing-plans` | `C:\Users\ThinkPad\.gemini\config\plugins\superpowers\skills\writing-plans\SKILL.md` | Perancangan arsitektur cache multi-tier, PostGIS ST_AsMVT pipeline, dan schema analitik armada | Phase 3.1, 3.2, 3.3 | **Planned** |
| `frontend-design` | `c:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\.agents\skills\frontend-design\SKILL.md` | Perancangan UI mapcn clean, asymmetric analytical dashboard, layout density, dan telemetry HUD | Phase 3.2, 3.3 | **Planned** |
| `web-design-guidelines` | `c:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\.agents\skills\web-design-guidelines\SKILL.md` | Verifikasi kepatuhan WCAG, kontras warna palette oceanographic, keyboard navigability pada kontrol peta | Phase 3.2, 3.3 | **Planned** |
| `emil-design-eng` | `c:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\.agents\skills\emil-design-eng\SKILL.md` | Micro-interactions, transisi halus layer, tactile feedback pada floating controls mapcn | Phase 3.2, 3.3 | **Planned** |
| `impeccable` | `c:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\.agent\skills\impeccable\SKILL.md` | Anti-AI-slop typography, scientific editorial aesthetic, penataan dense data cards dan map legend | Phase 3.2, 3.3 | **Planned** |
| `ui-taste` | `c:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\.agents\skills\ui-taste\SKILL.md` | Visual polish untuk tabular analytics, typography tabular-nums pada koordinat geospasial | Phase 3.2, 3.3, 3.4 | **Planned** |

---

### 2. Disiplin Operasional & Protokol Eksekusi Phase 3.0

1. **Subphase Isolation:** Phase 3.0 dieksekusi secara terisolasi. Tidak ada implementasi caching (Phase 3.1), refactoring MVT (Phase 3.2), atau modul analitik armada (Phase 3.3) yang dimulai sebelum Phase 3.0 diaudit, disetujui, dan ditandatangani.
2. **Preservation of Golden Assets:**
   - Direktori `data/` (1.296,42 MB, 207 file) tidak boleh dimodifikasi atau dihapus.
   - Direktori `models/` (9,35 MB, 5 file) termasuk scaler dan model `.joblib` tiga spesies tidak boleh diubah.
   - Direktori `web/` (665,51 MB, 16.403 file) dipertahankan sebagai baseline referensi / rollback.
   - Database PostGIS `seacast_prigi` diakses dengan akun `seacast_readonly` dengan hak akses tulis nol.
3. **Evidence Before Assertions:** Setiap klaim efisiensi atau kebersihan wajib diverifikasi dengan pengukuran ukuran file, baris perintah validasi dependency, dan eksekusi regression test suite secara riil.

