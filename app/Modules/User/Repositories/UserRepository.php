<?php

namespace App\Modules\User\Repositories;

use App\Modules\User\Models\User;
use App\Common\Helpers\UuidHelper;
use Illuminate\Support\Facades\Hash;
use App\Modules\User\DTOs\UpdateUserDTO;
use Illuminate\Database\Eloquent\Builder;
use App\Common\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findOrFail(string $id, bool $withRelations = false): User
    {
        return User::when($withRelations, function (Builder $q) {
            $q->with([
                'permissions:id,name',
                'roles.permissions:id,name'
            ]);
        })->findOrFail($id);
    }

    public function findOneBy(string $column, string $value): User
    {
        return User::firstWhere($column, $value);
    }

    public function getBySearch(?string $search, bool $withRelations = false): Collection
    {
        return User::withoutSuperAdmin()
            ->when(filled($search), function (Builder $q) use ($search) {
                $q->where(User::NAME, 'ILIKE', "%{$search}%")
                    ->orWhere(User::SURNAME, 'ILIKE', "%{$search}%")
                    ->orWhere(User::EMAIL, 'ILIKE', "%{$search}%")
                    ->orWhere(User::N_DOCUMENT, 'ILIKE', "%{$search}%");
            })
            ->when($withRelations, function (Builder $q) {
                $q->with([
                    'permissions:id,name',
                    'roles.permissions:id,name'
                ]);
            })
            ->orderByRaw('LOWER( CONCAT(' . User::NAME . ',' . User::SURNAME . ') ) ASC')
            ->get();
    }

    public function update(string $id, array $updateUserDTO): User
    {
        $user = User::updateOrCreate(
            [
                User::ID => $id
            ],
            [
                User::NAME => $updateUserDTO[UpdateUserDTO::NAME],
                User::SURNAME => $updateUserDTO[UpdateUserDTO::SURNAME],
                User::EMAIL => $updateUserDTO[UpdateUserDTO::EMAIL],
                User::PASSWORD => $updateUserDTO[UpdateUserDTO::PASSWORD],
                User::AVATAR => $updateUserDTO[UpdateUserDTO::AVATAR],
            ]
        );

        return $user;
    }

    /**
     * Delete all users except those whose specified column values are in the given array.
     *
     * @param string $column The column name to check against (defaults to User::ID).
     * @param array $values An array of values to exclude from deletion.
     * @return int The number of deleted users.
     */
    public function deleteAllExcept(string $column = User::ID, array $values): int
    {
        return User::whereNotIn($column, $values)->delete();
    }

    /**
     * Insert or update a user record in the database.
     * If a user with the given email exists, their name and password will be updated.
     * Otherwise, a new user will be created.
     *
     * @param string $name The name of the user.
     * @param string $surname The surname of the user.
     * @param string $email The email of the user (used for unique identification).
     * @param string $password The plain text password for the user.
     * @return void
     */
    public function upsert(
        string $name,
        string $surname,
        string $email,
        string $password,
        bool $superadmin = false
    ): void {
        User::upsert(
            [
                User::ID => UuidHelper::newBinaryUuid(),
                User::NAME => $name,
                User::SURNAME => $surname,
                User::EMAIL => $email,
                User::EMAIL_VERIFIED_AT => now(),
                User::PASSWORD => Hash::make($password),
                User::IS_SUPERADMIN => $superadmin,
            ],
            User::EMAIL,
            [
                User::NAME,
                User::PASSWORD,
            ]
        );
    }

    /**
     * Assigns an array of permissions to a user.
     *
     * This method can accept either a User object directly or a string representing the user's ID.
     * If a string ID is provided, the user is first retrieved from the database.
     * The permissions are then assigned to the user using the `givePermissionTo` method.
     *
     * @param User|string $user The User model instance or the ID of the user to assign permissions to.
     * @param array<int, string> $permissionIds An array of permission IDs (integers or strings) to be assigned.
     * @return User The User model instance with the assigned permissions.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the user with the given ID is not found.
     */
    public function assignPermissions(User|string $user, array $permissionIds): User
    {
        if (is_string($user)) {
            $user = $this->findOrFail($user);
        }

        $user->givePermissionTo($permissionIds);

        return $user;
    }

    /**
     * Synchronizes the permissions for a given user.
     *
     * This method accepts either a User model instance or a user ID (string).
     * If a string is provided, it attempts to find the user by that ID.
     * It then syncs the provided permission IDs to the user and reloads the user with its updated permissions.
     *
     * @param User|string $user The user object or the ID of the user whose permissions are to be synchronized.
     * @param array<int, string> $permissionIds An array of permission IDs to synchronize with the the user.
     * @return User
     */
    public function syncPermissions(User|string $user, array $permissionIds): User
    {
        if (is_string($user)) {
            $user = $this->findOrFail($user);
        }

        $user->syncPermissions($permissionIds);

        return $user;
    }

    /**
     * Assigns one or more roles to a user.
     *
     * This method accepts either a User model instance or a user ID string.
     * It assigns the specified roles to the user and then reloads the user
     * with their updated roles (containing only 'id' and 'name' fields).
     *
     * @param User|string $user The user model instance or the ID of the user to assign roles to.
     * @param array<int, string> $roleIds An array of role IDs to assign to the user.
     * @return User
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If a user with the given ID is not found.
     */
    public function assignRoles(User|string $user, array $roleIds): User
    {
        if (is_string($user)) {
            $user = $this->findOrFail($user);
        }

        $user->assignRole($roleIds);

        return $user;
    }

    /**
     * Synchronizes roles for a given user.
     *
     * This method can accept either a User model instance or a user ID string.
     * If a string ID is provided, it will attempt to find the user first.
     *
     * @param User|string $user The User model instance or the ID of the user.
     * @param array<int, string> $roleIds An array of role IDs to synchronize with the user.
     * @return User
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If a user with the given ID is not found.
     */
    public function syncRoles(User|string $user, array $roleIds): User
    {
        if (is_string($user)) {
            $user = $this->findOrFail($user);
        }

        $user->syncRoles($roleIds);

        return $user;
    }

    public function getAllPermissions(User $user): Collection
    {
        return $user->getAllPermissions();
    }

    public function getRoles(User $user): Collection
    {
        return $user->roles;
    }

    public function loadRelations(
        User $user,
        bool $permissions = false,
        bool $roles = false
    ): User {
        $relations = [];

        if ($permissions) {
            $relations[] = 'permissions:id,name';
        }

        if ($roles) {
            $relations[] = 'roles:id,name';
        }

        return $user->load($relations);
    }
}
