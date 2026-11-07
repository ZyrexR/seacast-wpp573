<?php

namespace App\Domain\DynamicOceanManagement\Services;

use App\Domain\DynamicOceanManagement\DTO\DomZoneClassification;

class DomClassificationService
{
    // Threshold constants matching PRD SEACAST V2.3.2 baseline
    public const Z4_MAX_DEPTH_M = 150.0;
    public const Z4_MAX_DIST_PORT_NM = 15.0;

    public const Z5_MIN_DEPTH_M = 150.0;
    public const Z5_MAX_DEPTH_M = 1200.0;
    public const Z5_MAX_SST_C = 26.5;
    public const Z5_MIN_GRAD_SST = 0.035;

    public const Z3_MIN_CHLA_MG_M3 = 0.40;
    public const Z3_MIN_GRAD_SST = 0.035;

    public const Z1_MAX_DIST_PORT_NM = 12.0;
    public const Z2_MIN_DIST_PORT_NM = 12.0;
    public const Z2_MAX_DIST_PORT_NM = 60.0;
    public const PFG_MIN_TUNA_HSI = 0.55;

    public function classify(array $params): DomZoneClassification
    {
        $depth = (float)($params['depth'] ?? 1000.0);
        $distPortNm = (float)($params['dist_port_nm'] ?? 100.0);
        $sst = (float)($params['sst'] ?? 28.5);
        $gradSst = (float)($params['grad_sst'] ?? 0.015);
        $chla = (float)($params['chla'] ?? 0.20);
        $hsiCakalang = (float)($params['hsi_cakalang'] ?? 0.0);
        $hsiMadidihang = (float)($params['hsi_madidihang'] ?? 0.0);

        $metCriteria = [];

        // Check individual condition flags
        $isNursery = ($depth <= self::Z4_MAX_DEPTH_M) && ($distPortNm <= self::Z4_MAX_DIST_PORT_NM);
        if ($isNursery) $metCriteria[] = 'Z4_NURSERY_GROUND';

        $isShelfBreak = ($depth >= self::Z5_MIN_DEPTH_M) && ($depth <= self::Z5_MAX_DEPTH_M);
        $isThermalFront = ($gradSst >= self::Z5_MIN_GRAD_SST);
        $isBycatchScreening = $isShelfBreak && ($sst <= self::Z5_MAX_SST_C) && $isThermalFront;
        if ($isBycatchScreening) $metCriteria[] = 'Z5_BYCATCH_SCREENING';

        $isFeedingCore = ($chla >= self::Z3_MIN_CHLA_MG_M3) && $isThermalFront;
        if ($isFeedingCore) $metCriteria[] = 'Z3_FEEDING_CORE';

        $hasTuna = ($hsiCakalang >= self::PFG_MIN_TUNA_HSI) || ($hsiMadidihang >= self::PFG_MIN_TUNA_HSI);

        $isInshorePfg = ($distPortNm <= self::Z1_MAX_DIST_PORT_NM) && $hasTuna;
        if ($isInshorePfg) $metCriteria[] = 'Z1_PFG_INSHORE';

        $isOffshorePfg = ($distPortNm > self::Z2_MIN_DIST_PORT_NM) && ($distPortNm <= self::Z2_MAX_DIST_PORT_NM) && $hasTuna;
        if ($isOffshorePfg) $metCriteria[] = 'Z2_PFG_OFFSHORE';

        // Strict Priority Order Enforcement: Z4 -> Z5 -> Z3 -> Z1 -> Z2 -> GEN
        if ($isNursery) {
            return new DomZoneClassification(
                zoneKey: 'Z4_NURSERY_GROUND',
                zoneName: 'Zona 4 — Paparan Dangkal & Daerah Asuhan (Nursery Ground)',
                description: 'Zona pesisir dangkal <=150m dan jarak <=15 NM dari PPN Prigi.',
                priorityRank: 1,
                reasonCode: 'DEPTH_LE_150M_AND_DIST_LE_15NM',
                metCriteria: $metCriteria
            );
        }

        if ($isBycatchScreening) {
            return new DomZoneClassification(
                zoneKey: 'Z5_BYCATCH_SCREENING',
                zoneName: 'Zona 5 — Front Termal Lereng Benua & Skrining Bycatch',
                description: 'Lereng benua 150-1200m dengan suhu dingin <=26.5°C dan gradien termal >=0.035°C/km.',
                priorityRank: 2,
                reasonCode: 'SHELF_BREAK_AND_SST_LE_26_5_AND_FRONT_GE_0_035',
                metCriteria: $metCriteria
            );
        }

        if ($isFeedingCore) {
            return new DomZoneClassification(
                zoneKey: 'Z3_FEEDING_CORE',
                zoneName: 'Zona 3 — Inti Produktivitas Klorofil (Feeding Core)',
                description: 'Konsentrasi klorofil-a tinggi >=0.40 mg/m³ dengan gradien termal aktif >=0.035°C/km.',
                priorityRank: 3,
                reasonCode: 'CHLA_GE_0_40_AND_FRONT_GE_0_035',
                metCriteria: $metCriteria
            );
        }

        if ($isInshorePfg) {
            return new DomZoneClassification(
                zoneKey: 'Z1_PFG_INSHORE',
                zoneName: 'Zona 1 — Potensi Penangkapan Ikan Pesisir (Inshore PFG)',
                description: 'Perairan pesisir <=12 NM dengan kesesuaian habitat tuna >=0.55 untuk armada <10 GT.',
                priorityRank: 4,
                reasonCode: 'DIST_LE_12NM_AND_TUNA_HSI_GE_0_55',
                metCriteria: $metCriteria
            );
        }

        if ($isOffshorePfg) {
            return new DomZoneClassification(
                zoneKey: 'Z2_PFG_OFFSHORE',
                zoneName: 'Zona 2 — Potensi Penangkapan Ikan Lepas Pantai (Offshore PFG)',
                description: 'Perairan lepas pantai 12-60 NM dengan kesesuaian habitat tuna >=0.55 untuk armada 10-30 GT.',
                priorityRank: 5,
                reasonCode: 'DIST_12_60NM_AND_TUNA_HSI_GE_0_55',
                metCriteria: $metCriteria
            );
        }

        return new DomZoneClassification(
            zoneKey: 'GENERAL_OCEANIC',
            zoneName: 'Zona Umum — Samudera Terbuka (General Oceanic)',
            description: 'Perairan samudera yang tidak memenuhi kriteria khusus Z1 s.d. Z5.',
            priorityRank: 6,
            reasonCode: 'DEFAULT_GENERAL_FALLBACK',
            metCriteria: ['GENERAL_OCEANIC']
        );
    }
}

