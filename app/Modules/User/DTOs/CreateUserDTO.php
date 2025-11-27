<?php

namespace App\Modules\User\DTOs;

class CreateUserDTO
{
    const EMAIL = 'email';
    const NAME = 'name';
    const SURNAME = 'surname';
    const PASSWORD = 'password';
    const AVATAR = 'avatar';
    const PHONE = 'phone';
    const TYPE_DOCUMENT = 'type_document';
    const N_DOCUMENT = 'n_document';
    const BIRTH_DATE = 'birth_date';
    const DESIGNATION = 'designation';
    const GENDER = 'gender';
    const ROLE_ID = 'role_id';

    public function __construct(
        public readonly string $email,
        public readonly string $name,
        public readonly string $surname,
        public string $password,

        public ?string $avatar = null,
        public readonly ?string $phone = null,
        public readonly ?string $type_document = null,
        public readonly ?string $n_document = null,
        public readonly ?string $birth_date = null,
        public readonly ?string $designation = null,
        public readonly ?string $gender = null,
        public readonly ?string $role_id = null,
    ) {}

    public function toArray(bool $onlyUser = false): array
    {
        $data = [
            self::EMAIL         => $this->email,
            self::NAME          => $this->name,
            self::SURNAME       => $this->surname,
            self::PASSWORD      => $this->password,
            self::AVATAR        => $this->avatar,
            self::PHONE         => $this->phone,
            self::TYPE_DOCUMENT => $this->type_document,
            self::N_DOCUMENT    => $this->n_document,
            self::BIRTH_DATE    => $this->birth_date,
            self::DESIGNATION   => $this->designation,
            self::GENDER        => $this->gender,
        ];

        if (!$onlyUser) {
            $data[self::ROLE_ID] = $this->role_id;
        }

        return array_filter($data, fn($value) => !is_null($value));
    }

    public static function fromRequest($request): self
    {
        return new self(
            email: $request->{self::EMAIL},
            name: $request->{self::NAME},
            surname: $request->{self::SURNAME},
            password: $request->{self::PASSWORD},
            avatar: $request->file(self::AVATAR),
            phone: $request->{self::PHONE},
            type_document: $request->{self::TYPE_DOCUMENT},
            n_document: $request->{self::N_DOCUMENT},
            birth_date: $request->{self::BIRTH_DATE},
            designation: $request->{self::DESIGNATION},
            gender: $request->{self::GENDER},
            role_id: $request->{self::ROLE_ID},
        );
    }
}
