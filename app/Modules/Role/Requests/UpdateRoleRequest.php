<?php

namespace App\Modules\Role\Requests;

use Illuminate\Support\Str;
use App\Common\Requests\ApiRequest;
use App\Modules\Permission\Models\Permission;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRoleRequest extends ApiRequest
{
    private ?string $roleId = null;

    const NAME = 'name';
    const PERMISSION_IDS = 'permission_ids';

    protected function prepareForValidation(): void
    {
        $this->roleId = $this->route('role');

        if (!Str::isUuid($this->roleId)) {
            throw new HttpResponseException(
                response()->json(['role' => [__('Must be a valid UUID.')]], 422)
            );
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $this->roleId = $this->route('role');

        return [
            self::NAME => 'required|string|unique:roles,name,' . $this->roleId,
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
