<?php

namespace Anil\FastApiCrud\Tests\TestSetup\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'required',
                'string',
                'max:25500',
            ],
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
