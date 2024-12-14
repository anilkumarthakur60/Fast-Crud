<?php

namespace Anil\FastApiCrud\Tests\TestSetup\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserModel extends Model
{
    /** @use HasFactory<UserModelFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return HasMany<PostModel,UserModel>
     */
    public function posts(): HasMany
    {
        /** @var HasMany<PostModel,UserModel> */
        return $this->hasMany(
            related: PostModel::class,
            foreignKey: 'user_id',
            localKey: 'id'
        );
    }

    /**
     * @param  Builder<UserModel>  $query
     * @return Builder<UserModel>
     */
    public function scopeQueryFilter(Builder $query, mixed $search): Builder
    {
        return $query->likeWhere(
            attributes: ['name', 'email'],
            searchTerm: $search
        );
    }

    /**
     * @param  Builder<UserModel>  $query
     * @return Builder<UserModel>
     */
    public function scopeActive(Builder $query, int $active = 1): Builder
    {
        return $query->where('active', $active);
    }

    /**
     * @param  Builder<UserModel>  $query
     * @return Builder<UserModel>
     */
    public function scopeStatus(Builder $query, int $status = 1): Builder
    {
        return $query->where('status', $status);
    }
}
