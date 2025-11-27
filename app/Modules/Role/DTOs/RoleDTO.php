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

    public function toArray(bool $onlyModel = false): array
    {
        $data = [
            self::NAME              => $this->{self::NAME},
        ];

        if (!$onlyModel) {
            $data[self::PERMISSION_IDS] = $this->{self::PERMISSION_IDS};
        }

        return array_filter($data, fn($value) => !is_null($value));
    }
}
