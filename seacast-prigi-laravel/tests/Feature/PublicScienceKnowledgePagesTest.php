<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicScienceKnowledgePagesTest extends TestCase
{
    public function test_science_dom_page_is_accessible(): void
    {
        $response = $this->get('/science/dynamic-ocean-management');

        $response->assertStatus(200);
        $response->assertSee('Dynamic Ocean Management');
        $response->assertSee('Zona Z1');
        $response->assertSee('Zona Z2');
        $response->assertSee('Zona Z3');
        $response->assertSee('Zona Z4');
        $response->assertSee('Zona Z5');
        $response->assertSee('Waterfall DOM');
    }

    public function test_science_methodology_page_is_accessible(): void
    {
        $response = $this->get('/science/methodology');

        $response->assertStatus(200);
        $response->assertSee('Metodologi Ensemble');
        $response->assertSee('Maximum Entropy');
        $response->assertSee('Random Forest');
        $response->assertSee('GLM');
        $response->assertSee('10-Fold Spatial Cross-Validation');
    }

    public function test_science_models_page_is_accessible(): void
    {
        $response = $this->get('/science/prediction-models');

        $response->assertStatus(200);
        $response->assertSee('Model Prediksi Komoditas TCT');
        $response->assertSee('Cakalang');
        $response->assertSee('Katsuwonus pelamis');
        $response->assertSee('Tuna Sirip Kuning');
        $response->assertSee('Thunnus albacares');
        $response->assertSee('Tongkol Krai');
        $response->assertSee('Auxis thazard');
    }
}

