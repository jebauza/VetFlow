<?php

namespace App\Modules\Role\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory, HasUuids;

    protected $primaryKey = self::ID;
    public $incrementing = false;
    protected $keyType = 'string';

    const TABLE = 'permissions';
    const ID = 'id';
    const NAME = 'name';


    const ADMIN_NAME = 'admin';
    const VET_NAME = 'vet';
    const ASSISTANT_NAME = 'assistant';
    const RECEPTIONIST_NAME = 'receptionist';
}
