<?php

namespace YajTech\Crud\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use YajTech\Crud\Resources\CommonDropDownResource;
use function App\Http\Controllers\app;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function dropdown(Request $request)
    {
        $request->validate([
            'model' => 'required',
            'columns' => 'nullable',
        ]);

        $model = app($request->model);
        $model = new $model();
        $model = $model::initializer();
        $columns = json_decode($request->columns, true);
        if ($request->has('columns') && count($columns) > 0) {
            $model = $model->select($columns);
        }

        return CommonDropDownResource::collection($model->paginates());
    }
}
