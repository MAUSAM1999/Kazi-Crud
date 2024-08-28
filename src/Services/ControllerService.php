<?php

namespace YajTech\Crud\Services;

use YajTech\Crud\Helper\GlobalHelper;
use YajTech\Crud\Interfaces\ControllerServiceInterface;
use Illuminate\Support\Str;
use YajTech\Crud\Traits\PathManager;

class ControllerService implements ControllerServiceInterface
{
    use PathManager;

    const type = 'controller';

    /**
     * Create a controller for the specified model.
     *
     * @param string $name The name of the model.
     * @param string|null $module The module name, if applicable.
     * @return string The name of the created controller.
     */
    public function createController(string $name, ?string $module): string
    {
        $name = Str::studly($name);
        $modelName = $name . 'Controller';

        // Get the namespace and controller path using the PathHelper
        $controllerPath = $this->getFullPath("$modelName.php", $module, $this::type);

        // Get fully qualified names for the model, resources, and requests
        $model = $this->getNamespace('model', $module, $name);
        $listResource = $this->getNamespace('resource', $module, $name . 'ListResource');
        $detailResource = $this->getNamespace('resource', $module, $name . 'DetailResource');
        $createRequest = $this->getNamespace('request', $module, $name . 'CreateRequest');
        $updateRequest = $this->getNamespace('request', $module, $name . 'UpdateRequest');

        // Generate the controller content using the stub
        $stubPath = $this->getStubPath($this::type);
        $controllerContent = GlobalHelper::generateFromStub($stubPath, [
            'namespace' => $this->getNamespace($this::type, $module),
            'model' => $model,
            'modelParsed' => $this->getBaseName($model),
            'listResource' => $listResource,
            'listResourceParsed' => $this->getBaseName($listResource),
            'detailResource' => $detailResource,
            'detailResourceParsed' => $this->getBaseName($detailResource),
            'createRequest' => $createRequest,
            'createRequestParsed' => $this->getBaseName($createRequest),
            'updateRequest' => $updateRequest,
            'updateRequestParsed' => $this->getBaseName($updateRequest),
            'className' => Str::studly($modelName),
        ]);

        file_put_contents($controllerPath, $controllerContent);

        return $controllerPath;
    }

    /**
     * Check if a controller already exists.
     *
     * @param string $name The name of the controller.
     * @param string|null $module The module name, if applicable.
     * @return bool True if the controller exists, false otherwise.
     */
    public function controllerExists(string $name, ?string $module): bool
    {
        $controllerName = Str::studly($name) . 'Controller.php';
        $controllerPath = $this->getPath($this::type, $module);

        return count(glob("$controllerPath/$controllerName")) > 0;
    }
}
