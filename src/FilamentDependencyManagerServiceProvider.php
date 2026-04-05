<?php

namespace Daljo25\FilamentDependencyManager;

use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Daljo25\FilamentDependencyManager\Testing\TestsFilamentDependencyManager;
use Daljo25\FilamentDependencyManager\Commands\InstallCommand;

class FilamentDependencyManagerServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-dependency-manager';

    public static string $viewNamespace = 'filament-dependency-manager';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile('dependency-manager')       // → config/dependency-manager.php
            ->hasViews(static::$viewNamespace)           // → resources/views/vendor/filament-dependency-manager
            ->hasTranslations()                          // → lang/vendor/filament-dependency-manager
            ->hasCommand(InstallCommand::class);
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Testing
        Testable::mixin(new TestsFilamentDependencyManager);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'daljo25/filament-dependency-manager';
    }
}
