<?php

namespace App\Modules\Permission\Resources;

use Illuminate\Http\Request;
use App\Modules\Permission\Models\Permission;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        parent::wrap(null);

        return [
            'id' => $this->{Permission::ID},
            'name' => $this->{Permission::NAME},
        ];
    }
}
