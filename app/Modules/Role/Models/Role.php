<?php

namespace App\Modules\Role\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends SpatieRole
{
    use HasFactory, HasUuids;

    protected $primaryKey = self::ID;
    public $incrementing = false;
    protected $keyType = 'string';

    const TABLE = 'roles';
    const ID = 'id';
    const NAME = 'name';


    const ADMIN_NAME = 'Admin';
    const VET_NAME = 'Vet';
    const ASSISTANT_NAME = 'Assistant';
    const RECEPTIONIST_NAME = 'Receptionist';

    const PERMISSION_REGISTER = 'role.register';
    const PERMISSION_LIST = 'role.list';
    const PERMISSION_SHOW = 'role.show';
    const PERMISSION_EDIT = 'role.edit';
    const PERMISSION_DELETE = 'role.delete';

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when(
            filled($search),
            fn(Builder $q) => $q->where(self::NAME, 'ILIKE', "%{$search}%")
        );
    }
}
