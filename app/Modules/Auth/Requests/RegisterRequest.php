<?php

namespace App\Modules\Auth\Requests;

use App\Modules\User\DTOs\UserDTO;
use App\Common\Requests\ApiRequest;

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
            UserDTO::NAME => 'required',
            UserDTO::SURNAME => 'required',
            UserDTO::EMAIL => 'required|email|unique:users',
            UserDTO::PASSWORD => 'required|min:8',
        ];
    }
}
