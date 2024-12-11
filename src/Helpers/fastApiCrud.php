<?php
declare(strict_types=1);

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

if (! function_exists('_dd')) {

    /**
     * Dump
     * @param mixed ...$args
     * @return void
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
     * @param string $param
     * @return string|null
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
     * @param string $times
     * @return int|float
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
            $seconds = (int)$times; // Ensure $seconds is an integer
        }

        return $seconds;
    }
}

if (! function_exists('duration')) {
    /**
     * Format duration in hours and minutes
     * @param int $duration
     * @return string
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
     * @param string|null $date
     * @return string|null
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
     * @param string|null $date
     * @param string $format
     * @return string|null
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
     * @param string|null $date
     * @param string $format
     * @return string|null
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
     * @param string $key
     * @return string|null
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
     * Get array filter value by key
     * @param string $key
     * @return array
     */
    function getArrayFilterByKey(string $key = 'date'): array
    {
        $jsonData = json_decode(request()->query('filters'), true);
        $value = collect($jsonData)->get($key);

        return flatData($value) ?? [];
    }
}

if (! function_exists('flatData')) {
    /**
     * Flatten data
     * @param array<mixed> $data
     * @param int $depth
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
     * @return string
     */
    function defaultOrder(): string
    {
        return (bool) request()->query('descending') === true ? 'ASC' : 'DESC';
    }
}

if (! function_exists('defaultSort')) {
    /**
     * Default sort
     * @param string $key
     * @return string|array<string>|null
     */
    function defaultSort(string $key = 'id'): string|array|null
    {
        return request()->query('sortBy', $key);
    }
}

if (! function_exists('getClassMethod')) {
    /**
     * Get class methods
     * @param object $class
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
     * @param string|Model $table
     * @return array<string>
     */
    function getColumns(string|Model $table = 'users'): array
    {
        if (is_string($table) && is_subclass_of($table, 'Illuminate\Database\Eloquent\Model')) {
            $model = new $table;

            $columns = $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());
        } else {
            $columns = \Illuminate\Support\Facades\DB::getSchemaBuilder()->getColumnListing($table);
        }

        $columns = array_diff($columns, ['id']);
        $specialColumns = ['created_at', 'updated_at', 'deleted_at'];
        $columns = array_diff($columns, $specialColumns);
        sort($columns);
        return array_merge(['id'], $columns, $specialColumns);
    }
}
