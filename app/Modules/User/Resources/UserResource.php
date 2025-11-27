<?php

namespace App\Modules\User\Resources;

use Illuminate\Http\Request;
use App\Modules\User\Models\User;
use Illuminate\Http\Resources\MissingValue;
use App\Modules\Permission\Models\Permission;
use App\Modules\Role\Resources\RoleLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Permission\Resources\PermissionResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        parent::wrap(null);

        $roles = ($value = $this->whenLoaded('roles')) instanceof MissingValue ? collect([]) : $value;
        $permissions = ($value = $this->whenLoaded('permissions')) instanceof MissingValue ? collect([]) : $value;

        foreach ($roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }

        return [
            'id' => $this->{User::ID},
            'name' => $this->{User::NAME},
            'surname' => $this->{User::SURNAME},
            'email' => $this->{User::EMAIL},
            'avatar' => $this->urlAvatar,
            'phone' => $this->{User::PHONE},
            'type_document' => $this->{User::TYPE_DOCUMENT},
            'n_document' => $this->{User::N_DOCUMENT},
            'birth_date' => $this->{User::BIRTH_DATE},

            'roles' => RoleLiteResource::collection($roles),
            'all_permissions' => PermissionResource::collection($permissions->unique(Permission::ID)->values()),
        ];
    }
}
