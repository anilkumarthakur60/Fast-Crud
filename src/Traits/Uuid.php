<?php

namespace Anil\FastApiCrud\Traits;

use Illuminate\Support\Str;

/**
 * Trait Uuid
 */
trait Uuid
{
    /**
     * Boot function from laravel.
     */
    protected static function bootUuid(): void
    {

        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the data type of the primary key.
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}
