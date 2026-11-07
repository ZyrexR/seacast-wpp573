# SEACAST — Trip Planner & System Streamlining Design Document

**Date:** 2026-11-06  
**Status:** In Review (Brainstorming Phase)  
**Target Platform:** SEACAST — Scientific Ocean Intelligence & Fisheries Decision Support  
**Framework:** Pure Laravel 13 Modular Monolith (Blade, Alpine.js, Tailwind CSS v4, PostgreSQL 17 / PostGIS, SQLite Auth/Internal DB)  

---

## 1. Intent & The "Why"

SEACAST is evolving from a passive fish-finding viewer ("mencari ikan") into an integrated operational decision-support system ("menangkap ikan terencana"). 

Currently, fishermen (particularly traditional <10 GT fleets and 10–30 GT vessels operating in WPPNRI 573 from PPN Prigi, PPS Cilacap, and PPN Palabuhanratu) face operational uncertainty: they lack pre-departure calculations for fuel consumption versus expected catch, weather/wave safety risks, moon phase impacts, and legal boundary compliance (KKP 0–12 nm zones).

Furthermore, the existing SEACAST interface is fragmented across 11 disparate dashboard modules, creating confusion. By pruning redundant theoretical modules, unifying all spatial data into a single DPI Radar engine, and launching a dedicated **Trip Planner** that outputs an official, printable **Sailing Advisory Document (PDF)** with visual map snippets and coordinates, SEACAST delivers tangible value to both dockside fishermen and port administrators.

---

## 2. Goals, Non-Goals, and Anti-Goals

### Goals
1. **Streamline Dashboard Navigation:** Reduce from 11 modules to 3 primary operational modules:
   - **Radar Geospasial Terpadu (`/dashboard/dpi-radar`)**: Single spatial cockpit with multi-layer switcher.
   - **Kalkulator Operasional & Rencana Melaut (`/dashboard/trip-planner`)**: Pre-departure decision calculator and PDF generator.
   - **Analitik Armada & Pendaratan (`/dashboard/fleet-analytics`)**: Historical port landing and fleet statistics for Prigi, Cilacap, and Palabuhanratu.
   - Supported by **Ringkasan Eksekutif (`/dashboard`)** and technical **Status Sistem (`/dashboard/system-status`)**.
2. **Dedicated Trip Planner with Official PDF Output:**
   - Multi-port support: PPN Prigi (East Java), PPS Cilacap (Central Java), and PPN Palabuhanratu (West Java).
   - Dynamic scientific calculations: Distance (NM/km), Fuel consumption (L & Rp with 15% safety reserve), Wave safety threshold & Moon phase advisory, KKP regulatory compliance (<10 GT within 0–12 NM), and catch projection derived dynamically from grid cell HSI.
   - High-fidelity PDF: "Dokumen Rencana Operasional Melaut" featuring official SEACAST branding, exact GPS waypoint coordinates, visual static map route snippet, and validation signature blocks.
3. **Port Admin Sailing Logbook:**
   - Saved trip plans are stored in the database with unique official reference codes (e.g., `DOC-PRG-202611-0001`).
   - Port admins can review scheduled departures for their port, monitor fleet destinations, and reprint PDFs for maritime safety/SAR purposes.

### Non-Goals
- Real-time AIS vessel transponder tracking (commercial AIS data is restricted/confidential; our scope is pre-departure trip planning).
- Integrating automated GEE cloud pipeline in this phase (GEE ingestion remains handled via existing batch scripts; data schema remains consistent).
- Rebuilding public pages or altering authentication infrastructure.

### Anti-Goals
- Do NOT clutter the Trip Planner with heavy 8,129-cell WebGL canvases that freeze mobile browsers.
- Do NOT generate arbitrary dummy catch numbers; catch projections must be calibrated using scientific HSI values and vessel GT capacity.
- Do NOT allow light/dark theme glitches or unstyled HTML in the printed PDF.

---

## 3. Constraints

