<?php

namespace Kazi\Crud\Services;

use Illuminate\Support\Str;

class CrudOperationResourceService
{
    public function makeResource($name, $fields): string
    {
        // Define the path for the resource
        $resourcePath = app_path('Http/Resources/' . $name . '.php');

        // Generate the resource content
        $resourceContent = $this->generateResourceContent($name, $fields);

        // Save the resource file
        file_put_contents($resourcePath, $resourceContent);

        return 'Http/Resources/' . $name . '.php';
    }

    protected function generateResourceContent($name, $fields): string
    {
        $className = Str::studly($name);

        // Prepare the fields array
        $fieldsArray = $this->generateFieldsArray($fields);
        if (empty($fieldsArray)) {
            $fieldsArray = '';
        } else {
            $fieldsArray = "$fieldsArray,";
        }

        return <<<EOT
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class $className extends JsonResource
{
    public function toArray(\$request)
    {
        return [
              $fieldsArray
            ];
    }
}
EOT;
    }

    protected function generateFieldsArray($fields): string
    {
        if (empty($fields)) {
            return '';
        }
        $fieldsArray = array_merge(['id'], $fields);
        $formattedFields = [];

        foreach ($fieldsArray as $field) {
            [$field] = explode(':', $field);
            $field = str_replace(' ', '', $field);
            $formattedFields[] = "'$field' => \$this->$field";
        }

        $formattedFields[] = "'created_by' => \$this->created_by";
        $formattedFields[] = "'extra' => \$this->extra";
        $formattedFields[] = "'edit_btn' => true";
        $formattedFields[] = "'delete_btn' => true";

        return implode(",\n            ", $formattedFields);
    }
}
