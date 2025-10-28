<?php

namespace App\Modules\Auth\Requests;

use App\Modules\User\Models\User;
use App\Common\Requests\ApiRequest;

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