- **Technology Stack:** Laravel 13, Tailwind CSS v4, Blade templates, Alpine.js. No React, Next.js, or external Node.js runtime.
- **Database:** PostgreSQL 17 + PostGIS for spatial grids (`canonical_grids`, `daily_predictions`), SQLite for user authentication and trip plan records (`trip_plans`).
- **PDF Generation:** Must render reliably in PHP (e.g. using `dompdf/dompdf` or standard headless print CSS) without external paid SaaS APIs.
- **Target Commodities:** Tuna Madidihang (*Thunnus albacares*), Cakalang (*Katsuwonus pelamis*), and Tongkol (*Euthynnus* spp.).
- **Scientific Foundation:** Peer-reviewed Habitat Suitability Index (HSI) formulations based on the Arithmetic Mean Model (AMM) and empirical weights (Yati et al., 2024; Zainuddin et al., 2023; Syah et al., 2023; Vaihola et al., 2023; Mondal et al., 2021).
- **Legal & Regulatory Foundation:** *Peraturan Menteri Kelautan dan Perikanan Republik Indonesia Nomor 59/PERMEN-KP/2020 tentang Jalur Penangkapan Ikan dan Alat Penangkapan Ikan di Wilayah Pengelolaan Perikanan Negara Republik Indonesia dan Laut Lepas* (Pasal 2–4, 21–22, 30, 32 & Lampiran).

---

## 4. Scientific Habitat Suitability Index (HSI) Calibration

The suitability score for each environmental parameter ($\text{SI}_i \in [0, 1]$) is derived from Gaussian response curves calibrated against peer-reviewed empirical optimum ranges:

$$\text{HSI} = \sum_{i=1}^{n} w_i \times \text{SI}_i \quad \text{atau} \quad \text{HSI} = \frac{1}{n} \sum_{i=1}^{n} \text{SI}_i$$

### Multi-Species Parameter Boundaries & Literature Citations:

1. **Madidihang / Yellowfin Tuna (*Thunnus albacares*)**:
   - **Primary Drivers:** Bathymetry (continental slope/break) & SST (Vaihola et al., 2023; Yati et al., 2024).
   - **Optimum SST:** 23.0 °C – 31.5 °C.
   - **Optimum Chl-a:** 0.05 – 0.10 mg/m³ (oceanic oligotrophic core; tolerated up to 3.0 mg/m³).
   - **Bathymetry Preference:** Oceanic waters and continental slope (> 200 m).

2. **Cakalang / Skipjack Tuna (*Katsuwonus pelamis*)**:
   - **Primary Drivers & Weights:** Chl-a (50%), SSH / Wave dynamics (40%), SST (10%) (Syah et al., 2023; Zainuddin et al., 2017, 2023; Muhyun et al., 2025).
   - **Optimum SST:** 28.5 °C – 31.5 °C (peak optimal ~29.5 °C).
   - **Optimum Chl-a:** 0.15 – 0.35 mg/m³ (peak optimal ~0.20 mg/m³).
   - **Bathymetry Preference:** Shelf break and oceanic productive fronts.

3. **Tongkol (*Euthynnus* spp.)**:
   - **Primary Drivers:** SST & Chl-a in coastal / neritic waters (Tangke et al., 2020; Hidayat et al., 2021; Syah et al., 2023).
   - **Optimum SST:** 27.0 °C – 30.5 °C (bays and neritic zones).
   - **Optimum Chl-a:** 0.20 – 0.50 mg/m³ (eutrophic coastal waters).
   - **Bathymetry Preference:** Shallow coastal / neritic waters (< 200 m).

*Threshold for High Potential Fishing Ground (Hotspot):* $\text{HSI} \ge 0.60$ (Good Habitat) and $\ge 0.70$ (Prime Hotspot) (Hsu et al., 2021).

---

## 5. Approaches Considered

### Approach 1: Integrated Drawer inside DPI Radar
- Place the Trip Planner inside a slide-out drawer on the full Leaflet/MapLibre radar map.
- *Trade-off:* Heavily cluttered on mobile phones used by fishermen on docks; hard to format a comprehensive form, tables, and PDF controls inside a drawer.

