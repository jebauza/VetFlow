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


    const ADMIN_NAME = 'admin';
    const VET_NAME = 'vet';
    const ASSISTANT_NAME = 'assistant';
    const RECEPTIONIST_NAME = 'receptionist';


    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when(
            filled($search),
            fn(Builder $q) => $q->where(self::NAME, 'LIKE', "%{$search}%")
        );
    }
}
