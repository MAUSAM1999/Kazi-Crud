<?php

namespace YajTech\Crud\Traits;

use Illuminate\Pagination\Paginator;
use YajTech\Crud\Interfaces\ControllerServiceInterface;
use YajTech\Crud\Interfaces\MigrationServiceInterface;
use YajTech\Crud\Interfaces\ModelServiceInterface;
use YajTech\Crud\Interfaces\RequestServiceInterface;
use YajTech\Crud\Interfaces\ResourceServiceInterface;
use YajTech\Crud\Interfaces\RouteServiceInterface;
use YajTech\Crud\Services\ControllerService;
use YajTech\Crud\Services\MigrationService;
use YajTech\Crud\Services\ModelService;
use YajTech\Crud\Services\RequestService;
use YajTech\Crud\Services\ResourceService;
use YajTech\Crud\Services\RouteService;

trait ProviderManager
{
    /**
     * Bind the package interfaces to their implementations.
     */
    protected function bindInterfaces(): void
    {
        $interfaces = [
            MigrationServiceInterface::class => MigrationService::class,
            ModelServiceInterface::class => ModelService::class,
            RequestServiceInterface::class => RequestService::class,
            ResourceServiceInterface::class => ResourceService::class,
            ControllerServiceInterface::class => ControllerService::class,
            RouteServiceInterface::class => RouteService::class,
        ];

        foreach ($interfaces as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }


    /**
     * Create a custom paginator.
     */
    protected function customPaginator(int $perPage = null, $columns = ['*'], $pageName = 'page', int $page = null, bool $simple = false)
    {
        // Validate rowsPerPage query parameter
        request()->validate([
            'rowsPerPage' => 'nullable|numeric|gte:0|lte:1000000000000000000'
        ]);

        // Resolve the current page
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        // Determine the number of records per page
        if ($perPage === null) {
            $rows = (int)request()->query('rowsPerPage', 20);
            $perPage = $rows === 0 ? $this->count() : $rows;
        }

        if ($simple) {
            // Simple pagination
            $this->offset(($page - 1) * $perPage)->limit($perPage + 1);
            return $this->simplePaginator($this->get($columns), $perPage, $page, [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]);
        }

        // Full pagination
        $total = $this->toBase()->getCountForPagination();
        $results = $total ? $this->forPage($page, $perPage)->get($columns) : $this->model->newCollection();

        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
}
