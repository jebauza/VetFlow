<?php

namespace App\Modules\User\Requests;

use Illuminate\Support\Str;
use App\Modules\User\Models\User;
use App\Common\Requests\ApiRequest;
use App\Modules\User\DTOs\UpdateUserDTO;
use App\Modules\Role\Repositories\RoleRepository;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserRequest extends ApiRequest
{
    private ?string $userId = null;

    public function rules(): array
    {
        $this->userId = $this->route('user');

        if (!Str::isUuid($this->userId)) {
            throw new HttpResponseException(
                response()->json(['role' => [__('Must be a valid UUID.')]], 422)
            );
        }

        return [
            UpdateUserDTO::EMAIL => 'required|email|unique:' . User::TABLE . ',' . User::EMAIL . ',' . $this->userId,
            UpdateUserDTO::NAME => 'required|string|max:255',
            UpdateUserDTO::SURNAME => 'required|string|max:255',
            UpdateUserDTO::PASSWORD => 'required|string|min:8',

            UpdateUserDTO::AVATAR => 'nullable|file|mimes:jpg,png|max:2048',
            UpdateUserDTO::PHONE => 'nullable|string|max:25',
            UpdateUserDTO::TYPE_DOCUMENT => 'nullable|string|max:50',
            UpdateUserDTO::N_DOCUMENT => 'nullable|string|max:25',
            UpdateUserDTO::BIRTH_DATE => 'nullable|date',
            UpdateUserDTO::DESIGNATION => 'nullable|string|max:255',

            UpdateUserDTO::ROLE_ID => 'nullable|uuid',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (empty($validator->errors()->all())) {
                if ($this->{UpdateUserDTO::ROLE_ID}) {
                    $this->checkRole($validator);
                }
            }
        });
    }

    public function checkRole($validator): void
    {
        $roleRepo = app(RoleRepository::class);

        if (!$roleRepo->find($this->{UpdateUserDTO::ROLE_ID})) {
            $validator->errors()->add(
                UpdateUserDTO::ROLE_ID,
                __("The role (" . $this->{UpdateUserDTO::ROLE_ID} . ") is not valid.")
            );
        }
    }
}
