<?php

namespace Tests;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Nova\Testing\Concerns\InteractsWithNova;
use Orchestra\Testbench\Concerns\WithWorkbench;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use InteractsWithNova;
    use LazilyRefreshDatabase;
    use WithWorkbench;
}
