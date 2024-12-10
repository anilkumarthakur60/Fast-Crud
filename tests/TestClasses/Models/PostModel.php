<?php

namespace Anil\FastApiCrud\Tests\TestClasses\Models;

use Anil\FastApiCrud\Database\Factories\PostModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostModel extends Model
{
    /** @use HasFactory<PostModelFactory> */
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

    /**
     * Get the attributes that should be cast.
     *
     * @return BelongsTo<UserModel,PostModel>
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<UserModel,PostModel> */
        return $this->belongsTo(
            related: UserModel::class,
            foreignKey: 'user_id',
            ownerKey: 'id'
        );
    }

    /**
     * The tags that belong to the post.
     *
     * @return BelongsToMany<TagModel,PostModel>
     */
    public function tags(): BelongsToMany
    {
        /** @var BelongsToMany<TagModel,PostModel> */
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
