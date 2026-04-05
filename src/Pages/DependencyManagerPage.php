<?php

namespace Daljo25\FilamentDependencyManager\Pages;

use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use UnitEnum;
use Daljo25\FilamentDependencyManager\Services\ComposerService;

class DependencyManagerPage extends Page implements HasTable
{
    use InteractsWithTable;

    // Page configuration
    protected static ?string $slug = 'composer-manager';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament-dependency-manager::pages.dependency-manager';

    public function getTitle(): string
    {
        return config('dependency-manager.composer.title')
            ?? __('filament-dependency-manager::dependency-manager.composer.title');
    }

    public static function getNavigationLabel(): string
    {
        return config('dependency-manager.composer.navigation_label')
            ?? __('filament-dependency-manager::dependency-manager.composer.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return config('dependency-manager.navigation.group')
            ?? __('filament-dependency-manager::dependency-manager.navigation.group');
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return config('dependency-manager.composer.icon')
            ?? Heroicon::OutlinedCodeBracketSquare;
    }

    public static function getNavigationBadge(): ?string
    {
        $outdatedCount = count(app(ComposerService::class)->getOutdatedPackages());

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
                    ->weight('bold')
                    ->url(fn($record) => app(ComposerService::class)->getRepositoryUrl($record), true),

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

                TextColumn::make('latest-release-date')
                    ->label(__('filament-dependency-manager::dependency-manager.table.columns.last_updated'))
                    ->formatStateUsing(
                        fn($state) => $state
                            ? Carbon::parse($state)->diffForHumans()
                            : '—'
                    ),

                TextColumn::make('description')
                    ->label(__('filament-dependency-manager::dependency-manager.table.columns.description'))
                    ->limit(50)
                    ->tooltip(fn($state) => $state)
                    ->color('gray'),
            ])
            ->recordActions([
                Action::make('copy_command')
                    ->label(__('filament-dependency-manager::dependency-manager.table.actions.copy_command'))
                    ->icon('heroicon-o-clipboard-document')
                    ->color('warning')
                    ->action(function ($record, $livewire) {
                        $command = "composer require {$record['name']}:{$record['latest']}";
                        $livewire->js("navigator.clipboard.writeText('{$command}')");
                    })
                    ->successNotificationTitle(__('filament-dependency-manager::dependency-manager.table.actions.copy_success')),

                Action::make('changelog')
                    ->label(__('filament-dependency-manager::dependency-manager.table.actions.changelog'))
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(fn($record) => app(ComposerService::class)->getReleaseUrl($record), true)
                    ->openUrlInNewTab(),
            ])
            ->headerActions([
                Action::make('refresh')
                    ->label(__('filament-dependency-manager::dependency-manager.table.actions.refresh'))
                    ->icon('heroicon-o-arrow-path')
                    ->action(function () {
                        app(ComposerService::class)->clearCache();
                    }),
            ])
            ->emptyStateHeading(__('filament-dependency-manager::dependency-manager.table.empty.heading'))
            ->emptyStateDescription(__('filament-dependency-manager::dependency-manager.table.empty.description'))
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    protected function getPackagesCollection(): Collection
    {
        return collect(app(ComposerService::class)->getOutdatedPackages());
    }
}
