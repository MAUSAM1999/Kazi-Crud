<?php

namespace Kazi\Crud\Providers;

use Illuminate\Support\ServiceProvider;
use Kazi\Crud\Console\Commands\GenerateCrudCommand;

class CrudServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            GenerateCrudCommand::class
        ]);
    }
}
