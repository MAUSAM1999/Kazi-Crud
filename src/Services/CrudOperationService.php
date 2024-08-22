<?php

namespace Kazi\Crud\Services;

use Illuminate\Support\Str;

class CrudOperationService
{
    public function removeDefaultFields($fields)
    {
        $fieldsCheck = [];
        $elementsToRemove = ["id", "created_by", "updated_by", 'extra', 'deleted_at'];
        foreach ($fields as $field) {
            $fieldsCheck[] = explode(':', $field)[0];
        }
        // Find the common elements between the two arrays
        $commonFields = array_intersect($fieldsCheck, $elementsToRemove);
        // Get the indices of the common elements from the main array
        $indices = [];
        foreach ($commonFields as $element) {
            $index = array_search($element, $fieldsCheck);
            if ($index !== false) {
                $indices[] = $index;
            }
        }
        // Loop through the indices and unset the corresponding elements
        foreach ($indices as $index) {
            unset($fields[$index]);
        }

        return $fields;
    }

    public function handleMigration(string $name, string $pluralModel, array $fields, $methods, $module): void
    {
        $this->info("\nCreating migration ...");
        if ($this->migrationExists($pluralModel, $module)) {
            $module = $module ? " ( $module )" : '';
            $this->error("\nMigration for the '{$pluralModel}' table already exists. $module");
        } else {
            $migration = $this->executeMigration($pluralModel, $fields, $module);
            $this->info("\nMigration $migration Created");
        }
    }

    public function handleModel(string $name, string $pluralModel, array $fields, $methods, $module): void
    {
        $this->info("\n\nCreating model...");
        $modelName = Str::studly($name);
        if ($this->modelExists($modelName, $module)) {
            $module = $module ? " ( $module )" : '';
            $this->error("\nA model named '{$modelName}' already exists. $module");
        } else {
            $model = $this->executeModel($modelName, $fields, $module);
            $this->info("\nModel $model Created");
        }
    }

    public function handleCreateRequest(string $name, string $pluralModel, array $fields, $methods, $module): void
    {
        $this->info("\n\nCreating create request...");
        $this->handleRequest($name, 'CreateRequest', $fields, $module);
    }

    public function handleUpdateRequest(string $name, string $pluralModel, array $fields, $methods, $module): void
    {
        $this->info("\n\nCreating update request...");
        $this->handleRequest($name, 'UpdateRequest', $fields, $module);
    }

    protected function handleRequest(string $name, string $type, array $fields, $module): void
    {
        $requestName = Str::studly($name) . $type;
        if ($this->requestExists($requestName, $module)) {
            $module = $module ? " ( $module )" : '';
            $this->error("\nA request class named '{$requestName}' already exists. $module");
        } else {
            $model = $this->executeRequest($requestName, $fields, $module);
            $this->info("\n$type Request $model Created");
        }
    }

    public function handleDetailResource(string $name, string $pluralModel, array $fields, $methods, $module): void
    {
        $this->info("\n\nCreating detail resources...");
        $this->handleResource($name, 'DetailResource', $fields, $module);
    }

    public function handleListResource(string $name, string $pluralModel, array $fields, $methods, $module): void
    {
        $this->info("\n\nCreating list resources...");
        $this->handleResource($name, 'ListResource', $fields, $module);
    }

    protected function handleResource(string $name, string $type, array $fields, $module): void
    {
        $resourceName = Str::studly($name) . $type;
        if ($this->resourceExists($resourceName, $module)) {
            $module = $module ? " ( $module )" : '';
            $this->error("\nA {$type} named '{$resourceName}' already exists. $module");
        } else {
            $model = $this->executeResource($resourceName, $fields, $module);
            $this->info("\n{$type} $model Created");
        }
    }

    public function handleController(string $name, string $pluralModel, array $fields, $methods, $module): void
    {
        $this->info("\n\nCreating controller...");
        $controllerName = Str::studly($name) . 'Controller';
        if ($this->controllerExists($controllerName, $module)) {
            $module = $module ? " ( $module )" : '';
            $this->error("\nA controller named '{$controllerName}' already exists. $module");
        } else {
            $model = $this->executeController($name, $controllerName, $module);
            $this->info("\nController $model Created");
        }
    }

