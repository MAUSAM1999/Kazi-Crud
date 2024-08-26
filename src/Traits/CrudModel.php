<?php

namespace YajTech\Crud\Traits;

use Illuminate\Support\Str;

trait CrudModel
{
    public static function initializer($initializeModel = 'initializeModel')
    {
        $request = request();
        $sortBy = $request->sortBy;
        $desc = $request->descending === 'true';
        $filters = json_decode($request->query('filters', ''), true);

        $model = method_exists(static::class, $initializeModel) ? static::$initializeModel() : static::query();

        if ($filters && count($filters) > 0) {
            foreach ($filters as $filter => $value) {
                if ($value !== null) {
                    $model = static::applyFilter($model, $filter, $value);
                }
            }
        }

        return $sortBy && $sortBy !== 'null'
            ? $model->orderBy($sortBy, $desc ? 'desc' : 'asc')
            : $model->latest();
    }

    public static function EXPORT_HEADER_MAPPINGS()
    {
        return (new static)->getFillable();
    }

    public static function EXPORT_MAPPINGS(): \Closure
    {
        return function ($model) {
            return array_map(function ($field) use ($model) {
                return ucfirst($model->{$field});
            }, $model->getFillable());
        };
    }

    public static function applyFilter($model, $filter, $value)
    {
        $availableFilters = collect(static::FILTERS)->where('name', $filter)->first();

        if ($availableFilters) {
            return static::typeWiseFilter($model, $availableFilters, $value);
        }

        $method = ucfirst(Str::camel($filter));

        if (method_exists(static::class, 'scope' . $method)) {
            $model->{$method}($value);
        } elseif (method_exists($model, $filter)) {
            $model->{$filter}($value);
        }

        return $model;
    }

    public static function typeWiseFilter($model, $filter, $value)
    {
        if (isset($filter['relation']) && $filter['relation']) {
            $column = $filter['column'] ?? null;
            $query = $filter['query'] ?? null;

            return match ($filter['relation']) {
                'scope' => static::applyScopeFilter($model, $filter['name'], $value),
                'query' => static::applyQueryFilter($model, $column, $value),
                'where' => $model->where($column, $value),
                'whereIn' => is_array($value) ? $model->whereIn($column, $value) : $model,
                'whereHas:where' => static::applyWhereHasFilter($model, $query, $column, $value),
                'whereHas:whereIn' => static::applyWhereHasInFilter($model, $query, $column, $value),
                default => $model,
            };
        }

        return $model;
    }

    protected static function applyScopeFilter($model, $scope, $value)
    {
        $method = ucfirst(Str::camel($scope));

        if (method_exists(static::class, 'scope' . $method)) {
            return $model->{$method}($value);
        }

        return method_exists($model, $scope) ? $model->{$scope}($value) : $model;
    }

    protected static function applyQueryFilter($model, $columns, $value)
    {
        if (is_array($columns)) {
            return $model->where(function ($query) use ($columns, $value) {
                foreach ($columns as $index => $column) {
                    $query->{$index === 0 ? 'where' : 'orWhere'}($column, 'LIKE', '%' . $value . '%');
                }
            });
        }

        return $model->where($columns, 'LIKE', '%' . $value . '%');
    }

    protected static function applyWhereHasFilter($model, $query, $column, $value)
    {
        return $model->whereHas($query, function ($q) use ($column, $value) {
            $q->where($column, $value);
        });
    }

    protected static function applyWhereHasInFilter($model, $query, $column, $value)
    {
        return is_array($value) ? $model->whereHas($query, function ($q) use ($column, $value) {
            $q->whereIn($column, $value);
        }) : $model;
    }

    public function scopeDateRange($query, $date)
    {
        $availableFilters = collect(static::FILTERS)->where('name', 'dateRange')->first();

        if ($date && $date != '') {
            $dateRange = preg_replace('/\s+/', '', $date);
            $date_range = explode('to', $dateRange);
            $start = $date_range[0];
            if (count($date_range) > 1) {
                $end = $date_range[1];
            } else {
                $end = $date_range[0];
            }
            $key = $availableFilters['column'] ?? 'created_at'; // default date range filter with created_at or else column set in filters

            $query = $query->whereRaw(
                $key . ' >= ? AND ' . $key . ' <= ?',
                [$start . ' 00:00:00', $end . ' 23:59:59']
            );
        }

        return $query;
    }
}
