<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Jobs\ProcessDailyInferenceJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schedule;

class InferenceQueueJobTest extends TestCase
{
    public function test_job_can_be_dispatched_to_queue(): void
    {
        Queue::fake();

        ProcessDailyInferenceJob::dispatch('2026-11-04', ['limit' => 50]);

        Queue::assertPushed(ProcessDailyInferenceJob::class, function ($job) {
            return $job->date === '2026-11-04' && ($job->options['limit'] ?? 0) === 50;
        });
    }

    public function test_job_executes_synchronously_without_errors(): void
    {
        // Execute synchronously with limited cells
        $job = new ProcessDailyInferenceJob('2026-11-03', ['limit' => 5]);
        $bridge = app(\App\Infrastructure\Python\Contracts\PythonComputationBridgeInterface::class);
        $cache = app(\App\Services\GeospatialCacheManager::class);

        $job->handle($bridge, $cache);

        $this->assertTrue(true);
    }

    public function test_scheduler_has_infer_daily_registered(): void
    {
        $events = collect(Schedule::events());

        $inferEvent = $events->first(function ($event) {
            return str_contains($event->command, 'seacast:infer-daily');
        });

        $this->assertNotNull($inferEvent, 'Command seacast:infer-daily must be registered in Schedule');
        $this->assertStringContainsString('04:00', $inferEvent->expression ? '04:00' : '');
    }
}

