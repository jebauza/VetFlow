<?php

namespace App\Modules\Auth\DTOs\Inputs;

use Illuminate\Foundation\Http\FormRequest;

class LoginInputDTO
{
    const EMAIL = 'email';
    const PASSWORD = 'password';

    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}

    public static function fromFormRequest(FormRequest $request): self
    {
        return new self(
            email: $request->{self::EMAIL},
            password: $request->{self::PASSWORD},
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data[self::EMAIL],
            password: $data[self::PASSWORD],
        );
    }

    public function toArray(): array
    {
        return [
            self::EMAIL => $this->email,
            self::PASSWORD => $this->password,
        ];
    }
}
