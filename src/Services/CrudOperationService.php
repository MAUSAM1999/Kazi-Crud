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
        if ($this->fileExists(app_path("Models/{$modelName}.php"))) {
            $this->error("\nA model named '{$modelName}' already exists.");
        } else {
            $model = $this->executeModel($modelName, $fields, $module);
            $this->info("\nModel $model Created");
        }
    }

    public function handleCreateRequest(string $name, string $pluralModel, array $fields, $methods): void
    {
        $this->info("\n\nCreating create request...");
        $this->handleRequest($name, 'CreateRequest', $fields);
    }

    public function handleUpdateRequest(string $name, string $pluralModel, array $fields, $methods): void
    {
        $this->info("\n\nCreating update request...");
        $this->handleRequest($name, 'UpdateRequest', $fields);
    }

    protected function handleRequest(string $name, string $type, array $fields): void
    {
        $requestName = Str::studly($name) . $type;
        if ($this->fileExists(app_path("Http/Requests/{$requestName}.php"))) {
            $this->error("\nA request class named '{$requestName}' already exists.");
        } else {
            $model = $this->executeRequest($requestName, $fields);
            $this->info("\n$type Request $model Created");
        }
    }

    public function handleDetailResource(string $name, string $pluralModel, array $fields, $methods): void
    {
        $this->info("\n\nCreating detail resources...");
        $this->handleResource($name, 'DetailResource', $fields);
    }

    public function handleListResource(string $name, string $pluralModel, array $fields, $methods): void
    {
        $this->info("\n\nCreating list resources...");
        $this->handleResource($name, 'ListResource', $fields);
    }

    protected function handleResource(string $name, string $type, array $fields): void
    {
        $resourceName = Str::studly($name) . $type;
        if ($this->fileExists(app_path("Http/Resources/{$resourceName}.php"))) {
            $this->error("\nA {$type} named '{$resourceName}' already exists.");
        } else {
            $model = $this->executeResource($resourceName, $fields);
            $this->info("\n{$type} $model Created");
        }
    }

    public function handleController(string $name, string $pluralModel, array $fields, $methods): void
    {
        $this->info("\n\nCreating controller...");
        $controllerName = Str::studly($name) . 'Controller';
        if ($this->fileExists(app_path("Http/Controllers/{$controllerName}.php"))) {
            $this->error("\nA controller named '{$controllerName}' already exists.");
        } else {
            $model = $this->executeController($name, $controllerName);
            $this->info("\nController $model Created");
        }
    }

    public function handleRoute(string $name, string $pluralModel, array $fields, $methods): void
    {
        $this->info("\n\nCreating route...");
        $modelName = Str::lower($name);
        $path = base_path('routes/api.php');
        $controllerName = Str::studly($name) . 'Controller';
        $controllerPath = 'App\\Http\\Controllers\\' . $controllerName;
        $basicRoutePattern = "Kazi\Crud\Helper\CrudRoute::generateRoutes('$modelName', " . $controllerPath . "::class";

        if ($methods) {
            $methods = "['" . implode("', '", explode(',', $methods)) . "']";
            $routeToCheck = "\nKazi\Crud\Helper\CrudRoute::generateRoutes('$modelName', " . $controllerPath . "::class, " . $methods . ");";
        } else {
            $routeToCheck = "\nKazi\Crud\Helper\CrudRoute::generateRoutes('$modelName', " . $controllerPath . "::class);";
        }

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

    protected function executeRequest($name, $fields): string
    {
        // Define the path for the request class
        $directory = app_path('Http/Requests');

        // Check if the directory exists, create it if not
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true); // Create directory with permissions
        }

        $request_service = new CrudOperationRequestService();
        return $request_service->makeRequest($name, $fields);
    }

    protected function executeResource($name, $fields): string
    {
        // Define the path for the request class
        $directory = app_path('Http/Resources');

        // Check if the directory exists, create it if not
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true); // Create directory with permissions
        }

        $request_service = new CrudOperationResourceService();
        return $request_service->makeResource($name, $fields);
    }

    protected function executeController($name, $model): string
    {
        $name = Str::studly($name);
        $migration_service = new CrudOperationControllerService();
        return $migration_service->makeController($name, $model);
    }

    protected function fileExists(string $path): bool
    {
        return file_exists($path);
    }

    protected function migrationExists($name, $module): bool
    {
        $migrationName = "*_create_" . $name . "_table.php";
        if ($module) {
            $migrationPath = base_path('Modules/' . $module . '/Database/migrations/tenant');
            $migrationFiles = glob("$migrationPath/$migrationName");
        } else {
            $migrationFiles = glob(database_path("migrations/$migrationName"));
        }

        return count($migrationFiles) > 0;
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
