<?php

namespace Anil\FastApiCrud\Providers;

use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ApiCrudServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/fastApiCrud.php' => config_path('fastApiCrud.php'),
        ], 'config');

        Builder::macro('likeWhere', function (array $attributes, ?string $searchTerm = null) {
            /** @var Builder<Model> $this */
            if (empty($searchTerm)) {
                return $this;
            }

            return $this->where(function (Builder $query) use ($attributes, $searchTerm) {
                foreach ($attributes as $attribute) {
                    $query->when(
                        Str::contains($attribute, '.'),
                        function (Builder $query) use ($attribute, $searchTerm) {
                            [$relationName, $relationAttribute] = explode('.', $attribute);
                            $query->orWhereHas($relationName, function (Builder $query) use ($relationAttribute, $searchTerm) {
                                $query->where($relationAttribute, 'LIKE', "%{$searchTerm}%");
                            });
                        },
                        function (Builder $query) use ($attribute, $searchTerm) {
                            $query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
                        }
                    );
                }
            });
        });

        /**
         * Paginate the given query.
         *
         * @param  int|null|Closure  $perPage
         * @param  array|string  $columns
         * @param  string  $pageName
         * @param  int|null  $page
         * @param  Closure|int|null  $total
         * @return Paginator
         *
         * @throws InvalidArgumentException
         */
        Builder::macro('paginates', function ($perPage = null, $columns = ['*'], $pageName = 'page', $page = null, $total = null): Paginator {

            /** @var Builder<Model> $this */
            $validated = request()->all();
            $rowsPerPage = $validated['rowsPerPage'] ?? 15;
            $perPage = $rowsPerPage === 0 ? 15 : $rowsPerPage;
            $perPage = (int) $perPage;

            return $this->paginate($perPage, $columns, $pageName, $page, $total);
        });

        /**
         * Paginate the given query into a simple paginator.
         *
         * @param  int|null  $perPage
         * @param  array|string  $columns
         * @param  string  $pageName
         * @param  int|null  $page
         * @return Paginator
         */
        Builder::macro('simplePaginates', function ($perPage = null, $columns = ['*'], $pageName = 'page', $page = null): Paginator {
            /** @var Builder<Model> $this */
            $validated = request()->all();
            $rowsPerPage = $validated['rowsPerPage'] ?? 15;
            $perPage = $rowsPerPage === 0 ? 15 : $rowsPerPage;
            $perPage = (int) $perPage;

            return $this->simplePaginate($perPage, $columns, $pageName, $page);
        });

        /**
         * Macro to initialize the query builder with filters and sorting.
         *
         * @param  bool  $orderBy  Whether to apply ordering based on request parameters.
         * @return Builder<Model> The initialized query builder.
         */
        Builder::macro('initializer', function (bool $orderBy = true): Builder {
            /** @var Builder<Model> $this */
            $request = request();
            $filters = [];

            // Validate and decode 'filters' from the request if present
            if ($request->filled('filters')) {
                $filtersInput = $request->query('filters', '{}');

                // Ensure 'filters' is a string before decoding
                if (is_string($filtersInput)) {
                    $decodedFilters = json_decode($filtersInput, true);

                    // Handle JSON decoding errors
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedFilters)) {
                        $filters = $decodedFilters;
                    }
                }
            }

            // Initialize the model's query
            if (method_exists($this->getModel(), 'initializeModel')) {
                $model = $this->getModel()->initializeModel()->newQuery();
            } else {
                $model = $this->newQuery();
            }

            // Apply filters using scopes
            if (! empty($filters)) {
                foreach (collect($filters) as $filter => $value) {
                    if (isset($value) && method_exists($model, 'scope'.Str::studly($filter))) {
                        // Dynamically call the scope method
                        $model->{$filter}($value);
                    }
                }
            }

            // Handle sorting
            $sortBy = $request->query('sortBy', 'id');
            $desc = $request->boolean('descending', true);

            if ($orderBy) {
                // Check if the model has sortByDefaults method
                if ($sortBy && method_exists($this->getModel(), 'sortByDefaults')) {
                    $sortByDefaults = $this->getModel()->sortByDefaults();

                    // Ensure 'sortBy' and 'sortByDesc' keys exist and are of correct types
                    if (
                        isset($sortByDefaults['sortBy']) && is_string($sortByDefaults['sortBy']) &&
                        isset($sortByDefaults['sortByDesc']) && is_bool($sortByDefaults['sortByDesc'])
                    ) {
                        $sortBy = $sortByDefaults['sortBy'];
                        $desc = $sortByDefaults['sortByDesc'];
                    }
                }

                // Ensure 'sortBy' is a string before applying sorting
                if (is_string($sortBy)) {
                    $desc ? $model->latest($sortBy) : $model->oldest($sortBy);
                }
            }

            return $model;
        });
        /**
         * Macro to add aggregates to the query.
         *
         * @param  array<string, string|array<string>>  $aggregates
         * @return Builder<Model>
         */
        Builder::macro('withAggregates', function (array $aggregates) {
            /** @var Builder<Model> $this */
            if (! count($aggregates)) {
                return $this;
            }
            foreach ($aggregates as $relation => $columns) {
                $columns = is_array($columns) ? $columns : [$columns];
                foreach ($columns as $column) {
                    $this->withAggregate($relation, $column);
                }
            }

            return $this;
        });

        /**
         * Macro to add a conditional withCount based on a relationship.
         *
         * @param  string  $relation
         * @param  Closure|null  $callback
         * @param  string  $operator
         * @param  int  $count
         * @return Builder<Model>
         */
        Builder::macro('withCountWhereHas', function ($relation, ?Closure $callback = null, $operator = '>=', $count = 1) {
            /** @var Builder<Model> $this */
            $this->whereHas(Str::before($relation, ':'), $callback, $operator, $count)
                ->withCount(relations: $callback ? [$relation => fn ($query) => $callback($query)] : $relation);

            return $this;
        });

        /**
         * Macro to add an OR conditional withCount based on a relationship.
         *
         * @param  string  $relation
         * @param  Closure|null  $callback
         * @param  string  $operator
         * @param  int  $count
         * @return Builder<Model>
         */
        Builder::macro('orWithCountWhereHas', function ($relation, ?Closure $callback = null, $operator = '>=', $count = 1) {
            /** @var Builder<Model> $this */
            $this->orWhereHas(Str::before($relation, ':'), $callback, $operator, $count)
                ->withCount(relations: $callback ? [$relation => fn ($query) => $callback($query)] : $relation);

            return $this;
        });

        /**
         * Paginate collection
         *
         * @param  int  $perPage
         * @param  int  $total
         * @param  int  $page
         * @param  string  $pageName
         * @return Paginator
         */
        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page'): Paginator {
            /** @var Collection $this */
            // @phpstan-ignore-next-line
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });

    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/fastApiCrud.php', 'fastApiCrud');
    }
}

