<?php

namespace NovaKit\NovaQueuedExportAsCsv\Tests;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\Concerns\WithLaravelMigrations;
use Orchestra\Testbench\Concerns\WithWorkbench;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithLaravelMigrations, WithWorkbench;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            Http::fake([
                'nova.laravel.com/*' => Http::response([], 200),
            ]);
        });

        parent::setUp();
    }
}
