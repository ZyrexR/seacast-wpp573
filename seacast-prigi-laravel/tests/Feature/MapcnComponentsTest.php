<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class MapcnComponentsTest extends TestCase
{
    public function test_map_controls_component_renders_cleanly(): void
    {
        $view = $this->blade('<x-ui.map-controls position="top-right" homeportLabel="PPN Prigi" />');

        $view->assertSee('Zoom In');
        $view->assertSee('Zoom Out');
        $view->assertSee('Reset Compass');
        $view->assertSee('Recenter Homeport');
        $view->assertSee('Toggle Fullscreen');
        $view->assertSee('Pusat PPN Prigi');
    }

    public function test_map_marker_component_renders_with_pulse_and_labels(): void
    {
        $view = $this->blade('<x-ui.map-marker color="cyan" label="PPN Prigi" sublabel="Homeport WPP 573" />');

        $view->assertSee('animate-ping');
        $view->assertSee('PPN Prigi');
        $view->assertSee('Homeport WPP 573');
    }

    public function test_map_popup_component_renders_with_header_and_badge(): void
    {
        $view = $this->blade('
            <x-ui.map-popup title="Inspeksi Sel Grid" badge="WPP 573" badgeColor="cyan">
                <span>Kedalaman: 450 m</span>
            </x-ui.map-popup>
        ');

        $view->assertSee('Inspeksi Sel Grid');
        $view->assertSee('WPP 573');
        $view->assertSee('Kedalaman: 450 m');
    }

    public function test_map_layer_switcher_component_renders_all_layers(): void
    {
        $view = $this->blade('<x-ui.map-layer-switcher activeVar="activeLayer" setFn="setLayerMode" />');

        $view->assertSee('HSI Tuna');
        $view->assertSee('Zonasi DOM');
        $view->assertSee('Bycatch IOTC');
        $view->assertSee('Batimetri');
    }

    public function test_map_basemap_switcher_component_renders_all_basemap_options(): void
    {
        $view = $this->blade('<x-ui.map-basemap-switcher activeVar="currentBasemap" setFn="setBasemap" />');

        $view->assertSee('Citra Satelit');
        $view->assertSee('Radar Gelap');
        $view->assertSee('Samudera');
        $view->assertSee('Topografi');
        $view->assertSee('Navigasi OSM');
    }

    public function test_dpi_radar_view_incorporates_mapcn_controls_and_homeport(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'name' => 'Operator Radar',
            'email' => 'operator@seacast.id',
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get('/dashboard/dpi-radar');

        $response->assertStatus(200);
        $response->assertSee('Homeport PPN Prigi');
        $response->assertSee('DPI Radar Spasial WPPNRI 573');
        $response->assertSee('mapcn-dark-popup');
        $response->assertSee('Citra Satelit');
        $response->assertSee('Opasitas Layer Poligon');
        $response->assertSee('x-ref="mapContainer"', false);
    }
}

