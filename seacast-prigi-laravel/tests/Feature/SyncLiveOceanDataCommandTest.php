<?php

namespace Tests\Feature;

use Tests\TestCase;

class SyncLiveOceanDataCommandTest extends TestCase
{
    public function test_sync_live_command_executes_successfully_with_skip_gee_flag(): void
    {
        $this->artisan('seacast:sync-live', ['--skip-gee' => true])
            ->expectsOutputToContain('SEACAST PRIGI V3 — LIVE ZERO-DUMMY DATA ORCHESTRATOR')
            ->expectsOutputToContain('Fetching Live BMKG Maritim Marine Bulletins')
            ->expectsOutputToContain('PPN Prigi (Jatim)')
            ->expectsOutputToContain('PPS Cilacap (Jateng)')
            ->assertExitCode(0);
    }
}
