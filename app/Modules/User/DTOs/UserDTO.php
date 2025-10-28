<?php

namespace App\Modules\User\DTOs;

class UserDTO
{
    const NAME = 'name';
    const SURNAME = 'surname';
    const EMAIL = 'email';
    const PASSWORD = 'password';

    public function __construct(
        public readonly string $name,
        public readonly string $surname,
        public readonly string $email,
        public readonly string $password
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            name: $request->{self::NAME},
            surname: $request->{self::SURNAME},
            email: $request->{self::PASSWORD},
            password: $request->{self::EMAIL}
        );
    }
}
