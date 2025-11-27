<?php

namespace App\Modules\Role\Resources;

use Illuminate\Http\Request;
use App\Modules\Role\Models\Role;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleLiteResource extends JsonResource
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
        ];
    }
}
