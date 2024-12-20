<?php

namespace Anil\FastApiCrud\Tests\TestSetup\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PermissionModel extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'name',
    ];

    /**
     * @return BelongsToMany<UserModel,PermissionModel>
     */
    public function users(): BelongsToMany
    {
        /** @var BelongsToMany<UserModel,PermissionModel> */
        return $this->belongsToMany(
            related: UserModel::class,
            table: 'user_permission',
            foreignPivotKey: 'permission_id',
            relatedPivotKey: 'user_id'
        );
    }
}
