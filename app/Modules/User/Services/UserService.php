<?php

namespace App\Modules\User\Services;

use App\Modules\User\Models\User;
use Illuminate\Http\UploadedFile;
use App\Common\Helpers\FileHelper;
use Illuminate\Support\Facades\Hash;
use App\Common\DTOs\PagePaginationDTO;
use App\Common\DTOs\OffsetPaginationDTO;
use App\Modules\User\DTOs\CreateUserDTO;
use App\Modules\User\DTOs\UpdateUserDTO;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\CursorPaginator;

class UserService
{
    public function __construct(
        protected readonly UserRepository $userRepo
    ) {}

    public function all(string $search = null)
    {
        return $this->userRepo->search($search, true);
    }

    public function pagePaginate(?string $search = null, int $page = null, int $perPage = null): PagePaginationDTO
    {
        $page = $page ?? 1;
        $perPage = $perPage ?? 100;

        return $this->userRepo->pagination(
            $this->userRepo->baseSearch($search, true),
            $page,
            $perPage,
        );
    }

    public function offsetPaginate(?string $search, int $offset = null, int $limit = null): OffsetPaginationDTO
    {
        $offset = $offset ?? 0;
        $limit = $limit ?? 100;

        return $this->userRepo->offsetPagination(
            $this->userRepo->baseSearch($search, true),
            $offset,
            $limit
        );
    }

    public function cursorPaginate(?string $search, int $perPage = null): CursorPaginator
    {
        $perPage = $perPage ?? 100;

        return $this->userRepo->cursorPagination(
            $this->userRepo->baseSearch($search, true),
            $perPage
        );
    }

    public function findById(string $id): User
    {
        /** @var User $user */
        $user = $this->userRepo->findOrFailWithRelations($id, [
            'permissions:id,name',
            'roles.permissions:id,name'
        ]);

        return $user;
    }

    public function create(CreateUserDTO $dto): User
    {
        if ($avatar = $dto->{CreateUserDTO::AVATAR}) {
            $dto->{CreateUserDTO::AVATAR} = FileHelper::saveFile(
                $avatar,
                User::PATH_FOLDER_AVATARS,
                'public'
            );
        }

        try {
            $user = $this->userRepo->create($dto->toArray());

            // Telescope::store(app('request'));

            if ($dto->{CreateUserDTO::ROLE_ID}) {
                $user = $this->userRepo->assignRoles($user, [$dto->{CreateUserDTO::ROLE_ID}]);
            }

            return $this->userRepo->loadRelations($user, false, true);
        } catch (\Throwable $th) {
            if (is_string($dto->{CreateUserDTO::AVATAR})) {
                FileHelper::deleteFile($dto->{CreateUserDTO::AVATAR}, 'public');
            }
            throw $th;
        }
    }

    public function update(string $id, UpdateUserDTO $dto): User
    {
        $user = $this->userRepo->findOrFail($id);

        $oldAvatar = null;
        if ($avatar = $dto->{UpdateUserDTO::AVATAR}) {
            $dto->{UpdateUserDTO::AVATAR} = FileHelper::saveFile(
                $avatar,
                User::PATH_FOLDER_AVATARS,
                'public'
            );

            $oldAvatar = $user->{User::AVATAR};
        }

        try {
            $user = $this->userRepo->update($user, $dto->toArray(true));

            if ($dto->{UpdateUserDTO::ROLE_ID}) {
                $user = $this->userRepo->syncRoles($user, [$dto->{UpdateUserDTO::ROLE_ID}]);
            }

            if ($oldAvatar) {
                FileHelper::deleteFile($oldAvatar, 'public');
            }

            return $this->userRepo->loadRelations($user, false, true);
        } catch (\Throwable $th) {
            if (is_string($dto->{UpdateUserDTO::AVATAR})) {
                FileHelper::deleteFile($dto->{UpdateUserDTO::AVATAR}, 'public');
            }
            throw $th;
        }
    }

    public function delete(string $id): void
    {
        $user = $this->userRepo->findOrFail($id);

        $this->userRepo->delete($user->{User::ID});

        if ($user->{User::AVATAR}) {
            FileHelper::deleteFile($user->{User::AVATAR}, 'public');
        }
    }
}
