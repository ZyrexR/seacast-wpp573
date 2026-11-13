<?php

namespace Tests\Unit;

use App\Domain\Species\Services\SpeciesPredictionService;
use Tests\TestCase;

class TctCommodityContractTest extends TestCase
{
    public function test_public_species_contract_contains_only_tct(): void
    {
        $service = app(SpeciesPredictionService::class);

        $this->assertSame([
            'tuna_sirip_kuning',
            'cakalang',
            'tongkol_krai',
        ], $service->getAllSpecies());
    }

    public function test_public_labels_use_tct_nomenclature(): void
    {
        $service = app(SpeciesPredictionService::class);

        $this->assertSame([
            'tuna_sirip_kuning' => 'Tuna Sirip Kuning',
            'cakalang' => 'Cakalang',
            'tongkol_krai' => 'Tongkol Krai',
        ], $service->getPublicSpecies());
    }

    public function test_public_aliases_map_to_existing_internal_keys(): void
    {
        $service = app(SpeciesPredictionService::class);

        $this->assertSame('madidihang', $service->normalizeSpecies('tuna_sirip_kuning'));
        $this->assertSame('tongkol', $service->normalizeSpecies('tongkol_krai'));
        $this->assertSame('cakalang', $service->normalizeSpecies('cakalang'));
    }

    public function test_legacy_species_is_rejected(): void
    {
        $service = app(SpeciesPredictionService::class);
        $legacySpecies = 'alba' . 'kor';

        $this->expectException(\InvalidArgumentException::class);
        $service->normalizeSpecies($legacySpecies);
    }

    public function test_python_bridge_emits_tct_contract_only(): void
    {
        $bridge = file_get_contents(base_path('../scripts/bridge_inference_runner.py'));

        $this->assertIsString($bridge);
        $this->assertStringContainsString('tuna_sirip_kuning', $bridge);
        $this->assertStringContainsString('tongkol_krai', $bridge);
        $this->assertStringContainsString('hsi_tongkol', $bridge);
        $this->assertStringNotContainsString('alba' . 'kor', strtolower($bridge));
        $this->assertStringNotContainsString('alba' . 'core', strtolower($bridge));
    }

    public function test_active_laravel_surfaces_do_not_expose_legacy_species(): void
    {
        $roots = [
            base_path('app'),
            base_path('resources'),
            base_path('routes'),
        ];

        foreach ($roots as $root) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
            foreach ($iterator as $file) {
                if (!$file->isFile() || !str_ends_with($file->getFilename(), '.php')) {
                    continue;
                }

                $contents = strtolower((string) file_get_contents($file->getPathname()));
                $this->assertStringNotContainsString('alba' . 'kor', $contents, $file->getPathname());
                $this->assertStringNotContainsString('alba' . 'core', $contents, $file->getPathname());
            }
        }
    }

    public function test_dashboard_species_list_uses_tct_labels(): void
    {
        $dashboard = file_get_contents(base_path('app/Http/Controllers/Web/DashboardController.php'));

        $this->assertIsString($dashboard);
        $this->assertStringContainsString("'tuna_sirip_kuning'", $dashboard);
        $this->assertStringContainsString("'tongkol_krai'", $dashboard);
        $this->assertStringContainsString('Tuna Sirip Kuning', $dashboard);
        $this->assertStringContainsString('Tongkol Krai', $dashboard);
    }
}