### Approach 2: Dedicated Standalone Trip Planner & Archival Hub (CHOSEN)
- Build a dedicated, clean, fast-loading view at `/dashboard/trip-planner`.
- Includes input parameters, dynamic calculation cards, an embedded static map route snippet in the PDF, and a tabbed archive for Port Admins.
- *Trade-off:* Requires a focused route visualizer or coordinate selector on the planner page, but guarantees optimal mobile usability and clean PDF generation.

### Approach 3: Radar-to-Planner Strict 2-Step Wizard
- Force the user to always find a cell on the map first before opening the calculator.
- *Trade-off:* Inflexible for fishermen who only want to test a known traditional coordinate or check fuel costs quickly without navigating 8,129 map cells.

---

## 6. Detailed Design & Architecture

### A. Module Pruning & Sidebar Navigation Cleanup
- **Remove Obsolete Routes & Views:**
  - Delete/Archive: `oceanographic-explorer.blade.php`, `species-models.blade.php`, `time-machine.blade.php`.
  - Decommission `/dashboard/dom-management` (merge layers into DPI Radar).
  - Decommission `/dashboard/bycatch-safety` (merge weather warnings into Trip Planner, and bycatch risk into DPI Radar).
  - Decommission `/dashboard/data-monitoring` (merge relevant status into System Status).
- **Refined Sidebar Navigation:**
  1. Ringkasan Eksekutif (`route('dashboard')`)
  2. Radar Geospasial WPPNRI 573 (`route('dashboard.dpi-radar')`)
  3. Rencana Melaut & Kalkulator (`route('dashboard.trip-planner')`) [NEW]
  4. Analitik Armada & Pendaratan (`route('dashboard.fleet-analytics')`)
  5. Status Sistem (`route('dashboard.system-status')`) — *Admin only*

### B. DPI Radar Consolidation & High-Precision Visuals
- Enhance `resources/views/dashboard/dpi-radar.blade.php`:
  - **Seamless Contour Rendering (No Rigid 11 km Squares):**
    - Disable rigid rectangular borders (`stroke: false`, `weight: 0`) and apply continuous multi-stop oceanographic color interpolation (blue -> cyan -> yellow -> crimson) so the map renders like a modern, smooth satellite heat gradient (comparable to *FishTrack* and *Windy*) rather than blocky 11x11 km squares.
  - **Focal Peak GPS Waypoint Extraction:**
    - Within high-suitability zones ($\text{HSI} \ge 0.60$), the system calculates and highlights the focal center-of-mass as a **Specific GPS Waypoint** (`DD° MM' SS"` and Decimal Degrees: e.g., `Lat: 08° 24' 15" S, Lon: 111° 42' 30" E`) with an operational radius advisory (~1.5 NM).
    - Fishermen receive an exact steering point for their GPS / compass rather than an abstract 121 km² polygon.
  - **Multi-Layer Switcher:**
    - [x] HSI Potensi Penangkapan (Tuna / Cakalang / Tongkol)
    - [x] Zonasi DOM (Z1 Inshore, Z2 Offshore, Z3 Feeding Core, Z4 Nursery Ground)
    - [x] Garis Batas Regulasi KKP (0–12 NM untuk kapal <10 GT)
    - [x] Skrining Konservasi & Bycatch (Z5 Lereng Benua)
  - In waypoint / cell popup: Direct shortcut button *"Rencanakan Pelayaran ke Titik Ini"* pre-filling the Trip Planner.

### C. Trip Planner Module (`/dashboard/trip-planner`)
- **Inputs:**
  - Departure Port: `PPN Prigi` (Lat -8.2833°, Lon 111.7275°), `PPS Cilacap` (Lat -7.7472°, Lon 109.0139°), `PPN Palabuhanratu` (Lat -6.9856°, Lon 106.5414°).
  - Departure Date (defaults to latest available prediction date; if user chooses future date, system utilizes the latest environmental forecast with dynamic mathematical moon phase calculation).
  - Vessel Profile: Vessel Name, Skipper Name, GT Class (`< 10 GT`, `10 - 20 GT`, `20 - 30 GT`).
  - Target Commodity: Cakalang (*Katsuwonus pelamis*), Tuna Madidihang (*Thunnus albacares*), and Tongkol (*Euthynnus* spp.).
  - Target Waypoint: Selected from top HSI recommendations (with specific GPS coordinates) or custom map pin.
