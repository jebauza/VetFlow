<?php

namespace App\Api\Auth\Requests;

use App\Models\User;
use App\Api\Common\Requests\ApiRequest;

class LoginRequest extends ApiRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            User::EMAIL => 'email|required',
            User::PASSWORD => 'required',
        ];
    }
}
