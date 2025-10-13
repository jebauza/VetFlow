<?php

namespace App\Modules\Role\DTOs;

class RoleDTO
{
    const NAME = 'name';
    const PERMISSION_IDS = 'permission_ids';

    public function __construct(
        public readonly string $name,
        public readonly array $permission_ids = []
    ) {}
}
