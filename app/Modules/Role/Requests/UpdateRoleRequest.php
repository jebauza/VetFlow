<?php

namespace App\Modules\Role\Requests;

use App\Modules\Role\DTOs\RoleDTO;
use App\Common\Requests\ApiRequest;
use App\Modules\Permission\Models\Permission;
use App\Modules\Permission\Repositories\PermissionRepository;

class UpdateRoleRequest extends ApiRequest
{
    private ?string $roleId = null;

    public function rules(): array
    {
        $this->roleId = $this->route('role');

        return [
            RoleDTO::NAME => 'required|string|max:255|unique:roles,name,' . $this->roleId,
            RoleDTO::PERMISSION_IDS => 'present|array',
            RoleDTO::PERMISSION_IDS . '.*' => 'uuid',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateUuidParam('role', $validator);

            if (empty($validator->errors()->all())) {
                $this->checkPermissions($validator);
            }
        });
    }

    public function checkPermissions($validator): void
    {
        $permissionRepo = app(PermissionRepository::class);
        $validIds = $permissionRepo->whereIn(Permission::ID, $this->{RoleDTO::PERMISSION_IDS})->pluck(Permission::ID);
        $notValidIds = collect($this->{RoleDTO::PERMISSION_IDS})->diff($validIds);

        foreach ($notValidIds as $key => $id) {
            $validator->errors()->add(
                RoleDTO::PERMISSION_IDS . ".$key",
                "The permission ($id) is not valid."
            );
        }
    }
}
