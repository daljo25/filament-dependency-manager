<?php

namespace Daljo25\FilamentDependencyManager\Commands;

use Illuminate\Console\Command;

class FilamentDependencyManagerCommand extends Command
{
    public $signature = 'filament-dependency-manager';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
