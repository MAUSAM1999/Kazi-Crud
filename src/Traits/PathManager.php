<?php

namespace YajTech\Crud\Traits;

use Illuminate\Support\Str;

trait PathManager
{
    /**
     * Get the path based on the presence of a module.
     *
     * @param string $type
     * @param string|null $module
     * @return string
     */
    protected function getPath(string $type, string $module = null): string
    {
        $baseModulePath = "Modules/$module/";

        $paths = [
            'controller' => $module ? base_path("{$baseModulePath}App/Http/Controllers") : app_path("Http/Controllers"),
            'model' => $module ? base_path("{$baseModulePath}App/Models") : app_path("Models"),
            'migration' => $module ? base_path("{$baseModulePath}Database/migrations") : database_path("migrations"),
            'request' => $module ? base_path("{$baseModulePath}App/Http/Requests") : app_path("Http/Requests"),
            'resource' => $module ? base_path("{$baseModulePath}App/Http/Resources") : app_path("Http/Resources"),
            'route' => $module ? base_path("{$baseModulePath}routes") : base_path('routes'),
        ];

        return $paths[$type] ?? '';
    }

    /**
     * Get the namespace based on the presence of a module.
     *
     * @param string $type
     * @param string|null $module
     * @param string|null $name
     * @return string
     */
    protected function getNamespace(string $type, string $module = null, string $name = null): string
    {
        $baseModuleNamespace = $module ? "Modules\\$module\\App\\" : "App\\";

        // Define the base namespaces for each type
        $namespaces = [
            'controller' => $baseModuleNamespace . ($name ? "Http\\Controllers\\$name" : "Http\\Controllers"),
            'model' => $baseModuleNamespace . ($name ? "Models\\$name" : "Models"),
            'request' => $baseModuleNamespace . ($name ? "Http\\Requests\\$name" : "Http\\Requests"),
            'resource' => $baseModuleNamespace . ($name ? "Http\\Resources\\$name" : "Http\\Resources"),
        ];

        return $namespaces[$type] ?? '';
    }


    /**
     * Get the path to the stub file.
     * @param string $type
     * @return string
     */
    protected function getStubPath(string $type): string
    {
        return __DIR__ . "/../Stubs/$type.stub";
    }

    /**
     * Get the full path
     *
     * @param string $modelName
     * @param string|null $module The module name, if applicable.
     * @param string $type
     * @return string
     */
    protected function getFullPath(string $modelName, ?string $module, string $type): string
    {
        // Use PathHelper to get the path for the controller
        $directory = static::getPath($type, $module);

        // Ensure the migration directory exists
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return "$directory/$modelName";
    }

    /**
     * Get the Base name only
     *
     * @param string $path
     * @return string
     */
    public static function getBaseName(string $path): string
    {
        return basename(str_replace('\\', '/', $path));
    }
}
