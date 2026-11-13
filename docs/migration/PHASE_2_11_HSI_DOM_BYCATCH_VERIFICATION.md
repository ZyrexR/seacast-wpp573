# SEACAST PRIGI V3 — PHASE 2.11
# HSI, DOM ZONE & BYCATCH ENGINE VERIFICATION REPORT

**Audit Date:** 2026-11-04  
**Audit Scope:** Mathematical formulas, boundary conditions, priority hierarchies, and biological heuristics  
**Status:** **VERIFIED PASS**  
**Audited Classes:** `OceanographicEngine.php`, `SpeciesPredictionService.php`  

---

## 1. HABITAT SUITABILITY INDEX (HSI) FORMULA VERIFICATION

The Habitat Suitability Index (HSI) quantifies pelagic fish presence probability on a normalized scale $[0.000, 1.000]$.

### 1.1 Species Ensemble Formula
For species $s \in \{\text{cakalang}, \text{madidihang}, \text{legacy_species_removed}\}$:
$$\text{HSI}_s = \text{clamp}\Big( w_{\text{maxent}, s} \cdot P_{\text{maxent}} + w_{\text{rf}, s} \cdot P_{\text{rf}} + w_{\text{glm}, s} \cdot P_{\text{glm}}, \; 0.0, \; 1.0 \Big)$$

Where weights satisfy $\sum w_i = 1.000$:
- **Cakalang:** $0.320 \cdot P_{\text{maxent}} + 0.355 \cdot P_{\text{rf}} + 0.325 \cdot P_{\text{glm}}$
- **Madidihang:** $0.295 \cdot P_{\text{maxent}} + 0.365 \cdot P_{\text{rf}} + 0.340 \cdot P_{\text{glm}}$
- **Legacy species removed from TCT scope:** $0.000 \cdot P_{\text{maxent}} + 0.517 \cdot P_{\text{rf}} + 0.483 \cdot P_{\text{glm}}$

### 1.2 Composite Multispecies HSI Formula
When the user selects `"all"` species, the composite HSI is computed as an unweighted arithmetic mean:
$$\text{HSI}_{\text{composite}} = \frac{\text{HSI}_{\text{cakalang}} + \text{HSI}_{\text{madidihang}} + \text{HSI}_{\text{legacy_species_removed}}}{3}$$

### 1.3 Scientific Classification Thresholds
- **Tuna Hotspot:** $\text{HSI} \ge 0.700$
- **Potential Fishing Ground (PFG):** $0.550 \le \text{HSI} < 0.700$
- **Moderate Suitability:** $0.300 \le \text{HSI} < 0.550$
- **Low Suitability:** $\text{HSI} < 0.300$

*Audit result: Verified in automated unit tests. All clamping strictly bounds values within $[0.000, 1.000]$.*

---

## 2. DYNAMIC OCEAN MANAGEMENT (DOM) ZONE CLASSIFICATION

The DOM engine classifies ocean grid cells into management zones based on oceanographic, bathymetric, and administrative features.

### 2.1 Strict Priority Order
The classification executes in strict waterfall order:

$$\mathbf{Z4} \;\longrightarrow\; \mathbf{Z5} \;\longrightarrow\; \mathbf{Z3} \;\longrightarrow\; \mathbf{Z1} \;\longrightarrow\; \mathbf{Z2} \;\longrightarrow\; \mathbf{GENERAL\_OCEANIC}$$

| Priority | Zone Identifier | Formal Designation | Oceanographic & Spatial Criteria |
|:---:|:---|:---|:---|
| **1 (Highest)** | `Z4_NURSERY_GROUND` | Daerah Asuhan Biologis | Depth $\le 150.0\text{ m}$ **AND** Distance $\le 15.0\text{ NM}$ |
| **2** | `Z5_BYCATCH_SCREENING` | Front Termal Lereng Benua | Depth between $150.0\text{ m}$ and $1200.0\text{ m}$ **AND** SST Gradient $\ge 0.50^\circ\text{C/km}$ |
| **3** | `Z3_FEEDING_CORE` | Inti Makan Upwelling | Chl-a $\ge 0.40\text{ mg/m³}$ **AND** SST between $25.0^\circ\text{C}$ and $29.0^\circ\text{C}$ |
| **4** | `Z1_PFG_INSHORE` | Daerah Penangkapan Pesisir | Distance to Port $\le 12.0\text{ NM}$ |
| **5** | `Z2_PFG_OFFSHORE` | Daerah Penangkapan Lepas Pantai | Distance to Port $> 12.0\text{ NM}$ **AND** Distance $\le 60.0\text{ NM}$ |
| **6 (Default)** | `GENERAL_OCEANIC` | Samudera Terbuka WPP 573 | Distance to Port $> 60.0\text{ NM}$ or unclassified deep waters |

