<?php

namespace Daljo25\FilamentDependencyManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Daljo25\FilamentDependencyManager\FilamentDependencyManager
 */
class FilamentDependencyManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Daljo25\FilamentDependencyManager\FilamentDependencyManager::class;
    }
}
