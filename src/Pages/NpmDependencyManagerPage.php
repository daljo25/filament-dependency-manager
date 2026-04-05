<?php

namespace Daljo25\FilamentDependencyManager\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Daljo25\FilamentDependencyManager\Clusters\DependencyManagerCluster;
use Daljo25\FilamentDependencyManager\Services\NpmService;

class NpmDependencyManagerPage extends Page implements HasTable
{
    use InteractsWithTable;

    // Page configuration
    protected static ?string $slug = 'npm-manager';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament-dependency-manager::pages.npm-dependency-manager';

    public function getTitle(): string
    {
        return config('dependency-manager.npm.title')
            ?? __('filament-dependency-manager::dependency-manager.npm.title');
    }

    public static function getNavigationLabel(): string
    {
        return config('dependency-manager.npm.navigation_label')
            ?? __('filament-dependency-manager::dependency-manager.npm.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return config('dependency-manager.navigation.group')
            ?? __('filament-dependency-manager::dependency-manager.navigation.group');
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return config('dependency-manager.npm.icon')
            ?? Heroicon::OutlinedCube;
    }

    public static function getNavigationBadge(): ?string
    {
        $outdatedCount = count(app(NpmService::class)->getOutdatedPackages());

        return $outdatedCount > 0 ? (string) $outdatedCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }


    // Table definition
    public function table(Table $table): Table
    {
        return $table
            ->records(fn() => $this->getPackagesCollection())
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament-dependency-manager::dependency-manager.table.columns.package'))
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label(__('filament-dependency-manager::dependency-manager.npm.columns.type'))
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'devDependencies' => 'gray',
                        'dependencies'    => 'info',
                        default           => 'gray',
                    }),

                TextColumn::make('version')
                    ->label(__('filament-dependency-manager::dependency-manager.table.columns.installed'))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('latest')
                    ->label(__('filament-dependency-manager::dependency-manager.table.columns.latest'))
                    ->badge()
                    ->color('success'),

                TextColumn::make('latest-status')
                    ->label(__('filament-dependency-manager::dependency-manager.table.columns.update_type'))
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'semver-safe-update' => 'warning',
                        'update-possible'    => 'danger',
                        default              => 'success',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'semver-safe-update' => __('filament-dependency-manager::dependency-manager.table.status.minor'),
                        'update-possible'    => __('filament-dependency-manager::dependency-manager.table.status.major'),
                        default              => __('filament-dependency-manager::dependency-manager.table.status.up_to_date'),
                    }),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('copy_command')
                    ->label(__('filament-dependency-manager::dependency-manager.table.actions.copy_command'))
                    ->icon('heroicon-o-clipboard-document')
                    ->color('warning')
                    ->action(function ($record, $livewire) {
                        $client = config('dependency-manager.npm_client', 'npm');
                        $command = match ($client) {
                            'yarn' => "yarn add {$record['name']}@{$record['latest']}",
                            'pnpm' => "pnpm add {$record['name']}@{$record['latest']}",
                            default => "npm install {$record['name']}@{$record['latest']}",
                        };
                        $livewire->js("navigator.clipboard.writeText('{$command}')");
                    }),

                \Filament\Actions\Action::make('npm_page')
                    ->label(__('filament-dependency-manager::dependency-manager.npm.actions.view_npm'))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('info')
                    ->url(fn($record) => "https://www.npmjs.com/package/{$record['name']}", true),
            ])
            ->headerActions([
                \Filament\Actions\Action::make('refresh')
                    ->label(__('filament-dependency-manager::dependency-manager.table.actions.refresh'))
                    ->icon('heroicon-o-arrow-path')
                    ->action(fn() => app(NpmService::class)->clearCache()),
            ])
            ->emptyStateHeading(__('filament-dependency-manager::dependency-manager.npm.empty.heading'))
            ->emptyStateDescription(__('filament-dependency-manager::dependency-manager.npm.empty.description'))
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    protected function getPackagesCollection(): Collection
    {
        return collect(app(NpmService::class)->getOutdatedPackages());
    }
}
