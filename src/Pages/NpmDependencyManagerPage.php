<?php

namespace Daljo25\FilamentDependencyManager\Pages;

use Daljo25\FilamentDependencyManager\Models\NpmPackage;
use Daljo25\FilamentDependencyManager\Services\NpmService;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class NpmDependencyManagerPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'npm-manager';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament-dependency-manager::pages.npm-dependency-manager';

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

    public static function getNavigationIcon(): ?string
    {
        return config('dependency-manager.npm.icon')
            ?? 'heroicon-o-cube';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = count(app(NpmService::class)->getOutdatedPackages());
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(NpmPackage::query())
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament-dependency-manager::dependency-manager.table.columns.package'))
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label(__('filament-dependency-manager::dependency-manager.npm.columns.type'))
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'dependencies'    => 'info',
                        'devDependencies' => 'gray',
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
                    ->sortable()
                    ->color(fn(string $state) => match ($state) {
                        'semver-safe-update' => 'warning',
                        'update-possible'    => 'danger',
                        default              => 'success',
                    })
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'semver-safe-update' => __('filament-dependency-manager::dependency-manager.table.status.minor'),
                        'update-possible'    => __('filament-dependency-manager::dependency-manager.table.status.major'),
                        default              => __('filament-dependency-manager::dependency-manager.table.status.up_to_date'),
                    }),
            ])
            ->actions([
                Action::make('copy_command')
                    ->label(__('filament-dependency-manager::dependency-manager.table.actions.copy_command'))
                    ->icon('heroicon-o-clipboard-document')
                    ->color('warning')
                    ->action(function (NpmPackage $record) {
                        $client = config('dependency-manager.npm_client', 'npm');
                        $command = match ($client) {
                            'yarn'  => "yarn add {$record->name}@{$record->latest}",
                            'pnpm'  => "pnpm add {$record->name}@{$record->latest}",
                            default => "npm install {$record->name}@{$record->latest}",
                        };
                        $this->js("navigator.clipboard.writeText('{$command}')");
                        Notification::make()
                            ->title(__('filament-dependency-manager::dependency-manager.table.actions.copy_success'))
                            ->body($command)
                            ->success()
                            ->send();
                    }),

                Action::make('npm_page')
                    ->label(__('filament-dependency-manager::dependency-manager.npm.actions.view_npm'))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('info')
                    ->url(fn(NpmPackage $record) => "https://www.npmjs.com/package/{$record->name}")
                    ->openUrlInNewTab(),
            ])
            ->headerActions([
                Action::make('refresh')
                    ->label(__('filament-dependency-manager::dependency-manager.table.actions.refresh'))
                    ->icon('heroicon-o-arrow-path')
                    ->action(function () {
                        app(NpmService::class)->clearCache();
                        $this->resetTable();
                    }),
            ])
            ->filters([
                SelectFilter::make('latest-status')
                    ->label(__('filament-dependency-manager::dependency-manager.table.columns.update_type'))
                    ->options([
                        'semver-safe-update' => __('filament-dependency-manager::dependency-manager.table.status.minor'),
                        'update-possible'    => __('filament-dependency-manager::dependency-manager.table.status.major'),
                    ])
                    ->query(
                        fn(Builder $query, array $data) =>
                        $query->when($data['value'] ?? null, fn($q) => $q->where('latest-status', $data['value']))
                    ),
            ])
            ->emptyStateHeading(__('filament-dependency-manager::dependency-manager.npm.empty.heading'))
            ->emptyStateDescription(__('filament-dependency-manager::dependency-manager.npm.empty.description'))
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
