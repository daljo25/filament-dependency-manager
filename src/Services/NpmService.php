<?php

namespace Daljo25\FilamentDependencyManager\Services;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class NpmService
{
    protected string $binary;
    protected string $phpBinary;
    protected string $client;

    public function __construct()
    {
        $finder = new ExecutableFinder();

        $this->client = config('dependency-manager.npm_client', 'npm');

        $this->binary = config('dependency-manager.npm_binary')
            ?? $finder->find($this->client)
            ?? $this->client;

        $this->phpBinary = config('dependency-manager.php_binary')
            ?? PHP_BINARY;
    }

    public function getOutdatedPackages(): array
    {
        return Cache::remember('filament-dependency-manager:npm-outdated', 3600, function () {
            $command = $this->buildCommand();

            $process = new Process(
                $command,
                base_path()
            );

            $process->setEnv([
                'PATH' => dirname($this->phpBinary) . ':/usr/local/bin:/usr/bin:/bin',
                'HOME' => getenv('HOME') ?: '/root',
            ]);

            $process->setTimeout(60);
            $process->run();

            // npm outdated exits with code 1 when there are outdated packages — not an error
            if ($process->getExitCode() > 1) {
                return [];
            }

            $output = json_decode($process->getOutput(), true);

            if (! is_array($output)) {
                return [];
            }

            return $this->normalize($output);
        });
    }

    public function clearCache(): void
    {
        Cache::forget('filament-dependency-manager:npm-outdated');
    }

    protected function buildCommand(): array
    {
        return match ($this->client) {
            'pnpm'  => [$this->binary, 'outdated', '--format', 'json'],
            'yarn'  => [$this->binary, 'outdated', '--json'],
            default => [$this->binary, 'outdated', '--json'],
        };
    }

    protected function normalize(array $output): array
    {
        return collect($output)
            ->map(function (array $package, string $name) {
                $current = $package['current'] ?? '—';
                $latest  = $package['latest'] ?? $package['wanted'] ?? '—';

                return [
                    'name'          => $name,
                    'version'       => $current,
                    'latest'        => $latest,
                    'latest-status' => $this->resolveStatus($current, $latest),
                    'type'          => $package['type'] ?? 'devDependencies',
                    'source'        => null,
                    'description'   => $package['location'] ?? null,
                ];
            })
            ->values()
            ->toArray();
    }

    protected function resolveStatus(string $current, string $latest): string
    {
        if ($current === $latest || $current === '—') {
            return 'up-to-date';
        }

        $currentParts = explode('.', ltrim($current, '^~v'));
        $latestParts  = explode('.', ltrim($latest, '^~v'));

        if (($currentParts[0] ?? 0) !== ($latestParts[0] ?? 0)) {
            return 'update-possible'; // major
        }

        return 'semver-safe-update'; // minor/patch
    }
}
