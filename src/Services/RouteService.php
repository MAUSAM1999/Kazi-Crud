<?php

namespace YajTech\Crud\Services;

use YajTech\Crud\Interfaces\RouteServiceInterface;
use Illuminate\Support\Str;
use YajTech\Crud\Traits\PathManager;

class RouteService implements RouteServiceInterface
{
    use PathManager;

    const type = 'route';

    /**
     * Create routes for the specified model.
     *
     * @param string $name The name of the model.
     * @param string|null $module The module name, if applicable.
     * @param mixed|null $methods The HTTP methods for the routes.
     */
    public function createRoute(string $name, ?string $module, mixed $methods = null): string
    {
        $modelName = Str::lower($name);
        $path = $this->getFullPath('api.php', $module, $this::type);
        $controllerPath = $this->getNamespace('controller', $module, Str::studly($name) . 'Controller');
        $methods = $methods ? "['" . implode("', '", explode(',', $methods)) . "']" : '';

        // Prepare the route definition.
        if ($methods) {
            $methods = "['" . implode("', '", explode(',', $methods)) . "']";
            $routeToCheck = "\nYajTech\Crud\Helper\CrudRoute::generateRoutes('$modelName', " . $controllerPath . "::class, " . $methods . ");";
        } else {
            $routeToCheck = "\nYajTech\Crud\Helper\CrudRoute::generateRoutes('$modelName', " . $controllerPath . "::class);";
        }

        // Create the routes file if it doesn't exist.
        if (!file_exists($path)) {
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }
            file_put_contents($path, "<?php\n\n");
        }

        // Append the route if it doesn't already exist in the file.
        if (!str_contains(file_get_contents($path), $routeToCheck)) {
            file_put_contents($path, $routeToCheck, FILE_APPEND);
        }

        return $path;
    }

    /**
     * Check if a route already exists.
     *
     * @param string $name The name of the route.
     * @param string|null $module The module name, if applicable.
     * @return bool True if the controller exists, false otherwise.
     */
    public function routeExists(string $name, ?string $module): bool
    {
        $name = Str::lower($name);
        $controllerPath = $this->getNamespace('controller', $module, Str::studly($name) . 'Controller');
        $path = $this->getFullPath('api.php', $module, $this::type);

        $basicRoutePattern = "YajTech\Crud\Helper\CrudRoute::generateRoutes('$name', " . $controllerPath . "::class";
        $fileContent = str_replace(["\n", "\r"], '', file_get_contents($path));

        return str_contains($fileContent, $basicRoutePattern);
    }
}
