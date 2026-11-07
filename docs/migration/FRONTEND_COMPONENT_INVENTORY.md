# SEACAST PRIGI V3 — FRONTEND COMPONENT INVENTORY
**Audit Date:** 4 November 2026  
**Incumbent Stack:** React 19 / TypeScript / Tailwind CSS  
**Target Migration Stack:** Laravel Blade Anonymous Components + Livewire 4 + Alpine.js  

---

## 1. INVENTARIS 16 KOMPONEN BASE UI (`components/ui`)

| Nama Komponen | File Incumbent | Fungsi / Varian | Target Laravel Blade | Status |
| :--- | :--- | :--- | :--- | :---: |
| **Alert** | `ui/Alert.tsx` | info, success, warning, danger | `<x-ui.alert :type="$type">` | **READY TO MIGRATE** |
| **Badge** | `ui/Badge.tsx` | default, success, warning, danger, neutral | `<x-ui.badge :variant="$variant">` | **READY TO MIGRATE** |
| **Breadcrumbs** | `ui/Breadcrumbs.tsx` | Navigasi hirarkis dengan separator | `<x-ui.breadcrumbs :items="$items">` | **READY TO MIGRATE** |
| **Button** | `ui/Button.tsx` | primary, secondary, outline, ghost, sizes | `<x-ui.button :variant="$variant">` | **READY TO MIGRATE** |
| **Card** | `ui/Card.tsx` | Kontainer dengan header, body, footer | `<x-ui.card>` | **READY TO MIGRATE** |
| **EmptyState** | `ui/EmptyState.tsx` | Tampilan data kosong dengan ikon & aksi | `<x-ui.empty-state>` | **READY TO MIGRATE** |
| **ErrorState** | `ui/ErrorState.tsx` | Tampilan kegagalan jaringan + tombol retry | `<x-ui.error-state>` | **READY TO MIGRATE** |
| **Input** | `ui/Input.tsx` | Input teks terintegrasi label & error message | `<x-ui.input>` | **READY TO MIGRATE** |
| **MapToolbar** | `ui/MapToolbar.tsx` | Kontrol zoom, reset, layer toggle peta | `<x-ui.map-toolbar>` (Alpine.js) | **READY TO MIGRATE** |
| **Modal** | `ui/Modal.tsx` | Dialog popover dengan overlay backdrop | `<x-ui.modal>` (Alpine.js `x-dialog`) | **READY TO MIGRATE** |
| **ScientificLegend**| `ui/ScientificLegend.tsx`| Skala warna HSI & batimetri | `<x-ui.scientific-legend>` | **READY TO MIGRATE** |
| **Skeleton** | `ui/Skeleton.tsx` | Skeleton loader teks & card | `<x-ui.skeleton>` | **READY TO MIGRATE** |
| **StatCard** | `ui/StatCard.tsx` | Kartu metrik dengan delta tren | `<x-ui.stat-card>` | **READY TO MIGRATE** |
| **Table** | `ui/Table.tsx` | Tabel data dengan scroll horizontal | `<x-ui.table>` | **READY TO MIGRATE** |
| **Tabs** | `ui/Tabs.tsx` | Pengalih tab dengan state aktif | `<x-ui.tabs>` (Alpine.js `x-data`) | **READY TO MIGRATE** |
| **Tooltip** | `ui/Tooltip.tsx` | Popover hover teks bantuan | `<x-ui.tooltip>` (Alpine.js) | **READY TO MIGRATE** |

---

## 2. INVENTARIS 7 SEKSI HOMEPAGE PUBLIK (`components/public`)

| Nama Seksi | File Incumbent | Peran Visual & Fungsi | Target Blade View / Livewire | Status |
| :--- | :--- | :--- | :--- | :---: |
| **HeroOceanIntelligence** | `public/HeroOceanIntelligence.tsx` | Focal point peta laut, isolines, partikel arus, beacon PPN Prigi | `views/components/public/hero.blade.php` + Alpine.js | **READY TO MIGRATE** |
| **OperationalSnapshotStrip** | `public/OperationalSnapshotStrip.tsx`| 5 kolom telemetri PostGIS (hotspots, wave, species) | Livewire `App\Livewire\Public\SnapshotStrip` | **READY TO MIGRATE** |
| **OceanographicIntelligenceExplorer** | `public/OceanographicIntelligenceExplorer.tsx` | Tab 4 parameter bio-fisik, color ramp | `views/components/public/ocean-explorer.blade.php` | **READY TO MIGRATE** |
| **SpeciesHabitatDossiers** | `public/SpeciesHabitatDossiers.tsx` | Dossier 3 tuna, AUC badges, progress bars | `views/components/public/species-dossiers.blade.php` | **READY TO MIGRATE** |
| **DynamicOceanManagementVisualizer** | `public/DynamicOceanManagementVisualizer.tsx`| Profil penampang lintang laut & matriks 5 zona | `views/components/public/dom-visualizer.blade.php` | **READY TO MIGRATE** |
| **ScientificFrameworkPipeline** | `public/ScientificFrameworkPipeline.tsx` | Alur linier 5 tahap satelit ke palka armada | `views/components/public/pipeline.blade.php` | **READY TO MIGRATE** |
| **CommandGatewayCTA** | `public/CommandGatewayCTA.tsx` | Mission control CTA & matriks rute portal | `views/components/public/gateway-cta.blade.php` | **READY TO MIGRATE** |

