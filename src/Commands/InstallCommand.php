<?php

namespace Daljo25\FilamentDependencyManager\Commands;

use Illuminate\Console\Command;
use function Laravel\Prompts\confirm;

class InstallCommand extends Command
{
    public $signature = 'dependency-manager:install';

    public $description = 'Install the Filament Dependency Manager plugin';

    public function handle(): int
    {
        $this->info('');
        $this->info('  ╔═══════════════════════════════════════╗');
        $this->info('  ║   Filament Dependency Manager         ║');
        $this->info('  ║   by Daljo25                          ║');
        $this->info('  ╚═══════════════════════════════════════╝');
        $this->info('');

        $this->info('  Installing Filament Dependency Manager...');
        $this->info('');

        // Publish config
        if (confirm(label: 'Publish configuration file?', default: true)) {
            $this->callSilently('vendor:publish', [
                '--tag' => 'filament-dependency-manager-config',
                '--force' => false,
            ]);
            $this->line('  <fg=green>✓</> Configuration published → <fg=gray>config/dependency-manager.php</>');
        }

        $this->info('');

        // Publish translations
        if (confirm(label: 'Publish translations?', default: false)) {
            $this->callSilently('vendor:publish', [
                '--tag' => 'filament-dependency-manager-translations',
                '--force' => false,
            ]);
            $this->line('  <fg=green>✓</> Translations published → <fg=gray>lang/vendor/filament-dependency-manager/</>');
        }

        $this->info('');

        // Publish views
        if (confirm(label: 'Publish views (only if you want to customize them)?', default: false)) {
            $this->callSilently('vendor:publish', [
                '--tag' => 'filament-dependency-manager-views',
                '--force' => false,
            ]);
            $this->line('  <fg=green>✓</> Views published → <fg=gray>resources/views/vendor/filament-dependency-manager/</>');
        }

        $this->info('');

        // Star on GitHub
        if (! $this->option('no-interaction')) {
            if (confirm(
                label: 'Would you like to show some love by starring the repo on GitHub? ⭐',
                default: true,
            )) {
                $url = 'https://github.com/daljo25/filament-dependency-manager';

                match (PHP_OS_FAMILY) {
                    'Darwin'  => exec("open {$url}"),
                    'Linux'   => exec("xdg-open {$url}"),
                    'Windows' => exec("start {$url}"),
                    default   => $this->line("  <fg=gray>→ Visit: {$url}</>"),
                };

                $this->line('  <fg=yellow>★</> Thank you for your support!');
                $this->info('');
            }
        }

        $this->info('');
        $this->info('  ┌─────────────────────────────────────────────────────────┐');
        $this->info('  │  Installation complete! Next steps:                     │');
        $this->info('  │                                                         │');
        $this->info('  │  Add the plugin to your AdminPanelProvider:             │');
        $this->info('  │                                                         │');
        $this->info('  │  ->plugins([                                            │');
        $this->info('  │      FilamentDependencyManagerPlugin::make(),           │');
        $this->info('  │  ])                                                     │');
        $this->info('  └─────────────────────────────────────────────────────────┘');
        $this->info('');

        return self::SUCCESS;
    }
}
