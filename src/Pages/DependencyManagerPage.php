<?php

namespace Daljo25\FilamentDependencyManager\Pages;

use Carbon\Carbon;
use Daljo25\FilamentDependencyManager\Models\ComposerPackage;
use Daljo25\FilamentDependencyManager\Services\ComposerService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DependencyManagerPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'composer-manager';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament-dependency-manager::pages.dependency-manager';

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

    public static function getNavigationIcon(): ?string
    {
        return config('dependency-manager.composer.icon')
            ?? 'heroicon-o-code-bracket-square';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = count(app(ComposerService::class)->getOutdatedPackages());

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ComposerPackage::query())
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament-dependency-manager::dependency-manager.table.columns.package'))
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->url(fn (ComposerPackage $record) => "https://packagist.org/packages/{$record->name}")
                    ->openUrlInNewTab(),

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
                    ->sortable()
                    ->color(fn (string $state) => match ($state) {
                        'semver-safe-update' => 'warning',
                        'update-possible' => 'danger',
                        default => 'success',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'semver-safe-update' => __('filament-dependency-manager::dependency-manager.table.status.minor'),
                        'update-possible' => __('filament-dependency-manager::dependency-manager.table.status.major'),
                        default => __('filament-dependency-manager::dependency-manager.table.status.up_to_date'),
                    }),

                TextColumn::make('latest-release-date')
                    ->label(__('filament-dependency-manager::dependency-manager.table.columns.last_updated'))
                    ->formatStateUsing(
                        fn ($state) => $state ? Carbon::parse($state)->diffForHumans() : '—'
                    ),

                TextColumn::make('description')
                    ->label(__('filament-dependency-manager::dependency-manager.table.columns.description'))
                    ->limit(50)
                    ->tooltip(fn ($state) => $state)
                    ->color('gray'),
            ])
            ->actions([
                Action::make('copy_command')
                    ->label(__('filament-dependency-manager::dependency-manager.table.actions.copy_command'))
                    ->icon('heroicon-o-clipboard-document')
                    ->color('warning')
                    ->action(function (ComposerPackage $record) {
                        $command = "composer require {$record->name}:{$record->latest}";
                        $this->js("navigator.clipboard.writeText('{$command}')");
                        Notification::make()
                            ->title(__('filament-dependency-manager::dependency-manager.table.actions.copy_success'))
                            ->body($command)
                            ->success()
                            ->send();
                    }),

                Action::make('changelog')
                    ->label(__('filament-dependency-manager::dependency-manager.table.actions.changelog'))
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(fn (ComposerPackage $record) => "https://github.com/{$record->name}/releases")
                    ->openUrlInNewTab(),
            ])
            ->headerActions([
                Action::make('refresh')
                    ->label(__('filament-dependency-manager::dependency-manager.table.actions.refresh'))
                    ->icon('heroicon-o-arrow-path')
                    ->action(function () {
                        app(ComposerService::class)->clearCache();
                        $this->resetTable();
                    }),
            ])
            ->filters([
                SelectFilter::make('latest-status')
                    ->label(__('filament-dependency-manager::dependency-manager.table.columns.update_type'))
                    ->options([
                        'semver-safe-update' => __('filament-dependency-manager::dependency-manager.table.status.minor'),
                        'update-possible' => __('filament-dependency-manager::dependency-manager.table.status.major'),
                    ])
                    ->query(
                        fn (Builder $query, array $data) => $query->when($data['value'] ?? null, fn ($q) => $q->where('latest-status', $data['value']))
                    ),
            ])
            ->emptyStateHeading(__('filament-dependency-manager::dependency-manager.table.empty.heading'))
            ->emptyStateDescription(__('filament-dependency-manager::dependency-manager.table.empty.description'))
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
