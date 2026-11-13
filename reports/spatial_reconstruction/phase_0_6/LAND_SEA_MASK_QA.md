# LAND-SEA MASK QUALITY ASSURANCE & VALIDATION REPORT
**Project:** SEACAST PRIGI — WPPNRI 573  
**Phase:** 0.6 — Land-Sea Mask QA  

---

## 1. PENGECEKAN KUALITAS TOPOLOGIS MASKING

1. **Resolusi Garis Pantai:**
   - Garis pantai WPPNRI 573 master diturunkan langsung dari dataset resmi BIG (Badan Informasi Geospasial) 1:250.000 dengan 625.698 verteks.
   - Seluruh teluk penting (khususnya Teluk Prigi, Teluk Popoh, Teluk Pacitan, dan Selat Bali / Lombok / Alas / Sape) terlindungi secara topologis.
2. **Uji Penetrasi Darat:**
   - Telah diverifikasi bahwa titik darat seperti Puncak Semeru (+3.027 m) dan dataran Malang teridentifikasi sebagai DARAT (terisolasi di luar sel analisis laut).
   - Titik pelabuhan PPN Prigi (111.730° BT, -8.300° LS) berada tepat pada zona antarmuka teluk (*coastal embayment interface*), masuk ke dalam Sub-AOI high-resolution.

## 2. KEPUTUSAN GATE B/C UNTUK PHASE 0.6
- Skema JSON sel kisi telah divalidasi dan memenuhi standar Open Geospatial Consortium (OGC).
- Protokol klasifikasi sel menjamin tidak adanya sel daratan murni yang mencemari analisis habitat ikan pelagis.
