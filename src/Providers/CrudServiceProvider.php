<?php

namespace YajTech\Crud\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;
use YajTech\Crud\Console\Commands\GenerateCrudCommand;
use YajTech\Crud\Traits\ProviderManager;

class CrudServiceProvider extends ServiceProvider
{
    use ProviderManager;

    /**
     * Register bindings in the container.
     */
    public function register(): void
    {
        $this->bindInterfaces();
    }

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerRoutes();
        $this->registerPaginationMacros();
    }

    /**
     * Register the CRUD command.
     */
    protected function registerCommands(): void
    {
        $this->commands([
            GenerateCrudCommand::class,
        ]);
    }

    /**
     * Load package routes.
     */
    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
