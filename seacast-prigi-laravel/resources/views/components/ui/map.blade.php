@props([
    'mapId' => 'seacast-leaflet-container',
    'center' => [111.7275, -8.45],
    'zoom' => 8,
    'minZoom' => 4,
    'maxZoom' => 18,
])

<!-- mapcn Map Component (Native Blade + Alpine.js + Leaflet 1.9.4) -->
<div 
    {{ $attributes->merge(['class' => 'relative w-full h-full bg-slate-950 overflow-hidden select-none']) }}
    x-data="{
        mapReady: false,
    }"
>
    <!-- Map Canvas Element -->
    <div id="{{ $mapId }}" class="w-full h-full cursor-crosshair"></div>

    <!-- Mapcn Overlay Slots -->
    {{ $slot }}
</div>

