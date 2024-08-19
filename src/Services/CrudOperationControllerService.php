<?php

namespace Kazi\Crud\Services;

use Illuminate\Support\Str;

class CrudOperationControllerService
{
    public function makeController($name, $modelName): string
    {
        $model = "App\\Models\\{$name}";
        $list_resource = "App\\Http\\Resources\\{$name}ListResource";
        $detail_resource = "App\\Http\\Resources\\{$name}DetailResource";
        $create_request = "App\\Http\\Requests\\{$name}CreateRequest";
        $update_request = "App\\Http\\Requests\\{$name}UpdateRequest";
        $controllerPath = app_path('Http/Controllers/' . $modelName . '.php');

        // Generate the controller content
        $controllerContent = $this->generateControllerContent($modelName, $model, $list_resource, $detail_resource, $create_request, $update_request);

        // Save the controller file
        file_put_contents($controllerPath, $controllerContent);

        return 'Http/Controllers/' . $modelName . '.php';
    }

    protected function generateControllerContent($name, $model, $list_resource, $detail_resource, $create_request, $update_request): string
    {
        $className = Str::studly($name);
        $parsed_model = basename(str_replace('\\', '/', $model));
        $parsed_list_resource = basename(str_replace('\\', '/', $list_resource));
        $parsed_detail_resource = basename(str_replace('\\', '/', $detail_resource));
        $parsed_create_request = basename(str_replace('\\', '/', $create_request));
        $parsed_update_request = basename(str_replace('\\', '/', $update_request));

        return <<<EOT
<?php

namespace App\Http\Controllers;

use Kazi\Crud\Controllers\CrudController;
use $model;
use $list_resource;
use $detail_resource;
use $create_request;
use $update_request;

class $className extends CrudController
{
    public function __construct()
    {
        parent::__construct(
            $parsed_model::class,
            $parsed_detail_resource::class,
            $parsed_list_resource::class,
            $parsed_create_request::class,
            $parsed_update_request::class,
        );
    }
}
EOT;
    }
}
