<?php

namespace Anil\FastApiCrud\Tests\TestClasses\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTagRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:tags,name,'.$this->route()->parameter('id'),
            ],
            'desc' => [
                'required',
                'string',
                'max:25500',

            ],
            'status' => [
                'required',
                'boolean',
            ],
            'active' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
