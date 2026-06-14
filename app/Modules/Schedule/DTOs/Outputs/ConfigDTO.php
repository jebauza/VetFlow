<?php

namespace App\Modules\Schedule\DTOs\Outputs;

use App\Modules\Role\Models\Role;
use Illuminate\Support\Collection;

readonly class ConfigDTO
{
    const ROLES = 'roles';
    const HOURS = 'hours';

    /**
     * @param Collection<int, Role> $roles
     * @param Collection<int, HourItemDTO> $hours
     */
    public function __construct(
        public Collection $roles,
        public Collection $hours
    ) {}
}
