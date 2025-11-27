<?php

namespace App\Modules\Auth\Requests;

use App\Modules\User\Models\User;
use App\Common\Requests\ApiRequest;
use App\Modules\User\DTOs\CreateUserDTO;

class RegisterRequest extends ApiRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            CreateUserDTO::EMAIL => 'required|email|unique:' . User::TABLE . ',' . User::EMAIL,
            CreateUserDTO::NAME => 'required|string|max:255',
            CreateUserDTO::SURNAME => 'required|string|max:255',
            CreateUserDTO::PASSWORD => 'required|string|min:8'
        ];
    }
}