//        Builder::macro('equalWhere', function (array $attributes, mixed $searchTerm = null) {
//            if (is_array($searchTerm) && count($searchTerm) === 0) {
//                return $this;
//            }
//            if (! is_array($searchTerm) && ! isset($searchTerm)) {
//                return $this;
//            }
//            return $this->where(function (Builder $query) use ($attributes, $searchTerm) {
//                foreach ($attributes as $attribute) {
//                    $query->when(
//                        Str::contains($attribute, '.'),
//                        function (Builder $query) use ($attribute, $searchTerm) {
//                            $relationName = Str::beforeLast($attribute, '.');
//                            $relationAttribute = Str::afterLast($attribute, '.');
//                            $relation = $this->getRelationWithoutConstraints($relationName);
//                            $table = $relation->getModel()->getTable();
//                            $query->whereHas($relationName, function (Builder $query) use ($relationAttribute, $searchTerm, $table) {
//                                if (is_array($searchTerm)) {
//                                    $query->whereIn($table.'.'.$relationAttribute, $searchTerm);
//                                } else {
//                                    $query->where($table.'.'.$relationAttribute, $searchTerm);
//                                }
//                            });
//                        },
//                        function (Builder $query) use ($attribute, $searchTerm) {
//                            if (is_array($searchTerm)) {
//                                $query->whereIn($attribute, $searchTerm);
//                            } else {
//                                $query->where($attribute, $searchTerm);
//                            }
//                        }
//                    );
//                }
//            });
//        });
