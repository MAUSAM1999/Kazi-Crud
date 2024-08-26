<?php

namespace YajTech\Crud\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;
use YajTech\Crud\Exports\CommonExport;
use YajTech\Crud\Helper\ApiResponse;
use YajTech\Crud\Helper\GlobalHelper;
use YajTech\Crud\Traits\Super;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use ReflectionClass;

class CrudController extends BaseController
{
    use Super;

    public array $withAll = [];

    public array $withCount = [];

    public array $withAggregate = [];

    public array $scopes = [];

    public array $scopeWithValue = [];

    public $model;

    public $resource;

    public $listResource;

    public $storeRequest;

    public $updateRequest;

    public function __construct($model, $resource, $listResource, $storeRequest, $updateRequest)
    {
        $this->updateRequest = $updateRequest;
        $this->storeRequest = $storeRequest;
        $this->resource = $resource;
        $this->listResource = $listResource;
        $this->model = $model;
        $constants = new ReflectionClass($this->model);
        try {
            $permissionSlug = $constants->getConstant('PERMISSION_SLUG');
        } catch (Exception $e) {
            $permissionSlug = null;
        }
        if ($permissionSlug) {
//             $this->middleware(['permission:view-' . $this->model::PERMISSION_SLUG])->only(['index', 'show']);
//             $this->middleware('permission:create-' . $this->model::PERMISSION_SLUG)->only(['store',]);
//             $this->middleware('permission:update-' . $this->model::PERMISSION_SLUG)->only(['update', 'changeStatus']);
//             $this->middleware('permission:delete-' . $this->model::PERMISSION_SLUG)->only(['delete']);
        }
    }

    public function index(): JsonResource
    {
        DB::enableQueryLog();

        return $this->getIndexCollection();
    }

    public function getAll(): JsonResource
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

