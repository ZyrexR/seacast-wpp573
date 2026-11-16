<?php

namespace Tests\Feature;

use Database\Seeders\PpsCilacapLandingsSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PpsCilacapLandingsSeederTest extends TestCase
{
    public function test_cilacap_seed_parses_all_monthly_trips_and_positive_cpue(): void
    {
        $connection = DB::connection();
        $connection->beginTransaction();

        try {
            (new PpsCilacapLandingsSeeder())->run();

            $rows = $connection->table('port_landings')
                ->where('port_code', 'PPS_CILACAP')
                ->orderBy('year_month')
                ->get();

            $this->assertCount(120, $rows);
            $this->assertSame('2016-01', $rows->first()->year_month);
            $this->assertSame('2025-12', $rows->last()->year_month);
            $this->assertSame(3389, (int)$rows->first()->trips_count);
            $this->assertGreaterThan(0, (float)$rows->first()->cpue_proxy);
            $this->assertGreaterThan(0, $rows->sum('trips_count'));

            foreach ($rows as $row) {
                $this->assertGreaterThan(0, (int)$row->trips_count, $row->year_month);
                if ((float)$row->total_pelagic_tons > 0.0) {
                    $this->assertGreaterThan(0, (float)$row->cpue_proxy, $row->year_month);
                    $this->assertEqualsWithDelta(
                        round((float)$row->total_pelagic_tons * 1000 / (int)$row->trips_count, 2),
                        (float)$row->cpue_proxy,
                        0.02,
                        $row->year_month
                    );
                }
            }
        } finally {
            $connection->rollBack();
        }
    }
}
