<?php
namespace Daljo25\FilamentDependencyManager\Services;

use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Cache;

class ComposerService
{
    public function getOutdatedPackages(): array
    {
        // Cacheamos por 1 hora para que la tabla de Filament no sea lenta
        return Cache::remember('filament-dependency-manager:composer-outdated', 3600, function () {
            
            // Ejecutamos el comando de composer
            // El flag --direct es clave para no ver las 200 dependencias internas, solo las tuyas
            $process = new Process(['composer', 'outdated', '--direct', '--format=json']);
            $process->run();

            if (!$process->isSuccessful()) {
                return [];
            }

            $output = json_decode($process->getOutput(), true);

            return $output['installed'] ?? [];
        });
    }
}