        return $resource::collection($model->get());
    }

    public function store()
    {
        $model = new $this->model();

        $request = resolve($this->storeRequest);

        if (method_exists($model, 'mergeRequest')) {
            $request->merge($model->mergeRequest());
        }
        $data = $request->only($model->getFillable());
        try {
            DB::beginTransaction();
            $model = $this->model::create($data);
            if (method_exists(new $this->model(), 'afterCreateProcess')) {
                $model->afterCreateProcess();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            // dd($e);
            if ($e->getCode() == 0) {
                return ApiResponse::validationError([], $e->getMessage());
            }
            return ApiResponse::onException($e);
        }

        return $this->getResourceObject($this->resource, $model);
    }

    public function delete(Request $request)
    {
        $this->validate($request, [
            'delete_rows' => ['required', 'array'],
            'delete_rows.*' => ['required', 'exists:' . (new $this->model())->getTable() . ',id'],
        ]);

        try {
            DB::beginTransaction();
            foreach ((array)$request->input('delete_rows') as $item) {
                $model = $this->model::findOrFail($item);
                if (method_exists(new $this->model(), 'afterDeleteProcess') && $model) {
                    $model->afterDeleteProcess();
                }
                if ($model) {
                    $model->delete();
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            if ($e->getCode() == 422) {
                return ApiResponse::validationError([], $e->getMessage());
            } else {
                return ApiResponse::onException($e);
            }
        }

        return ApiResponse::success('Data deleted successfully');
    }

    public function show($id)
    {
        $model = $this->model::initializer()
            ->when(property_exists($this, 'withAll'), fn($query) => $query->with($this->withAll))
            ->when(property_exists($this, 'withCount'), fn($query) => $query->withCount($this->withCount))
            ->when(property_exists($this, 'withAggregate'), fn($query) => $this->applyWithAggregate($query))
            ->when(property_exists($this, 'scopes'), fn($query) => $this->applyScopes($query))
            ->when(property_exists($this, 'scopeWithValue'), fn($query) => $this->applyScopesWithValue($query))
            ->findOrFail($id);

        $resource = $this->resource;

        return $this->getResourceObject($resource, $model);
    }

    public function changeStatus($id, Request $request)
    {
        $model = $this->model::findOrFail($id);
        try {
            DB::beginTransaction();
            if (method_exists(new $this->model(), 'beforeChangeStatusProcess')) {
                $model->beforeChangeStatusProcess();
            }
            if (!$this->checkFillable($model, ['status'])) {
                DB::rollBack();
                throw new Exception('Status column not found in fillable');
            }

            $model->update(['status' => $request?->status ?? ($model->status?->value ?? $model->status === 1 ? 0 : 1)]);
            if (method_exists(new $this->model(), 'afterChangeStatusProcess')) {
                $model->afterChangeStatusProcess();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ApiResponse::onException($e);
        }

        return $this->resource::make($model);
    }

    public function update($id)
    {
        $model = new $this->model();
        $request = resolve($this->updateRequest);
        if (method_exists($model, 'mergeRequest')) {
            $request->merge($model->mergeRequest($id));
        }
        $data = $request->only($model->getFillable());

        if ($model->isFillable('updated_by')) {
            $data['updated_by'] = auth()->id();
        }
        $model = $this->model::findOrFail($id);

        try {
            if (method_exists(new $this->model(), 'beforeUpdateProcess')) {
                $model->beforeUpdateProcess($model);
            }
            DB::beginTransaction();
            $model->update($data);
            if (method_exists(new $this->model(), 'afterUpdateProcess')) {
                $model->afterUpdateProcess();
            }

            DB::commit();
        } catch (Exception $e) {
            // dd($e);
            DB::rollBack();
            // dd($e);
            if ($e->getCode() == 0) {
                return ApiResponse::validationError([], $e->getMessage());
            }
            return ApiResponse::onException($e);
        }

        return $this->getResourceObject($this->resource, $model);
    }

    public function export(Request $request, $headers = null, $mapping = null)
    {
        $rowsPerPage = $request->get('rowsPerPage') ? $request->get('rowsPerPage') : 10;
        $model = $this->model::initializer();
        $rowsPerPage = $request->get('rowsPerPage') == 0 ? $model->count() : $rowsPerPage;

        $model = $model->paginate($rowsPerPage);
        if (!isset($headers)) {
            if (method_exists($this->model, 'EXPORT_HEADER_MAPPINGS')) {
                $headers = $this->model::EXPORT_HEADER_MAPPINGS();
            } else {
                $headers = $this->model::EXPORT_HEADERS;
            }
        }

        if (!isset($mapping)) {
            if (method_exists($this->model, 'EXPORT_VALUE_MAPPINGS')) {
                $mapping = $this->model::EXPORT_VALUE_MAPPINGS();
            } else {
                $mapping = $this->model::EXPORT_MAPPINGS();
            }
        }

        $exportClass = new CommonExport($model, $headers, $mapping);
        // generic solution
        $reflection = new ReflectionClass($this->model);

        if ($request->has('exportName')) {
            $exportName = $request->get('exportName') . time() . '.xlsx';
        } else {
            $exportName = Str::slug($reflection->getShortName()) . time() . '.xlsx';
        }
        $path = 'export/' . $exportName;
        $export = Excel::store($exportClass, $path, 'minio');
        $expiry = Carbon::today()->addDays(7);
        if ($export) {
            return Response::json([
                'code' => 200,
                'url' => Storage::cloud()->temporaryUrl($path, $expiry),
                'message' => 'Success',
            ]);
        } else {
            return ApiResponse::errorResponse('Error ! Export failed, please try again later');
        }
    }

    public function getMetaData()
    {
        $service = new GlobalHelper();

        $columns = $service->constant_exists($this->model, 'COLUMNS') ? $this->model::COLUMNS : [];
        $fields = $service->constant_exists($this->model, 'FIELDS') ? $this->model::FIELDS : [];
        $table = $service->constant_exists($this->model, 'TABLE') ? $this->model::TABLE : [];
        $filters = $service->constant_exists($this->model, 'FILTERS') ? $this->model::FILTERS : [];

        return ApiResponse::successData([
            'columns' => $columns,
            'fields' => $fields,
            'table' => $table,
            'filters' => $filters
        ]);
    }
}
