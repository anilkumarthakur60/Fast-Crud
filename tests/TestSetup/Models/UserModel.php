<?php

namespace Anil\FastApiCrud\Tests\TestSetup\Models;

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
}
