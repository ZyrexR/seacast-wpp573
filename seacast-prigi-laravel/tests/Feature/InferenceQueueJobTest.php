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

    public function test_scheduler_runs_single_gee_pipeline_without_second_local_inference(): void
    {
        $events = collect(Schedule::events());

        $this->assertCount(1, $events->filter(
            fn ($event) => str_contains($event->command, 'seacast:sync-live')
        ));
        $this->assertCount(0, $events->filter(
            fn ($event) => str_contains($event->command, 'seacast:infer-daily')
        ));
    }
}

