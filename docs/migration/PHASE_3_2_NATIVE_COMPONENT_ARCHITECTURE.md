# PHASE 3.2 NATIVE COMPONENT ARCHITECTURE REPORT
## SEACAST PRIGI V3 — Reusable Blade Component System for Geospatial Intelligence

**Tanggal:** 2026-11-04  
**Proyek:** SEACAST PRIGI V3  
**Framework:** Laravel 13.40.0 + Blade + Alpine.js  
**Status:** **ARCHITECTED & DEPLOYED**  

---

### 1. Eksekutif Ringkasan Komponen

Lapisan antarmuka geospasial SEACAST PRIGI V3 distandarisasi menggunakan pustaka komponen Blade native di bawah direktori `resources/views/components/ui/`. Setiap komponen dirancang modular, dapat dikomposisikan (*composable*), dan memiliki enkapsulasi gaya Tailwind CSS atomik.

---

### 2. Katalog Komponen Spasial mapcn

#### 2.1. `<x-ui.map>` (`map.blade.php`)
- **Fungsi:** Wrapper container utama kanvas peta WebGL MapLibre.
- **Props:**
  - `mapId` (string, default: `'seacast-maplibre-container'`): ID elemen DOM kanvas.
  - `center` (array, default: `[111.7275, -8.45]`): Koordinat awal WGS84 Teluk Prigi.
  - `zoom` (float, default: `8.0`): Tingkat perbesaran awal.
- **Slot:** Menerima overlay kontrol, legenda, dan inspektor.

#### 2.2. `<x-ui.map-controls>` (`map-controls.blade.php`)
- **Fungsi:** Toolbar vertikal mengambang (*floating pill*) yang menyatukan kendali peta spasial.
- **Fitur Kontrol:**
  1. `Perbesar Peta (+)` — Zoom in dengan transisi halus.
  2. `Perkecil Peta (-)` — Zoom out dengan transisi halus.
  3. `Reset Arah Utara (Kompas)` — Mengembalikan bearing ke 0° dan pitch ke 0°.
  4. `Pusatkan ke PPN Prigi` — Fly-to koordinat PPN Prigi ($111.7275^\circ\text{E}, -8.2833^\circ\text{S}$).
  5. `Layar Penuh (Fullscreen)` — Memaksimalkan kanvas peta ke mode layar penuh.

#### 2.3. `<x-ui.map-layers>` (`map-layers.blade.php`)
- **Fungsi:** Switcher layer spasial horizontal mengambang (*floating layer pills*).
- **Mode Layer:**
  - `hsi`: Peta panas kesesuaian habitat tuna (Cakalang, Madidihang, Albakor).
  - `dom`: Zonasi konservasi dinamis Z1–Z5 dan General Oceanic.
  - `bycatch`: Peta risiko tangkapan sampingan (Green, Yellow, Red).
  - `bathymetry`: Kontur kedalaman laut dan lereng batimetri GEBCO.

#### 2.4. `<x-ui.map-legend>` (`map-legend.blade.php`)
- **Fungsi:** Panel legenda ilmiah adaptif di sudut kanan bawah.
- **Karakteristik:** Menampilkan skala warna kontinyu untuk HSI ($0.00 - 1.00$) dan skala diskrit untuk zonasi DOM dan Bycatch dengan penanda datum SRID EPSG:4326.

#### 2.5. `<x-ui.map-inspector>` (`map-inspector.blade.php`)
- **Fungsi:** Kartu inspeksi detail sel grid kanonikal PostGIS yang muncul saat pengguna mengklik poligon pada peta.
- **Informasi yang Ditampilkan:**
  - ID Sel Kanonikal dan Koordinat WGS84.
  - Kedalaman laut aktual (meter) dan jarak ke PPN Prigi (NM & km).
  - Variabel oseanografi: SST (°C), Klorofil-a ($\text{mg/m}^3$), Tinggi Gelombang (m).
  - Rincian skor HSI untuk tiga spesies tuna secara terpisah dengan visual bar.
  - Status zonasi DOM dan rekomendasi operasional bycatch IOTC.

#### 2.6. `<x-ui.map-marker>` & `<x-ui.map-popup>`
- **Fungsi:** Komponen visual untuk penanda titik penting (Homeport PPN Prigi, pelampung oseanografi, stasiun BMKG) dan popup WebGL informatif.

