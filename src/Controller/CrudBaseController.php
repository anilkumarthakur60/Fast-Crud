<?php

namespace Anil\FastApiCrud\Controller;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @property class-string<Model>                 $model
 * @property class-string<JsonResource>          $resource
 * @property array<string>                       $scopes
 * @property array<string, mixed>                $scopeWithValue
 * @property array<string>                       $loadScopes
 * @property array<string, mixed>                $loadScopeWithValue
 * @property array<string>                       $withAll
 * @property array<string>                       $withCount
 * @property array<string, array<string, mixed>> $withAggregate
 * @property array<string>                       $loadAll
 * @property array<string>                       $loadCount
 * @property array<string>                       $loadAggregate
 * @property bool                                $isApi
 * @property bool                                $forceDelete
 * @property array<string>                       $deleteScopes
 * @property array<string, mixed>                $deleteScopeWithValue
 * @property array<string>                       $changeStatusScopes
 * @property array<string, mixed>                $changeStatusScopeWithValue
 * @property array<string>                       $restoreScopes
 * @property array<string, mixed>                $restoreScopeWithValue
 * @property array<string>                       $updateScopes
 * @property array<string, mixed>                $updateScopeWithValue
 * @property array<string>                       $deleteScopes
 * @property array<string, mixed>                $deleteScopeWithValue
 * @property array<string>                       $changeStatusScopes
 * @property array<string, mixed>                $changeStatusScopeWithValue
 * @property array<string>                       $restoreScopes
 * @property array<string, mixed>                $restoreScopeWithValue
 */
