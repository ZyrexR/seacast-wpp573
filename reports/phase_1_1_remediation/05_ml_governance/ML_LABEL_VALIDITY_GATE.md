# MACHINE LEARNING LABEL VALIDITY GATE & MODEL BLOCKER DECISION
## SEACAST PRIGI — Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi
### WPPNRI 573 | Independent AI/ML Data Governance Audit

**Document ID:** ML-GATE-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Auditor:** Machine Learning Data Governance Specialist & Independent SQA Auditor  
**Date:** 2026-11-02  
**Final Governance Status:** **`SPECIES_MODEL_NOT_READY — MODEL TRAINING LOCKED`**  

---

## 1. STATUS KESIAPAN MODEL SPESIFIK SPESIES (MANDATORY GATE BLOCKER)

Tim audit AI/ML data governance menetapkan keputusan mutlak:

| Nama Spesies Target | Nama Ilmiah | Kode Taksonomi ITIS | Ketersediaan Titik GPS Tangkapan | Status Kesiapan Model ML | Rekomendasi Eksekusi |
|---|---|---|---|---|---|
| **Cakalang** | *Katsuwonus pelamis* | `166240` | **TIDAK TERSEDIA (0 titik)** | **`SPECIES_MODEL_NOT_READY`** | **DILARANG DILATIH (LOCKED)** |
| **Madidihang** | *Thunnus albacares* | `166277` | **TIDAK TERSEDIA (0 titik)** | **`SPECIES_MODEL_NOT_READY`** | **DILARANG DILATIH (LOCKED)** |
| **Albakor** | *Thunnus alalunga* | `166278` | **TIDAK TERSEDIA (0 titik)** | **`SPECIES_MODEL_NOT_READY`** | **DILARANG DILATIH (LOCKED)** |

---

## 2. BATASAN KETAT DATA PENDARATAN PELABUHAN PPN PRIGI

Data pendaratan bulanan PPN Prigi dicatat di dermaga pelabuhan perikanan.
Data ini **TIDAK MEMILIKI ATRIBUT LOKASI KOORDINAT PENANGKAPAN (LAT/LON)**, jalur berlayar kapal (*tracking*), atau catatan tebar jaring (*gear set position*).

### A. Penggunaan yang DIIZINKAN (Permitted Uses):
1. Menggambarkan tren deret waktu volume produksi bulanan di tingkat pelabuhan.
2. Analisis fluktuasi musiman tangkapan perikanan (siklus Monsun Timur vs Monsun Barat).
3. Karakterisasi komposisi persentase spesies pelagis tahunan.
4. Konteks perikanan makro dalam laporan naratif.

### B. Penggunaan yang DILARANG KERAS (Prohibited Uses):
1. **DILARANG** merekayasa titik koordinat acak (*random pseudo-coordinates*) di perairan WPPNRI 573 lalu mengaitkannya dengan berat pendaratan bulanan dermaga.
2. **DILARANG** melatih model MaxEnt, Random Forest, XGBoost, atau Neural Network untuk menghasilkan peta kesesuaian habitat spesies (*habitat suitability index*) berbasis data non-spasial pendaratan pelabuhan.
3. **DILARANG** mengklaim bahwa sel kisi dengan probabilitas prediksi tinggi merupakan "lokasi penangkapan aktual ikan" (*actual fishing ground*).

---

## 3. PERSYARATAN WAJIB MEMBUKA GATE MODEL HABITAT SPESIES

Pelatihan model habitat spesies hanya boleh dibuka pada masa mendatang apabila tersedia dataset kejadian mandiri (*independent occurrence records*) yang memenuhi 8 syarat integritas data ilmiah:
1. **Koordinat GPS Riil:** Lintang dan bujur penangkapan dengan ketidakpastian spasial $< 10\text{ km}$.
2. **Waktu Pengamatan Presisi:** Tanggal dan jam penarikan jaring yang sinkron dengan waktu rekaman satelit.
3. **Verifikasi Taksonomi di Kapal:** Terverifikasi oleh observer ilmiah atau logbook terstandarisasi.
4. **Data Upaya Penangkapan (*Fishing Effort*):** Jumlah setting jaring, durasi penarikan jaring, atau CPUE terukur.
5. **Cakupan Spasial Representatif:** Sampel tersebar di perairan pantai, transisi, dan samudra lepas WPPNRI 573.
6. **Cakupan Temporal Multi-Musim:** Mewakili musim barat, timur, dan peralihan.
7. **Pemisahan Data Validasi Independen:** Set pengujian (*test set*) yang terisolasi secara spasial dan temporal dari set pelatihan (*spatial-block cross-validation*).

---

## 4. ALTERNATIF SAINTIFIK YANG DIIZINKAN: ZONASI OSEANOGRAFI LINGKUNGAN

Sebagai ganti dari model prediksi spesies ikan semu, sistem SEACAST PRIGI pada tahap ini hanya diizinkan mengembangkan:

**`OCEANOGRAPHIC ENVIRONMENTAL ZONATION (OEZ)`**

### Aturan Nomenklatur Luaran (Strict Output Naming):
- **Luaran yang Diizinkan:**
  - *Environmental Clusters* (Klaster Lingkungan)
  - *Oceanographic Regimes* (Rezim Dinamika Oseanografi)
  - *Potential Oceanographic Zones* (Zona Oseanografi Potensial)
  - *Upwelling & Frontal Aggregation Zones* (Zona Agregasi Frontal)
- **Nomenklatur yang DILARANG KERAS Digunakan:**
  - *Fishing Grounds* (DILARANG)
  - *Nursery Grounds* (DILARANG)
  - *Spawning Grounds* (DILARANG)
  - *Feeding Grounds* (DILARANG)
  - *Fish Distribution Map* (DILARANG)
"""

with open(os.path.join(WORKSPACE, "reports", "phase_1_1_remediation", "05_ml_governance", "ML_LABEL_VALIDITY_GATE.md"), "w", encoding="utf-8") as f:
    f.write(Content)

print("[Audit G] Successfully generated ML_LABEL_VALIDITY_GATE.md.")

