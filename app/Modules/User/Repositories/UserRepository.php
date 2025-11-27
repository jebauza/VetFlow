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
     * Synchronize permissions for a given user.
     * Any existing permissions not in the provided array will be removed, and new ones will be added.
     *
     * @param User $user The user model to sync permissions for.
     * @param array $permissionIds An array of permission IDs to assign to the user.
     * @return User The refreshed user model with updated permissions.
     */
    public function syncPermissionIdsToUser(User $user, array $permissionIds): User
    {
        $user->syncPermissions($permissionIds);

        return $user->load('permissions:id,name');
    }

    /**
     * Synchronize roles for a given user.
     * Any existing roles not in the provided array will be removed, and new ones will be added.
     *
     * @param User $user The user model to sync roles for.
     * @param array $roleIds An array of role IDs to assign to the user.
     * @return User The refreshed user model with updated roles.
     */
    public function syncRoleIdsToUser(User $user, array $roleIds): User
    {
        $user->syncRoles($roleIds);

        return $user->load('roles:id,name');
    }

    /**
     * Assign one or more roles to a user.
     * This method adds roles without detaching existing ones.
     *
     * @param User $user The user model to assign roles to.
     * @param array $roleIds An array of role IDs to assign.
     * @return User The refreshed user model with assigned roles.
     */
    public function assignRoles(User $user, array $roleIds): User
    {
        $user->assignRole($roleIds);

        return $user->load('roles:id,name');
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
