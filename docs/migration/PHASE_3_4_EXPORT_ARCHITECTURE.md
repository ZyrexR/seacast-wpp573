# SEACAST PRIGI V3 — PHASE 3.4
# SCIENTIFIC EXPORT ENGINE ARCHITECTURE REPORT

**Document ID:** SEACAST-DOC-P34-ARCH-01  
**Phase:** 3.4 — Scientific Export Engine  
**Release Target:** v3.0-rc1  
**Timestamp:** 2026-11-04T19:46:00+07:00  
**Author:** Senior Laravel Architect & Scientific Software Auditor  
**Classification:** Technical Architecture Specification  

---

## 1. EXECUTIVE SUMMARY

The **Scientific Export Engine** in SEACAST PRIGI V3 provides high-fidelity, standards-compliant spatial and tabular data serialization for researchers, fisheries managers, and field oceanographers. Unlike consumer export tools that flatten or omit scientific dimensions, SEACAST PRIGI V3 preserves the complete multi-species habitat suitability index (HSI), oceanographic parameters, Dynamic Ocean Management (DOM) zones, and Bycatch Risk screening.

The export architecture operates under strict read-only constraints, utilizes memory-efficient streaming where appropriate, and packages datasets with formal OGC-compliant metadata, coordinate reference systems (CRS), and encoding specifications.

```
                          [ Client Request ]
                                   |
                     GET /api/export/{geojson|csv|gis-package}
                                   |
                     [ ExportApiController ]
                                   |
                                   v
                    [ ScientificExportService ]
                     /             |            \
                    v              v             v
            exportGeoJson()   exportCsv()   exportGisPackage()
                   |               |                 |
                   v               v                 v
            RFC 7946 OGC     UTF-8 BOM CSV     ZIP Bundle:
               CRS84         Streaming Buffer   - .geojson
            Pretty Print     Excel/QGIS Ready   - .csv
                                                - .prj (EPSG:4326)
                                                - .cpg (UTF-8)
                                                - METADATA_README.txt
```

---

## 2. COMPONENT DESIGN & RESPONSIBILITIES

### 2.1 Service Layer (`App\Services\ScientificExportService`)
- Encapsulates all export business logic independent of HTTP controllers.
- Injects `ScientificDataRepositoryInterface` and `ScientificDataService`.
- Exposes three primary serialization methods:
  - `exportGeoJson(string $date, array $filters = []): string`
  - `exportCsv(string $date, array $filters = []): string`
  - `exportGisPackage(string $date, array $filters = []): string`
- Enforces strict deterministic metadata headers and UTF-8 Byte Order Mark (BOM).

### 2.2 Controller Layer (`App\Http\Controllers\Api\ExportApiController`)
- Validates query parameters:
  - `date`: Optional ISO 8601 `YYYY-MM-DD` (defaults to latest available observation date `2026-11-03`).
  - `min_hsi`: Numeric float between `0.0` and `1.0`.
  - `target_species`: In `[cakalang, madidihang, albakor]`.
  - `dom_zone`: Valid zone code `[Z1, Z2, Z3, Z4, Z5, GEN]`.
  - `bycatch_risk`: Valid screening code `[LOW, MEDIUM, HIGH]`.
  - `hotspots_only`: Boolean flag.
- Generates RFC 6266 `Content-Disposition` attachment headers with timestamped filenames:
  - `seacast_prigi_{date}.geojson` (`application/geo+json`)
  - `seacast_prigi_{date}.csv` (`text/csv; charset=UTF-8`)
  - `seacast_prigi_{date}_gis_package.zip` (`application/zip`)

---

## 3. SUPPORTED EXPORT FORMATS & SPECIFICATIONS

| Format | Content-Type | Standard / Specification | Key Properties Included |
| :--- | :--- | :--- | :--- |
| **GeoJSON** | `application/geo+json` | RFC 7946 / OGC CRS84 | MultiPolygon / Polygon geometries, 23 scientific properties, unescaped slashes, pretty formatted. |
| **CSV** | `text/csv; charset=UTF-8` | RFC 4180 + UTF-8 BOM | 23 columns (cell coordinates, depth, distance to PPN Prigi, HSI scores, DOM zone, oceanography). |
| **GIS Package** | `application/zip` | ZIP Archive (PKWARE) | GeoJSON + CSV + ESRI `.prj` WKT + `.cpg` UTF-8 + `METADATA_README.txt`. |

---

## 4. SCIENTIFIC METADATA EMBEDDING

Every GIS archive includes `METADATA_README.txt` containing complete institutional attribution, coordinate reference definitions, model accuracy metrics (AUC & TSS), DOM waterfall priority rules, and bycatch mitigation protocols.

```
================================================================================
SEACAST PRIGI V3 — SCIENTIFIC DATASET EXPORT METADATA
================================================================================
Project: SEACAST PRIGI V3 (Scientific Ocean Intelligence Platform)
Location: Pelabuhan Perikanan Nusantara (PPN) Prigi, Trenggalek, Jawa Timur
Spatial Domain: WPPNRI 573 (Southern Java, Indian Ocean)
Coordinate Reference System (CRS): EPSG:4326 (WGS 84 Geodetic Coordinates)
Model AUC / TSS Baselines:
  - Katsuwonus pelamis (Cakalang): AUC 0.892, TSS 0.684
  - Thunnus albacares (Madidihang): AUC 0.874, TSS 0.648
  - Thunnus alalunga (Albakor): AUC 0.908, TSS 0.712
DOM Priority Hierarchy: Z4 (Turtle High) > Z5 (Shark High) > Z3 (Cetacean High) > Z1 (Core Fishing) > Z2 (Buffer) > GEN (General)
Bycatch Mitigation: Turtle Bycatch Screening & Shark Conservation Buffers Active
Data Limitations: BMKG / In-situ calibration fallback active when sensor coverage is partial.
================================================================================
```

---

## 5. PERFORMANCE AND MEMORY CONSTRAINTS

1. **In-Memory Buffer Streaming:** CSV generation uses `php://temp` stream buffers, avoiding disk I/O bottlenecks and ensuring complete garbage collection.
2. **Deterministic Querying:** Utilizes indexed PostGIS views and geospatial cache tags (`predictions:{date}`).
3. **ZipArchive Auto-Cleanup:** Temporary zip files created during package assembly are unlinked immediately after memory retrieval.

