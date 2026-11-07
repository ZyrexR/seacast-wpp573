<?php

namespace App\Domain\Species\Services;

use App\Domain\Species\DTO\EnsembleWeights;
use App\Domain\Species\DTO\SpeciesPrediction;

class SpeciesPredictionService
{
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
        'albakor' => [
            'maxent' => 0.000, // Strictly 0.000 due to circular proxy AUC = 0.507
            'rf' => 0.517,
            'glm' => 0.483,
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
            'species_name' => 'Madidihang (Thunnus albacares)',
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
        'albakor' => [
            'species_name' => 'Albakor (Thunnus alalunga)',
            'auc' => 0.860,
            'tss' => 0.743,
            'status' => 'EXPLORATORY_HEURISTIC_SCREENING',
            'components' => [
                'maxent' => ['auc' => 0.507, 'tss' => 0.143, 'weight' => 0.000, 'note' => 'MaxEnt zeroed because circular proxy AUC = 0.507 is random guessing level'],
                'rf' => ['auc' => 0.862, 'tss' => 0.723, 'weight' => 0.517],
                'glm' => ['auc' => 0.825, 'tss' => 0.000, 'weight' => 0.483],
            ],
            'caveat' => 'MaxEnt component completely excluded (0.000) from prediction.'
        ],
    ];

    public function getEnsembleWeights(string $species): EnsembleWeights
    {
        $key = strtolower($species);
        if (!isset($this->lockedWeights[$key])) {
            throw new \InvalidArgumentException("Unknown species: '{$species}'. Approved: cakalang, madidihang, albakor.");
        }

        $w = $this->lockedWeights[$key];
        return new EnsembleWeights($w['maxent'], $w['rf'], $w['glm']);
    }

    public function getModelEvaluationSummary(string $species): array
    {
        $key = strtolower($species);
        if (!isset($this->evaluationSummaries[$key])) {
            throw new \InvalidArgumentException("Unknown species: '{$species}'.");
        }

        return $this->evaluationSummaries[$key];
    }

    public function getAllSpecies(): array
    {
        return array_keys($this->lockedWeights);
    }
}

