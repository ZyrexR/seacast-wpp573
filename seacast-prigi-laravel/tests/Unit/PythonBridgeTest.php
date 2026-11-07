<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Infrastructure\Python\Contracts\PythonComputationBridgeInterface;
use App\Infrastructure\Python\PythonComputationBridge;
use App\Infrastructure\Python\Exceptions\PythonSecurityException;
use App\Infrastructure\Python\Exceptions\PythonBridgeException;
use App\Infrastructure\Python\DTO\PythonComputationResult;

class PythonBridgeTest extends TestCase
{
    protected PythonComputationBridgeInterface $bridge;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bridge = new PythonComputationBridge();
    }

    public function test_bridge_detects_valid_python_binary(): void
    {
        $binary = $this->bridge->getPythonBinary();
        $this->assertFileExists($binary);
        $this->assertStringContainsString('python.exe', strtolower($binary));
    }

    public function test_blocks_arbitrary_unlisted_scripts(): void
    {
        $this->expectException(PythonSecurityException::class);
        $this->bridge->executeScript('../../malicious_script.py', []);
    }

    public function test_prevents_command_injection_via_arguments(): void
    {
        $this->expectException(PythonSecurityException::class);
        $this->bridge->executeScript('daily_inference', [
            '--date' => '2026-11-04; rm -rf /',
        ]);
    }

    public function test_can_execute_whitelisted_script_and_receive_valid_json(): void
    {
        $result = $this->bridge->runInference('2026-11-03', ['limit' => 20]);
        $this->assertInstanceOf(PythonComputationResult::class, $result);
        $this->assertEquals('SUCCESS', $result->status);
        $this->assertNotEmpty($result->modelVersion);
        $this->assertIsArray($result->species);
        $this->assertGreaterThan(0, $result->totalCells);
        $this->assertArrayHasKey('summary', $result->data);
    }
}
