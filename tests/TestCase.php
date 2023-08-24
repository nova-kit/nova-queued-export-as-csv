<?php

namespace NovaKit\NovaQueuedExportAsCsv\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\Concerns\WithWorkbench;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase, WithWorkbench;

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
