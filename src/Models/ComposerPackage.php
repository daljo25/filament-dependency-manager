<?php

namespace Daljo25\FilamentDependencyManager\Models;

use Daljo25\FilamentDependencyManager\Services\ComposerService;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class ComposerPackage extends Model
{
    use Sushi;

    protected $schema = [
        'name'                 => 'string',
        'version'              => 'string',
        'latest'               => 'string',
        'latest-status'        => 'string',
        'latest-release-date'  => 'string',
        'description'          => 'string',
    ];

    public function getRows(): array
    {
        return app(ComposerService::class)->getOutdatedPackages();
    }
}