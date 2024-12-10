<?php

namespace Anil\FastApiCrud\Tests\TestClasses\Resources;

use Anil\FastApiCrud\Tests\TestClasses\Models\PostModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PostModel
 */
class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this['name'],
            'desc' => $this['desc'],
            'user_id' => $this['user_id'],
            'status' => $this['status'],
            'active' => $this['active'],
        ];
    }
}
