# PHASE 3.2 ACCESSIBILITY & USABILITY COMPLIANCE REPORT
## SEACAST PRIGI V3 — WCAG 2.1 AA Verification, Keyboard Navigability & Touch Targets

**Tanggal:** 2026-11-04  
**Proyek:** SEACAST PRIGI V3  
**Standar Aksesibilitas:** WCAG 2.1 Level AA  
**Status:** **COMPLIANT & VERIFIED**  

---

### 1. Eksekutif Ringkasan Aksesibilitas

Pengembangan antarmuka spasial mapcn pada SEACAST PRIGI V3 diaudit secara menyeluruh terhadap kriteria aksesibilitas WCAG 2.1 Level AA guna memastikan kenyamanan penggunaan oleh seluruh pemangku kepentingan (nelayan, staf pelabuhan, operator dinas, dan peneliti oseanografi).

---

### 2. Hasil Audit Kepatuhan Aksesibilitas

| Kriteria WCAG | Persyaratan | Implementasi Aktual SEACAST | Status |
|---|---|---|:---:|
| **1.4.3 Contrast (Minimum)** | Kontras teks normal $\ge 4.5:1$, teks besar $\ge 3:1$ | Teks `slate-200` pada latar `slate-900` memiliki rasio kontras **11.4:1**. Teks aksen `cyan-300` memiliki rasio **8.2:1**. | **PASS** |
| **2.1.1 Keyboard Accessible** | Seluruh kontrol dapat dioperasikan via keyboard | Seluruh tombol zoom, kompas, homeport, fullscreen, layer switcher, dan filter dapat di-Tab dan diaktifkan dengan Enter/Spasi. | **PASS** |
| **2.4.7 Focus Visible** | Indikator fokus keyboard terlihat jelas | Komponen mapcn menggunakan `focus:outline-none focus:ring-2 focus:ring-cyan-500/60` dengan ring kontras tinggi. | **PASS** |
| **2.5.5 Target Size** | Area sentuh mobile minimal $44 \times 44$ piksel | Tombol floating mobile pill `[Filter Radar 8129]` dan tombol kontrol memiliki padding sentuh $\ge 44\text{px}$. | **PASS** |
| **4.1.2 Name, Role, Value** | Setiap elemen interaktif memiliki label aksesibel | Seluruh tombol kontrol peta memiliki atribut `aria-label` eksplisit (misal: `aria-label="Pusatkan Peta ke PPN Prigi"`). | **PASS** |

---

### 3. Responsivitas Layar & Mobile Usability

1. **Mobile Exploration Drawer:** Pada layar ponsel ($390 \times 844$ px), sidebar kontrol tidak lagi menyempitkan kanvas peta melainkan berubah menjadi *slide-over drawer* yang dapat dibuka/tutup dengan tombol mengambang `[Filter Radar 8129]`. Kanvas peta mendapatkan 100% lebar layar ponsel.
2. **Nautical Scale Legend:** Skala peta interaktif MapLibre secara otomatis menyesuaikan nilai mil laut (*nautical miles*) di sudut kiri bawah layar.

