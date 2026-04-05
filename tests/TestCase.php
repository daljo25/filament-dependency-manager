<?php

namespace Daljo25\FilamentDependencyManager\Tests;

use Daljo25\FilamentDependencyManager\FilamentDependencyManagerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            FilamentDependencyManagerServiceProvider::class,
        ];
    }
}