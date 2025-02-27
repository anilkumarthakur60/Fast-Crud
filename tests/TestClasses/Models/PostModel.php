<?php

namespace Anil\FastApiCrud\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'posts';

    protected $fillable = [
        'name',
        'desc',
        'user_id',
        'status',
        'active',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            related: UserModel::class,
            foreignKey: 'user_id',
            ownerKey: 'id'
        );
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            related: TagModel::class,
            table: 'post_tag',
            foreignPivotKey: 'post_id',
            relatedPivotKey: 'tag_id',
            parentKey: 'id',
            relatedKey: 'id'
        );
    }
}
