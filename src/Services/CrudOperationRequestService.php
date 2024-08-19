<?php

namespace Kazi\Crud\Services;

use Illuminate\Support\Str;

class CrudOperationRequestService
{
    public function makeRequest($name, $fields): string
    {
        $fields = implode(', ', $fields);
        // Generate the request class content
        $requestContent = $this->generateRequestContent($name, $fields);

        // Save the request file
        $requestPath = app_path("Http/Requests/{$name}.php");
        file_put_contents($requestPath, $requestContent);


        return "Http/Requests/{$name}.php";
    }

    protected function generateRequestContent($name, $fields): string
    {
        $className = Str::studly($name);

        // Prepare validation rules
        $rulesArray = $this->formatRules($fields);

        return <<<EOT
<?php

namespace App\Http\Requests;

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
