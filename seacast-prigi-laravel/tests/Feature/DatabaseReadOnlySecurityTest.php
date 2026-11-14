<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\CanonicalGrid;

class DatabaseReadOnlySecurityTest extends TestCase
{
    public function test_postgresql_connection_uses_least_privilege_user(): void
    {
        $currentUser = DB::connection('pgsql')->selectOne("SELECT current_user, session_user");

        // Must connect as seacast_readonly, NOT postgres superuser
        $this->assertEquals('seacast_readonly', $currentUser->current_user);
        $this->assertEquals('seacast_readonly', $currentUser->session_user);

        // Verify that user is NOT superuser and cannot create DB
        $roleInfo = DB::connection('pgsql')->selectOne("
            SELECT rolsuper, rolcreatedb, rolcreaterole 
            FROM pg_roles 
            WHERE rolname = current_user
        ");

        $this->assertFalse((bool)$roleInfo->rolsuper, 'Database user must not be a superuser');
        $this->assertFalse((bool)$roleInfo->rolcreatedb, 'Database user must not have CREATEDB privilege');
        $this->assertFalse((bool)$roleInfo->rolcreaterole, 'Database user must not have CREATEROLE privilege');
    }

    public function test_select_queries_succeed_on_scientific_tables(): void
    {
        $count = DB::connection('pgsql')->table('canonical_grids')->count();
        $this->assertEquals(8129, $count);

        $dailyCount = DB::connection('pgsql')->table('daily_predictions')->count();
        $this->assertEquals(16258, $dailyCount);

        $monthlyCount = DB::connection('pgsql')->table('grid_monthly_predictions')->count();
        $this->assertEquals(97548, $monthlyCount);

        $landingsCount = DB::connection('pgsql')->table('prigi_port_landings')->count();
        $this->assertEquals(132, $landingsCount);

        $fleetCount = DB::connection('pgsql')->table('prigi_fleet_by_gt')->count();
        $this->assertEquals(3, $fleetCount);
    }

    public function test_insert_is_strictly_rejected_by_postgresql(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Code 42501: Insufficient privilege
        DB::connection('pgsql')->statement("
            INSERT INTO canonical_grids (cell_id) 
            VALUES ('SECURITY_TEST_PROBE_CELL')
        ");
    }

    public function test_update_is_strictly_rejected_by_postgresql(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Code 42501: Insufficient privilege
        DB::connection('pgsql')->statement("
            UPDATE canonical_grids 
            SET cell_id = 'MUTATION_PROBE' 
            WHERE cell_id = 'CELL_0001'
        ");
    }

    public function test_delete_is_strictly_rejected_by_postgresql(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Code 42501: Insufficient privilege
        DB::connection('pgsql')->statement("
            DELETE FROM canonical_grids 
            WHERE cell_id = 'CELL_0001'
        ");
    }

    public function test_ddl_create_table_is_strictly_rejected_by_postgresql(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Code 42501: Insufficient privilege
        DB::connection('pgsql')->statement("
            CREATE TABLE security_probe_table (id int)
        ");
    }

    public function test_scientific_baseline_row_counts_remain_unmodified(): void
    {
        // Assert zero data drift after security probes
        $this->assertEquals(8129, CanonicalGrid::count());
    }
}

