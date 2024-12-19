<?php

namespace Anil\FastApiCrud\Tests\TestSetup\Models;

use Anil\FastApiCrud\Database\Factories\PostModelFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static Builder<PostModel> initializer()
 * @method static Builder<PostModel> likeWhere(array<string> $attributes, ?string $searchTerm = null)
 *
 * @mixin Builder<PostModel>
 */
class PostModel extends Model
{
    /** @use HasFactory<PostModelFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'posts';

    protected $fillable = [
        'active',
        'desc',
        'name',
        'status',
        'user_id',
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

    public function afterCreateProcess(): void
    {
        $request = request();
        if ($request->filled('tag_ids')) {
            $this->tags()->sync((array) $request->input('tag_ids'));
        }
    }

    public function afterUpdateProcess(): void
    {
        $request = request();
        if ($request->filled('tag_ids')) {
            $this->tags()->sync((array) $request->input('tag_ids'));
        }
    }
}
