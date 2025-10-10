<?php

namespace App\Modules\Role\Requests;

use App\Common\Requests\ApiRequest;
use App\Models\Permission;

class StoreRoleRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'required|array',
            'permissions.*' => 'uuid',
        ];
    }

    /**
     * Method withValidator
     *
     * @param $validator $validator [explicite description]
     *
     * @return void
     */
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
        $permissions = Permission::whereIn(Permission::ID, $this->permissions)->pluck(Permission::ID);

        $notValidIds = collect($this->permissions)->diff($permissions);

        foreach ($notValidIds as $key => $id) {
            $validator->errors()->add(
                'permissions.' . $key,
                "El permiso '$id' no es válido."
            );
        }
    }
}
