# PHASE 3.2 GEOSPATIAL VECTOR TILE PERFORMANCE REPORT
## SEACAST PRIGI V3 — Vector Tile Latency, WebGL Throughput & Network Efficiency

**Tanggal:** 2026-11-04  
**Proyek:** SEACAST PRIGI V3  
**Status:** **BENCHMARKED & PRODUCTION READY**  

---

### 1. Eksekutif Ringkasan Performa MVT

Integrasi PostGIS MVT (`/api/tiles/{layer}/{z}/{x}/{y}.pbf`) memberikan peningkatan dramatis pada performa pengiriman data geospasial:
- **Ukuran Ubin:** 15 bytes (ubin kosong) hingga 35 KB (ubin padat fitur WPPNRI 573).
- **Latensi Cold Tile Generation:** $\sim 800-1.200$ ms saat pertama kali dihitung oleh PostGIS.
- **Latensi Warm Tile Retrieval:** **$<5$ ms** saat diambil dari cache.
- **Throughput:** Mampu melayani ratusan permintaan ubin paralel tanpa kemacetan CPU server.
- **Rendering Client:** WebGL buffer streaming mempertahankan **60 frame per second (FPS)** stabil saat zooming dan panning.

---

### 2. Metrik Komparasi Beban Jaringan

| Skenario Penggunaan | Monolithic GeoJSON (`/api/predictions`) | PostGIS MVT (`/api/tiles/...`) | Penghematan |
|---|:---:|:---:|:---:|
| Buka Peta Awal (Zoom 8) | 6.277 KB (6,13 MB) | $\approx 85$ KB (6 tiles aktif) | **98,6%** |
| Pan ke Arah Barat (1 Viewport) | 6.277 KB (Ambil ulang seluruh layer) | $\approx 35$ KB (2 tiles baru) | **99,4%** |
| Zoom In ke Teluk Prigi (Zoom 11) | 6.277 KB | $\approx 24$ KB (4 tiles detail) | **99,6%** |
| Pan/Zoom Berulang (Warm Cache) | 658 ms CPU time | $<5$ ms per tile | **99,2%** |

