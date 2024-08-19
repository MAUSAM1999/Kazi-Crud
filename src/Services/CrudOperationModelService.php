<?php

namespace Kazi\Crud\Services;

use Illuminate\Support\Str;

class CrudOperationModelService
{
    public function makeModel($name, $fields, $module): string
    {
        $fillableFields = $this->generateFillableFields($fields);

        // Generate model content
        $modelContent = $this->generateModelContent($name, $fillableFields);

        // Save the model file
        $modelPath = app_path("Models/{$name}.php");
        file_put_contents($modelPath, $modelContent);

        return "Models/{$name}.php";
    }

    protected function generateFillableFields($fieldsArray): string
    {
        $fillableFields = [];

        foreach ($fieldsArray as $field) {
            $fillableFields[] = "'" . explode(':', $field)[0] . "'";
        }

        return implode(', ', $fillableFields);
    }

    protected function generateModelContent($name, $fillableFields): string
    {
        $className = Str::studly($name);
        $fillableFieldsCustom = explode(', ', $fillableFields);
        $columns = [];
        $form_fields = [];
        $filters = [];

        $columns[] = [
            'name' => 'sn',
            'label' => 'SN',
            'align' => "left",
            'type' => "text",
            'sortable' => true
        ];

        foreach ($fillableFieldsCustom as $custom) {
            $custom = str_replace("'", "", $custom);
            $columns[] = [
                'name' => strtolower($custom),
                'label' => ucwords($custom),
                'align' => "left",
                'type' => "text",
                'sortable' => true
            ];

            $form_fields[] = [
                'name' => strtolower($custom),
                'label' => ucwords($custom),
                'type' => 'text',
                'wrapper' => [
                    'class' => 'col-6'
                ],
                'rules' => [
                    'required' => true
                ]
            ];

            $filters[] = [
                'name' => strtolower($custom),
                'column' => strtolower($custom),
                'type' => 'text',
                'relation' => 'where',
                'dense' => true,
                'label' => ucwords($custom),
                'wrapper' => [
                    'class' => 'col-3'
                ]
            ];
        }

        $table = [
            'add_button' => true,
            'refresh_button' => true,
            'export_button' => true,
            'filter_button' => true,
        ];

        $string_fields = $fillableFields != "" ? $this->arrayToString($form_fields) : "[ ]";
        $string_filters = $fillableFields != "" ? $this->arrayToString($filters) : "[ ]";
        // Convert the $columns array to a string
        if ($fillableFields != "") {
            $string_columns = "[\n";
            foreach ($columns as $item) {
                $string_columns .= "    [\n";
                foreach ($item as $key => $value) {
                    $value_string = is_bool($value) ? ($value ? 'true' : 'false') : $value;
                    if (is_bool($value)) {
                        $string_columns .= "        '" . $key . "' => " . $value_string . ",\n";
                    } else {
                        $string_columns .= "        '" . $key . "' => '" . $value_string . "',\n";
                    }
                }
                $string_columns .= "    ],\n";
            }

            $string_columns .= "]";
            $fillableFields .= ", 'created_by', 'updated_by', 'extra'";
        } else {
            $string_columns = "[ ]";
            $fillableFields .= "'created_by', 'updated_by', 'extra'";
        }

        $string_table = "[\n";
        foreach ($table as $key => $value) {
            $value_string = $value ? 'true' : 'false';
            $string_table .= "    '" . $key . "' => " . $value_string . ",\n";
        }
        $string_table .= "]";

        return <<<EOT
   <?php

     namespace App\Models;

     use Illuminate\Database\Eloquent\Factories\HasFactory;
     use Illuminate\Database\Eloquent\Model;
     use Kazi\Crud\Traits\CrudModel;
     use Kazi\Crud\Traits\CrudEventListener;
     use \Illuminate\Database\Eloquent\SoftDeletes;
     use Illuminate\Database\Eloquent\Relations\BelongsTo;

     class $className extends Model
     {
          use HasFactory, CrudModel, SoftDeletes, CrudEventListener;

          const COLUMNS = $string_columns;
          const FIELDS = $string_fields;
          const TABLE = $string_table;
          const FILTERS = $string_filters;

          protected \$fillable = [$fillableFields];

           protected \$casts = [
               'extra' => 'array'
            ];
    }
   EOT;
    }

    public function arrayToString($array, $indentation = '    '): string
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
                $string .= $this->arrayToString($value, $indentation . '    ');
            } else {
                $string .= (is_bool($value) ? ($value ? 'true' : 'false') : "'$value'") . ",\n";
            }
        }
        $string .= $remove ? $indentation . "]\n" : $indentation . "],\n";
        return $string;
    }
}

