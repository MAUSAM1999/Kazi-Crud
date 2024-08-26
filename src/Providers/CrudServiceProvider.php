<?php

namespace YajTech\Crud\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use YajTech\Crud\Console\Commands\GenerateCrudCommand;

class CrudServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerPagination();
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

    /**
     * Register and manage pagination
     */
    protected function registerPagination(): void
    {
        \Illuminate\Database\Eloquent\Builder::macro('paginates', function (int $perPage = null, $columns = ['*'], $pageName = 'page', int $page = null) {
            request()->validate(['rowsPerPage' => 'nullable|numeric|gte:0|lte:1000000000000000000']);

            $page = $page ?: Paginator::resolveCurrentPage($pageName);

            $total = $this->toBase()->getCountForPagination();

            if ($perPage === null) {
                $rows = (int)request()->query('rowsPerPage', 20);
                if ($rows === 0) {
                    $perPage = $total;
                } else {
                    $perPage = $rows;
                }
            }
            $results = $total
                ? $this->forPage($page, $perPage)->get($columns)
                : $this->model->newCollection();

            return $this->paginator($results, $total, $perPage, $page, [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]);
        });

        \Illuminate\Database\Eloquent\Builder::macro('simplePaginates', function (int $perPage = null, $columns = ['*'], $pageName = 'page', $page = null) {
            request()->validate(['rowsPerPage' => 'nullable|numeric|gte:0|lte:1000000000000000000']);
            $page = $page ?: Paginator::resolveCurrentPage($pageName);

            if ($perPage === null) {
                $rows = (int)request()->query('rowsPerPage', 20);
                if ($rows === 0) {
                    $perPage = $this->count();
                } else {
                    $perPage = $rows;
                }
            }

            $this->offset(($page - 1) * $perPage)->limit($perPage + 1);

            return $this->simplePaginator($this->get($columns), $perPage, $page, [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]);
        });
    }
}
