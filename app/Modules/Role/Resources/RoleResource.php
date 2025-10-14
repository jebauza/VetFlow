<?php

namespace App\Modules\Role\Resources;

use Illuminate\Http\Request;
use App\Modules\Role\Models\Role;
use App\Modules\Permission\Models\Permission;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        parent::wrap(null);

        return [
            'id' => $this->{Role::ID},
            'name' => $this->{Role::NAME},
            'permissions' => $this->permissions->map(function ($permission) {
                return [
                    'id' => $permission->{Permission::ID},
                    'name' => $permission->{Permission::NAME},
                ];
            }),
        ];
    }
}
