@props([
    'activeLayer' => 'activeLayer'
])

<!-- mapcn MapLegend Component -->
<x-ui.scientific-legend 
    :activeLayer="$activeLayer" 
    {{ $attributes }} 
/>