- **Calculation Engine (`TripPlanningService`):**
  - **Distance & Bearing:** Rhumb-line / Great Circle distance between selected port and target coordinate in NM and km, plus compass heading (e.g., 205° SSW).
  - **Fuel Economics (Calibrated from PPN Prigi Operational Specs):**
    - `< 10 GT`: Speed 7.5 knots, 1.5 L/NM, Max operational range 12 NM, Base capacity 180 kg.
    - `10 - 20 GT`: Speed 9.0 knots, 2.8 L/NM, Max operational range 30 NM, Base capacity 450 kg.
    - `20 - 30 GT`: Speed 11.0 knots, 4.5 L/NM, Max operational range 60 NM, Base capacity 1,200 kg.
    - Total fuel = `(Round-trip Distance in NM * Fuel Rate per NM) * 1.15` (15% safety reserve).
    - Subsidized solar fuel benchmark = Rp 6.800 / Liter.
  - **KKP Regulatory Compliance Engine (Permen KP No. 59/PERMEN-KP/2020):**
    - Authoritative legal basis: *Peraturan Menteri Kelautan dan Perikanan No. 59/PERMEN-KP/2020 tentang Jalur Penangkapan Ikan dan Alat Penangkapan Ikan di WPPNRI dan Laut Lepas*.
    - **Zonasi Jalur Penangkapan (Pasal 2 & 3):**
      - *Jalur I:* IA (0–2 NM dari garis pantai) & IB (2–4 NM).
      - *Jalur II:* Perairan di luar Jalur I hingga 12 NM (4–12 NM).
      - *Jalur III:* Perairan di luar Jalur II (> 12 NM hingga batas ZEE).
    - **Karakteristik WPPNRI 573 (Pasal 4 ayat 2.b):** Perairan dalam (> 200 m) Samudera Hindia sebelah Selatan Jawa hingga Nusa Tenggara.
    - **Validasi Zonasi Kapal & Alat Tangkap (Pasal 21, 22, 30, 32 & Lampiran):**
      - *Kapal Motor $\le$ 10 GT:* Beroperasi di Jalur I dan Jalur II (maksimal 12 NM). Jika target `distance_nm > 12.0 NM`, sistem menandai `WARNING_OUT_OF_LEGAL_RANGE` (Melanggar batas Jalur II Permen KP 59/2020).
      - *Kapal Motor > 10 GT s/d 30 GT:* Beroperasi di Jalur II dan Jalur III (diperbolehkan melaut > 12 NM).
      - *Kapal Motor > 30 GT:* Wajib beroperasi di Jalur III (> 12 NM / ZEE) atau Laut Lepas. Dilarang beroperasi di Jalur I dan Jalur II.
      - *Validasi Alat Tangkap (API):* Pancing Ulur Tuna (LHP-PUT), Pancing Tonda (LTL), Rawai Tuna Hanyut (LLD-RT), dan Pukat Cincin Pelagis Besar (PS1-B). Status kepatuhan dicantumkan di slip PDF resmi.
  - **Maritime Safety & Moon Phase:**
    - Wave Height threshold: Safe (< 1.5 m), Caution (1.5–2.5 m), Danger (> 2.5 m).
    - Moon Phase calculation from date (New Moon, Waxing, Full Moon, Waning). Highlight fishing implications (e.g. Full moon scatters pelagic fish and lowers light-lure purse seine efficiency; dark moon concentrates fish around FADs/rumpon).
  - **Catch Projection & Revenue Formula (Scientific HSI-driven):**
    - Benchmark Market Prices: Cakalang Rp 22.000/kg; Madidihang Rp 45.000/kg; Tongkol/Albakor Rp 28.000/kg.
    - `Projected Catch (kg) = Base Capacity (kg) * HSI_Score * Seasonal_Factor`.
    - `Gross Revenue = Projected Catch (kg) * Benchmark Price (Rp/kg)`.
    - `Net Profit = Gross Revenue - Total Fuel Cost`.

