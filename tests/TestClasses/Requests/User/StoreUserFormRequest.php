<?php

namespace Anil\FastApiCrud\Tests\TestClasses\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
