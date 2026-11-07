<?php

namespace App\Domain\Bycatch\Services;

use App\Domain\Bycatch\DTO\BycatchClassification;

class BycatchRiskService
{
    public const SHELF_BREAK_MIN_DEPTH = 150.0;
    public const SHELF_BREAK_MAX_DEPTH = 1200.0;
    public const COLD_FRONT_MAX_SST = 26.5;
    public const COLD_FRONT_MIN_GRAD = 0.035;
    public const RED_RISK_MIN_CAKALANG_HSI = 0.70;

    public function classify(float $depthM, float $sstC, float $gradSst, float $hsiCakalang): BycatchClassification
    {
        $isShelfBreak = ($depthM >= self::SHELF_BREAK_MIN_DEPTH) && ($depthM <= self::SHELF_BREAK_MAX_DEPTH);

        // Outside shelf break is GREEN
        if (!$isShelfBreak) {
            return new BycatchClassification(
                trafficLight: 'GREEN',
                level: 'LOW',
                reasonCode: 'OUTSIDE_SHELF_BREAK_ZONE',
                description: 'Risiko tangkapan sampingan (bycatch) rendah; kedalaman berada di luar zona lereng benua 150-1200m.',
                conditions: [
                    'is_shelf_break' => false,
                    'depth_m' => $depthM,
                ]
            );
        }

        $isColdFront = ($sstC <= self::COLD_FRONT_MAX_SST) && ($gradSst >= self::COLD_FRONT_MIN_GRAD);
        $isHighTunaActivity = ($hsiCakalang >= self::RED_RISK_MIN_CAKALANG_HSI);

        // Cold front on shelf break with dense Cakalang aggregation is RED
        if ($isColdFront && $isHighTunaActivity) {
            return new BycatchClassification(
                trafficLight: 'RED',
                level: 'HIGH',
                reasonCode: 'SHELF_BREAK_COLD_FRONT_HIGH_HSI',
                description: 'Risiko tangkapan sampingan tinggi! Tumpang tindih antara lereng benua 150-1200m, front termal dingin (SST <= 26.5°C), dan agregasi tuna intensif (HSI Cakalang >= 0.70).',
                conditions: [
                    'is_shelf_break' => true,
                    'is_cold_front' => true,
                    'high_hsi' => true,
                    'depth_m' => $depthM,
                    'sst_c' => $sstC,
                    'grad_sst' => $gradSst,
                    'hsi_cakalang' => $hsiCakalang,
                ]
            );
        }

        // On shelf break without all red escalation triggers is YELLOW
        return new BycatchClassification(
            trafficLight: 'YELLOW',
            level: 'PRECAUTIONARY',
            reasonCode: 'SHELF_BREAK_PRECAUTIONARY_MONITORING',
            description: 'Zona kehati-hatian; lereng benua 150-1200m memerlukan pemantauan ketat alat tangkap ramah lingkungan standar IOTC.',
            conditions: [
                'is_shelf_break' => true,
                'is_cold_front' => $isColdFront,
                'high_hsi' => $isHighTunaActivity,
                'depth_m' => $depthM,
            ]
        );
    }
}

