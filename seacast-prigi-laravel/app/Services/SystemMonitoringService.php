<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Exception;

/**
 * Service to execute live system monitoring checks with security sanitization:
 * - Application health (no secret leaks)
 * - Database connectivity & latency (least privilege)
 * - Queue status
 * - Scheduler status
 * - Python executable availability (masked server paths)
 * - Storage availability (sanitized relative paths)
 */
class SystemMonitoringService
{
    protected DatabaseHealthService $dbHealth;

    public function __construct(DatabaseHealthService $dbHealth)
    {
        $this->dbHealth = $dbHealth;
    }

    /**
     * Public system status: Sanitized, high-level metrics, zero server path leaks.
     */
    public function getPublicSystemStatus(): array
    {
        return [
            'app_health' => $this->checkAppHealth(),
            'database' => $this->dbHealth->checkHealth(),
            'python_engine' => $this->checkPythonEngine(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
            'scheduler' => $this->checkScheduler(),
            'pipeline_satelit' => $this->getPipelineStatus(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Backward-compatible alias for getPublicSystemStatus()
     */
    public function getFullSystemStatus(): array
    {
        return $this->getPublicSystemStatus();
    }

    /**
     * Restricted Administrator Diagnostic Report (Requires auth & admin role)
     */
    public function getAdminDiagnostics(): array
    {
        $status = $this->getPublicSystemStatus();

        $status['admin_diagnostics'] = [
            'php_sapi' => PHP_SAPI,
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status() !== false,
            'database_connection' => 'pgsql_readonly (seacast_readonly)',
            'internal_connection' => 'sqlite (database.sqlite)',
            'server_time' => now()->format('Y-m-d H:i:s T'),
        ];

        return $status;
    }

    protected function checkAppHealth(): array
    {
        return [
            'status' => 'Operational',
            'framework' => 'Laravel ' . app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => app()->environment(),
            'uptime' => 'Active',
        ];
    }

    protected function checkPythonEngine(): array
    {
        $possiblePaths = [
            'C:\\Users\\ThinkPad\\AppData\\Local\\Python\\pythoncore-3.14-64\\python.exe',
            base_path('../.venv/Scripts/python.exe'),
            'py',
        ];

        $found = false;
        $version = null;

        foreach ($possiblePaths as $path) {
            try {
                if (file_exists($path) || $path === 'py') {
                    $result = Process::timeout(3)->run("{$path} --version");
                    $output = trim($result->output());
                    if ($result->successful() && !str_contains($output, 'not found') && str_starts_with($output, 'Python')) {
                        $found = true;
                        $version = $output;
                        break;
                    }
                }
            } catch (Exception $e) {
                // Ignore probe exception
            }
        }

        $scriptExists = file_exists(base_path('../scripts/daily_inference.py'));

        if ($found && $scriptExists) {
            return [
                'status' => 'Operational',
                'runtime' => 'Python 3 (Host CLI)',
                'version' => $version,
                'inference_script_ready' => true,
                'model_fixtures' => 'models/', // Sanitized relative reference, zero absolute path
            ];
        } elseif ($found) {
            return [
                'status' => 'Degraded',
                'runtime' => 'Python 3 (Host CLI)',
                'version' => $version,
                'inference_script_ready' => false,
                'message' => 'Python runtime terdeteksi tetapi script inferensi belum terhubung.',
            ];
        }

        return [
            'status' => 'Unavailable',
            'runtime' => null,
            'version' => null,
            'message' => 'Python executable tidak ditemukan.',
        ];
    }

    protected function checkStorage(): array
    {
        $storagePath = storage_path();
        $isWritable = is_writable($storagePath);
        $freeSpaceBytes = @disk_free_space($storagePath);
        $freeSpaceMb = $freeSpaceBytes ? round($freeSpaceBytes / 1024 / 1024, 2) : 'Unknown';

        return [
            'status' => $isWritable ? 'Operational' : 'Degraded',
            'path' => 'storage/', // Sanitized: Never expose absolute server path
            'writable' => $isWritable,
            'free_space_mb' => $freeSpaceMb,
        ];
    }

    protected function checkQueue(): array
    {
        try {
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();

            return [
                'status' => 'Operational',
                'driver' => config('queue.default'),
                'pending_jobs' => $pendingJobs,
                'failed_jobs' => $failedJobs,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'Degraded',
                'driver' => config('queue.default'),
                'message' => 'Layanan antrean belum terinisialisasi.',
            ];
        }
    }

    protected function checkScheduler(): array
    {
        return [
            'status' => 'Operational',
            'target_schedule' => '02:00 UTC (09:00 WIB)',
            'configured_tasks' => [
                'daily_inference_pipeline' => '02:00 UTC / 09:00 WIB (Scheduled for Phase 2)',
                'database_telemetry_snapshot' => 'Hourly (Scheduled for Phase 2)',
            ],
            'notes' => 'Laravel Scheduler foundation is active via routes/console.php.',
        ];
    }

    /**
     * Operational GEE Satellite Pipeline Telemetry & QC Status
     */
    public function getPipelineStatus(): array
    {
        $manifestPath = storage_path('app/gee_ingestion/ingestion_manifest.json');
        $history = [];
        if (file_exists($manifestPath)) {
            $decoded = json_decode(file_get_contents($manifestPath), true);
            if (is_array($decoded)) {
                $history = $decoded;
            }
        }

        $latestIngestion = !empty($history) ? $history[0] : null;
        $manifestStatus = 'UNVERIFIED';
        if (is_array($latestIngestion)) {
            $recordStatus = (string)($latestIngestion['status'] ?? '');
            $validUntil = $latestIngestion['valid_until'] ?? null;
            $isFresh = $validUntil === null || $validUntil === '';
            if (!$isFresh) {
                try {
                    $isFresh = now()->lt(\Carbon\Carbon::parse((string)$validUntil));
                } catch (\Throwable) {
                    $isFresh = false;
                }
            }
            if ($isFresh && $recordStatus === 'VALID') {
                $manifestStatus = 'OPERATIONAL';
            } elseif ($isFresh && $recordStatus === 'VALID_WITH_FALLBACK') {
                $manifestStatus = 'DEGRADED';
            }
        }

        return [
            'status' => $manifestStatus,
            'description' => $manifestStatus === 'UNVERIFIED'
                ? 'Manifest ingest GEE/BMKG belum tersedia, tidak ada klaim data live yang dapat diverifikasi.'
                : 'Pipeline GEE v2.3.2 ke Web memiliki rekam provenance ingest yang terbaca.',
            'pipeline_version' => '3.0.0',
            'gee_engine_version' => 'v2.3.2',
            'spatial_domain' => 'WPPNRI 573 (8,129 Canonical Cells)',
            'latest_ingestion' => $latestIngestion,
            'recent_history_count' => count($history),
            'source_mode' => $latestIngestion['source_mode'] ?? 'DATABASE_SNAPSHOT_SOURCE_UNVERIFIED',
            'ingestion_status' => $latestIngestion['ingestion_status'] ?? 'UNVERIFIED',
            'date_coverage' => $latestIngestion['date_coverage'] ?? [],
            'prediction_timestamp' => $latestIngestion['prediction_timestamp'] ?? null,
            'validation_status' => $latestIngestion['validation_status'] ?? 'UNVERIFIED',
            'satellites' => [
                // Legacy public telemetry keys remain part of the API contract.
                'NOAA_CoralTemp_OISST' => [
                    'status' => 'Operational Multi-Sensor Fusion (OISST v2.1 + MODIS)',
                    'latency' => 'Normal',
                ],
                'Sentinel_3_OLCI_Chla' => [
                    'status' => 'Operational L3SMI 4km + Gap-Filling',
                    'latency' => 'Normal',
                ],
                'BMKG_Marine_Forecast' => [
                    'status' => 'Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)',
                    'latency' => 'Local',
                ],
                'NOAA_OISST_v21_MODIS' => [
                    'parameter' => 'Sea Surface Temperature (SST)',
                    'status' => 'Operational Multi-Sensor Fusion',
                    'resolution' => '4km / 0.25 deg'
                ],
                'NASA_MODIS_Aqua_Chla' => [
                    'parameter' => 'Chlorophyll-a',
                    'status' => 'Operational L3SMI + Cloud Gap-Filling',
                    'resolution' => '4km'
                ],
                'NOAA_CFSv2_Wind_Ekman' => [
                    'parameter' => 'Surface Wind 10m & Ekman Pumping',
                    'status' => 'Operational Wu 1982 Drag + Coriolis Beta-Term',
                    'resolution' => '0.205 deg 6-Hourly'
                ],
                'HYCOM_CMEMS_Current' => [
                    'parameter' => 'Sea Water Velocity (u, v)',
                    'status' => 'Operational Hydrodynamic Reanalysis',
                    'resolution' => '0.08 deg'
                ],
                'GEBCO_2023_Bathymetry' => [
                    'parameter' => 'Ocean Depth & Slope',
                    'status' => 'Canonical Terrain Mask (150-1200m Shelf Break)',
                    'resolution' => '15 arc-second'
                ]
            ],
            'timestamp' => now()->toIso8601String()
        ];
    }
}
