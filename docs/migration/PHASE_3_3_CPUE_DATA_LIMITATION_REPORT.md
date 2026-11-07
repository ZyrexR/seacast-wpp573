# PHASE 3.3 SCIENTIFIC DISCLOSURE: CPUE DATA LIMITATIONS
## SEACAST PRIGI V3 — Nominal CPUE Proxy vs. Standardized CPUE & Fishing Effort Caveats

**Tanggal:** 2026-11-04  
**Proyek:** SEACAST PRIGI V3  
**Standar Ilmiah:** IOTC / WCPFC Fisheries Assessment Guidelines  
**Status:** **AUDITED, ENFORCED & SCIENTIFICALLY RESTRICTED**  

---

### 1. Mandat Batasan Metodologi CPUE

Dalam kajian biologi perikanan tangkap (*fisheries science*), **Catch Per Unit Effort (CPUE)** merupakan indikator kelimpahan stok (*stock abundance index*) hanya apabila telah melalui proses standarisasi statistik (*Standardized CPUE* via GLM/GAM) yang memperhitungkan faktor upaya penangkapan riil (*fishing effort*), daya mesin kapal, tipe alat tangkap, waktu rendam (*soak time*), dan koordinat posisi penangkapan (*fishing ground*).

Tabel `prigi_port_landings` hanya mencatat **tiket timbang agregat bulanan** dan **jumlah trip melaut kasar**. Oleh karena itu, arsitektur SEACAST PRIGI V3 secara tegas membatasi penggunaan data tersebut guna menjaga integritas metodologis.

---

### 2. Larangan Rekayasa & Pembatasan Klaim (Scientific Restrictions)

Sistem SEACAST PRIGI V3 secara mutlak **DILARANG** melakukan:

1. **CPUE per Zona Jarak (Distance Zone CPUE):** Tidak diperkenankan menghitung rasio CPUE per zona jarak (0–12 NM vs 12–30 NM) karena tiket timbang pelabuhan tidak memuat koordinat GPS penangkapan aktual per trip.
2. **CPUE per Kelas GT Kapal:** Tidak diperkenankan menghitung rasio hasil tangkapan terhadap armada kapal GT tertentu, karena data pendaratan tidak dipisahkan berdasarkan GT kapal yang membawanya.
3. **Tracking Kapal Individual:** Data 1.269 unit kapal pada `prigi_fleet_by_gt` adalah data statistik registrasi tahunan per kelas tonase, bukan data transponder dinamis AIS atau VMS.
4. **Estimasi Biomassa Stok:** Angka pendaratan tidak boleh diklaim sebagai biomassa populasi laut tanpa model surplus produksi analitis.

---

### 3. Klasifikasi Metrik yang Sah Disajikan

Sesuai dengan ketentuan PRD dan audit ilmiah, sistem menyajikan:
- **Tren Pendaratan Historis (11 Tahun):** Volume pendaratan total pelagis per bulan dan per tahun.
- **Komposisi Spesies Nominal:** Proporsi tangkapan Cakalang, Madidihang, dan Tongkol.
- **CPUE Proxy Nominal:** Didefinisikan secara transparan sebagai $\text{CPUE Proxy} = \frac{\text{Total Tangkapan (kg)}}{\text{Jumlah Trip Tercatat}}$, dan **selalu disertai label "Proxy Nominal"**.
- **Korelasi Musiman (Seasonality):** Keterkaitan antara fluktuasi pendaratan bulanan dengan siklus upwelling monsun tenggara di WPPNRI 573.

