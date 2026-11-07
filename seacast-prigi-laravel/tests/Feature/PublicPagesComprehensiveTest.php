<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesComprehensiveTest extends TestCase
{
    public function test_about_page_focuses_purely_on_seacast_application(): void
    {
        $response = $this->get('/about');

        $response->assertStatus(200);

        // Application identity and core pillars
        $response->assertSee('Tentang Platform SEACAST');
        $response->assertSee('Scientific Ocean Intelligence');
        $response->assertSee('Tiga Pilar Nilai Aplikasi');
        $response->assertSee('Efisiensi Bahan Bakar');
        $response->assertSee('Keselamatan Maritim');

        // Application features
        $response->assertSee('DPI Radar Spasial Interaktif');
        $response->assertSee('Trip Planner');
        $response->assertSee('Penerbitan Dokumen Sailing Advisory');

        // Target users and tech stack
        $response->assertSee('nahkoda', false);
        $response->assertSee('syahbandar', false);
        $response->assertSee('Laravel 13');
        $response->assertSee('PostgreSQL 17 + PostGIS 3.6');

        // Must NOT contain monolithic local port profile of PPN Prigi
        $response->assertDontSee('PPN Prigi yang terletak di Teluk Prigi');
        $response->assertDontSee('Struktur Armada Penangkap Ikan PPN Prigi');
    }

    public function test_science_page_covers_three_species_and_dom_framework(): void
    {
        $response = $this->get('/science');

        $response->assertStatus(200);

        // Core methodology
        $response->assertSee('Metodologi Bio-Oseanografi');
        $response->assertSee('Ensemble Species Distribution Model');

        // 3 Pelagic target species
        $response->assertSee('Tongkol');
        $response->assertSee('Auxis thazard');
        $response->assertSee('Cakalang');
        $response->assertSee('Katsuwonus pelamis');
        $response->assertSee('Madidihang');
        $response->assertSee('Thunnus albacares');

        // Mathematical formulation and algorithms
        $response->assertSee('PERSAMAAN ENSEMBLE KOMPOSIT MULTI-ALGORITMA');
        $response->assertSee('MaxEnt');
        $response->assertSee('Random Forest');
        $response->assertSee('GLM');

        // 5-zone Dynamic Ocean Management
        $response->assertSee('Kerangka Kerja Dynamic Ocean Management (DOM) 5-Zona');
        $response->assertSee('Inshore Pelagic Fishing Ground');
        $response->assertSee('Offshore Pelagic Fishing Ground');
        $response->assertSee('Pelagic Feeding Core');
        $response->assertSee('High Bycatch Risk Zone');
    }

    public function test_ocean_data_page_renders_live_telemetry_and_satellite_missions(): void
    {
        $response = $this->get('/ocean-data');

        $response->assertStatus(200);

        // Header and live telemetry bar
        $response->assertSee('Katalog Data Oseanografi Satelit');
        $response->assertSee('TELEMETRI HARIAN AKTIF BASIS DATA POSTGIS');
        $response->assertSee('RATA-RATA SST');
        $response->assertSee('KLOROFIL-A');
        $response->assertSee('GELOMBANG RATA-RATA');

        // Satellite missions constellation
        $response->assertSee('Sentinel-3A');
        $response->assertSee('Ina-WAVES Maritim');
        $response->assertSee('HYCOM Global');
        $response->assertSee('NOAA OISST v2.1');
    }

    public function test_documentation_page_renders_multirole_guides_and_regulations(): void
    {
        $response = $this->get('/documentation');

        $response->assertStatus(200);

        // Header and role workflows
        $response->assertSee('Dokumentasi Teknis');
        $response->assertSee('Nahkoda & Nelayan', false);
        $response->assertSee('Syahbandar & Otoritas Pelabuhan', false);
        $response->assertSee('Peneliti & Pengelola Sumberdaya', false);

        // Regulatory compliance & safety
        $response->assertSee('Permen KP No. 59/PERMEN-KP/2020');
        $response->assertSee('Jalur I');
        $response->assertSee('Jalur II');
        $response->assertSee('Jalur III');
        $response->assertSee('Standar Keselamatan Pelayaran BMKG');
        $response->assertSee('Bycatch IOTC');

        // Glossary
        $response->assertSee('Habitat Suitability Index (HSI)');
        $response->assertSee('Dynamic Ocean Management (DOM)');
        $response->assertSee('Upwelling');
        $response->assertSee('Thermal Front');
    }
}
