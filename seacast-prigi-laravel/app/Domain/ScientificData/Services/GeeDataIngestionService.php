<?php

namespace App\Domain\ScientificData\Services;

use App\Infrastructure\Python\Contracts\PythonComputationBridgeInterface;
use App\Infrastructure\Python\DTO\PythonComputationResult;
use App\Services\GeospatialCacheManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class GeeDataIngestionService
{
    protected string $manifestPath;

    public function __construct(
        protected PythonComputationBridgeInterface $bridge,
        protected GeospatialCacheManager $cacheManager
    ) {
        $storageDir = storage_path('app/gee_ingestion');
        if (!File::isDirectory($storageDir)) {
            File::makeDirectory($storageDir, 0755, true, true);
        }
        $this->manifestPath = $storageDir . '/ingestion_manifest.json';
    }

    /**
     * Validate the GEE CSV file before attempting inference or database persistence.
     */
    public function validateGeeCsv(string $filePath): array
    {
        $realPath = realpath($filePath);
        if (!$realPath || !File::exists($realPath)) {
            return [
                'is_valid' => false,
                'errors' => ["Target file not found at: {$filePath}"],
                'warnings' => [],
                'metadata' => []
            ];
        }

        if (strtolower(pathinfo($realPath, PATHINFO_EXTENSION)) !== 'csv') {
            return [
                'is_valid' => false,
                'errors' => ["File must be a CSV format (.csv). Given: " . pathinfo($realPath, PATHINFO_EXTENSION)],
                'warnings' => [],
                'metadata' => []
            ];
        }

        $fileSize = File::size($realPath);
        if ($fileSize < 1024) { // Less than 1 KB is certainly empty or corrupted
            return [
                'is_valid' => false,
                'errors' => ["File size ({$fileSize} bytes) is suspiciously small for an 8,129-cell GEE grid."],
                'warnings' => [],
                'metadata' => []
            ];
        }

        // Read header row
        $handle = fopen($realPath, 'r');
        if (!$handle) {
            return [
                'is_valid' => false,
                'errors' => ["Unable to open file for reading: {$realPath}"],
                'warnings' => [],
                'metadata' => []
            ];
        }

        $header = fgetcsv($handle);
        if (!$header || count($header) < 5) {
            fclose($handle);
            return [
                'is_valid' => false,
                'errors' => ["Invalid or empty CSV header in {$realPath}"],
                'warnings' => [],
                'metadata' => []
            ];
        }

        $cols = array_map('trim', $header);
        $colSet = array_flip($cols);

        $errors = [];
        $warnings = [];

        // Check required cell_id
        if (!isset($colSet['cell_id'])) {
            $errors[] = "Required column 'cell_id' missing from CSV header.";
        }

        // Determine schema variant
        $schemaVariant = 'UNKNOWN';
        if (count($cols) >= 100 && isset($colSet['sst_mean_c_mean'], $colSet['chla_mg_m3_mean'])) {
            $schemaVariant = '104_COL_GEE_MONTHLY_PRODUCTION';
        } elseif (count($cols) >= 45 && isset($colSet['sst_c'], $colSet['chla_mg_m3'])) {
            $schemaVariant = '51_COL_PILOT_2024';
        } elseif (isset($colSet['sst'], $colSet['chla'])) {
            $schemaVariant = 'STANDARDIZED_PREDICTION_CSV';
        } else {
            $warnings[] = "Unrecognized column schema variant with " . count($cols) . " columns.";
        }

        // Count data lines
        $rowCount = 0;
        $sampleNulls = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $rowCount++;
            // Check for empty cells in first 10 rows
            if ($rowCount <= 10) {
                foreach ($row as $val) {
                    if ($val === '' || strtolower($val) === 'null' || strtolower($val) === 'nan') {
                        $sampleNulls++;
                    }
                }
            }
        }
        fclose($handle);

        if ($rowCount !== 8129) {
            $errors[] = "Canonical grid cell count mismatch: Expected exactly 8,129 cells, found {$rowCount}.";
        }

        if ($sampleNulls > 0) {
            $warnings[] = "Missing satellite pixels detected ({$sampleNulls} nulls in initial sample). Pipeline will apply domain median fallback imputation.";
        }

        $isValid = count($errors) === 0;

        return [
            'is_valid' => $isValid,
            'file_name' => basename($realPath),
            'file_path' => $realPath,
            'file_size_bytes' => $fileSize,
            'row_count' => $rowCount,
            'column_count' => count($cols),
            'schema_variant' => $schemaVariant,
            'errors' => $errors,
            'warnings' => $warnings,
            'metadata' => [
                'sha256' => hash_file('sha256', $realPath),
                'verified_at' => Carbon::now('Asia/Jakarta')->toIso8601String(),
            ]
        ];
    }

    /**
     * Ingest and process a GEE export CSV dataset.
     */
    public function ingest(string $filePath, ?string $date = null, array $options = []): array
    {
        $startTime = microtime(true);

        // 1. Preflight Validation Gate
        $validation = $this->validateGeeCsv($filePath);
        if (!$validation['is_valid']) {
            $errSummary = implode('; ', $validation['errors']);
            Log::error("GEE Ingestion rejected at preflight gate: {$errSummary}", [
                'file' => $filePath,
                'validation' => $validation
            ]);
            throw new InvalidArgumentException("GEE data preflight validation failed: {$errSummary}");
        }

        $realPath = $validation['file_path'];

        // 2. Resolve Target Date
        if ($date === null) {
            if (preg_match('/(\d{4})_(\d{2})/', basename($realPath), $matches)) {
                $date = "{$matches[1]}-{$matches[2]}-01";
            } elseif (preg_match('/(\d{4})-(\d{2})-(\d{2})/', basename($realPath), $matches)) {
                $date = "{$matches[1]}-{$matches[2]}-{$matches[3]}";
            } else {
                $date = Carbon::today()->toDateString();
            }
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new InvalidArgumentException("Invalid date format: {$date}. Expected YYYY-MM-DD.");
        }

        $dryRun = (bool)($options['dry_run'] ?? false);
        $persistDb = (bool)($options['persist_db'] ?? true);
        $limit = (int)($options['limit'] ?? 0);

        Log::info("Starting GEE dataset ingestion", [
            'file' => basename($realPath),
            'target_date' => $date,
            'schema' => $validation['schema_variant'],
            'dry_run' => $dryRun,
            'persist_db' => $persistDb,
        ]);

        // 3. Execute Model Inference via Python Bridge
        $bridgeOptions = [
            'limit' => $limit,
            'dry_run' => $dryRun,
            'persist_db' => $persistDb && !$dryRun,
            'timeout' => 240
        ];

        /** @var PythonComputationResult $result */
        $result = $this->bridge->runGeeTransformer($realPath, $date, $bridgeOptions);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        // 4. Cache Invalidation (if not dry-run)
        $invalidatedKeys = 0;
        if (!$dryRun) {
            $invalidatedKeys = $this->cacheManager->invalidateDate($date);
        }

        // 5. Build Comprehensive Ingestion Audit Report
        $report = [
            'status' => 'SUCCESS',
            'action' => $dryRun ? 'DRY_RUN_VERIFICATION' : 'INGESTION_AND_PERSISTENCE',
            'dataset_name' => basename($realPath),
            'target_date' => $date,
            'total_cells' => $result->totalCells,
            'hotspots_count' => $result->hotspotsCount,
            'schema_variant' => $validation['schema_variant'],
            'sha256' => $validation['metadata']['sha256'],
            'bridge_duration_ms' => $result->executionDurationMs,
            'total_duration_ms' => $durationMs,
            'cache_invalidated_keys' => $invalidatedKeys,
            'summary' => $result->data['summary'] ?? [],
            'provenance' => $result->provenance,
            'warnings' => array_merge($validation['warnings'], $result->warnings),
            'completed_at' => Carbon::now('Asia/Jakarta')->toIso8601String()
        ];

        // 6. Record Audit Trail
        if (!$dryRun) {
            $this->recordIngestionAudit($report);
        }

        Log::info("GEE dataset ingestion finished successfully", [
            'dataset' => basename($realPath),
            'date' => $date,
            'total_cells' => $result->totalCells,
            'duration_ms' => $durationMs
        ]);

        return $report;
    }

    /**
     * Record ingestion audit trail to local manifest store.
     */
    protected function recordIngestionAudit(array $report): void
    {
        $manifest = [];
        if (File::exists($this->manifestPath)) {
            $existing = json_decode(File::get($this->manifestPath), true);
            if (is_array($existing)) {
                $manifest = $existing;
            }
        }

        $provenance = is_array($report['provenance'] ?? null) ? $report['provenance'] : [];
        $warnings = array_values(array_filter(
            array_map(static fn (mixed $warning): string => (string)$warning, $report['warnings'] ?? []),
            static fn (string $warning): bool => $warning !== ''
        ));
        $sourceMode = (string)($provenance['source_mode'] ?? 'UNKNOWN');
        $status = ($report['status'] ?? null) !== 'SUCCESS'
            ? 'FAILED'
            : ($warnings !== [] || str_contains(strtoupper($sourceMode), 'FALLBACK')
                ? 'VALID_WITH_FALLBACK'
                : 'VALID');
        $completedAt = Carbon::parse((string)($report['completed_at'] ?? Carbon::now('UTC')->toIso8601String()));

        $record = [
            'manifest_version' => '1.0',
            'dataset' => $report['dataset_name'],
            'source' => $provenance['sources'] ?? $provenance['satellite_sources'] ?? $provenance['source'] ?? $sourceMode,
            'source_mode' => $sourceMode,
            'evidence_level' => (string)($provenance['evidence_level'] ?? 'UNVERIFIED'),
            'model_version' => (string)($provenance['model_version'] ?? 'unknown'),
            'prediction_timestamp' => (string)($provenance['prediction_timestamp'] ?? $report['completed_at']),
            'validation_status' => (string)($provenance['validation_status'] ?? 'UNVERIFIED'),
            'status' => $status,
            'ingestion_status' => $status,
            'fallback_used' => $status === 'VALID_WITH_FALLBACK',
            'target_date' => $report['target_date'],
            'date_coverage' => [
                'start' => $report['target_date'],
                'end' => $report['target_date'],
            ],
            'total_cells' => $report['total_cells'],
            'hotspots_count' => $report['hotspots_count'],
            'sha256' => $report['sha256'],
            'duration_ms' => $report['total_duration_ms'],
            'completed_at' => $completedAt->toIso8601String(),
            'valid_until' => $completedAt->copy()->addHours(48)->toIso8601String(),
            'summary' => $report['summary'],
            'warnings' => $warnings,
        ];

        // Keep one authoritative record per dataset/date and retain the last 100 records.
        $manifest = array_values(array_filter($manifest, static fn (mixed $existing): bool => !(
            is_array($existing)
            && ($existing['dataset'] ?? null) === $record['dataset']
            && ($existing['target_date'] ?? null) === $record['target_date']
        )));
        array_unshift($manifest, $record);

        if (count($manifest) > 100) {
            $manifest = array_slice($manifest, 0, 100);
        }

        File::put($this->manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Retrieve the recent GEE ingestion audit history.
     */
    public function getIngestionHistory(int $limit = 10): array
    {
        if (!File::exists($this->manifestPath)) {
            return [];
        }

        $decoded = json_decode(File::get($this->manifestPath), true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_slice($decoded, 0, $limit);
    }
}

