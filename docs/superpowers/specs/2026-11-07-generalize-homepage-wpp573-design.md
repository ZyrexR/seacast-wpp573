# Desain Spesifikasi: Generalisasi Beranda Publik & Dashboard Eksekutif ke WPPNRI 573 Multi-Pelabuhan

## 1. Latar Belakang & Tujuan
Sebelumnya, antarmuka beranda publik (`/`) dan beranda dashboard (`/dashboard`) SEACAST masih terfokus secara spesifik pada **PPN Prigi**. Dengan diperluasnya SEACAST menjadi platform operasional terpadu perikanan pelagis **WPPNRI 573** (mencakup **PPN Prigi**, **PPS Cilacap**, dan **PPN Palabuhanratu**), beranda perlu digeneralisasi agar mencerminkan skala multi-pelabuhan yang sesungguhnya.

Selain itu, Hero section di Beranda Publik diubah dari tampilan polosan/peta menjadi desain **Centered Cinematic Hero** dengan latar belakang gambar atmosferik kapal pelagis di samudera lepas, tipografi bersih kontras tinggi, dan penyajian 3 komoditas ikan pelagis utama empiris (Tongkol, Cakalang, Madidihang).

---

## 2. Keputusan Desain Hasil Wawancara (/grill-me)

1. **Cakupan Wilayah & Portofolio Pelabuhan:**
   - Beranda mencakup seluruh wilayah pengelolaan perikanan **WPPNRI 573 (Samudera Hindia Selatan Jawa)**.
   - Pelabuhan pangkalan aktif: **PPN Prigi (Jatim)**, **PPS Cilacap (Jateng)**, dan **PPN Palabuhanratu (Jabar)**.
2. **Tata Letak Hero Beranda Publik (`home.blade.php`):**
   - **Layout:** Satu Kolom Terpusat (*Centered Cinematic*).
   - **Latar Belakang:** Citra sinematik kapal penangkap ikan & samudera lepas (*deep sea pelagic fisheries*) dengan overlay gradien gelap (`bg-[#071B2B]/85` ke `#091F30`) dan ambient glow cyan.
   - **Peta Preview:** Peta Leaflet di hero section publik **dihapus** agar tampilan hero lega, bersih (*clear*), dan berdampak visual kuat.
   - **Metrik Strip:** Menampilkan "Armada WPPNRI 573 Terintegrasi" (gabungan armada resmi) dilengkapi pill rincian per pelabuhan.
3. **Kartu Spesies Pelagis Unggulan (Species Habitat Dossiers):**
   - Menampilkan **3 Komoditas Terbanyak WPPNRI 573** secara seimbang:
     1. **Tongkol Lisong / Krai (*Auxis thazard / Euthynnus* spp.)** — Menggantikan Albakor sesuai statistik pendaratan empiris terbanyak.
     2. **Cakalang (*Katsuwonus pelamis*)** — Tuna permukaan cepat.
     3. **Madidihang (*Thunnus albacares*)** — Tuna epipelagis bernilai ekonomi tinggi.
4. **Beranda Dashboard Eksekutif (`overview.blade.php`):**
   - Teks header & subtitle beralih dari eksklusif PPN Prigi menjadi **WPPNRI 573 Multi-Pelabuhan**.
   - Peta Leaflet dipertahankan di dashboard, namun dipusatkan ke seluruh WPPNRI 573 (`center: [110.5, -8.8], zoom: 6.8`) dengan 3 marker pelabuhan pangkalan (Prigi, Cilacap, Palabuhanratu) yang interaktif.
   - Kartu KPI Albakor diganti dengan **Kesesuaian Habitat Tongkol (*Auxis thazard*)**.

---

## 3. Mockup Antarmuka

