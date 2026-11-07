<?php

namespace App\Infrastructure\Python;

use App\Infrastructure\Python\Contracts\PythonComputationBridgeInterface;
use App\Infrastructure\Python\DTO\PythonComputationResult;
use App\Infrastructure\Python\Exceptions\PythonBridgeException;
use App\Infrastructure\Python\Exceptions\PythonSecurityException;
use App\Infrastructure\Python\Exceptions\PythonTimeoutException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Illuminate\Support\Facades\Log;

class PythonComputationBridge implements PythonComputationBridgeInterface
{
    protected string $baseDir;

    protected array $whitelistedScripts = [
        'daily_inference' => 'scripts/bridge_inference_runner.py',
        'raw_daily_inference' => 'scripts/daily_inference.py',
        'audit_monthly' => 'scripts/audit_year_monthly_data.py',
        'gee_transformer' => 'scripts/gee_pipeline_transformer.py',
    ];

    public function __construct(?string $baseDir = null)
    {
        $this->baseDir = $baseDir ?? dirname(base_path());
    }

    public function getPythonBinary(): string
    {
        $knownPaths = [
            'C:\\Users\\ThinkPad\\AppData\\Local\\Python\\pythoncore-3.14-64\\python.exe',
            'C:\\Python314\\python.exe',
            'C:\\Python312\\python.exe',
        ];

        foreach ($knownPaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        // Fallback: check where python is in system path without using WindowsApps stub
        $which = shell_exec('where.exe python 2>NUL');
        if ($which) {
            $lines = array_filter(array_map('trim', explode("\n", $which)));
            foreach ($lines as $line) {
                if (stripos($line, 'WindowsApps') === false && file_exists($line)) {
                    return $line;
                }
            }
        }

        throw new PythonBridgeException('Python executable not found in host environment.');
    }

    public function executeScript(string $scriptAlias, array $parameters = [], int $timeoutSeconds = 60): array
    {
        // 1. Script Whitelist Validation
        if (!array_key_exists($scriptAlias, $this->whitelistedScripts)) {
            throw new PythonSecurityException("Script execution denied: '{$scriptAlias}' is not in the approved whitelist.");
        }

        $scriptRelPath = $this->whitelistedScripts[$scriptAlias];
        $scriptFullPath = $this->baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $scriptRelPath);

        if (!file_exists($scriptFullPath)) {
            throw new PythonBridgeException("Target script file not found at: {$scriptFullPath}");
        }

        // 2. Parameter Sanitization & Injection Prevention
        $sanitizedArgs = [];
        $dangerousChars = [';', '|', '&', "\n", "\r", '$', '`', '<', '>'];

        foreach ($parameters as $flag => $value) {
            foreach ($dangerousChars as $char) {
                if (str_contains($flag, $char) || (is_string($value) && str_contains($value, $char))) {
                    throw new PythonSecurityException("Security violation: Dangerous character '{$char}' detected in argument.");
                }
            }

            if (is_numeric($flag)) {
                $sanitizedArgs[] = (string)$value;
            } else {
                $sanitizedArgs[] = $flag;
                if ($value !== null && $value !== '') {
                    $sanitizedArgs[] = (string)$value;
                }
            }
        }

        // 3. Process Invocation with Array Arguments (No shell interpolation)
        $pythonBinary = $this->getPythonBinary();
        $command = array_merge([$pythonBinary, $scriptFullPath], $sanitizedArgs);

        $startTime = microtime(true);
        $process = new Process($command);
        $process->setTimeout($timeoutSeconds);

        try {
            $process->run();
        } catch (ProcessTimedOutException $e) {
            throw new PythonTimeoutException("Python computation timed out after {$timeoutSeconds}s: " . $e->getMessage());
        }

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        $stdout = trim($process->getOutput());
        $stderr = trim($process->getErrorOutput());

        if (!$process->isSuccessful()) {
            Log::error("Python computation bridge failed", [
                'script' => $scriptAlias,
                'exit_code' => $process->getExitCode(),
                'stderr' => $stderr,
                'duration_ms' => $durationMs,
            ]);

            throw new PythonBridgeException(
                "Python script '{$scriptAlias}' failed with exit code {$process->getExitCode()}: " . ($stderr ?: $stdout)
            );
        }

        // 4. Output JSON Schema Validation
        $decoded = json_decode($stdout, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw new PythonBridgeException("Python script '{$scriptAlias}' returned invalid JSON output: " . substr($stdout, 0, 500));
        }

        if (isset($decoded['status']) && $decoded['status'] === 'ERROR') {
            throw new PythonBridgeException("Scientific computation engine error: " . ($decoded['message'] ?? 'Unknown error'));
        }

        $decoded['_execution_duration_ms'] = $durationMs;
        return $decoded;
    }

    public function runInference(string $date, array $options = []): PythonComputationResult
    {
        $params = ['--date' => $date];

        if (!empty($options['species'])) {
            $params['--species'] = $options['species'];
        }

        if (!empty($options['limit'])) {
            $params['--limit'] = (int)$options['limit'];
        }

        $payload = $this->executeScript('daily_inference', $params, $options['timeout'] ?? 120);
        $durationMs = (float)($payload['_execution_duration_ms'] ?? 0.0);

        return PythonComputationResult::fromArray($payload, $durationMs);
    }

    public function runGeeTransformer(string $inputCsv, ?string $date = null, array $options = []): PythonComputationResult
    {
        $params = ['--input-csv' => $inputCsv];

        if ($date !== null) {
            $params['--date'] = $date;
        }

        if (!empty($options['limit'])) {
            $params['--limit'] = (int)$options['limit'];
        }

        if (!empty($options['dry_run'])) {
            $params['--dry-run'] = null;
        }

        if (!empty($options['persist_db'])) {
            $params['--persist-db'] = null;
        }

        if (!empty($options['output_json'])) {
            $params['--output-json'] = $options['output_json'];
        }

        if (!empty($options['output_csv'])) {
            $params['--output-csv'] = $options['output_csv'];
        }

        $payload = $this->executeScript('gee_transformer', $params, $options['timeout'] ?? 180);
        $durationMs = (float)($payload['_execution_duration_ms'] ?? 0.0);

        return PythonComputationResult::fromArray($payload, $durationMs);
    }
}

