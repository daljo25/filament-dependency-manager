<?php

use Daljo25\FilamentDependencyManager\Services\ComposerService;
use Daljo25\FilamentDependencyManager\Services\NpmService;

it('composer service returns an array', function () {
    $service = app(ComposerService::class);
    expect($service->getOutdatedPackages())->toBeArray();
});

it('npm service returns an array', function () {
    $service = app(NpmService::class);
    expect($service->getOutdatedPackages())->toBeArray();
});
