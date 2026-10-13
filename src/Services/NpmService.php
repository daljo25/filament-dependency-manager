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
        $finder = new ExecutableFinder;

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

            $output = $this->decodeOutput($process->getOutput());

            if (! is_array($output)) {
                // Fallback for bun: `bun outdated` has no --json support,
                // so parse its native table if the bunx npm call failed
                // (e.g. no registry access to download npm).
                if ($this->client === 'bun') {
                    return $this->normalize($this->bunTableOutdated());
                }

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
            // `bun outdated` has no --json support (oven-sh/bun#25571),
            // so run npm's JSON output through bunx instead.
            'bun' => [$this->binary, 'x', 'npm', 'outdated', '--json'],
            'pnpm' => [$this->binary, 'outdated', '--format', 'json'],
            'yarn' => [$this->binary, 'outdated', '--json'],
            default => [$this->binary, 'outdated', '--json'],
        };
    }

    /**
     * Decode command output, tolerating non-JSON noise on stdout
     * (progress lines, version banners, ...).
     */
    protected function decodeOutput(string $output): ?array
    {
        $output = trim($output);

        if ($output === '') {
            return [];
        }

        $decoded = json_decode($output, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($output, '{');
        $arrayStart = strpos($output, '[');

        if ($arrayStart !== false && ($start === false || $arrayStart < $start)) {
            $start = $arrayStart;
            $end = strrpos($output, ']');
        } else {
            $end = strrpos($output, '}');
        }

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $decoded = json_decode(substr($output, $start, $end - $start + 1), true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Run native `bun outdated` and parse its table output.
     * Used as a fallback when `bunx npm outdated --json` fails.
     */
    protected function bunTableOutdated(): array
    {
        $process = new Process(
            [$this->binary, 'outdated'],
            base_path()
        );

        $process->setEnv([
            'PATH' => dirname($this->phpBinary) . ':/usr/local/bin:/usr/bin:/bin',
            'HOME' => getenv('HOME') ?: '/root',
        ]);

        $process->setTimeout(60);
        $process->run();

        return $this->decodeBunTable($process->getOutput()) ?? [];
    }

    /**
     * Parse the native `bun outdated` table.
     *
     * Returns data in npm-outdated JSON shape so normalize() can be reused,
     * or null when the output is not a recognizable table.
     */
    protected function decodeBunTable(string $output): ?array
    {
        $result = [];

        foreach (explode("\n", $output) as $line) {
            $line = trim($line, " \t\r\n|");

            if ($line === '' || str_contains($line, '---')) {
                continue;
            }

            $columns = array_map('trim', explode('|', $line));

            if (count($columns) < 4) {
                continue;
            }

            // Skip the header row ("Package | Current | Update | Latest").
            if (strcasecmp($columns[0], 'Package') === 0) {
                continue;
            }

            [$package, $current, , $latest] = $columns;

            // bun marks dev dependencies as "name (dev)".
            $type = 'dependencies';

            if (str_ends_with($package, '(dev)')) {
                $type = 'devDependencies';
                $package = trim(substr($package, 0, -5));
            }

            if ($package === '' || $current === '' || $latest === '') {
                continue;
            }

            $result[$package] = [
                'current' => $current,
                'latest' => $latest,
                'type' => $type,
            ];
        }

        return $result === [] && ! str_contains($output, 'Package') ? null : $result;
    }

    protected function normalize(array $output): array
    {
        $manifestTypes = $this->manifestTypes();

        return collect($output)
            ->map(function (array $package, string $name) use ($manifestTypes) {
                $current = $package['current'] ?? '—';
                $latest = $package['latest'] ?? $package['wanted'] ?? '—';

                return [
                    'name' => $name,
                    'version' => $current,
                    'latest' => $latest,
                    'latest-status' => $this->resolveStatus($current, $latest),
                    'type' => $package['type'] ?? $manifestTypes[$name] ?? 'devDependencies',
                    'source' => null,
                    'description' => $package['location'] ?? null,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Map package names to dependencies/devDependencies from package.json,
     * since `npm outdated --json` does not report the dependency type.
     *
     * @return array<string, string>
     */
    protected function manifestTypes(): array
    {
        $path = base_path('package.json');

        if (! is_file($path)) {
            return [];
        }

        $manifest = json_decode((string) file_get_contents($path), true);

        if (! is_array($manifest)) {
            return [];
        }

        $types = [];

        foreach ((array) ($manifest['dependencies'] ?? []) as $name => $version) {
            $types[$name] = 'dependencies';
        }

        foreach ((array) ($manifest['devDependencies'] ?? []) as $name => $version) {
            $types[$name] = 'devDependencies';
        }

        return $types;
    }

    protected function resolveStatus(string $current, string $latest): string
    {
        if ($current === $latest || $current === '—') {
            return 'up-to-date';
        }

        $currentParts = explode('.', ltrim($current, '^~v'));
        $latestParts = explode('.', ltrim($latest, '^~v'));

        if (($currentParts[0] ?? 0) !== ($latestParts[0] ?? 0)) {
            return 'update-possible'; // major
        }

        return 'semver-safe-update'; // minor/patch
    }
}
