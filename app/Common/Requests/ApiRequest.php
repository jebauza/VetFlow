<?php

namespace App\Common\Requests;

use Illuminate\Support\Str;
use App\Common\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiRequest extends FormRequest
{
    // protected function failedValidation(Validator $validator)
    // {
    //     throw new HttpResponseException(
    //         ApiResponse::validation($validator->errors())
    //     );
    // }

    public function validateUuidParam(string $paramName, $validator,): void
    {
        $uuid = $this->route($paramName);

        if (!Str::isUuid($uuid)) {
            $validator->errors()->add(
                $paramName,
                __('Must be a valid UUID.')
            );
        }
    }
}
