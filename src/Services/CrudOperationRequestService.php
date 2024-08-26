<?php

namespace YajTech\Crud\Services;

use Illuminate\Support\Str;

class CrudOperationRequestService
{
    public function makeRequest($name, $fields, $module): string
    {
        $fields = implode(', ', $fields);

        // Save the model file
        $file_name = "{$name}.php";
        if ($module) {
            $requestPath = base_path('Modules/' . $module . '/App/Http/Requests');
            // Check if the directory exists
            if (!is_dir($requestPath)) {
                mkdir($requestPath, 0777, true);
            }

            // Generate the request class content
            $requestContent = $this->generateRequestContent($name, $fields, "Modules\\$module\\App\\Http\\Requests");
            $requestPath = $requestPath . '/' . $file_name;
        } else {
            // Define the path for the request class
            $directory = app_path('Http/Requests');

            // Check if the directory exists, create it if not
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true); // Create directory with permissions
            }

            // Generate the request class content
            $requestContent = $this->generateRequestContent($name, $fields, 'App\\Http\\Requests');
            $requestPath = app_path("Http/Requests/" . $file_name);
        }

        file_put_contents($requestPath, $requestContent);

        return $requestPath;
    }

    protected function generateRequestContent($name, $fields, $namespace): string
    {
        $className = Str::studly($name);

        // Prepare validation rules
        $rulesArray = $this->formatRules($fields);

        return <<<EOT
<?php

namespace $namespace;

use Illuminate\Foundation\Http\FormRequest;

class $className extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            $rulesArray
        ];
    }
}
EOT;
    }

    protected function formatRules($rules): string
    {
        $rulesArray = explode(',', $rules);
        $formattedRules = [];

        if (count($rulesArray) > 0) {
            foreach ($rulesArray as $rule) {
                [$field] = explode(':', $rule);
                $field = str_replace(' ', '', $field);
                if ($field) {
                    $formattedRules[] = "'$field' => 'required'";
                }

            }
        }

        return implode(",\n            ", $formattedRules);
    }
}