### D. Scope of Multi-Port Data in Fleet Analytics
- `/dashboard/fleet-analytics` now incorporates full 10-year empirical datasets (2016–2025) for both **PPN Prigi** and **PPS Cilacap** (saved in `database/data/raw/pps_cilacap_statistics_2016_2025.csv`), featuring:
  - Monthly landings breakdown (Yellowfin Tuna, Cakalang, and Tongkol Krai).
  - Vessel fleet composition by GT class (<10 GT, 10–20 GT, 20–30 GT, >30 GT).
  - Monthly trip effort metrics.
- The interface features a Port Selector (PPN Prigi vs. PPS Cilacap), allowing comparative CPUE and seasonal trend analysis across Central and East Java.
- PPN Palabuhanratu will be ingested into the same schema when its dataset is provided.

### E. Official PDF Advisory Generator
- Rendered via a clean Blade template (`resources/views/pdf/sailing-advisory.blade.php`) and converted to PDF.
- **Document Contents:**
  - Header: Official SEACAST logo, Document Title ("DOKUMEN RENCANA OPERASIONAL & PANDUAN MELAUT"), Unique Reference No., Timestamp.
  - Section 1: Identitas Kapal & Nahkoda (Vessel name, GT, Port of origin).
  - Section 2: Informasi Rute & Koordinat Presisi (Departure Lat/Lon, Destination Waypoint Lat/Lon in `DD°MM'SS"` and Decimal Degrees, Distance in NM/km, Compass Heading).
  - Section 3: Cuplikan Peta Visual (Static snapshot image or mini-vector diagram showing departure port, waypoint, and route line).
  - Section 4: Analisis Keselamatan Maritim & Cuaca (Tinggi gelombang, kecepatan angin, fase bulan, status izin berlayar: AMAN/WASPADA).
  - Section 5: Rencana Bahan Bakar & Proyeksi Ekonomi (Liter solar, biaya BBM, estimasi tangkapan kg, estimasi pendapatan).
  - Section 6: Lembar Validasi & Catatan Syahbandar (Tanda tangan Nahkoda & Petugas Pelabuhan).

### F. Port Admin Sailing Logbook (`trip_plans` Table)
- **Database Schema (`trip_plans`):**
  - `id` (UUID / bigint)
  - `reference_no` (string, unique, e.g. `SEACAST-PRG-202611-0001`)
  - `user_id` (foreign key to users)
  - `port_code` (enum: `PPN_PRIGI`, `PPS_CILACAP`, `PPN_PALABUHANRATU`)
  - `vessel_name` (string)
  - `vessel_gt_class` (string)
  - `departure_date` (date)
  - `target_species` (string)
  - `target_cell_id` (string, nullable)
  - `target_lat` (decimal), `target_lon` (decimal)
  - `distance_nm` (decimal)
  - `fuel_liters` (decimal), `fuel_cost_rp` (decimal)
  - `projected_catch_kg` (decimal), `projected_revenue_rp` (decimal)
  - `wave_height_m` (decimal), `moon_phase` (string), `safety_status` (string)
  - `created_at`, `updated_at`
- **Port Scoping:**
  - Port Admins are assigned a `port_code` on their user record.
  - Port Admin Prigi sees all trip plans departing from Prigi; Cilacap admin sees Cilacap; Superadmin sees all.

---

## 7. What's Left to the Builder

- Choosing the specific PDF rendering package (e.g. `barryvdh/laravel-dompdf` vs custom print-optimized Blade view).
- Exact visual styling of the static route map thumbnail in the PDF (generated via SVG or static tile snapshot).
- Adjusting unit tests for deleted routes and new trip planning endpoints.
- Seed data for default admin accounts for Prigi, Cilacap, and Palabuhanratu.

