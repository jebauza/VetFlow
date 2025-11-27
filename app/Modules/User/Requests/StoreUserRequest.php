<?php

namespace App\Modules\User\Requests;

use App\Modules\User\Models\User;
use App\Common\Requests\ApiRequest;
use App\Modules\User\DTOs\CreateUserDTO;
use App\Modules\Role\Repositories\RoleRepository;

class StoreUserRequest extends ApiRequest
{
    public function rules(): array
    {
        $validDocTypes = implode(',', User::TYPE_DOCUMENT_VALUES);
        $validGenders = implode(',', User::GENDER_VALUES);

        return [
            CreateUserDTO::EMAIL => 'required|email|unique:' . User::TABLE . ',' . User::EMAIL,
            CreateUserDTO::NAME => 'required|string|max:255',
            CreateUserDTO::SURNAME => 'required|string|max:255',
            CreateUserDTO::PASSWORD => 'required|string|min:8',

            CreateUserDTO::AVATAR => 'nullable|file|mimes:jpg,png|max:2048',
            CreateUserDTO::PHONE => 'nullable|string|max:25',
            CreateUserDTO::TYPE_DOCUMENT => "nullable|in:$validDocTypes",
            CreateUserDTO::N_DOCUMENT => 'nullable|string|max:25',
            CreateUserDTO::BIRTH_DATE => 'nullable|date',
            CreateUserDTO::DESIGNATION => 'nullable|string|max:255',
            CreateUserDTO::GENDER => "nullable|in:$validGenders",

            CreateUserDTO::ROLE_ID => 'nullable|uuid',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (empty($validator->errors()->all())) {
                if ($this->{CreateUserDTO::ROLE_ID}) {
                    $this->checkRole($validator);
                }
            }
        });
    }

    public function checkRole($validator): void
    {
        $roleRepo = app(RoleRepository::class);

        if (!$roleRepo->find($this->{CreateUserDTO::ROLE_ID})) {
            $validator->errors()->add(
                CreateUserDTO::ROLE_ID,
                "The role (" . $this->{CreateUserDTO::ROLE_ID} . ") is not valid."
            );
        }
    }
}
