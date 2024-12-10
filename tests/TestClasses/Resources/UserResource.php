<?php

namespace Anil\FastApiCrud\Tests\TestClasses\Resources;

use Anil\FastApiCrud\Tests\TestClasses\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserModel
 */
class UserResource extends JsonResource
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
            'email' => $this['email'],
            'password' => $this['password'],
            'status' => $this['status'],
            'active' => $this['active'],
        ];
    }
}
