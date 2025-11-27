<?php

namespace App\Modules\User\Services;

use App\Modules\User\Models\User;
use Illuminate\Http\UploadedFile;
use App\Common\Helpers\FileHelper;
use Illuminate\Support\Facades\Hash;
use App\Modules\User\DTOs\CreateUserDTO;
use App\Modules\User\DTOs\UpdateUserDTO;
use App\Modules\User\Repositories\UserRepository;

class UserService
{
    public function __construct(
        protected readonly UserRepository $userRepo
    ) {}

    public function getUsers(string $search = null)
    {
        return $this->userRepo->getBySearch($search, true);
    }

    public function getUserById(string $id): User
    {
        return $this->userRepo->findOrFail($id, true);
    }

    public function createUser(CreateUserDTO $createUserDTO, UploadedFile $avatar = null): User
    {
        $createUserDTO->{CreateUserDTO::PASSWORD} = Hash::make($createUserDTO->{CreateUserDTO::PASSWORD});

        if ($avatar) {
            $createUserDTO->{CreateUserDTO::AVATAR} = FileHelper::saveFile(
                $avatar,
                User::PATH_FOLDER_AVATARS,
                'public'
            );
        }

        try {
            $user = $this->userRepo->create($createUserDTO->toArray());

            if ($createUserDTO->{CreateUserDTO::ROLE_ID}) {
                $user = $this->userRepo->syncRoleIdsToUser($user, [$createUserDTO->{CreateUserDTO::ROLE_ID}]);
            }

            return $this->userRepo->loadRelations($user, false, true);
        } catch (\Throwable $th) {
            if ($createUserDTO->{CreateUserDTO::AVATAR}) {
                FileHelper::deleteFile($createUserDTO->{CreateUserDTO::AVATAR}, 'public');
            }
            throw $th;
        }
    }

    public function updateUser(string $id, UpdateUserDTO $updateUserDTO, ?UploadedFile $avatar): User
    {
        $updateUserDTO->{UpdateUserDTO::PASSWORD} = Hash::make($updateUserDTO->{UpdateUserDTO::PASSWORD});
        $oldAvatar = null;

        if ($avatar) {
            $updateUserDTO->{UpdateUserDTO::AVATAR} = FileHelper::saveFile(
                $avatar,
                User::PATH_FOLDER_AVATARS,
                'public'
            );

            $oldAvatar = $this->userRepo->findOrFail($id)->{User::AVATAR};
        }

        try {
            $user = $this->userRepo->update($id, $updateUserDTO->toArray(true));

            if ($updateUserDTO->{UpdateUserDTO::ROLE_ID}) {
                $user = $this->userRepo->syncRoleIdsToUser($user, [$updateUserDTO->{CreateUserDTO::ROLE_ID}]);
            }

            if ($oldAvatar) {
                FileHelper::deleteFile($oldAvatar, 'public');
            }

            return $this->userRepo->loadRelations($user, false, true);
        } catch (\Throwable $th) {
            if ($updateUserDTO->{UpdateUserDTO::AVATAR}) {
                FileHelper::deleteFile($updateUserDTO->{UpdateUserDTO::AVATAR}, 'public');
            }
            throw $th;
        }
    }

    public function deleteUser(string $id)
    {
        $user = $this->userRepo->findOrFail($id);

        $this->userRepo->delete($user->{User::ID});

        if ($user->{User::AVATAR}) {
            FileHelper::deleteFile($user->{User::AVATAR}, 'public');
        }
    }
}
