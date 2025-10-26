<?php

namespace App\Modules\Role\Requests;

use Illuminate\Support\Str;
use App\Modules\Role\DTOs\RoleDTO;
use App\Common\Requests\ApiRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Modules\Permission\Repositories\PermissionRepository;

class UpdateRoleRequest extends ApiRequest
{
    private ?string $roleId = null;

    public function rules(): array
    {
        $this->roleId = $this->route('role');

        if (!Str::isUuid($this->roleId)) {
            throw new HttpResponseException(
                response()->json(['role' => [__('Must be a valid UUID.')]], 422)
            );
        }

        return [
            RoleDTO::NAME => 'required|string|unique:roles,name,' . $this->roleId,
            RoleDTO::PERMISSION_IDS => 'present|array',
            RoleDTO::PERMISSION_IDS . '.*' => 'uuid',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (empty($validator->errors()->all())) {
                $this->checkPermissions($validator);
            }
        });
    }

    public function checkPermissions($validator): void
    {
        $permissionRepo = app(PermissionRepository::class);
        $validIds = $permissionRepo->getValidIds($this->{RoleDTO::PERMISSION_IDS});
        $notValidIds = collect($this->{RoleDTO::PERMISSION_IDS})->diff($validIds);

        foreach ($notValidIds as $key => $id) {
            $validator->errors()->add(
                RoleDTO::PERMISSION_IDS . ".$key",
                "The permission ($id) is not valid."
            );
        }
    }
}
