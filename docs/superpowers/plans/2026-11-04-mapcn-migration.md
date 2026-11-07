# mapcn Migration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transform the geospatial map interface of SEACAST PRIGI V3 to adopt the **mapcn** (https://www.mapcn.dev) design system, architecture, and UI components using a native Laravel Blade + Alpine.js + Tailwind CSS implementation.

**Architecture:** Create composable Blade UI components (`<x-ui.map>`, `<x-ui.map-controls>`, `<x-ui.map-marker>`, `<x-ui.map-popup>`, `<x-ui.map-layer-switcher>`) replicating the sleek shadcn/ui design language of `mapcn.dev`. Integrate them directly into `resources/views/dashboard/dpi-radar.blade.php`, replacing raw MapLibre default controls with modern glassmorphism widgets, custom animated pulse markers, and tactile micro-interactions.

**Tech Stack:** Laravel 13, Blade Components, Livewire 4, Alpine.js, Tailwind CSS, MapLibre GL JS, PostgreSQL 17 + PostGIS 3.6.2.

**Spec:** User directive: "untuk penggunaan map kita beralih pakai mapcn https://www.mapcn.dev/ - Opsi 1 (Native Blade + Alpine + Tailwind mapcn)".

## Global Constraints

- Preserve pure Laravel Modular Monolith (no unnecessary React runtime dependencies).
- Retain all scientific logic, PostGIS 8,129 cell rendering, DOM waterfall hierarchy, Bycatch screening, and HSI color ramps.
- Maintain font floor $\ge 12\text{px}$ across all map HUD elements and popup cards.
- Implement Emil Kowalski's tactile micro-interactions (`active:scale-95`, snappy ease-out transitions).
- Zero regression in PHPUnit test suite (maintain 100% pass rate).

## Review Focus

1. **Map Controls Usability:** Floating `<x-ui.map-controls>` must properly trigger MapLibre camera actions (`zoomIn()`, `zoomOut()`, `resetNorthPitch()`, `flyTo([111.7275, -8.2833])`, and `toggleFullscreen()`).
2. **Marker Pulse Performance:** The Homeport PPN Prigi marker pulse ring must be GPU-accelerated without layout thrashing.
3. **Popup Formatting & Contrast:** Cell inspector popup must maintain WCAG AA contrast ratio against dark basemap tiles.
4. **Layer Switcher State Synchronization:** Switching layers (HSI, DOM, Bycatch, Batimetri) must update both MapLibre paint properties and the floating scientific legend without visual flicker.
5. **Mobile & Responsive HUD:** On small screens, controls must not obstruct the bottom drawer or header controls.

---

### Task 1: Create Core `<x-ui.map-controls>` Component

**Files:**
- Create: `seacast-prigi-laravel/resources/views/components/ui/map-controls.blade.php`
- Test: `seacast-prigi-laravel/tests/Feature/MapcnComponentsTest.php`

**Steps:**
- [ ] Create `map-controls.blade.php` with shadcn/ui dark aesthetics (`bg-slate-900/90 backdrop-blur-md border border-slate-800 rounded-xl shadow-2xl p-1 gap-1`).
- [ ] Implement Zoom In (`+`), Zoom Out (`-`), Reset Bearing/Compass (`🧭`), Recenter Homeport (`🎯`), and Fullscreen (`⛶`) buttons with SVG icons, tooltips, and `active:scale-95` tactile feedback.
- [ ] Bind buttons to Alpine.js events / MapLibre instance methods (`zoomIn`, `zoomOut`, `resetNorth`, `recenterHomeport`, `toggleFullscreen`).

---

### Task 2: Create `<x-ui.map-marker>` and `<x-ui.map-popup>` Components

**Files:**
- Create: `seacast-prigi-laravel/resources/views/components/ui/map-marker.blade.php`
- Create: `seacast-prigi-laravel/resources/views/components/ui/map-popup.blade.php`
- Test: `seacast-prigi-laravel/tests/Feature/MapcnComponentsTest.php`

**Steps:**
- [ ] Build `map-marker.blade.php` supporting animated radar pulse outer rings (`animate-ping`), glowing inner beacons, and customizable color variants (cyan, amber, emerald, rose).
- [ ] Build `map-popup.blade.php` providing a styled dark card container with subtle borders (`border-slate-800`), header badge, metadata grid, and close button following `mapcn` popover patterns.

---

### Task 3: Create `<x-ui.map-layer-switcher>` Component

**Files:**
- Create: `seacast-prigi-laravel/resources/views/components/ui/map-layer-switcher.blade.php`
- Test: `seacast-prigi-laravel/tests/Feature/MapcnComponentsTest.php`

**Steps:**
- [ ] Build `map-layer-switcher.blade.php` featuring a segmented control pill with active slide highlight for layer modes:
  - `HSI Habitat Tuna`
  - `Zonasi DOM (Z1-Z5)`
  - `Risiko Bycatch IOTC`
  - `Batimetri GEBCO`
- [ ] Style with subtle active pill backgrounds (`bg-cyan-500/20 text-cyan-300 border-cyan-500/40`), typography $\ge 12\text{px}$, and keyboard navigation support.

---

### Task 4: Refactor `dashboard/dpi-radar.blade.php` to mapcn Architecture

**Files:**
- Modify: `seacast-prigi-laravel/resources/views/dashboard/dpi-radar.blade.php`
- Test: `seacast-prigi-laravel/tests/Feature/DpiRadarViewTest.php`

**Steps:**
- [ ] Remove legacy default `maplibregl.NavigationControl` from `dpi-radar.blade.php`.
- [ ] Embed `<x-ui.map-controls>` positioned at `top-4 right-4` with Alpine.js hooks.
- [ ] Embed `<x-ui.map-layer-switcher>` into the radar HUD header.
- [ ] Update Homeport PPN Prigi marker DOM element to use the `mapcn` animated pulse markup.
- [ ] Integrate modern popup container for cell inspection.
- [ ] Ensure scale control (`maxWidth: 100, unit: 'nautical'`) integrates seamlessly at `bottom-left`.

---

### Task 5: Automated Testing & Visual Verification

**Files:**
- Create: `seacast-prigi-laravel/tests/Feature/MapcnComponentsTest.php`
- Modify: `seacast-prigi-laravel/tests/Feature/DpiRadarViewTest.php`

**Steps:**
- [ ] Write unit/feature tests for `map-controls`, `map-marker`, `map-popup`, and `map-layer-switcher` components in `MapcnComponentsTest.php`.
- [ ] Update `DpiRadarViewTest.php` to assert presence of `mapcn` UI controls and attributes.
- [ ] Run full PHPUnit suite (`php artisan test`) and verify 100% PASS with zero regressions.
- [ ] Take screenshots of the redesigned DPI radar at desktop (1440x900) and mobile (390x844).

---

### Task 6: Documentation & Deliverables

**Files:**
- Create: `docs/migration/PHASE_2_MAPCN_MIGRATION_REPORT.md`
- Update: `docs/migration/PHASE_2_CHANGELOG.md`

**Steps:**
- [ ] Document the mapcn migration, component specifications, Before/After comparison, and visual design improvements.
- [ ] Update changelog with Phase 2 mapcn enhancement details.

