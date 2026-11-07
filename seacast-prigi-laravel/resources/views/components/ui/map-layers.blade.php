@props([
    'activeVar' => 'activeLayer',
    'setFn' => 'setLayerMode'
])

<!-- mapcn MapLayers Component -->
<x-ui.map-layer-switcher 
    :activeVar="$activeVar" 
    :setFn="$setFn" 
    {{ $attributes }} 
/>