---

## 3. INVENTARIS LAYOUT & NAVIGASI

| Komponen | File Incumbent | Deskripsi | Target Blade View | Status |
| :--- | :--- | :--- | :--- | :---: |
| **PublicNavbar** | `public/PublicNavbar.tsx` | Navigasi publik (varian dark & light, dropdown) | `views/layouts/partials/public-navbar.blade.php` | **READY TO MIGRATE** |
| **PublicFooter** | `public/PublicFooter.tsx` | Footer resmi platform kelautan WPP 573 | `views/layouts/partials/public-footer.blade.php` | **READY TO MIGRATE** |
| **DashboardSidebar**| `components/DashboardSidebar.tsx` | Navigasi samping dashboard operasional | `views/layouts/partials/dashboard-sidebar.blade.php` | **READY TO MIGRATE** |
| **DashboardTopbar** | `components/DashboardTopbar.tsx` | Topbar status DB, sensor, profile user | `views/layouts/partials/dashboard-topbar.blade.php` | **READY TO MIGRATE** |

---

## 4. INVENTARIS MODUL GEOSPASIAL & ANALISIS DASHBOARD

| Modul | File Incumbent | Teknologi | Target Implementasi Laravel | Status |
| :--- | :--- | :--- | :--- | :---: |
| **MapCanvas** | `components/MapCanvas.tsx` | MapLibre GL JS + CARTO Dark | `resources/js/components/maplibre-canvas.js` (Alpine wrapper) | **REQUIRES REFACTORING** |
| **LayerControls** | `components/LayerControls.tsx` | State sakelar layer peta | Livewire `App\Livewire\Dashboard\LayerControls` | **READY TO MIGRATE** |
| **RegionalSectorNav**| `components/RegionalSectorNav.tsx`| Filter sektor Jatim/Jabar/Bali | Livewire event dispatcher | **READY TO MIGRATE** |
| **SmartActionCard** | `components/SmartActionCard.tsx` | Inspektor detail sel terpilih | Livewire reactive listener | **READY TO MIGRATE** |
| **TimeMachineSlider**| `components/TimeMachineSlider.tsx`| Slider pemutar 12 bulan 2025 | Livewire reactive slider + Alpine timer | **READY TO MIGRATE** |
| **OceanSimulator** | `components/OceanSimulator.tsx` | Skenario pertubasi iklim | Livewire `App\Livewire\Dashboard\OceanSimulator` | **READY TO MIGRATE** |
| **PdfExportModal** | `components/PdfExportModal.tsx` | Generator laporan PDF | Laravel DomPDF / Browsershot integration | **READY TO MIGRATE** |
| **Modul Analisis Khusus** | `components/modules/*.tsx` (6 modals) | Modul HSI, CPUE, Routing, Logbook, Export | Livewire Modals / Alpine Components | **READY TO MIGRATE** |

---

## 5. STRATEGI IMPLEMENTASI MAPLIBRE GL DI LARAVEL

MapLibre GL JS tetap menjadi satu-satunya library geospasial browser-side, dibungkus secara bersih dalam Alpine.js:

```html
<div x-data="dpiRadarMap({
    apiUrl: '{{ route('api.predictions.index') }}',
    defaultCenter: [111.7275, -8.2833],
    selectedSpecies: @entangle('species')
})" x-init="initMap()" class="w-full h-full relative">
    <div x-ref="mapContainer" class="w-full h-full"></div>
    <x-dashboard.map-overlay />
</div>
```

Komponen ini menghilangkan ketergantungan runtime Node.js/React secara total tanpa mengurangi kehalusan animasi WebGL dan responsivitas peta 8.129 sel.

