<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicOceanPlatformPagesTest extends TestCase
{
    public function test_ocean_data_page_is_accessible(): void
    {
        $response = $this->get('/ocean-data');

        $response->assertStatus(200);
        $response->assertSee('Data Oseanografi');
        $response->assertSee('Suhu Permukaan Laut');
        $response->assertSee('Klorofil-a');
        $response->assertSee('NOAA OISST');
        $response->assertSee('Sentinel-3');
    }

    public function test_platform_index_page_is_accessible(): void
    {
        $response = $this->get('/platform');

        $response->assertStatus(200);
        $response->assertSee('Arsitektur Platform End-to-End');
        $response->assertSee('4-Tier Data Pipeline');
        $response->assertSee('Tingkat 1: Ingesti Satelit');
        $response->assertSee('Tingkat 2: PostGIS Spasial');
    }

    public function test_platform_data_sources_page_is_accessible(): void
    {
        $response = $this->get('/platform/data-sources');

        $response->assertStatus(200);
        $response->assertSee('Sumber Data Satelit');
        $response->assertSee('Copernicus Marine');
        $response->assertSee('NASA OceanColor');
        $response->assertSee('GEBCO 2024');
    }

    public function test_platform_features_page_is_accessible(): void
    {
        $response = $this->get('/platform/features');

        $response->assertStatus(200);
        $response->assertSee('Fitur Spesifik Berbasis Profil Armada');
        $response->assertSee('Nelayan Tradisional');
        $response->assertSee('Kapal Motor / Purse Seine');
        $response->assertSee('Syahbandar & Otoritas Pelabuhan');
    }
}
