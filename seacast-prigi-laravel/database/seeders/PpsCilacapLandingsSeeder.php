<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PortLanding;
use Illuminate\Support\Facades\DB;

class PpsCilacapLandingsSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = base_path('database/data/raw/pps_cilacap_statistics_2016_2025.csv');
        if (!file_exists($csvPath)) {
            return;
        }

        $lines = file($csvPath, FILE_IGNORE_NEW_LINES);

        // 1. Parse Yearly Fleet Profile (Lines 8-17)
        // Format: ,No,Tahun,Kapal Motor < 10 GT,Kapal Motor 10 - < 20 GT,Kapal Motor 20 - < 30 GT,Kapal Motor > 30 GT,Total
        $fleetByYear = [];
        for ($i = 7; $i <= 16; $i++) {
            if (!isset($lines[$i])) continue;
            $row = str_getcsv($lines[$i]);
            if (count($row) >= 8 && is_numeric($row[2])) {
                $year = (int)$row[2];
                $fleetByYear[$year] = [
                    'fleet_lt_10gt' => (int)$row[3],
                    'fleet_10_20gt' => (int)$row[4],
                    'fleet_20_30gt' => (int)$row[5],
                    'fleet_gt_30gt' => (int)$row[6],
                    'total_fleet'   => (int)$row[7],
                ];
            }
        }

        $monthMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];

        // 2. Parse Monthly Trips
        $tripsByYearMonth = [];
        $currentYear = null;
        $inTripsSection = false;

        foreach ($lines as $lineIndex => $line) {
            $row = str_getcsv($line);
            if (empty($row)) continue;

            // Stop trips section when reaching landings section
            if ($inTripsSection
                && isset($row[0], $row[3])
                && preg_match('/^20\d\d$/', trim($row[0]))
                && str_contains($row[3], 'Yellowfin Tuna')) {
                $inTripsSection = false;
                break;
            }

            // Check for header like "2016,No,Bulan,KM < 10 GT,..."
            if (isset($row[0]) && preg_match('/^20\d\d$/', trim($row[0])) && isset($row[2]) && trim($row[2]) === 'Bulan') {
                $currentYear = (int)trim($row[0]);
                $inTripsSection = true;
                continue;
            }

            if ($inTripsSection && $currentYear && isset($row[2])) {
                $monthName = strtolower(trim($row[2]));
                if (isset($monthMap[$monthName])) {
                    $monthNum = $monthMap[$monthName];
                    $ym = sprintf('%04d-%02d', $currentYear, $monthNum);
                    // The "Jumlah" column is the last populated column
                    $lastVal = 0;
                    for ($c = count($row) - 1; $c >= 3; $c--) {
                        if (is_numeric(trim($row[$c]))) {
                            $lastVal = (int)trim($row[$c]);
                            break;
                        }
                    }
                    $tripsByYearMonth[$ym] = $lastVal;
                }
            }
        }

        // 3. Parse Monthly Landings (Lines 196 to 352)
        $currentLandingsYear = null;
        $inLandingsSection = false;

        foreach ($lines as $line) {
            $row = str_getcsv($line);
            if (empty($row)) continue;

            if (isset($row[0]) && preg_match('/^20\d\d$/', trim($row[0])) && isset($row[3]) && str_contains($row[3], 'Yellowfin')) {
                $currentLandingsYear = (int)trim($row[0]);
                $inLandingsSection = true;
                continue;
            }

            if ($inLandingsSection && $currentLandingsYear && isset($row[2])) {
                $monthName = strtolower(trim($row[2]));
                if (isset($monthMap[$monthName])) {
                    $monthNum = $monthMap[$monthName];
                    $ym = sprintf('%04d-%02d', $currentLandingsYear, $monthNum);

                    $madidihang = isset($row[3]) ? (float)str_replace(',', '', trim($row[3])) : 0.0;
                    $cakalang   = isset($row[4]) ? (float)str_replace(',', '', trim($row[4])) : 0.0;
                    $tongkol    = isset($row[5]) ? (float)str_replace(',', '', trim($row[5])) : 0.0;
                    $totalTons  = $madidihang + $cakalang + $tongkol;

                    $trips = $tripsByYearMonth[$ym] ?? 0;
                    $cpue = $trips > 0 ? round(($totalTons * 1000.0) / $trips, 2) : 0.0;

                    $fleet = $fleetByYear[$currentLandingsYear] ?? [
                        'fleet_lt_10gt' => 25,
                        'fleet_10_20gt' => 72,
                        'fleet_20_30gt' => 601,
                        'fleet_gt_30gt' => 116,
                        'total_fleet'   => 814,
                    ];

                    PortLanding::updateOrCreate(
                        [
                            'port_code' => 'PPS_CILACAP',
                            'year_month' => $ym,
                        ],
                        [
                            'port_name' => 'PPS Cilacap',
                            'province' => 'Jawa Tengah',
                            'year' => $currentLandingsYear,
                            'month' => $monthNum,
                            'cakalang_tons' => $cakalang,
                            'madidihang_tons' => $madidihang,
                            'tongkol_tons' => $tongkol,
                            'total_pelagic_tons' => $totalTons,
                            'trips_count' => $trips,
                            'cpue_proxy' => $cpue,
                            'fleet_lt_10gt' => $fleet['fleet_lt_10gt'],
                            'fleet_10_20gt' => $fleet['fleet_10_20gt'],
                            'fleet_20_30gt' => $fleet['fleet_20_30gt'],
                            'fleet_gt_30gt' => $fleet['fleet_gt_30gt'],
                            'total_fleet' => $fleet['total_fleet'],
                        ]
                    );
                }
            }
        }

        // 4. Ingest PPN Prigi into PortLanding if table prigi_port_landings exists
        try {
            $prigiRows = DB::connection('pgsql')->table('prigi_port_landings')->get();
            foreach ($prigiRows as $r) {
                PortLanding::updateOrCreate(
                    [
                        'port_code' => 'PPN_PRIGI',
                        'year_month' => $r->year_month,
                    ],
                    [
                        'port_name' => 'PPN Prigi',
                        'province' => 'Jawa Timur',
                        'year' => (int)$r->year,
                        'month' => (int)$r->month,
                        'cakalang_tons' => (float)$r->cakalang_tons,
                        'madidihang_tons' => (float)$r->madidihang_tons,
                        'tongkol_tons' => (float)$r->tongkol_tons,
                        'total_pelagic_tons' => (float)$r->total_pelagic_tons,
                        'trips_count' => (int)$r->trips_count,
                        'cpue_proxy' => (float)$r->cpue_proxy,
                        'fleet_lt_10gt' => 380,
                        'fleet_10_20gt' => 95,
                        'fleet_20_30gt' => 42,
                        'fleet_gt_30gt' => 0,
                        'total_fleet' => 517,
                    ]
                );
            }
        } catch (\Throwable $e) {
            // Prigi PG table optional in SQLite test harness
        }
    }
}

