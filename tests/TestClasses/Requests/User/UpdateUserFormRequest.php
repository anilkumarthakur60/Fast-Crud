<?php

namespace Anil\FastApiCrud\Tests\TestClasses\Requests\User;

use Anil\FastApiCrud\Tests\TestClasses\Models\UserModel;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var UserModel $route */
        $route = $this->route('user');

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
                'unique:users,email,'.$route->id,
            ],
            'password' => [
                'nullable',
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
