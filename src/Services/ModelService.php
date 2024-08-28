<?php

namespace YajTech\Crud\Services;

use YajTech\Crud\Helper\FieldFormatHelper;
use YajTech\Crud\Helper\GlobalHelper;
use YajTech\Crud\Interfaces\ModelServiceInterface;
use Illuminate\Support\Str;
use YajTech\Crud\Traits\PathManager;

class ModelService implements ModelServiceInterface
{
    use PathManager;

    const type = 'model';

    /**
     * Create a model with the specified fields.
     *
     * @param string $name The name of the model.
     * @param array $fields The fields to include in the model.
     * @param string|null $module The module name, if applicable.
     * @return string The name of the created model.
     */
    public function createModel(string $name, array $fields, ?string $module): string
    {
        $name = Str::studly($name);
        $namespace = $this->getNamespace('model', $module);
        $fillableFields = FieldFormatHelper::generateModelFillableFields($fields);

        // Determine the model file path
        $fileName = "{$name}.php";
        $modelPath = $this->getFullPath($fileName, $module, $this::type);

        // Generate model content and save the file
        $modelContent = $this->generateModelContent($name, $fillableFields, $namespace);

        file_put_contents($modelPath, $modelContent);

        return $modelPath;
    }

    /**
     * Generate the model content.
     *
     * @param string $name The name of the model.
     * @param string $fillableFields The fillable fields string.
     * @param string $namespace The namespace of the model.
     * @return string The model content.
     */
    protected function generateModelContent(string $name, string $fillableFields, string $namespace): string
    {
        $className = Str::studly($name);
        $fillableFieldsCustom = array_map(function ($field) {
            return str_replace("'", "", $field);
        }, explode(', ', $fillableFields));

        $columns = FieldFormatHelper::generateModelColumns($fillableFieldsCustom);
        $formFields = FieldFormatHelper::generateModelFormFields($fillableFieldsCustom);
        $filters = FieldFormatHelper::generateModelFilters($fillableFieldsCustom);
        $fillableFields .= ($fillableFields == "" ? '' : ", ") . "'created_by', 'updated_by', 'extra'";
        $stubPath = $this->getStubPath($this::type);

        // Generate the model file content
        return GlobalHelper::generateFromStub($stubPath, [
            'namespace' => $namespace,
            'className' => $className,
            'stringColumns' => $fillableFields ? FieldFormatHelper::arrayToStringFroModelFormat($columns) : "[ ]",
            'stringFields' => $fillableFields ? FieldFormatHelper::arrayToStringFroModelFormat($formFields) : "[ ]",
            'stringFilters' => $fillableFields ? FieldFormatHelper::arrayToStringFroModelFormat($filters) : "[ ]",
            'fillableFields' => $fillableFields
        ]);
    }

    /**
     * Check if a model already exists.
     *
     * @param string $name The name of the model.
     * @param string|null $module The module name, if applicable.
     * @return bool True if the model exists, false otherwise.
     */
    public function modelExists(string $name, ?string $module): bool
    {
        $modelName = Str::studly($name) . ".php";
        $modelPath = $this->getPath($this::type, $module);

        return count(glob("$modelPath/$modelName")) > 0;
    }
}


