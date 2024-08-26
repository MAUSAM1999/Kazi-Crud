<?php

namespace YajTech\Crud\Services;

use Illuminate\Support\Str;

class CrudOperationResourceService
{
    public function makeResource($name, $fields, $module): string
    {
        // Save the model file
        $file_name = "{$name}.php";
        if ($module) {
            $resourcePath = base_path('Modules/' . $module . '/App/Http/Resources');
            // Check if the directory exists
            if (!is_dir($resourcePath)) {
                mkdir($resourcePath, 0777, true);
            }
            // Generate the request class content
            $resourceContent = $this->generateResourceContent($name, $fields, "Modules\\$module\\App\\Http\\Resources");
            $resourcePath = $resourcePath . '/' . $file_name;
        } else {
            // Define the path for the request class
            $directory = app_path('Http/Resources');

            // Check if the directory exists, create it if not
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true); // Create directory with permissions
            }

            // Define the path for the resource
            // Generate the resource content
            $resourceContent = $this->generateResourceContent($name, $fields, 'App\\Http\\Resources');
            $resourcePath = app_path('Http/Resources/' . $name . '.php');

        }

        // Save the resource file
        file_put_contents($resourcePath, $resourceContent);

        return $resourcePath;
    }

    protected function generateResourceContent($name, $fields, $namespace): string
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

namespace $namespace;

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