    public function handleRoute(string $name, string $pluralModel, array $fields, $methods, $module): void
    {
        $this->info("\n\nCreating route...");
        $modelName = Str::lower($name);
        if ($module) {
            $path = base_path('Modules/' . $module . '/routes/api.php');
            $controllerName = Str::studly($name) . 'Controller';
            $controllerPath = 'Modules\\' . $module . '\\App\\Http\\Controllers\\' . $controllerName;

        } else {
            $path = base_path('routes/api.php');
            $controllerName = Str::studly($name) . 'Controller';
            $controllerPath = 'App\\Http\\Controllers\\' . $controllerName;

        }
        if ($methods) {
            $methods = "['" . implode("', '", explode(',', $methods)) . "']";
            $routeToCheck = "\nKazi\Crud\Helper\CrudRoute::generateRoutes('$modelName', " . $controllerPath . "::class, " . $methods . ");";
        } else {
            $routeToCheck = "\nKazi\Crud\Helper\CrudRoute::generateRoutes('$modelName', " . $controllerPath . "::class);";
        }

        // Check if the directory exists
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $basicRoutePattern = "Kazi\Crud\Helper\CrudRoute::generateRoutes('$modelName', " . $controllerPath . "::class";
        $fileContent = str_replace(["\n", "\r"], '', file_get_contents($path));
        if (str_contains($fileContent, $basicRoutePattern)) {
            $this->error("\n{$modelName} Route already exists.");
        } else {
            file_put_contents($path, $routeToCheck, FILE_APPEND);
            $this->info("\nRoute $modelName created.");
        }
    }

    protected function executeMigration($name, $fields, $module): string
    {
        $migration_service = new CrudOperationMigrationService();
        return $migration_service->makeMigration($name, $fields, $module);
    }

    protected function executeModel($name, $fields, $module): string
    {
        $model_service = new CrudOperationModelService();
        return $model_service->makeModel($name, $fields, $module);
    }

    protected function executeRequest($name, $fields, $module): string
    {
        $request_service = new CrudOperationRequestService();
        return $request_service->makeRequest($name, $fields, $module);
    }

    protected function executeResource($name, $fields, $module): string
    {
        $request_service = new CrudOperationResourceService();
        return $request_service->makeResource($name, $fields, $module);
    }

    protected function executeController($name, $model, $module): string
    {
        $name = Str::studly($name);
        $migration_service = new CrudOperationControllerService();
        return $migration_service->makeController($name, $model, $module);
    }

    protected function fileExists(string $path): bool
    {
        return file_exists($path);
    }

    protected function migrationExists($name, $module): bool
    {
        $migrationName = "*_create_" . $name . "_table.php";
        if ($module) {
            $migrationPath = base_path('Modules/' . $module . '/Database/migrations');
            $migrationFiles = glob("$migrationPath/$migrationName");
        } else {
            $migrationFiles = glob(database_path("migrations/$migrationName"));
        }

        return count($migrationFiles) > 0;
    }

    protected function modelExists($name, $module): bool
    {
        $modelName = $name . ".php";
        if ($module) {
            $modelPath = base_path('Modules/' . $module . '/App/Models');
            $modelFiles = glob("$modelPath/$modelName");
        } else {
            $modelFiles = glob(app_path("Models/$modelName"));
        }

        return count($modelFiles) > 0;
    }

    protected function requestExists($name, $module): bool
    {
        $requestName = $name . ".php";
        if ($module) {
            $requestPath = base_path('Modules/' . $module . '/App/Http/Requests');
            $requestFiles = glob("$requestPath/$requestName");
        } else {
            $requestFiles = glob(app_path("Http/Requests/$requestName"));
        }

        return count($requestFiles) > 0;
    }

    protected function resourceExists($name, $module): bool
    {
        $resourceName = $name . ".php";
        if ($module) {
            $resourcePath = base_path('Modules/' . $module . '/App/Http/Resources');
            $resourceFiles = glob("$resourcePath/$resourceName");
        } else {
            $resourceFiles = glob(app_path("Http/Resources/$resourceName"));
        }

        return count($resourceFiles) > 0;
    }

    protected function controllerExists($name, $module): bool
    {
        $controllerName = $name . ".php";
        if ($module) {
            $controllerPath = base_path('Modules/' . $module . '/App/Http/Controllers');
            $controllerFiles = glob("$controllerPath/$controllerName");
        } else {
            $controllerFiles = glob(app_path("Http/Controllers/$controllerName"));
        }

        return count($controllerFiles) > 0;
    }

    protected function info(string $line): void
    {
        echo $line;
    }

    protected function error(string $line): void
    {
        echo $line;
    }
}
