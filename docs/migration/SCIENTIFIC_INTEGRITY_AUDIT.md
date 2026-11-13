# SEACAST PRIGI V3 — SCIENTIFIC INTEGRITY AUDIT
**Audit Date:** 4 November 2026  
**Auditor:** Scientific Software Engineer & Fisheries Modeling Specialist  
**Reference Document:** PRD SEACAST PRIGI v2.3.2  

---

## 1. SPATIAL BASELINE & HOMEPORT SPECIFICATIONS

- **Wilayah Pengelolaan Perikanan:** WPPNRI 573 (Samudera Hindia sebelah selatan Jawa hingga Nusa Tenggara).
- **Cakupan Koordinat:** `102.50° E – 126.40° E`, `5.00° S – 14.00° S`.
- **Homeport Rujukan Operasional:**
  - Nama: Pelabuhan Perikanan Nusantara (PPN) Prigi, Kabupaten Trenggalek, Jawa Timur.
  - Bujur (Longitude): `111.7275° E`
  - Lintang (Latitude): `-8.2833° S`
  - Proyeksi Spasial: `EPSG:4326 (WGS 84)`
- **Karakteristik Grid:** 8.129 sel kanonikal beresolusi 0,10° (~11,13 km).

---

## 2. SPESIFIKASI SPESIES TARGET & EVALUASI MODEL (AUC / TSS)

Evaluasi model aktual diperoleh langsung dari file kredibel `models/sdm/ensemble/model_evaluations_summary.json`:

| Spesies | Nama Ilmiah | Bobot MaxEnt | Bobot RF | Bobot GLM | Ensemble AUC | Ensemble TSS | Status Metodologis |
| :--- | :--- | :---: | :---: | :---: | :---: | :---: | :--- |
| **Cakalang** | *Katsuwonus pelamis* | 0,320 | 0,355 | 0,325 | **0,795** | **0,630** | Heuristic Proxy Screening |
| **Madidihang** | *Thunnus albacares* | 0,295 | 0,365 | 0,340 | **0,784** | **0,606** | Heuristic Proxy Screening |
| **Legacy species removed from TCT scope** | *Current TCT scope exclusion* | 0,000 | 0,517 | 0,483 | **0,860** | **0,743** | Heuristic Proxy Screening |

### Catatan Kritis Terhadap Integritas Ilmiah (Model Caveat)
1. **Bobot Legacy species removed from TCT scope MaxEnt Di-nolkan:** Bobot MaxEnt untuk Legacy species removed from TCT scope bernilai `0.000` karena performa evaluasi proxy sirkular hanya menghasilkan AUC 0,507 ($\le 0,51$).
2. **Kategori Bobot:** Ditandai secara jujur sebagai `PROVISIONAL_ANALYST_SELECTED_WEIGHTS`.
3. **Pemisahan Konsep:** Nilai AUC-ROC (Area Under Curve) mengukur kemampuan diskriminasi model ($0,00 - 1,00$), sedangkan HSI (Habitat Suitability Index) mengukur derajat kesesuaian lingkungan harian ($0,000 - 1,000$). Keduanya dilarang dicampuradukkan dalam kode Laravel.

---

## 3. FORMULASI HABITAT SUITABILITY INDEX (HSI)

Prediksi HSI dihitung melalui perpaduan variabel oseanografi:
$$\text{HSI}_{\text{spesies}} = w_{\text{maxent}} P_{\text{maxent}} + w_{\text{rf}} P_{\text{rf}} + w_{\text{glm}} P_{\text{glm}}$$
dengan normalisasi min-max rentang $[0,000 - 1,000]$.

### Kontribusi Variabel Lingkungan:
- **Cakalang:** SST (38,5%), Klorofil-a (31,2%), Front Termal (18,6%), Batimetri (8,2%), Slope (3,5%).
- **Madidihang:** Front Termal (34,8%), SST (29,4%), Klorofil-a (20,1%), Batimetri (11,5%), Slope (4,2%).
- **Legacy species removed from TCT scope:** SST (46,2%), Batimetri (25,1%), Klorofil-a (15,3%), Front Termal (9,8%), Slope (3,6%).

### Kriteria Hotspot Penangkapan:
$$\text{is\_hotspot} = (\text{HSI}_{\text{cakalang}} \ge 0,70) \lor (\text{HSI}_{\text{madidihang}} \ge 0,70)$$

---

## 4. DYNAMIC OCEAN MANAGEMENT (DOM) — ATURAN ZONASI & HIERARKI

Sesuai kode sumber aktual di `scripts/daily_inference.py#L137-L153`:

