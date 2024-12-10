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
            'name' => $this['name'],
            'desc' => $this['desc'],
            'status' => $this['status'],
            'active' => $this['active'],
        ];
    }
}
