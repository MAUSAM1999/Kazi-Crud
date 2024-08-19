<?php

namespace Kazi\Crud\Traits;

use Illuminate\Support\Facades\DB;

trait CrudEventListener
{
    public static function bootCrudEventListener(): void
    {
        static::deleting(function ($model) {
            $table = $model->getTable();

            if ($table != 'media') {
                $columns = DB::select("SHOW INDEXES FROM $table WHERE NOT Non_unique and Key_Name <> 'PRIMARY'");

                foreach ($columns as $column) {
                    $columnName = $column->Column_name;
                    $newValue = $model->getAttribute($columnName) . '_' . time();
                    $model->setAttribute($columnName, $newValue);
                }

                $model->save();
            }
        });

        static::creating(function ($model) {
            $columns = $model->getFillable();

            if (in_array('created_by', $columns) && !request()->has('created_by')) {
                $userId = auth()?->id() ?? null;
                $model->created_by = $userId;
                $model->updated_by = $userId;
            }
        });

        static::updating(function ($model) {
            if (request()->has('updated_by')) {
                $model->updated_by = auth()?->id() ?? null;
            }
        });
    }
}
