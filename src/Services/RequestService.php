<?php

namespace YajTech\Crud\Services;

use YajTech\Crud\Helper\FieldFormatHelper;
use YajTech\Crud\Helper\GlobalHelper;
use YajTech\Crud\Interfaces\RequestServiceInterface;
use Illuminate\Support\Str;
use YajTech\Crud\Traits\PathManager;

class RequestService implements RequestServiceInterface
{
    use PathManager;

    const type = 'request';

    /**
     * Create a request class for the specified request.
     *
     * @param string $name The name of the request.
     * @param string $type The type of request (e.g., CreateRequest, UpdateRequest).
     * @param array $fields The fields to include in the request.
     * @param string|null $module The module name, if applicable.
     * @return string The name of the created request.
     */
    public function createRequest(string $name, string $type, array $fields, ?string $module): string
    {
        $name = Str::studly($name) . $type;
        $requestPath = $this->getFullPath("$name.php", $module, $this::type);

        // Generate the request content using the stub
        $stubPath = $this->getStubPath($this::type);
        $requestContent = GlobalHelper::generateFromStub($stubPath, [
            'namespace' => $this->getNamespace($this::type, $module),
            'className' => Str::studly($name),
            'rulesArray' => FieldFormatHelper::generateRequestRules($fields)
        ]);

        file_put_contents($requestPath, $requestContent);

        return $requestPath;
    }

    /**
     * Check if a request class already exists.
     *
     * @param string $name The name of the request.
     * @param string|null $module The module name, if applicable.
     * @param string $type The type, if applicable.
     * @return bool True if the request exists, false otherwise.
     */
    public function requestExists(string $name, ?string $module, string $type): bool
    {
        $requestName = Str::studly($name) . $type . ".php";
        $requestPath = $this->getPath($this::type, $module);

        return count(glob("$requestPath/$requestName")) > 0;
    }
}
