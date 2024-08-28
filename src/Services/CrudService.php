<?php

namespace YajTech\Crud\Services;

use YajTech\Crud\Interfaces\{
    MigrationServiceInterface,
    ModelServiceInterface,
    RequestServiceInterface,
    ResourceServiceInterface,
    ControllerServiceInterface,
    RouteServiceInterface
};
use YajTech\Crud\Traits\PathManager;

class CrudService
{
    use PathManager;

    /**
     * Initialize the service classes via constructor injection.
     *
     * @param MigrationServiceInterface $migrationService
     * @param ModelServiceInterface $modelService
     * @param RequestServiceInterface $requestService
     * @param ResourceServiceInterface $resourceService
     * @param ControllerServiceInterface $controllerService
     * @param RouteServiceInterface $routeService
     */
    public function __construct(
        protected MigrationServiceInterface  $migrationService,
        protected ModelServiceInterface      $modelService,
        protected RequestServiceInterface    $requestService,
        protected ResourceServiceInterface   $resourceService,
        protected ControllerServiceInterface $controllerService,
        protected RouteServiceInterface      $routeService
    )
    {
    }

    /**
     * Handle migration creation, checking if it already exists before creating a new one.
     *
     * @param string $name
     * @param string $pluralModel
     * @param array $fields
     * @param mixed|null $methods
     * @param string|null $module
     * @return void
     */
    public function handleMigration(string $name, string $pluralModel, array $fields, mixed $methods = null, ?string $module = null): void
    {
        $this->info("Creating migration...");

        if ($this->migrationService->migrationExists($pluralModel, $module)) {
            $moduleText = $module ? " (Module: $module)" : '';
            $this->info("Migration for '{$pluralModel}' already exists.$moduleText");
        } else {
            $migration = $this->migrationService->createMigration($pluralModel, $fields, $module);
            $this->info("Migration created at $migration");
        }
    }

    /**
     * Handle model creation, checking if it already exists before creating a new one.
     *
     * @param string $name
     * @param string $pluralModel
     * @param array $fields
     * @param mixed|null $methods
     * @param string|null $module
     * @return void
     */
    public function handleModel(string $name, string $pluralModel, array $fields, mixed $methods = null, ?string $module = null): void
    {
        $this->info("Creating model...");

        if ($this->modelService->modelExists($name, $module)) {
            $moduleText = $module ? " (Module: $module)" : '';
            $this->info("Model '{$name}' already exists.$moduleText");
        } else {
            $model = $this->modelService->createModel($name, $fields, $module);
            $this->info("Model created at $model");
        }
    }

    /**
     * Handle the creation of a request class for creating a new resource.
     *
     * @param string $name
     * @param string $pluralModel
     * @param array $fields
     * @param mixed $methods
     * @param string|null $module
     * @return void
     */
    public function handleCreateRequest(string $name, string $pluralModel, array $fields, mixed $methods = null, ?string $module = null): void
    {
        $this->handleRequest($name, 'CreateRequest', $fields, $module);
    }

    /**
     * Handle the creation of a request class for updating an existing resource.
     *
     * @param string $name
     * * @param string $pluralModel
     * * @param array $fields
     * * @param mixed $methods
     * * @param string|null $module
     * * @return void
     */
    public function handleUpdateRequest(string $name, string $pluralModel, array $fields, mixed $methods = null, ?string $module = null): void
    {
        $this->handleRequest($name, 'UpdateRequest', $fields, $module);
    }

    /**
     * Handle creation of a request (create or update).
     *
     * @param string $name
     * @param string $requestType
     * @param array $fields
     * @param string|null $module
     * @return void
     */
    public function handleRequest(string $name, string $requestType, array $fields, ?string $module = null): void
    {
        $this->info("Creating {$requestType} request...");

        if ($this->requestService->requestExists($name, $module, $requestType)) {
            $moduleText = $module ? " (Module: $module)" : '';
            $this->info("$requestType '{$name}' already exists.$moduleText");
        } else {
            $model = $this->requestService->createRequest($name, $requestType, $fields, $module);
            $this->info("$requestType created at $model");
        }
    }

    /**
     * Handle the creation of a resources class for list.
     *
     * @param string $name
     * * @param string $pluralModel
     * * @param array $fields
     * * @param mixed $methods
     * * @param string|null $module
     * * @return void
     */
    public function handleDetailResource(string $name, string $pluralModel, array $fields, $methods, $module): void
    {
        $this->handleResource($name, 'DetailResource', $fields, $module);
    }

    /**
     * Handle the creation of a resources class for detail.
     *
     * @param string $name
     * * @param string $pluralModel
     * * @param array $fields
     * * @param mixed $methods
     * * @param string|null $module
     * * @return void
     */
    public function handleListResource(string $name, string $pluralModel, array $fields, $methods, $module): void
    {
        $this->handleResource($name, 'ListResource', $fields, $module);
    }

    /**
     * Handle creation of resource files (detail or list).
     *
     * @param string $name
     * @param string $resourceType
     * @param array $fields
     * @param string|null $module
     * @return void
     */
    public function handleResource(string $name, string $resourceType, array $fields, ?string $module = null): void
    {
        $this->info("Creating {$resourceType} resource...");

        if ($this->resourceService->resourceExists($name, $module, $resourceType)) {
            $moduleText = $module ? " (Module: $module)" : '';
            $this->info("$resourceType '{$name}' already exists.$moduleText");
        } else {
            $model = $this->resourceService->createResource($name, $resourceType, $fields, $module);;
            $this->info("$resourceType created at $model");
        }

    }

    /**
     * Handle creation of a controller.
     *
     * @param string $name
     * @param string $pluralModel
     * @param array $fields
     * @param mixed|null $methods
     * @param string|null $module
     * @return void
     */
    public function handleController(string $name, string $pluralModel, array $fields, mixed $methods = null, ?string $module = null): void
    {
        $this->info("Creating controller...");

        if ($this->controllerService->controllerExists($name, $module)) {
            $moduleText = $module ? " (Module: $module)" : '';
            $this->info("Controller '{$name}' already exists.$moduleText");
        } else {
            $controller = $this->controllerService->createController($name, $module);
            $this->info("Controller created at $controller");
        }
    }

    /**
     * Handle creation of a route.
     *
     * @param string $name
     * @param string $pluralModel
     * @param array $fields
     * @param mixed|null $methods
     * @param string|null $module
     * @return void
     */
    public function handleRoute(string $name, string $pluralModel, array $fields, mixed $methods = null, ?string $module = null): void
    {
        $this->info("Creating route...");
        $path = $this->getFullPath('api.php', $module, 'route');
        // check does api.php
        if (!file_exists($path)) {
            $this->info("Route File api.php not found. Path : $path");
            $this->info("Read Docs For Solution");
        } else if ($this->routeService->routeExists($name, $module)) {
            $moduleText = $module ? " (Module: $module)" : '';
            $this->info("Route '{$name}' already exists.$moduleText");
        } else {
            $route = $this->routeService->createRoute($name, $module, $methods);
            $this->info("Route created at $route");
        }
    }

    /**
     * Print an informational message to the console.
     *
     * @param string $message
     * @return void
     */
    protected function info(string $message): void
    {
        // Ensure the message is well-formatted
        echo PHP_EOL . $message . PHP_EOL;
    }
}
