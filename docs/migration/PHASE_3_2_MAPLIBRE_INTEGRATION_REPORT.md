# PHASE 3.2 MAPLIBRE INTEGRATION & LIFECYCLE MANAGEMENT REPORT
## SEACAST PRIGI V3 — WebGL Rendering Engine, Single Ownership & DOM Morphing Resilience

**Tanggal:** 2026-11-04  
**Proyek:** SEACAST PRIGI V3  
**Rendering Engine:** MapLibre GL JS v6.16.0 (WebGL Native)  
**Status:** **INTEGRATED & VERIFIED**  

---

### 1. Eksekutif Ringkasan Integrasi MapLibre

Integrasi MapLibre GL JS pada SEACAST PRIGI V3 dirancang dengan mematuhi prinsip **"Single Lifecycle Ownership"**. Kanvas peta sepenuhnya dimiliki dan dikendalikan oleh modul Alpine.js (`x-data="dpiRadarApp()"`) tanpa interferensi dari Livewire DOM morphing, guna mencegah re-initialization ganda, hilangnya WebGL context, atau kebocoran memori (*memory leak*).

---

### 2. Penanganan Lifecycle & Pencegahan Masalah Umum

#### 2.1. Inisialisasi Tunggal (Single Initialization)
MapLibre hanya diinisialisasi satu kali saat hook `x-init="initRadar()"` dipanggil. Elemen container dibungkus menggunakan `x-ref="mapContainer"` murni tanpa atribut `wire:model` langsung pada container WebGL.

#### 2.2. Sinkronisasi Rotasi & Kompas
Event listener `'rotate'` disinkronkan ke state reaktif Alpine:
```javascript
this.map.on('rotate', () => {
    this.mapBearing = Math.round(this.map.getBearing());
});
```
Hal ini memungkinkan ikon jarum kompas pada `<x-ui.map-controls>` berputar secara dinamis sesuai orientasi peta pengguna, dan mengklik kompas mengeksekusi `resetNorthPitch({ duration: 500 })`.

#### 2.3. Penanganan Resize & Layar Penuh
Event listener `fullscreenchange` pada dokumen secara otomatis memanggil `map.resize()` setelah transisi 150 ms untuk memastikan rasio aspek viewport dan buffer WebGL tidak terdistorsi saat berganti ukuran layar.

#### 2.4. Pembersihan Instance (Cleanup Protocol)
Saat komponen dihancurkan (*unmount*), metode `map.remove()` dipanggil untuk melepaskan seluruh listener DOM, worker thread WebGL, dan alokasi memori buffer grafis GPU.

---

### 3. Integrasi Sumber Ubin Vektor PostGIS

MapLibre mendukung penambahan layer MVT biner langsung dari endpoint Laravel:

```javascript
this.map.addSource('predictions-mvt', {
    type: 'vector',
    tiles: [
        window.location.origin + '/api/tiles/predictions/{z}/{x}/{y}.pbf?date=' + this.predictionDate
    ],
    minzoom: 4,
    maxzoom: 16
});
```

Peta dapat beralih antara rendering ubin vektor MVT (untuk performa tinggi di zoom menengah/jauh) dan GeoJSON lokal (untuk query analitis client-side cepat).

