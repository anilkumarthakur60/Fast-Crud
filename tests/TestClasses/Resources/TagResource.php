<?php

namespace Anil\FastApiCrud\Tests\TestClasses\Resources;

use Anil\FastApiCrud\Tests\TestClasses\Models\TagModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TagModel
 */
class TagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'name' => $this['name'],
            'desc' => $this['desc'],
            'status' => $this['status'],
            'active' => $this['active'],
            'created_at' => $this['created_at'],
            'updated_at' => $this['updated_at'],
            'deleted_at' => $this['deleted_at'],
        ];
    }
}
