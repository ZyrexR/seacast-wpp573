<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Infrastructure\Python\Contracts\PythonComputationBridgeInterface;
use App\Infrastructure\Python\Exceptions\PythonSecurityException;
use App\Infrastructure\Python\Exceptions\PythonBridgeException;
use App\Infrastructure\Python\Exceptions\PythonTimeoutException;

class PythonBridgeSecurityTest extends TestCase
{
    protected PythonComputationBridgeInterface $bridge;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bridge = app(PythonComputationBridgeInterface::class);
    }

    public function test_blocks_arbitrary_path_traversal(): void
    {
        $this->expectException(PythonSecurityException::class);
        $this->bridge->executeScript('../../../windows/system32/cmd.exe');
    }

    public function test_blocks_pipe_command_injection(): void
    {
        $this->expectException(PythonSecurityException::class);
        $this->bridge->executeScript('daily_inference', [
            '--date' => '2026-11-04 | dir',
        ]);
    }

    public function test_blocks_ampersand_command_injection(): void
    {
        $this->expectException(PythonSecurityException::class);
        $this->bridge->executeScript('daily_inference', [
            '--date' => '2026-11-04 & echo injected',
        ]);
    }

    public function test_blocks_backtick_command_injection(): void
    {
        $this->expectException(PythonSecurityException::class);
        $this->bridge->executeScript('daily_inference', [
            '--species' => 'cakalang`calc.exe`',
        ]);
    }

    public function test_provenance_and_locked_weights_are_strictly_present(): void
    {
        // Preview run with limit = 5 to be fast
        $result = $this->bridge->runInference('2026-11-03', ['limit' => 5]);
        $this->assertEquals('SUCCESS', $result->status);
        $this->assertArrayHasKey('applied_weights', $result->provenance);

        $applied = $result->provenance['applied_weights'];
        // Cakalang baseline weights
        $this->assertEquals(0.320, $applied['cakalang']['maxent']);
        $this->assertEquals(0.355, $applied['cakalang']['rf']);
        $this->assertEquals(0.325, $applied['cakalang']['glm']);

        // Tuna Sirip Kuning baseline weights (internal key: madidihang)
        $this->assertEquals(0.295, $applied['madidihang']['maxent']);
        $this->assertEquals(0.365, $applied['madidihang']['rf']);
        $this->assertEquals(0.340, $applied['madidihang']['glm']);
        $this->assertArrayNotHasKey('albakor', $applied);
    }
}