| Kode Zona | Nama Zona Fungsional | Kriteria Ilmiah Pembentukan | Tingkat Prioritas |
| :--- | :--- | :--- | :---: |
| **Z4** | Nursery Conservation Ground | Kedalaman $\le 150\text{ m}$ dan Jarak Pelabuhan $\le 15\text{ NM}$ | **1 (Tertinggi Konservasi)** |
| **Z5** | Bycatch Risk Screening | Kedalaman $150–1.200\text{ m}$, SST $\le 26,5^\circ\text{C}$, dan $\text{grad\_sst} \ge 0,035^\circ\text{C/km}$ | **2 (Prioritas Mitigasi)** |
| **Z3** | Feeding Core Ground | Klorofil-a $\ge 0,40\text{ mg/m}^3$ dan $\text{grad\_sst} \ge 0,035^\circ\text{C/km}$ | **3** |
| **Z1** | PFG Inshore | Jarak $\le 12\text{ NM}$ dan ($\text{HSI}_{\text{cakalang}} \ge 0,55 \lor \text{HSI}_{\text{madidihang}} \ge 0,55$) | **4** |
| **Z2** | PFG Offshore | Jarak $12–60\text{ NM}$ dan ($\text{HSI}_{\text{cakalang}} \ge 0,55 \lor \text{HSI}_{\text{madidihang}} \ge 0,55$) | **5** |
| **GEN** | General Oceanic | Seluruh sel yang tidak memenuhi kriteria di atas | **6 (Dasar)** |

### Hierarki Penegakan Logika:
`Z4 -> Z5 -> Z3 -> Z1 -> Z2 -> GENERAL_OCEANIC`
> **PENTING:** Aturan prioritas ini ditegakkan mutlak dalam Laravel untuk memastikan bahwa perlindungan benih ikan (Z4) dan screening bycatch (Z5) selalu menganulir rekomendasi penangkapan inshore/offshore (Z1/Z2).

---

## 5. KLASIFIKASI BYCATCH TRAFFIC LIGHT (STANDAR IOTC)

- **GREEN (Hijau - Aman):** Seluruh sel di luar lereng paparan benua (kedalaman $< 150\text{ m}$ atau $> 1.200\text{ m}$).
- **RED (Merah - Risiko Tinggi):** Berada pada lereng benua ($150–1.200\text{ m}$), terdeteksi `is_bycatch_risk` (SST dingin $\le 26,5^\circ\text{C}$ & front termal $\ge 0,035^\circ\text{C/km}$), **DAN** $\text{HSI}_{\text{cakalang}} \ge 0,70$.
- **YELLOW (Kuning - Waspada):** Berada pada lereng benua ($150–1.200\text{ m}$) tetapi tidak memenuhi ambang batas merah; armada diwajibkan menggunakan *circle hook* 16/0 dan *de-hooker*.

---

## 6. PARAMETER KESELAMATAN BMKG & ASUMSI ARMADA

### 6.1. Standar Keselamatan Gelombang:
- Rata-rata Tinggi Gelombang Signifikan $\ge 3,5\text{ m}$: `BAHAYA_GELOMBANG_EKSTREM`.
- Rata-rata Tinggi Gelombang Signifikan $\ge 2,0\text{ m}$: `WASPADA_KAPAL_KECIL_GELOMBANG_TINGGI` (rekomendasi kapal $<10\text{ GT}$ dibatalkan).
- Rata-rata Tinggi Gelombang Signifikan $< 2,0\text{ m}$: `AMAN_MELAUT`.
- **Status Transparansi:** Dilabeli sebagai **"Fallback Terkalibrasi Aktif"** tanpa mengklaim sinkronisasi API live BMKG sebelum integrasi resmi terhubung.

### 6.2. Asumsi Operasional Armada:
- Kecepatan jelajah rata-rata: `7,5 knots`.
- Konsumsi BBM Small Boat ($<10\text{ GT}$): `3,5 Liter / Mil Laut` (pulang-pergi).
- Konsumsi BBM Large Purse Seine ($20–30\text{ GT}$): `7,5 Liter / Mil Laut` (pulang-pergi).
- Status label: Tetap ditandai sebagai **Asumsi Operasional Model**, bukan pengukuran tangki riil.

### 6.3. Nominal CPUE Proxy:
- Rumus: $\text{CPUE Proxy} = \frac{\text{Total Pendaratan Pelagis (kg)}}{\text{Jumlah Trip Resmi PPN Prigi}}$.
- Label wajib: **Proksi CPUE Tahunan (Nominal)** dengan disclaimer ketiadaan data mikro *hook rate* dan *soaking time*.

