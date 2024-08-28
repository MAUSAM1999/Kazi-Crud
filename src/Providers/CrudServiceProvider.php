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
     * Register custom pagination macros.
     */
    protected function registerPaginationMacros(): void
    {
        Builder::macro('paginates', function (int $perPage = null, $columns = ['*'], $pageName = 'page', int $page = null) {
            return $this->customPaginator($perPage, $columns, $pageName, $page, false);
        });

        Builder::macro('simplePaginates', function (int $perPage = null, $columns = ['*'], $pageName = 'page', int $page = null) {
            return $this->customPaginator($perPage, $columns, $pageName, $page, true);
        });
    }

    /**
     * Load package routes.
     */
    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
