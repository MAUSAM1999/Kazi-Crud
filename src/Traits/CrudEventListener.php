<?php

namespace YajTech\Crud\Traits;

use Illuminate\Support\Facades\DB;
use Plank\Mediable\Media;

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

        static::created(function ($model) {
            if (request()->has('upload')) {
                $tag = 'upload_' . strtolower(class_basename($model));
                $upload_request = request()->get('upload');
                $media = Media::findOrFail($upload_request['id'] ?? $upload_request);
                $model->syncMedia($media, [$tag]);
            }

            if (request()->has('upload_multiple') && count(request()->get('upload_multiple')) > 0) {
                $tag = 'upload_multiple_' . strtolower(class_basename($model));
                foreach (request()->get('upload_multiple') as $upload) {
                    $media = Media::findOrFail($upload['id'] ?? $upload);
                    $model->attachMedia($media, [$tag]);
                }
            }
        });

        static::updated(function ($model) {

            if (request()->has('upload')) {
                $tag = 'upload_' . strtolower(class_basename($model));
                $upload_request = request()->get('upload');
                $media = Media::findOrFail($upload_request['id'] ?? $upload_request);
                $model->syncMedia($media, [$tag]);
            }

            if (request()->has('upload_multiple') && count(request()->get('upload_multiple')) > 0) {
                $tag = 'upload_multiple_' . strtolower(class_basename($model));
                $oldMedias = $model->getMedia($tag);
                foreach ($oldMedias as $oldMedia) {
                    $model->detachMedia($oldMedia->id);
                }

                foreach (request()->get('upload_multiple') as $upload) {
                    $media = Media::findOrFail($upload['id'] ?? $upload);
                    $model->attachMedia($media, [$tag]);
                }
            }
        });
    }
}
