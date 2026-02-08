<?php

namespace App\Modules\Role\Resources;

use Illuminate\Http\Request;
use App\Modules\Role\Models\Role;
use App\Modules\Permission\Models\Permission;
use App\Modules\Permission\Resources\PermissionResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

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
            'date' => Carbon::create($this->{Role::CREATED_AT})->toDateTimeString(),
            'permissions' => PermissionResource::collection(
                $this->whenLoaded('permissions')
            ),
        ];
    }
}
