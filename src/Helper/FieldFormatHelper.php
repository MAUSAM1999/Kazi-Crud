<?php

namespace YajTech\Crud\Helper;

use ReflectionException;

class FieldFormatHelper
{
    public static function removeGlobalDefaultFields(array $fields): array
    {
        return array_filter($fields, function ($field) {
            return !in_array(explode(':', $field)[0], ["id", "created_by", "updated_by", 'extra', 'deleted_at']);
        });
    }

    /**
     * Generate the fields for the migration.
     *
     * @param array $fieldsArray The array of fields with their types.
     * @return string The generated fields as a string to be inserted into the migration.
     */
    public static function generateMigrationFields(array $fieldsArray): string
    {
        $fields = '';

        foreach ($fieldsArray as $field) {
            [$fieldName, $fieldType] = explode(':', $field);
            $fields .= "\$table->{$fieldType}('{$fieldName}');\n            ";
        }

        return $fields;
    }

    /**
     * Generate the fillable fields string for the model.
     *
     * @param array $fieldsArray The fields to include in the model.
     * @return string The fillable fields string.
     */
    public static function generateModelFillableFields(array $fieldsArray): string
    {
        $fillableFields = array_map(function ($field) {
            return "'" . explode(':', $field)[0] . "'";
        }, $fieldsArray);

        return implode(', ', $fillableFields);
    }

    /**
     * Generate the columns array for model class.
     *
     * @param array $fillableFieldsCustom The custom fillable fields.
     * @return array The columns array.
     */
    public static function generateModelColumns(array $fillableFieldsCustom): array
    {
        $columns = [
            [
                'name' => 'sn',
                'label' => 'SN',
                'align' => 'left',
                'type' => 'text',
                'sortable' => true,
            ]
        ];

        foreach ($fillableFieldsCustom as $field) {
            $columns[] = [
                'name' => strtolower($field),
                'label' => ucwords($field),
                'align' => 'left',
                'type' => 'text',
                'sortable' => true,
            ];
        }

        return $columns;
    }

    /**
     * Generate the form fields array for model class.
     *
     * @param array $fillableFieldsCustom The custom fillable fields.
     * @return array The form fields array.
     */
    public static function generateModelFormFields(array $fillableFieldsCustom): array
    {
        $formFields = [];

        foreach ($fillableFieldsCustom as $field) {
            $formFields[] = [
                'name' => strtolower($field),
                'label' => ucwords($field),
                'type' => 'text',
                'wrapper' => [
                    'class' => 'col-6',
                ],
                'rules' => [
                    'required' => true,
                ],
            ];
        }

        return $formFields;
    }

    /**
     * Generate the filters array for model class.
     *
     * @param array $fillableFieldsCustom The custom fillable fields.
     * @return array The filters array.
     */
    public static function generateModelFilters(array $fillableFieldsCustom): array
    {
        $filters = [];

        foreach ($fillableFieldsCustom as $field) {
            $filters[] = [
                'name' => strtolower($field),
                'columns' => strtolower($field),
                'type' => 'text',
                'relation' => 'where',
                'dense' => true,
                'label' => ucwords($field),
                'wrapper' => [
                    'class' => 'col-3',
                ],
            ];
        }

        return $filters;
    }

    /**
     * Convert an array to a formatted string.
     *
     * @param array $array The array to convert.
     * @param string $indentation The indentation for nested arrays.
     * @return string The formatted string.
     */
    public static function arrayToStringFroModelFormat(array $array, string $indentation = '    '): string
    {
        $string = "[\n";
        $remove = false;
        foreach ($array as $key => $value) {
            if (is_int($key)) {
                $remove = true;
            } else {
                $string .= $indentation . "'" . $key . "' => ";
            }
            if (is_array($value)) {
                $string .= self::arrayToStringFroModelFormat($value, $indentation . '    ');
            } else {
                $string .= (is_bool($value) ? ($value ? 'true' : 'false') : "'$value'") . ",\n";
            }
        }
        $string .= $remove ? $indentation . "]\n" : $indentation . "],\n";

        return $string;
    }

    /**
     * Format the fields into validation rules for crud request.
     *
     * @param array $fields The fields to format.
     * @return string The formatted rules.
     */
    public static function generateRequestRules(array $fields): string
    {
        $formattedRules = array_map(function ($field) {
            [$fieldName] = explode(':', $field);
            $fieldName = str_replace(' ', '', $fieldName);
            return "'$fieldName' => 'required'";
        }, $fields);

        return implode(",\n            ", $formattedRules);
    }

    /**
     * Format the fields into an array for the resource.
     *
     * @param array $fields The fields to format.
     * @param string $type define type of resource.
     * @return string The formatted fields.
     */
    public static function generateResourceFieldsArray(array $fields, string $type): string
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

        if ($type == 'DetailResource') {
            $formattedFields[] = "'edit_btn' => true";
            $formattedFields[] = "'delete_btn' => true";
        }


        return implode(",\n            ", $formattedFields);
    }
}
