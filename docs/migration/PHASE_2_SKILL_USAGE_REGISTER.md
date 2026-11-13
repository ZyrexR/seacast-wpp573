# PHASE 2 SKILL USAGE REGISTER
## SEACAST PRIGI V3 — Core Radar Engine & Dynamic Filter

**Tanggal:** 2026-11-04  
**Proyek:** SEACAST PRIGI V3  
**Framework:** Laravel 13.40.0 + PHP 8.3.36  
**Spatial Engine:** PostgreSQL 17.11 + PostGIS 3.6.2  
**Scientific Engine:** Python 3.14.7  
**Status:** AUDITED & REGISTERED  

---

### 1. Inventaris Skill & Registrasi Penggunaan

Berdasarkan mandat pemeriksaan keterampilan (Skill Discovery) pada workspace `.agent/`, `.agents/`, plugins internal, dan global builtin Antigravity, berikut adalah tabel registrasi pemanfaatan skill untuk seluruh subfase implementasi Phase 2:

| Skill | Lokasi | Relevansi | Subphase Penggunaan | Status |
|---|---|---|---|---|
| `using-superpowers` | `C:\Users\ThinkPad\.gemini\config\plugins\superpowers\skills\using-superpowers\SKILL.md` | Protokol tata kelola eksekusi agen, pemanggilan skill terstruktur, dan penegakan disiplin engineering | Seluruh Tahap Phase 2 (Global Meta-Governance) | **Used** |
| `test-driven-development` | `C:\Users\ThinkPad\.gemini\config\plugins\superpowers\skills\test-driven-development\SKILL.md` | Metodologi Red-Green-Refactor untuk implementasi data access, formula HSI, DOM rule prioritization, Bycatch engine, dan dynamic filtering | Phase 2.1, 2.2, 2.4, 2.5, 2.6, 2.7, 2.8, 2.9, 2.10 | **Used** |
| `verification-before-completion` | `C:\Users\ThinkPad\.gemini\config\plugins\superpowers\skills\verification-before-completion\SKILL.md` | Penegakan bukti empiris mutlak (fresh verification evidence) sebelum membuat klaim status PASS, integrasi berhasil, atau data valid | Phase 2.0 s.d. Phase 2.10, Final Sign-Off | **Used** |
| `systematic-debugging` | `C:\Users\ThinkPad\.gemini\config\plugins\superpowers\skills\systematic-debugging\SKILL.md` | Prosedur investigasi terstruktur apabila terjadi mismatch data PostGIS, kegagalan bridge Python, atau deviasi numerik | Phase 2.1, 2.2, 2.3, 2.4, 2.9 (Saat investigasi error) | **Used** |
| `executing-plans` | `C:\Users\ThinkPad\.gemini\config\plugins\superpowers\skills\executing-plans\SKILL.md` | Disiplin eksekusi berurutan per subphase (2.0 hingga 2.10) tanpa melompati tahap atau mengabaikan acceptance criteria | Seluruh Tahap Phase 2 | **Used** |
| `writing-plans` | `C:\Users\ThinkPad\.gemini\config\plugins\superpowers\skills\writing-plans\SKILL.md` | Standar perancangan arsitektur dan spesifikasi teknis modular monolith Laravel | Phase 2.1, 2.2, 2.8, 2.9 | **Used** |
| `frontend-design` | `c:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\.agents\skills\frontend-design\SKILL.md` | Perancangan interface visual radar MapLibre, panel kontrol filter dinamis, layer toggle, dan data density | Phase 2.8, 2.10 | **Used** |
| `web-design-guidelines` | `c:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\.agents\skills\web-design-guidelines\SKILL.md` | Verifikasi kepatuhan aksesibilitas WCAG, kontras warna legend ilmiah, struktur tipografi, dan responsivitas UI | Phase 2.8, 2.10 | **Used** |
| `emil-design-eng` | `c:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\.agents\skills\emil-design-eng\SKILL.md` | Interaksi spasial tingkat tinggi: kehalusan transisi layer, micro-feedback tooltip, debounce filter, dan layout density | Phase 2.8, 2.10 | **Used** |
| `impeccable` | `c:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\.agent\skills\impeccable\SKILL.md` | Audit estetika editorial ilmiah, eliminasi AI slop pada komponen peta, penataan legenda oseanografi, dan token desain | Phase 2.10 | **Used** |
| `ui-taste` | `c:\Users\ThinkPad\.gemini\antigravity\scratch\prigi-pelagis\.agents\skills\ui-taste\SKILL.md` | Penyempurnaan styling komponen radar kontrol, typography tabular numerik, dan status telemetri | Phase 2.8, 2.10 | **Used** |
| `Laravel Architecture & Backend Engineering` | *Workspace internal / Builtin* | Modul arsitektur backend Laravel spesifik | Phase 2.1, 2.2, 2.9 | **SKILL NOT AVAILABLE** (Dijalankan via Laravel 13 Best Practice & PSR-12 Architecture) |
| `PHP Application Development` | *Workspace internal / Builtin* | Rekayasa aplikasi PHP tingkat lanjut | Phase 2.1 s.d. 2.9 | **SKILL NOT AVAILABLE** (Dijalankan via Modern PHP 8.3 Strict Typing Standards) |
| `Python Scientific Computing` | *Workspace internal / Builtin* | Eksekusi komputasi ilmiah Python dan bridging | Phase 2.2, 2.4 | **SKILL NOT AVAILABLE** (Dijalankan via Process Architecture & JSON IPC Bridge) |
| `Geospatial Analysis & PostGIS` | *Workspace internal / Builtin* | Operasi spasial PostGIS dan optimasi GiST index | Phase 2.1, 2.3, 2.6 | **SKILL NOT AVAILABLE** (Dijalankan via OGC Standards & PostGIS 3.6 Geodesic SQL) |
| `GIS and Spatial Database Engineering` | *Workspace internal / Builtin* | Rekayasa skema geospasial | Phase 2.1, 2.6 | **SKILL NOT AVAILABLE** (Dijalankan via PostGIS Architecture Standards) |
| `Remote Sensing & Oceanographic Data` | *Workspace internal / Builtin* | Pengolahan variabel oseanografi fisik | Phase 2.3, 2.6, 2.7 | **SKILL NOT AVAILABLE** (Dijalankan via Baseline Audit & PRD SEACAST PRIGI V2.3.2) |
| `Machine Learning & Ensemble Modeling` | *Workspace internal / Builtin* | Inferensi model habitat Cakalang, Madidihang, Legacy species removed from TCT scope | Phase 2.4, 2.5 | **SKILL NOT AVAILABLE** (Dijalankan via Model Evaluation Summary & Baseline Weights) |
| `Security & Application Architecture` | *Workspace internal / Builtin* | Audit keamanan command injection, role enforcement | Phase 2.1, 2.2, 2.9 | **SKILL NOT AVAILABLE** (Dijalankan via OWASP Top 10 & Laravel Security Best Practices) |

---

### 2. Disiplin Operasional & Protokol Eksekusi

1. **Test-First Requirement:** Setiap service, repository, dan bridge yang dibangun pada Phase 2 wajib memiliki tes PHPUnit yang mendahului atau memverifikasi secara langsung fungsionalitasnya.
2. **Read-Only Database Guarantee:** Koneksi `seacast_readonly` diuji secara berkesinambungan agar tidak memiliki hak akses tulis pada tabel `canonical_grids`, `daily_predictions`, `grid_monthly_predictions`, `prigi_port_landings`, dan `prigi_fleet_by_gt`.
3. **Traceability of Computation:** Tidak ada angka atau status zonasi yang di-generate tanpa keterlacakan formula, versi model, input parameter, dan timestamp.
4. **Evidence Before Assertions:** Setiap klaim kepatuhan didukung oleh keluaran aktual command, query profiling, atau test run.

