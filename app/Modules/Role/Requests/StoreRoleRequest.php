<?php

namespace App\Modules\Role\Requests;

use App\Common\Requests\ApiRequest;
use App\Models\Permission;

class StoreRoleRequest extends ApiRequest
{
    const NAME = 'name';
    const PERMISSION_IDS = 'permission_ids';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            self::NAME => 'required|string|unique:roles,name',
            self::PERMISSION_IDS => 'present|array',
            self::PERMISSION_IDS . '.*' => 'uuid',
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
        $permissions = Permission::whereIn(Permission::ID, $this->{self::PERMISSION_IDS})->pluck(Permission::ID);
        $notValidIds = collect($this->{self::PERMISSION_IDS})->diff($permissions);

        foreach ($notValidIds as $key => $id) {
            $validator->errors()->add(
                self::PERMISSION_IDS . ".$key",
                "The permission ($id) is not valid."
            );
        }
    }
}
