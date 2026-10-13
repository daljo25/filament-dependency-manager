<?php

use Daljo25\FilamentDependencyManager\Services\NpmService;

function npmServiceForClient(string $client, string $binary = '/usr/bin/fake'): NpmService
{
    $service = app(NpmService::class);

    $clientProp = new ReflectionProperty(NpmService::class, 'client');
    $clientProp->setAccessible(true);
    $clientProp->setValue($service, $client);

    $binaryProp = new ReflectionProperty(NpmService::class, 'binary');
    $binaryProp->setAccessible(true);
    $binaryProp->setValue($service, $binary);

    return $service;
}

function callProtected(NpmService $service, string $method, array $args = []): mixed
{
    $reflection = new ReflectionMethod(NpmService::class, $method);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs($service, $args);
}

it('builds the correct outdated command per client', function (string $client, array $expected) {
    $service = npmServiceForClient($client, '/fake/bin/' . $client);

    expect(callProtected($service, 'buildCommand'))->toBe($expected);
})->with([
    'npm' => ['npm', ['/fake/bin/npm', 'outdated', '--json']],
    'pnpm' => ['pnpm', ['/fake/bin/pnpm', 'outdated', '--format', 'json']],
    'yarn' => ['yarn', ['/fake/bin/yarn', 'outdated', '--json']],
    // bun has no native --json output, so it runs npm through bunx
    'bun' => ['bun', ['/fake/bin/bun', 'x', 'npm', 'outdated', '--json']],
]);

it('decodes pure npm outdated json', function () {
    $service = npmServiceForClient('npm');
    $json = '{"lodash":{"current":"4.17.20","wanted":"4.17.20","latest":"4.18.1"}}';

    expect(callProtected($service, 'decodeOutput', [$json]))->toBe([
        'lodash' => ['current' => '4.17.20', 'wanted' => '4.17.20', 'latest' => '4.18.1'],
    ]);
});

it('decodes json with noise on stdout', function () {
    $service = npmServiceForClient('bun');
    $noisy = "bunx: installing npm...\n" . '{"lodash":{"current":"4.17.20","latest":"4.18.1"}}' . "\n";

    expect(callProtected($service, 'decodeOutput', [$noisy]))->toBe([
        'lodash' => ['current' => '4.17.20', 'latest' => '4.18.1'],
    ]);
});

it('decodes empty output as empty array and garbage as null', function () {
    $service = npmServiceForClient('npm');

    expect(callProtected($service, 'decodeOutput', ['']))->toBe([]);
    expect(callProtected($service, 'decodeOutput', ['   ']))->toBe([]);
    expect(callProtected($service, 'decodeOutput', ['not json at all']))->toBeNull();
});

it('parses the native bun outdated table', function () {
    $service = npmServiceForClient('bun');
    $table = <<<'TABLE'
        bun outdated v1.4.2 (744846f84)
        |---------------------------------------|
        | Package  | Current | Update  | Latest |
        |----------|---------|---------|--------|
        | lodash   | 4.17.20 | 4.17.20 | 4.18.1 |
        | eslint (dev) | 8.57.1 | 8.57.1 | 9.20.0 |
        |---------------------------------------|
        TABLE;

    expect(callProtected($service, 'decodeBunTable', [$table]))->toBe([
        'lodash' => ['current' => '4.17.20', 'latest' => '4.18.1', 'type' => 'dependencies'],
        'eslint' => ['current' => '8.57.1', 'latest' => '9.20.0', 'type' => 'devDependencies'],
    ]);
});

it('returns empty array for empty bun table and null for garbage', function () {
    $service = npmServiceForClient('bun');

    expect(callProtected($service, 'decodeBunTable', ["bun outdated v1.4.2\n"]))->toBeNull();
    expect(callProtected($service, 'decodeBunTable', ['garbage without header']))->toBeNull();
});

it('normalizes npm outdated output', function () {
    $service = npmServiceForClient('npm');

    $normalized = callProtected($service, 'normalize', [[
        'lodash' => ['current' => '4.17.20', 'wanted' => '4.17.20', 'latest' => '4.18.1', 'type' => 'dependencies'],
        'eslint' => ['current' => '8.57.1', 'wanted' => '8.57.1', 'latest' => '9.20.0', 'type' => 'devDependencies'],
    ]]);

    expect($normalized)->toHaveCount(2)
        ->and($normalized[0]['name'])->toBe('lodash')
        ->and($normalized[0]['version'])->toBe('4.17.20')
        ->and($normalized[0]['latest'])->toBe('4.18.1')
        ->and($normalized[0]['latest-status'])->toBe('semver-safe-update')
        ->and($normalized[0]['type'])->toBe('dependencies')
        ->and($normalized[1]['latest-status'])->toBe('update-possible');
});
