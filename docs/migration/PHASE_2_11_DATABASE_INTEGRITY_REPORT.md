# SEACAST PRIGI V3 — PHASE 2.11
# DATABASE & POSTGIS INTEGRITY AUDIT REPORT

**Audit Date:** 2026-11-04  
**Audit Scope:** PostgreSQL 17.11, PostGIS 3.6.2, Canonical Grids, Historical Landings, and Fleet Statistics  
**Database User:** `seacast_readonly`  
**Status:** **VERIFIED PASS**  
**Audit Script:** `scratch/audit_database.php`  

---

## 1. USER PERMISSIONS & READ-ONLY ENFORCEMENT

The audit was executed exclusively through user `seacast_readonly` to guarantee that verification operations could not mutate table data.

```sql
SELECT current_user, session_user;
-- Output: current_user = 'seacast_readonly', session_user = 'seacast_readonly'
```

Attempts to execute `UPDATE` or `DROP` queries under this session fail with standard PostgreSQL permission denials:
`ERROR: permission denied for table canonical_grids`.

---

## 2. CANONICAL GRIDS & SPATIAL INTEGRITY

The spatial foundation of SEACAST PRIGI is the discrete global grid system covering Indonesia Fisheries Management Area WPPNRI 573.

| Parameter | Baseline Requirement | Verified Value | Compliance Status |
|:---|:---:|:---:|:---:|
| **Total Grid Cells** | 8,129 | **8,129** | PASS |
| **Coordinate Reference System** | EPSG:4326 (WGS 84) | **EPSG:4326** | PASS |
| **Geometry Validity (`ST_IsValid`)** | 100% (8,129 / 8,129) | **100% (8,129 / 8,129)** | PASS |
| **Spatial Extent (Bounding Box)** | `BOX(102.5 -14, 126.5 -6.8)` | `BOX(102.5 -14, 126.5 -6.8)` | PASS |
| **Geometry Hash (MD5 Sum)** | `7a500f01fae9ae112f9c921b071eba7f` | `7a500f01fae9ae112f9c921b071eba7f` | PASS |

### Geometry Hash Formula:
```sql
SELECT md5(string_agg(grid_id || ':' || ST_AsText(geom), ',' ORDER BY grid_id)) 
FROM canonical_grids;
-- Verified Result: 7a500f01fae9ae112f9c921b071eba7f
```
*Zero spatial drift has occurred since initial database ingestion.*

---

## 3. TABLE ROW COUNTS & VOLUME RECONCILIATION

| Table Name | Description | Verified Row Count | Status |
|:---|:---|:---:|:---:|
| `canonical_grids` | Spatial grid polygon geometries for WPP 573 | **8,129** | PASS |
| `daily_predictions` | Current snapshot inference results (2026-11-03) | **8,129** | PASS |
| `grid_monthly_predictions` | Monthly climatological baseline (12 months x 8,129 cells) | **97,548** | PASS |
| `prigi_port_landings` | Monthly historical landing records (2015 - 2025) | **132** | PASS |
| `prigi_fleet_by_gt` | Fishing fleet distribution by Gross Tonnage | **3** | PASS |

---

## 4. SCIENTIFIC DATASET VALIDATION

### 4.1 Historical Port Landings (`prigi_port_landings`)
- **Total Records:** 132 monthly records spanning 11 full years (2015 to 2025).
- **Total Pelagic Catch:** **18,405.33 metric tons**
- **Cakalang (*K. pelamis*):** **8,294.16 metric tons** (45.1% of total pelagic catch)
- **Madidihang (*T. albacares*):** **2,934.01 metric tons** (15.9% of total pelagic catch)
- **Total Registered Fishing Trips:** **189,334 trips**
- **Average CPUE:** 0.0972 tons/trip

### 4.2 Local Fishing Fleet Profile (`prigi_fleet_by_gt`)
- **Total Registered Vessels:** **1,269 vessels**
- **Class Breakdown:**
  1. `< 10 GT` (Small artisanal fleet): 1,185 vessels (93.4%), operational range $\le 12\text{ NM}$
  2. `10 - 30 GT` (Medium offshore fleet): 74 vessels (5.8%), operational range $\le 60\text{ NM}$
  3. `> 30 GT` (Industrial purse seine): 10 vessels (0.8%), operational range $> 60\text{ NM}$

---

## 5. DATABASE INTEGRITY CONCLUSION

PostgreSQL 17.11 and PostGIS 3.6.2 maintain complete data consistency, exact spatial bounds, and zero schema drift. All 8,129 canonical cells are structurally sound and validated.

---
*Report verified by SEACAST PRIGI V3 Migration Audit Directorate.*

