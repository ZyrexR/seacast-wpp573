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
        $response->assertSee('0.795');
        $response->assertSee('0.784');
        $response->assertSee('0.892');
        $response->assertSee('0.752');
        $response->assertSee('PROXY');
        $response->assertDontSee('0.874');
        $response->assertDontSee('0.908');
        $response->assertDontSee('≥ 0.75');
        $response->assertDontSee('>= 0.75');
        $response->assertDontSee('Rule-Based Neritik');
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
        $response->assertSee('0.795');
        $response->assertSee('0.784');
        $response->assertSee('0.892');
        $response->assertSee('0.752');
        $response->assertSee('PROXY');
        $response->assertDontSee('0.874');
        $response->assertDontSee('0.908');
        $response->assertDontSee('≥ 0.75');
        $response->assertDontSee('>= 0.75');
        $response->assertDontSee('Rule-based HSI');
    }

    public function test_official_threshold_contract_remains_at_point_seven(): void
    {
        $summaryPath = dirname(base_path()) . '/models/sdm/ensemble/model_evaluations_summary.json';
        $summary = json_decode((string) file_get_contents($summaryPath), true);

        $this->assertSame(0.7, $summary['cakalang']['individual_models']['glm']['opt_threshold']);
        $this->assertSame(0.7, $summary['madidihang']['individual_models']['glm']['opt_threshold']);
        $this->assertSame(0.892, $summary['tongkol_krai']['ensemble']['auc']);
        $this->assertSame(0.752, $summary['tongkol_krai']['ensemble']['tss']);
    }
}

