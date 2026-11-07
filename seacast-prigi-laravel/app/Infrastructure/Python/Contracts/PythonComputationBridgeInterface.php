<?php

namespace App\Infrastructure\Python\Contracts;

use App\Infrastructure\Python\DTO\PythonComputationResult;

interface PythonComputationBridgeInterface
{
    public function getPythonBinary(): string;

    public function executeScript(string $scriptAlias, array $parameters = [], int $timeoutSeconds = 60): array;

    public function runInference(string $date, array $options = []): PythonComputationResult;

    public function runGeeTransformer(string $inputCsv, ?string $date = null, array $options = []): PythonComputationResult;
}

