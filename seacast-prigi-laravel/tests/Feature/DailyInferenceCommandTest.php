<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class DailyInferenceCommandTest extends TestCase
{
    public function test_command_rejects_invalid_date_format(): void
    {
        $exitCode = Artisan::call('seacast:infer-daily', [
            '--date' => 'invalid-date',
        ]);

        $this->assertEquals(1, $exitCode);
        $output = Artisan::output();
        $this->assertStringContainsString('Invalid date format', $output);
    }

    public function test_command_executes_successfully_with_valid_date(): void
    {
        $exitCode = Artisan::call('seacast:infer-daily', [
            '--date' => '2026-11-03',
            '--limit' => 5,
        ]);

        $this->assertEquals(0, $exitCode);
        $output = Artisan::output();
        $this->assertStringContainsString('INFERENCE PIPELINE EXECUTED SUCCESSFULLY', $output);
        $this->assertStringContainsString('Total Cells Processed', $output);
        $this->assertStringContainsString('Geospatial cache flushed', $output);
    }
}

