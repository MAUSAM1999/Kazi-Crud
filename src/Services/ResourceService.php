<?php

namespace YajTech\Crud\Services;

use YajTech\Crud\Helper\FieldFormatHelper;
use YajTech\Crud\Helper\GlobalHelper;
use YajTech\Crud\Interfaces\ResourceServiceInterface;
use Illuminate\Support\Str;
use YajTech\Crud\Traits\PathManager;

class ResourceService implements ResourceServiceInterface
{
    use PathManager;

    const type = 'resource';

    /**
     * Create a resource class for the specified model.
     *
     * @param string $name The name of the model.
     * @param string $type The type of resource (e.g., Resource, Collection).
     * @param array $fields The fields to include in the resource.
     * @param string|null $module The module name, if applicable.
     * @return string The name of the created resource.
     */
    public function createResource(string $name, string $type, array $fields, ?string $module): string
    {
        $name = Str::studly($name) . $type;
        $resourcePath = $this->getFullPath("$name.php", $module, $this::type);

        // Generate the request content using the stub
        $stubPath = $this->getStubPath($this::type);
        $resourceContent = GlobalHelper::generateFromStub($stubPath, [
            'namespace' => $this->getNamespace($this::type, $module),
            'className' => Str::studly($name),
            'fieldsArray' => FieldFormatHelper::generateResourceFieldsArray($fields, $type)
        ]);

        file_put_contents($resourcePath, $resourceContent);

        return $resourcePath;
    }

    /**
     * Check if a resource class already exists.
     *
     * @param string $name The name of the resource.
     * @param string|null $module The module name, if applicable.
     * @param string $type The type, if applicable.
     * @return bool True if the resource exists, false otherwise.
     */
    public function resourceExists(string $name, ?string $module, string $type): bool
    {
        $resourceName = Str::studly($name) . $type . ".php";
        $resourcePath = $this->getPath($this::type, $module);

        return count(glob("$resourcePath/$resourceName")) > 0;
    }
}
