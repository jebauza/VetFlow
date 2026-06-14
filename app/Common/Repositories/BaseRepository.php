<?php

namespace App\Common\Repositories;

use App\Common\DTOs\PagePaginationDTO;
use Illuminate\Database\Eloquent\Model;
use App\Common\DTOs\OffsetPaginationDTO;
use App\Common\Helpers\DbExceptionHelper;
use App\Common\Helpers\UuidHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function query()
    {
        return $this->model->newQuery();
    }

    public function create(array $data): Model
    {
        try {
            return $this->model->create($data);
        } catch (QueryException $e) {
            DbExceptionHelper::handle(static::class, $e);
        }
    }

    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        try {
            return $this->model->updateOrCreate($attributes, $values);
        } catch (QueryException $e) {
            DbExceptionHelper::handle(static::class, $e);
        }
    }

    public function update(Model|string $record, array $data): Model
    {
        if (is_string($record)) {
            $record = $this->model->findOrFail($record);
        }

        try {
            $record->update($data);
            return $record;
        } catch (QueryException $e) {
            DbExceptionHelper::handle(static::class, $e);
        }
    }

    public function delete(Model|string $record)
    {
        if (is_string($record)) {
            $record = $this->model->findOrFail($record);
        }

        // return $this->model->destroy($id);
        return $record->delete();
    }

    public function deleteAll(): void
    {
        $this->model->query()->delete();
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function count(): int
    {
        return $this->model->count();
    }

    public function find(string $id)
    {
        return $this->model->find($id);
    }

    public function findWithRelations(string $id, array $relations)
    {
        return $this->model->with($relations)->find($id);
    }

    public function findOrFail(string $id)
    {
        if (!UuidHelper::isUuid($id)) {
            throw new ModelNotFoundException(
                "Invalid UUID '{$id}' for [" . get_class($this->model) . "]."
            );
        }

        return $this->model->findOrFail($id);
    }

    public function findOrFailWithRelations(string $id, array $relations)
    {
        if (!UuidHelper::isUuid($id)) {
            throw new ModelNotFoundException(
                "Invalid UUID '{$id}' for [" . get_class($this->model) . "]."
            );
        }

        return $this->model->with($relations)->findOrFail($id);
    }

    public function first()
    {
        return $this->model->first();
    }

    public function firstWithRelations(array $relations)
    {
        return $this->model->with($relations)->first();
    }

    public function random(int $limit = 1): Model|Collection
    {
        $query = $this->model->inRandomOrder();

        return ($limit === 1) ? $query->first() : $query->limit($limit)->get();
    }

    public function randomWithRelations(array $relations, int $limit = 1): Model|Collection
    {
        $query = $this->model->with($relations)
            ->inRandomOrder()
            ->limit($limit);

        return ($limit === 1) ? $query->first() : $query->get();
    }

    public function whereIn(string $column, array $values): Collection
    {
        return $this->model->whereIn($column, $values)->get();
    }

    public function whereNotIn(string $column, array $values): Collection
    {
        return $this->model->whereNotIn($column, $values)->get();
    }

    public function load($model, array $relations)
    {
        return $model->load($relations);
    }

    public function pagination(Builder $query, int $page, int $perPage): PagePaginationDTO
    {
        $total = $query->clone()->count();
        $items = $query->forPage($page, $perPage)->get();

        return new PagePaginationDTO(
            $items,
            $total,
            $perPage,
            $page
        );
    }

    public function offsetPagination(Builder $query, int $offset, int $limit): OffsetPaginationDTO
    {
        $total = $query->clone()->count();
        $items = $query->offset($offset)->limit($limit)->get();

        return new OffsetPaginationDTO(
            $items,
            $total,
            $limit,
            $offset
        );
    }

    public function cursorPagination(Builder $query, int $perPage): CursorPaginator
    {
        return $query->cursorPaginate($perPage);
    }
}
