<?php

namespace YajTech\Crud\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use YajTech\Crud\Resources\CommonDropDownResource;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function dropdown(Request $request)
    {
        $request->validate([
            'model' => 'required|string',
            'columns' => 'nullable|string',
        ]);

        $modelClass = $this->getModelClass($request->model);

        if (!class_exists($modelClass)) {
            return response()->json(['error' => 'Model not found'], 404);
        }


        $model = new $modelClass();
        $model = $model::initializer();

        $columns = $request->has('columns') ? json_decode($request->columns, true) : [];

        if ($request->query && $request->query != '') {
            $value = $request->query;
            $model = $model->where(function ($query) use ($columns, $value) {
                foreach ($columns as $index => $column) {
                    $query->{$index === 0 ? 'where' : 'orWhere'}($column, 'LIKE', '%' . $value . '%');
                }
            });
        }

        if (count($columns) > 0) {
            $model = $model->select($columns);
        }

        return CommonDropDownResource::collection($model->paginate());
    }

    protected function getModelClass($model)
    {
        // If the model name is fully qualified, return it as is
        if (class_exists($model)) {
            return $model;
        }

        // Otherwise, prepend the default namespace
        $namespace = 'App\\Models\\';
        return $namespace . $model;
    }
}

