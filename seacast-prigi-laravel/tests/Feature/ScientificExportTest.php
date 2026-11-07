<?php

namespace Tests\Feature;

use Tests\TestCase;
use ZipArchive;

class ScientificExportTest extends TestCase
{
    public function test_geojson_export_returns_valid_attachment(): void
    {
        $response = $this->get('/api/export/geojson?limit=5');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/geo+json', (string)$response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="seacast_prigi_predictions_2026-11-03.geojson"', (string)$response->headers->get('Content-Disposition'));

        $json = json_decode($response->getContent(), true);
        $this->assertEquals('FeatureCollection', $json['type']);
        $this->assertCount(5, $json['features']);
        $this->assertEquals('Polygon', $json['features'][0]['geometry']['type']);
    }

    public function test_csv_export_returns_valid_csv_with_bom_and_headers(): void
    {
        $response = $this->get('/api/export/csv?limit=5');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string)$response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="seacast_prigi_predictions_2026-11-03.csv"', (string)$response->headers->get('Content-Disposition'));

        $content = $response->getContent();
        // Assert UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // Assert CSV headers
        $this->assertStringContainsString('cell_id,cell_class,center_longitude,center_latitude,depth_m', $content);
        $this->assertStringContainsString('hsi_cakalang,hsi_madidihang,hsi_albakor', $content);
        $this->assertStringContainsString('dom_zone,bycatch_traffic_light', $content);
    }

    public function test_gis_package_zip_export_contains_all_gis_components(): void
    {
        $response = $this->get('/api/export/gis-package?limit=5');

        $response->assertStatus(200);
        $this->assertEquals('application/zip', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="seacast_prigi_gis_package_2026-11-03.zip"', (string)$response->headers->get('Content-Disposition'));

        $tempFile = tempnam(sys_get_temp_dir(), 'test_zip_');
        file_put_contents($tempFile, $response->getContent());

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($tempFile) === true);

        $this->assertNotFalse($zip->locateName('seacast_prigi_2026-11-03.geojson'));
        $this->assertNotFalse($zip->locateName('seacast_prigi_2026-11-03.csv'));
        $this->assertNotFalse($zip->locateName('seacast_prigi_2026-11-03.prj'));
        $this->assertNotFalse($zip->locateName('seacast_prigi_2026-11-03.cpg'));
        $this->assertNotFalse($zip->locateName('METADATA_README.txt'));

        $readme = $zip->getFromName('METADATA_README.txt');
        $this->assertStringContainsString('WPPNRI 573', $readme);
        $this->assertStringContainsString('EPSG:4326', $readme);

        $prj = $zip->getFromName('seacast_prigi_2026-11-03.prj');
        $this->assertStringContainsString('GEOGCS["WGS 84"', $prj);

        $zip->close();
        @unlink($tempFile);
    }

    public function test_export_rejects_invalid_date_format(): void
    {
        $response = $this->getJson('/api/export/geojson?date=invalid-date');
        $response->assertStatus(422);
    }
}

