<?php

namespace App\Modules\User\Repositories;

use App\Modules\User\Models\User;
use App\Common\Helpers\UuidHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    /**
     * Retrieve all users from the database.
     *
     * @return Collection A collection of User models.
     */
    public function all(): Collection
    {
        return User::all();
    }

    /**
     * Find a user by their ID.
     *
     * @param mixed $id The ID of the user to find.
     * @return User The found User model.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If no user is found.
     */
    public function find($id): User
    {
        return User::findOrFail($id);
    }

    /**
     * Find a single user by a specific column and value.
     *
     * @param string $column The column name to search by (e.g., 'email').
     * @param string $value The value to match in the specified column.
     * @return User The found User model.
     */
    public function findOneBy(string $column, string $value): User
    {
        return User::firstWhere($column, $value);
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
        string $password
    ): void {
        User::upsert(
            [
                User::ID => UuidHelper::newBinaryUuid(),
                User::NAME => $name,
                User::SURNAME => $surname,
                User::EMAIL => $email,
                User::EMAIL_VERIFIED_AT => now(),
                User::PASSWORD => Hash::make($password),
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

        return $user->refresh();
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

        return $user->refresh();
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

        return $user->refresh();
    }
}
