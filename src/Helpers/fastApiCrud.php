<?php

declare(strict_types=1);

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;

if (! function_exists('_dd')) {

    /**
     * Dump
     *
     * @param  mixed  ...$args
     */
    function _dd(...$args): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');
        http_response_code(500);

        foreach ($args as $x) {
            (new Symfony\Component\VarDumper\VarDumper)->dump($x);
        }

        exit(1);
    }
}

if (! function_exists('shortName')) {
    /**
     * Get the short name of the class
     *
     * @throws ReflectionException
     */
    function shortName(string $param): ?string
    {
        if (! app($param)) {
            return null;
        }
        $reflection = new ReflectionClass(app($param));

        return $reflection->getShortName();
    }
}

if (! function_exists('totalSeconds')) {
    /**
     * Convert time string to total seconds
     */
    function totalSeconds(string $times): int|float
    {
        $time = explode(':', $times);
        $seconds = 0;

        if (count($time) >= 3) {
            $carbon = new Carbon($times);
            $seconds = $carbon->diffInSeconds(Carbon::createFromFormat('H:i:s', '00:00:00'));
        } elseif (count($time) == 2) {
            $minSec = '00:'.$times;
            $carbon = new Carbon($minSec);
            $seconds = $carbon->diffInSeconds(Carbon::createFromFormat('H:i:s', '00:00:00'));
        } else {
            $seconds = (int) $times; // Ensure $seconds is an integer
        }

        return $seconds;
    }
}

if (! function_exists('duration')) {
    /**
     * Format duration in hours and minutes
     */
    function duration(int $duration): string
    {
        $interval = CarbonInterval::seconds($duration)->cascade();

        return sprintf('%dh %dm', $interval->totalHours, $interval->toArray()['minutes']);
    }
}

if (! function_exists('dateForHumans')) {
    /**
     * Get human-readable date difference
     */
    function dateForHumans(?string $date): ?string
    {
        if ($date) {
            return Carbon::parse($date)->diffForHumans();
        }

        return null;
    }
}

if (! function_exists('ymdDate')) {
    /**
     * Format date to specified format
     */
    function ymdDate(?string $date, string $format = 'Y-m-d'): ?string
    {
        if ($date) {
            return Carbon::parse($date)->format($format);
        }

        return null;
    }
}

if (! function_exists('dateForReports')) {
    /**
     * Format date for reports
     */
    function dateForReports(?string $date, string $format = 'Y-m-d H:i'): ?string
    {
        try {
            return Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return null;
        }
    }
}

if (! function_exists('getFilterByKey')) {
    /**
     * Get filter value by key
     */
    function getFilterByKey(string $key = 'date'): ?string
    {
        $filters = Request::get('filters');
        $jsonData = is_string($filters) ? json_decode($filters, true) : [];
        $value = collect($jsonData)->get($key);

        return is_string($value) ? $value : null;
    }
}

if (! function_exists('getArrayFilterByKey')) {
    /**
     * Get array filter by key
     *
     * @param  array<string, mixed>|string|null  $data
     * @return array<string, mixed>
     */
    function getArrayFilterByKey($data): array
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data = $decoded;
            } else {
                $data = [];
            }
        }

        /** @var array<string, mixed> */
        return collect($data ?? [])->filter()->all();
    }
}

if (! function_exists('flatData')) {
    /**
     * Flatten data
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    function flatData(array $data, int $depth = 0): array
    {
        return collect($data)->flatten($depth)->toArray();
    }
}

if (! function_exists('defaultOrder')) {
    /**
     * Get default order direction
     */
    function defaultOrder(): string
    {
        return (bool) request()->query('descending') === true ? 'ASC' : 'DESC';
    }
}

if (! function_exists('defaultSort')) {
    /**
     * Get default sort
     *
     * @return array<string>|string|null
     */
    function defaultSort(): array|string|null
    {
        $sort = request('sort');

        if (is_string($sort)) {
            return $sort;
        }

        if (is_array($sort)) {
            /** @var array<string> */
            return array_filter($sort);
        }

        return null;
    }
}

if (! function_exists('getClassMethod')) {
    /**
     * Get class methods
     *
     * @return array<string>
     */
    function getClassMethod(object $class): array
    {
        $class = new ReflectionClass($class);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $scopeMethods = [];
        foreach ($methods as $method) {
            if (str_starts_with($method->getName(), 'scope')) {
                $scopeMethods[] = $method->getName();
            }
        }

        return $scopeMethods;
    }
}

if (! function_exists('getColumns')) {
    /**
     * Get columns from a table
     *
     * @return array<string>
     */
    function getColumns(string|Model $table = 'users'): array
    {
        if (is_string($table)) {
            if (is_subclass_of($table, Model::class)) {
                /** @var Model */
                $model = new $table;
                $columns = Schema::getColumnListing($model->getTable());
            } else {
                $columns = Schema::getColumnListing($table);
            }
        } else {
            $columns = Schema::getColumnListing($table->getTable());
        }

        $columns = array_diff($columns, ['id']);
        $specialColumns = ['created_at', 'updated_at', 'deleted_at'];
        $columns = array_diff($columns, $specialColumns);
        sort($columns);

        /** @var array<string> */
        return array_merge(['id'], $columns, $specialColumns);
    }
}
