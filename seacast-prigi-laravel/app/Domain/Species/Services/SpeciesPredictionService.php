<?php

namespace App\Domain\Species\Services;

use App\Domain\Species\DTO\EnsembleWeights;
use App\Domain\Species\DTO\SpeciesPrediction;

class SpeciesPredictionService
{
    protected array $publicSpecies = [
        'tuna_sirip_kuning' => 'Tuna Sirip Kuning',
        'cakalang' => 'Cakalang',
        'tongkol_krai' => 'Tongkol Krai',
    ];

    protected array $speciesAliases = [
        'tuna_sirip_kuning' => 'madidihang',
        'tuna sirip kuning' => 'madidihang',
        'yellowfin' => 'madidihang',
        'yellowfin tuna' => 'madidihang',
        'madidihang' => 'madidihang',
        'cakalang' => 'cakalang',
        'tongkol_krai' => 'tongkol',
        'tongkol krai' => 'tongkol',
        'tongkol' => 'tongkol',
    ];

    protected array $lockedWeights = [
        'cakalang' => [
            'maxent' => 0.320,
            'rf' => 0.355,
            'glm' => 0.325,
        ],
        'madidihang' => [
            'maxent' => 0.295,
            'rf' => 0.365,
            'glm' => 0.340,
        ],
        'tongkol' => [
            'maxent' => 0.332,
            'rf' => 0.335,
            'glm' => 0.333,
        ],
    ];

    protected array $evaluationSummaries = [
        'cakalang' => [
            'species_name' => 'Cakalang (Katsuwonus pelamis)',
            'auc' => 0.795,
            'tss' => 0.630,
            'status' => 'EXPLORATORY_HEURISTIC_SCREENING',
            'components' => [
                'maxent' => ['auc' => 0.774, 'tss' => 0.084, 'weight' => 0.320],
                'rf' => ['auc' => 0.805, 'tss' => 0.610, 'weight' => 0.355],
                'glm' => ['auc' => 0.750, 'tss' => 0.473, 'weight' => 0.325],
            ],
            'caveat' => 'Component scores evaluated via analytical heuristic response equations against deterministic presence proxies. No statistical ML fitting executed in code.'
        ],
        'madidihang' => [
            'species_name' => 'Tuna Sirip Kuning (Thunnus albacares)',
            'auc' => 0.784,
            'tss' => 0.606,
            'status' => 'EXPLORATORY_HEURISTIC_SCREENING',
            'components' => [
                'maxent' => ['auc' => 0.722, 'tss' => 0.000, 'weight' => 0.295],
                'rf' => ['auc' => 0.804, 'tss' => 0.586, 'weight' => 0.365],
                'glm' => ['auc' => 0.755, 'tss' => 0.422, 'weight' => 0.340],
            ],
            'caveat' => 'Thermal front and SST dominant proxy discrimination.'
        ],
        'tongkol' => [
            'species_name' => 'Tongkol Krai (Auxis thazard)',
            'auc' => 0.892,
            'tss' => 0.752,
            'status' => 'EXPLORATORY_HEURISTIC_SCREENING',
            'components' => [
                'maxent' => ['auc' => 0.888, 'tss' => 0.725, 'weight' => 0.332],
                'rf' => ['auc' => 0.891, 'tss' => 0.756, 'weight' => 0.335],
                'glm' => ['auc' => 0.888, 'tss' => 0.725, 'weight' => 0.333],
            ],
            'caveat' => 'Ensemble SDM (MaxEnt, RF, GLM) dilatih terhadap sampel presence-proxy neritik 132 bulan WPPNRI 573; belum melalui uji blok spasial tangkapan lapangan.'
        ],
    ];

    public function getEnsembleWeights(string $species): EnsembleWeights
    {
        $key = $this->normalizeSpecies($species);
        if (!isset($this->lockedWeights[$key])) {
            throw new \InvalidArgumentException("Species '{$species}' does not use a locked ensemble model. Tongkol Krai uses its rule-based HSI.");
        }

        $w = $this->lockedWeights[$key];
        return new EnsembleWeights($w['maxent'], $w['rf'], $w['glm']);
    }

    public function getModelEvaluationSummary(string $species): array
    {
        $key = $this->normalizeSpecies($species);
        if (!isset($this->evaluationSummaries[$key])) {
            throw new \InvalidArgumentException("Unknown species: '{$species}'.");
        }

        return $this->evaluationSummaries[$key];
    }

    public function getAllSpecies(): array
    {
        return array_keys($this->publicSpecies);
    }

    public function getPublicSpecies(): array
    {
        return $this->publicSpecies;
    }

    public function normalizeSpecies(string $species): string
    {
        $key = strtolower(trim(str_replace('-', '_', $species)));
        $key = preg_replace('/\s+/', ' ', $key) ?? $key;

        if (isset($this->speciesAliases[$key])) {
            return $this->speciesAliases[$key];
        }

        throw new \InvalidArgumentException(
            "Unknown species: '{$species}'. Approved public species: tuna_sirip_kuning, cakalang, tongkol_krai."
        );
    }

    public function toPublicSpecies(string $species): string
    {
        return match ($this->normalizeSpecies($species)) {
            'madidihang' => 'tuna_sirip_kuning',
            'cakalang' => 'cakalang',
            'tongkol' => 'tongkol_krai',
        };
    }
}