### 2.2 Boundary Condition Audit Results
A dedicated test script (`scratch/audit_dom_bycatch_boundaries.php`) verified exact boundary behavior:

```
Boundary Exactness Audit:
 - Depth 150.0m & Dist 15.0 NM:  Z4_NURSERY_GROUND -> PASS
 - Depth 150.1m & Dist 15.0 NM:  (not Z4)          -> PASS
 - Depth 150.0m & Dist 15.1 NM:  (not Z4)          -> PASS
 - Dist 12.0 NM (Depth 200m):    Z1_PFG_INSHORE    -> PASS
 - Dist 12.1 NM (Depth 200m):    Z2_PFG_OFFSHORE   -> PASS
 - Dist 60.0 NM (Depth 200m):    Z2_PFG_OFFSHORE   -> PASS
 - Dist 60.1 NM (Depth 200m):    GENERAL_OCEANIC   -> PASS

Priority Overlap Audit:
 - Z4 vs Z3/Z1 Conflict:        Z4_NURSERY_GROUND -> PASS (Z4 took priority)
 - Z5 vs Z3/Z2 Conflict:        Z5_BYCATCH_SCREENING -> PASS (Z5 took priority)
 - Z3 vs Z2 Conflict:           Z3_FEEDING_CORE   -> PASS (Z3 took priority)
```

---

## 3. IOTC BYCATCH SCREENING ENGINE

The Bycatch engine evaluates operational risk for non-target pelagic megafauna (sea turtles, oceanic sharks, juvenile tuna) following Indian Ocean Tuna Commission (IOTC) conservation principles.

### 3.1 Three-Tier Traffic Light Classification
1. **`RED` (High Alert / Bycatch Hotspot):**
   - Shelf break depth: $150.0\text{ m} \le \text{Depth} \le 1200.0\text{ m}$
   - Thermal front present: SST Gradient $\ge 0.50^\circ\text{C/km}$ (or SST $< 26.0^\circ\text{C}$ cold tongue)
   - Cakalang Hotspot present: $\text{HSI}_{\text{cakalang}} \ge 0.700$
2. **`YELLOW` (Caution / Continental Slope Transition):**
   - Continental slope zone: $150.0\text{ m} \le \text{Depth} \le 1200.0\text{ m}$ without meeting `RED` multi-factor triggers.
3. **`GREEN` (Low Risk / Open Pelagic or Shallow Coastal):**
   - Depth $< 150.0\text{ m}$ OR Depth $> 1200.0\text{ m}$.

### 3.2 Boundary Verification Audit
- **Depth 149.9m:** `GREEN` (Shelf edge boundary strictly respected)
- **Depth 150.0m:** `RED` (When cold front and Cakalang HSI $\ge 0.70$ present)
- **Depth 1200.0m:** `RED` (Lower slope boundary strictly respected)
- **Depth 1200.1m:** `GREEN` (Oceanic abyss)
- **Cakalang HSI 0.699:** `YELLOW` (Below hotspot trigger)
- **Cakalang HSI 0.700:** `RED` (Hotspot trigger activated)

---

## 4. SCIENTIFIC & REGULATORY CAVEAT (IOTC RECOGNITION)

> [!WARNING]
> **Regulatory Disclaimer:**
> The Bycatch screening algorithm implemented in SEACAST PRIGI V3 represents an oceanographic spatial heuristic derived from peer-reviewed literature on shelf-break aggregation in the Indian Ocean.
> 
> It has **NOT** been audited or certified by the Indian Ocean Tuna Commission (IOTC) Scientific Committee or the Indonesian Ministry of Marine Affairs and Fisheries (KKP) Observer Program. 
> 
> The labels `GREEN`, `YELLOW`, and `RED` must be treated by vessel operators as **advisory risk indicators**, not as legally binding spatial fishery closures.

---
*Report verified by SEACAST PRIGI V3 Migration Audit Directorate.*

