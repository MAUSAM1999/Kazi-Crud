<?php

namespace Kazi\Crud\Traits;

use Kazi\Crud\Helper\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

trait Super
{
    public function getIndexCollection()
    {
        $model = $this->model::initializer()
            ->when(property_exists($this, 'withAll'), fn($query) => $query->with($this->withAll))
            ->when(property_exists($this, 'withCount'), fn($query) => $query->withCount($this->withCount))
            ->when(property_exists($this, 'withAggregate'), fn($query) => $this->applyWithAggregate($query))
            ->when(property_exists($this, 'scopes'), fn($query) => $this->applyScopes($query))
            ->when(property_exists($this, 'scopeWithValue'), fn($query) => $this->applyScopesWithValue($query));

        $resource = $this->resource;
        if (property_exists($this, 'listResource')) {
            $resource = $this->listResource;
        }

        return $resource::collection($model->paginates());
    }

    public function applyWithAggregate($query): void
    {
        foreach ($this->withAggregate as $key => $value) {
            $query->withAggregate($key, $value);
        }
    }

    public function applyScopes($query): void
    {
        foreach ($this->scopes as $value) {
            $query->$value();
        }
    }

    public function applyScopesWithValue($query): void
    {
        foreach ($this->scopeWithValue as $key => $value) {
            $query->$key($value);
        }
    }

    public function checkFillable($model, $columns): bool
    {
        $fillableColumns = $this->fillableColumn($model);

        $diff = array_diff($columns, $fillableColumns);

        return !(count($diff) > 0);
    }

    public function getResourceObject($class, $builder, $additional = [])
    {
        try {
            return $class::make($builder)->additional($additional);
        } catch (Throwable $exception) {
            return ApiResponse::onException($exception);
        }
    }

    public function fillableColumn($model): array
    {
        return Schema::getColumnListing($this->tableName($model));
    }

    public function tableName($model): string
    {
        return $model->getTable();
    }

    public function deletedAtString(): string
    {
        return '_deleted_' . Str::slug(Carbon::now()->toDateTimeString(), '_');
    }
}
