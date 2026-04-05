<?php

namespace Daljo25\FilamentDependencyManager\Services;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class ComposerService
{
    protected string $composerBinary;

    protected string $phpBinary;

    public function __construct()
    {
        $finder = new ExecutableFinder;

        $this->composerBinary = config('dependency-manager.composer_binary')
            ?? $finder->find('composer')
            ?? 'composer';

        $this->phpBinary = config('dependency-manager.php_binary')
            ?? PHP_BINARY;
    }

    public function getOutdatedPackages(): array
    {
        return Cache::remember('filament-dependency-manager:composer-outdated', 3600, function () {
            $process = new Process(
                [$this->composerBinary, 'outdated', '--direct', '--format=json'],
                base_path()
            );

            $process->setEnv([
                'PATH' => dirname($this->phpBinary) . ':/usr/local/bin:/usr/bin:/bin',
                'HOME' => getenv('HOME') ?: '/root',
                'COMPOSER_HOME' => getenv('HOME') . '/.composer',
            ]);

            $process->setTimeout(60);
            $process->run();

            if (! $process->isSuccessful()) {
                return [];
            }

            $output = json_decode($process->getOutput(), true);

            return $output['installed'] ?? [];
        });
    }

    public function clearCache(): void
    {
        Cache::forget('filament-dependency-manager:composer-outdated');
    }

    public function getRepositoryUrl(array $record): ?string
    {
        $source = $record['source'] ?? null;

        if (! $source) {
            return null;
        }

        $source = rtrim($source, '/');

        if (str_contains($source, '/tree/')) {
            return preg_replace('#/tree/[^/]+$#', '', $source);
        }

        return $source;
    }

    public function getReleaseUrl(array $record): ?string
    {
        $repositoryUrl = $this->getRepositoryUrl($record);
        $latest = $record['latest'] ?? null;

        if (! $repositoryUrl || ! $latest) {
            return null;
        }

        if (! str_contains($repositoryUrl, 'github.com')) {
            return $repositoryUrl;
        }

        return "{$repositoryUrl}/releases/tag/{$latest}";
    }
}
