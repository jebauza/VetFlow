<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Common\Fakers\IdentityProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\User\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Registrar provider
        $this->faker->addProvider(new IdentityProvider($this->faker));

        $docType = fake()->randomElement([
            User::TYPE_DOCUMENT_DNI_VALUE,
            User::TYPE_DOCUMENT_NIE_VALUE,
            User::TYPE_DOCUMENT_PASSPORT_VALUE,
        ]);

        $gender = fake()->randomElement([
            User::GENDER_MALE_VALUE,
            User::GENDER_FEMALE_VALUE,
            User::GENDER_OTHER_VALUE,
        ]);

        return [
            User::EMAIL => fake()->unique()->safeEmail(),
            User::NAME => fake()->name($gender),
            User::SURNAME => fake()->lastName($gender),
            User::EMAIL_VERIFIED_AT => now(),
            User::PASSWORD => Hash::make('123456789'),
            User::REMEMBER_TOKEN => Str::random(10),

            User::AVATAR => null,
            User::PHONE => fake()->e164PhoneNumber(),
            User::TYPE_DOCUMENT => $docType,
            User::N_DOCUMENT => $docType ? $this->getDocument($docType) : null,
            User::GENDER => $gender,

            User::BIRTH_DATE => fake()->dateTimeBetween('-65 years', '-20 years')->format('Y-m-d'),
            // User::DESIGNATION => null,
            // User::IS_SUPERADMIN => false,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withPassword(string $password): static
    {
        return $this->state(fn(array $attributes) => [
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Generates a document number based on the document type.
     *
     * @param string $docType The type of the document ('DNI', 'NIE', 'PASSPORT').
     * @return string The generated document number or an empty string if the type is invalid.
     */
    private function getDocument(string $docType): string
    {
        switch ($docType) {
            case User::TYPE_DOCUMENT_DNI_VALUE:
                return $this->faker->dni();
            case User::TYPE_DOCUMENT_NIE_VALUE:
                return $this->faker->nie();
            case User::TYPE_DOCUMENT_PASSPORT_VALUE:
                return $this->faker->passport();

            default:
                return '';
        }
    }
}
