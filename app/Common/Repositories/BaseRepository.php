<?php

namespace App\Common\Repositories;

use App\Common\DTOs\PagePaginationDTO;
use Illuminate\Database\Eloquent\Model;
use App\Common\DTOs\OffsetPaginationDTO;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\CursorPaginator;

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

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(Model|string $record, array $data)
    {
        if (is_string($record)) {
            $record = $this->model->findOrFail($record);
        }
        $record->update($data);

        return $record;
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
        return $this->model->findOrFail($id);
    }

    public function findOrFailWithRelations(string $id, array $relations)
    {
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
