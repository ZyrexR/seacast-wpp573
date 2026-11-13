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

    public function test_albakor_is_rejected(): void
    {
        $service = app(SpeciesPredictionService::class);

        $this->expectException(\InvalidArgumentException::class);
        $service->normalizeSpecies('albakor');
    }
}
