<?php

namespace App\Modules\Auth\Requests;

use App\Common\Requests\ApiRequest;

class LoginRequest extends ApiRequest
{
    const EMAIL = 'email';
    const PASSWORD = 'password';

    public function rules(): array
    {
        return [
            self::EMAIL => 'required|email',
            self::PASSWORD => 'required|string|min:8',
        ];
    }
}
