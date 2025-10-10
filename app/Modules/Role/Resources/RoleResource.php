<?php

namespace App\Modules\Role\Resources;

use App\Models\Permission;
use Illuminate\Http\Request;
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
            'id' => $this->id,
            'name' => $this->name,
            'permissions' => $this->permissions->map(function ($permission) {
                return [
                    'id' => $permission->{Permission::ID},
                    'name' => $permission->{Permission::NAME},
                ];
            }),
        ];
    }
}