class CrudBaseController extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    /**
     * @var array<string>
     */
    public array $scopes = [];

    /**
     * @var array<string, mixed>
     */
    public array $scopeWithValue = [];

    /**
     * @var array<string>
     */
    public array $loadScopes = [];

    /**
     * @var array<string, mixed>
     */
    public array $loadScopeWithValue = [];

    /**
     * @var array<string>
     */
    public array $withAll = [];

    /**
     * @var array<string>
     */
    public array $withCount = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    public array $withAggregate = [];

    /**
     * @var array<string>
     */
    public array $loadAll = [];

    /**
     * @var array<string>
     */
    public array $loadCount = [];

    /**
     * @var array<string>
     */
    public array $loadAggregate = [];

    public bool $isApi = true;

    public bool $forceDelete = false;

    /**
     * @var array<string>
     */
    public array $deleteScopes = [];

    /**
     * @var array<string, mixed>
     */
    public array $deleteScopeWithValue = [];

    /**
     * @var array<string>
     */
    public array $changeStatusScopes = [];

    /**
     * @var array<string, mixed>
     */
    public array $changeStatusScopeWithValue = [];

    /**
     * @var array<string>
     */
    public array $restoreScopes = [];

    /**
     * @var array<string, mixed>
     */
    public array $restoreScopeWithValue = [];

    /**
     * @var array<string>
     */
    public array $updateScopes = [];

    /**
     * @var array<string, mixed>
     */
    public array $updateScopeWithValue = [];

    /**
     * @var Model
     */
    public Model $model;

    /**
     * @var JsonResource
     */
    public JsonResource $resource;

    /**
     * @var FormRequest
     */
    public FormRequest $storeRequest;

    /**
     * @var FormRequest
     */
    public FormRequest $updateRequest;

    public function __construct(Model $model, FormRequest $storeRequest, FormRequest $updateRequest, JsonResource $resource)
    {
        $this->model = $model;
        $this->storeRequest = $storeRequest;
        $this->updateRequest = $updateRequest;
        $this->resource = $resource;

        $this->validateModel($this->model);
        $this->validateRequest($this->storeRequest, 'StoreRequest');
        $this->validateRequest($this->updateRequest, 'UpdateRequest');

        $this->setupPermissions();
    }

    protected function validateModel(string $modelClass): void
    {
        if (!is_subclass_of($modelClass, Model::class)) {
            throw new Exception('Model is not instance of Model', ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function validateRequest(FormRequest $request, string $requestName): void
    {
        if (!(new $request() instanceof FormRequest)) {
            throw new Exception("$requestName is not instance of FormRequest", ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function setupPermissions(): void
    {
        $constants = new ReflectionClass($this->model);
        $permissionSlug = $constants->getConstant('permissionSlug') ?? null;

        if ($permissionSlug) {
            $this->middleware('permission:view-'.$permissionSlug)->only(['index', 'show']);
            $this->middleware('permission:alter-'.$permissionSlug)->only(['store', 'update', 'changeStatus', 'changeStatusOtherColumn', 'restore']);
            $this->middleware('permission:delete-'.$permissionSlug)->only(['delete']);
        }
    }

    /**
     * @return AnonymousResourceCollection<JsonResource>
     */
    public function index(): AnonymousResourceCollection
    {
        $query = $this->model::query();

        if (!empty($this->withAll)) {
            $query->with($this->withAll);
        }

        if (!empty($this->withCount)) {
            $query->withCount($this->withCount);
        }

        // if (!empty($this->withAggregate)) {
        //     $query->withAggregate($this->withAggregate);
        // }

        if (!empty($this->scopes)) {
            $this->applyScopes($query, $this->scopes);
        }

        if (!empty($this->scopeWithValue)) {
            $this->applyScopeWithValue($query, $this->scopeWithValue);
        }

        return $this->resource::collection($query->paginate());
    }

    /**
     * @param Builder<Model> $query
     * @param array<string>  $scopes
     *
     * @return Builder<Model>
     */
    protected function applyScopes(Builder $query, array $scopes): Builder
    {
        foreach ($scopes as $scope) {
            $query->{$scope}();
        }

        return $query;
    }

    /**
     * @param Builder<Model>       $query
     * @param array<string, mixed> $scopeWithValue
     *
     * @return Builder<Model>
     */
    protected function applyScopeWithValue(Builder $query, array $scopeWithValue): Builder
    {
        foreach ($scopeWithValue as $key => $value) {
            $query->$key($value);
        }

        return $query;
    }

    public function store(): JsonResponse|JsonResource
    {
        $data = resolve($this->storeRequest)->safe()->only((new $this->model())->getFillable());

        try {
            DB::beginTransaction();
            $model = $this->model::create($data);
            $this->afterCreateProcess($model);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage());
        }

        return new $this->resource($model);
    }

    protected function afterCreateProcess(Model $model): Model|string
    {
        if (method_exists($model, 'afterCreateProcess')) {
            $model->afterCreateProcess();
        }

        return $model;
    }

    /**
     * @param string               $message
     * @param array<string, mixed> $data
     * @param int                  $code
     */
    protected function error(
        string $message = 'Something went wrong',
        array $data = [],
        int $code = ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public function show(int|string $id): JsonResource|JsonResponse
    {
        $model = $this->model::initializer()
            ->when($this->loadAll, fn (Builder $query): Builder => $query->with($this->loadAll))
            ->when($this->loadCount, fn (Builder $query): Builder => $query->withCount($this->loadCount))
            ->when($this->loadAggregate, fn (Builder $query): Builder => $this->applyLoadAggregate($query, $this->loadAggregate))
            ->when($this->loadScopes, fn (Builder $query): Builder => $this->applyScopes($query, $this->loadScopes))
            ->when($this->loadScopeWithValue, fn (Builder $query): Builder => $this->applyScopeWithValue($query, $this->loadScopeWithValue))
            ->findOrFail($id);

        return new $this->resource($model);
    }

    /**
     * @param Builder<Model>                      $query
     * @param array<string, array<string, mixed>> $loadAggregate
     *
     * @return Builder<Model>
     */
    protected function applyLoadAggregate(Builder $query, array $loadAggregate): Builder
    {
        foreach ($loadAggregate as $key => $value) {
            $query->withAggregate($key, $value);
        }

        return $query;
    }

    public function destroy(int|string $id): JsonResponse
    {
        $model = $this->findModel($id, $this->deleteScopes, $this->deleteScopeWithValue);
        $this->beforeDeleteProcess($model);
        $this->forceDelete ? $model->forceDelete() : $model->delete();
        $this->afterDeleteProcess($model);

        return $this->success(message: 'Data deleted successfully', code: ResponseAlias::HTTP_NO_CONTENT);
    }

    /**
     * Find model by ID with optional scopes.
     *
     * @param int|string           $id
     * @param array<string>        $scopes
     * @param array<string, mixed> $scopeWithValue
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function findModel(int|string $id, array $scopes = [], array $scopeWithValue = []): Model
    {
        $query = $this->model::query()
            ->when(!empty($scopes), fn (Builder $query): Builder => $this->applyScopes($query, $scopes))
            ->when(!empty($scopeWithValue), fn (Builder $query): Builder => $this->applyScopeWithValue($query, $scopeWithValue));

        return $query->findOrFail($id);
    }

    protected function beforeDeleteProcess(Model $model): Model
    {
        if (method_exists(object_or_class: $model, method: 'beforeDeleteProcess')) {
            $model->beforeDeleteProcess();
        }

        return $model;
    }

    protected function afterDeleteProcess(Model $model): Model
    {
        if (method_exists(object_or_class: $model, method: 'afterDeleteProcess')) {
            $model->afterDeleteProcess();
        }

        return $model;
    }

    public function delete(): JsonResponse
    {
        request()->validate([
            'delete_rows'   => ['required', 'array'],
            'delete_rows.*' => ['required', 'exists:'.(new $this->model())->getTable().',id'],
        ]);

        try {
            DB::beginTransaction();
            foreach ((array) request()->input('delete_rows') as $item) {
                $model = $this->findModel($item, $this->deleteScopes, $this->deleteScopeWithValue);
                if ($model) {
                    $this->beforeDeleteProcess($model);
                    $this->forceDelete ? $model->forceDelete() : $model->delete();
                    $this->afterDeleteProcess($model);
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage());
        }

        return $this->success(message: 'Data deleted successfully', code: ResponseAlias::HTTP_NO_CONTENT);
    }

    /**
     * @param array<string, mixed>|null $data
     * @param string                    $message
     * @param int                       $code
     */
    protected function success(
        ?array $data = null,
        string $message = 'Success',
        int $code = ResponseAlias::HTTP_OK
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public function changeStatusOtherColumn(int|string $id, string $column): JsonResource|JsonResponse
    {
        $model = $this->findModel($id, $this->changeStatusScopes, $this->changeStatusScopeWithValue);
        $this->validateColumn($model, $column);

        try {
            DB::beginTransaction();
            $this->beforeChangeStatusProcess($model);
            $model->update([$column => $model->$column === 1 ? 0 : 1]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage());
        }

        return new $this->resource($model);
    }

    protected function validateColumn(Model $model, string $column): bool
    {
        if (!$this->checkFillable($model, [$column])) {
            throw new Exception("$column column not found in fillable");
        }

        return true;
    }

    protected function beforeChangeStatusProcess(Model $model): Model
    {
        if (method_exists($model, 'beforeChangeStatusProcess')) {
            $model->beforeChangeStatusProcess();
        }

        return $model;
    }

    /**
     * @param Model         $model
     * @param array<string> $columns
     *
     * @return bool
     */
    protected function checkFillable(Model $model, array $columns): bool
    {
        $fillableColumns = $this->fillableColumn($model);

        return count(array_diff($columns, $fillableColumns)) === 0;
    }

    public function update(int|string $id): JsonResource|JsonResponse
    {
        $data = resolve($this->updateRequest)->safe()->only((new $this->model())->getFillable());
        $model = $this->findModel($id, $this->updateScopes, $this->updateScopeWithValue);

        try {
            DB::beginTransaction();
            $this->beforeUpdateProcess($model);
            $model->update($data);
            $this->afterUpdateProcess($model);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage());
        }

        return new $this->resource($model);
    }

    protected function beforeUpdateProcess(Model $model): Model
    {
        if (method_exists($model, 'beforeUpdateProcess')) {
            $model->beforeUpdateProcess();
        }

        return $model;
    }

    protected function afterUpdateProcess(Model $model): Model
    {
        if (method_exists($model, 'afterUpdateProcess')) {
            $model->afterUpdateProcess();
        }

        return $model;
    }

    /**
     * @param Model $model
     *
     * @return array<string>
     */
    protected function fillableColumn(Model $model): array
    {
        return Schema::getColumnListing($this->tableName($model));
    }

    /**
     * @param Model $model
     *
     * @return string
     */
    protected function tableName(Model $model): string
    {
        return $model->getTable();
    }

    public function changeStatus(int|string $id): JsonResource|JsonResponse
    {
        $model = $this->findModel($id, $this->changeStatusScopes, $this->changeStatusScopeWithValue);
        $this->validateColumn($model, 'status');

        try {
            DB::beginTransaction();
            $this->beforeChangeStatusProcess($model);
            // $model->update(['status' => $model->status === 1 ? 0 : 1]);
            $this->afterChangeStatusProcess($model);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage());
        }

        return new $this->resource($model);
    }

    public function restoreTrashed(int|string $id): JsonResource|JsonResponse
    {
        $model = $this->model::initializer()->onlyTrashed()
            ->when($this->restoreScopes, fn ($query) => $this->applyScopes($query, $this->restoreScopes))
            ->when($this->restoreScopeWithValue, fn ($query) => $this->applyScopeWithValue($query, $this->restoreScopeWithValue))
            ->findOrFail($id);

        try {
            DB::beginTransaction();
            $this->beforeRestoreProcess($model);
            $model->restore();
            $this->afterRestoreProcess($model);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage());
        }

        return new $this->resource($model);
    }

    protected function beforeRestoreProcess(Model $model): Model
    {
        if (method_exists($model, 'beforeRestoreProcess')) {
            $model->beforeRestoreProcess();
        }

        return $model;
    }

    protected function afterRestoreProcess(Model $model): Model
    {
        if (method_exists($model, 'afterRestoreProcess')) {
            $model->afterRestoreProcess();
        }

        return $model;
    }

    public function restoreAllTrashed(): JsonResponse
    {
        try {
            DB::beginTransaction();
            $this->model::initializer()->onlyTrashed()->restore();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage());
        }

        return $this->success(message: 'Data restored successfully');
    }

    public function forceDeleteTrashed(int|string $id): JsonResponse|Model
    {
        $model = $this->model::initializer()->onlyTrashed()->findOrFail($id);

        try {
            DB::beginTransaction();
            $this->beforeForceDeleteProcess($model);
            $model->forceDelete();
            $this->afterForceDeleteProcess($model);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage());
        }

        return $this->success(message: 'Data deleted successfully', code: ResponseAlias::HTTP_NO_CONTENT);
    }

    protected function beforeForceDeleteProcess(Model $model): Model
    {
        if (method_exists($model, 'beforeForceDeleteProcess')) {
            $model->beforeForceDeleteProcess();
        }

        return $model;
    }

    protected function afterForceDeleteProcess(Model $model): Model
    {
        if (method_exists($model, 'afterForceDeleteProcess')) {
            $model->afterForceDeleteProcess();
        }

        return $model;
    }

    protected function afterChangeStatusProcess(Model $model): Model|string
    {
        if (method_exists($model, 'afterChangeStatusProcess')) {
            $model->afterChangeStatusProcess();
        }

        return $model;
    }
}
