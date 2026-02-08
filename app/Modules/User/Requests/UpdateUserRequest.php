<?php

namespace App\Modules\User\Requests;

use App\Modules\User\Models\User;
use App\Common\Requests\ApiRequest;
use App\Modules\User\DTOs\UpdateUserDTO;
use App\Modules\Role\Repositories\RoleRepository;

class UpdateUserRequest extends ApiRequest
{
    private ?string $userId = null;

    public function rules(): array
    {
        $this->userId = $this->route('user');

        $validDocTypes = implode(',', User::TYPE_DOCUMENT_VALUES);
        $validGenders = implode(',', User::GENDER_VALUES);

        return [
            UpdateUserDTO::EMAIL => 'required|email|unique:' . User::TABLE . ',' . User::EMAIL . ',' . $this->userId,
            UpdateUserDTO::NAME => 'required|string|max:255',
            UpdateUserDTO::SURNAME => 'required|string|max:255',
            UpdateUserDTO::PASSWORD => 'required|string|min:8',

            UpdateUserDTO::AVATAR => 'nullable|file|mimes:jpg,png|max:2048',
            UpdateUserDTO::PHONE => 'nullable|string|max:25',
            UpdateUserDTO::TYPE_DOCUMENT => "nullable|string|in:$validDocTypes",
            UpdateUserDTO::N_DOCUMENT => 'nullable|string|max:25',
            UpdateUserDTO::BIRTH_DATE => 'nullable|date',
            UpdateUserDTO::DESIGNATION => 'nullable|string|max:255',
            UpdateUserDTO::GENDER => "nullable|string|in:$validGenders",
            UpdateUserDTO::ROLE_ID => 'nullable|uuid',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateUuidParam('user', $validator);

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