### A. Beranda Publik (Hero Section Terpusat Sinematik)
```text
┌─────────────────────────────────────────────────────────────────────────────┐
│                             [ LOGO SEACAST ]                                │
│      [PostGIS 17.11 Kanonikal Terverifikasi • 8.129 Sel • WPPNRI 573]       │
│                                                                             │
│                    Kecerdasan Oseanografi & Prediksi                        │
│                 Presisi Pelagis Samudera Hindia (WPP 573)                   │
│                                                                             │
│   Platform intelijen maritim terpadu untuk efisiensi bahan bakar nelayan,   │
│     keselamatan pelayaran, dan kepatuhan zonasi Permen KP No. 59/2020       │
│         di PPN Prigi, PPS Cilacap, dan PPN Palabuhanratu.                   │
│                                                                             │
│          [ Masuk Ruang Kendali ]        [ Rencanakan Melaut ]               │
│                                                                             │
│ ┌──────────────────────┬──────────────────────┬───────────────────────────┐ │
│ │ 8.129 Sel Kanonik    │ 342 Sel Hotspot      │ 2.450+ Kapal Terintegrasi │ │
│ │ Resolusi 0,10° PostGIS│ Kesesuaian HSI ≥ 0.70│ Prigi • Cilacap • Palabuhan│ │
│ └──────────────────────┴──────────────────────┴───────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────┘
```

### B. Species Dossier (3 Komoditas Utama)
```text
┌──────────────────────────┬──────────────────────────┬──────────────────────────┐
│ TONGKOL LISONG / KRAI    │ CAKALANG (SKIPJACK)      │ MADIDIHANG (YELLOWFIN)   │
│ Auxis thazard / Euthynnus│ Katsuwonus pelamis       │ Thunnus albacares        │
│ Pendaratan Terbesar      │ Optimum SST: 29.25°C     │ Koridor Lereng Benua     │
│ HSI Hari Ini: 0.682      │ HSI Hari Ini: 0.714      │ HSI Hari Ini: 0.589      │
└──────────────────────────┴──────────────────────────┴──────────────────────────┘
```

### C. Dashboard Eksekutif (`/dashboard`)
```text
Header: Ruang Kendali Eksekutif Oseanografi — WPPNRI 573 (PPN Prigi, PPS Cilacap, PPN Palabuhanratu)
KPI 1: Hotspot Tangkap Hari Ini (Sel)
KPI 2: Tinggi Gelombang Signifikan (m)
KPI 3: Kesesuaian Habitat Tongkol (HSI) [Menggantikan Albakor]
KPI 4: Proteksi Bycatch Megafauna (%)
Peta: Samudera Hindia Selatan Jawa (WPP 573) dengan Pin Marker: PPN Prigi, PPS Cilacap, PPN Palabuhanratu.
```

---

## 4. Rencana Perubahan Kode & Data

1. **`app/Http/Controllers/Web/HomeController.php`**:
   - Query `hsi_tongkol`, `hsi_cakalang`, `hsi_madidihang`.
   - Hitung total armada gabungan (PPN Prigi + PPS Cilacap + estimasi Palabuhanratu) atau sajikan rincian array per pelabuhan.
2. **`app/Http/Controllers/Web/DashboardController.php`**:
   - Update `index()` method untuk mengkueri rata-rata `hsi_tongkol`.
   - Teruskan koordinat 3 pelabuhan pangkalan ke view `overview.blade.php`.
3. **`resources/views/public/home.blade.php`**:
   - Ubah hero section ke tata letak centered cinematic dengan background visual kapal pelagis & deep sea.
   - Hapus container peta dari hero publik agar halaman depan bersih.
   - Perbarui species cards ke 3 komoditas utama (Tongkol, Cakalang, Madidihang).
4. **`resources/views/dashboard/overview.blade.php`**:
   - Perbarui copy judul, subtitle, kartu KPI Tongkol, dan center koordinat peta ke WPPNRI 573 dengan marker 3 pelabuhan pangkalan.
5. **Aset Gambar Hero:**
   - Menyediakan background image maritim beresolusi tinggi di `public/images/hero-marine-bg.webp` (atau SVG gradient-mesh atmosferik berkualitas tinggi).
6. **Pengujian:**
   - Jalankan `php ./vendor/bin/phpunit` dan pastikan seluruh tes regresi tetap hijau (170+ passed).
