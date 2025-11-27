<?php

namespace App\Common\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function findOrFail(string $id)
    {
        return $this->model->findOrFail($id);
    }

    public function find(string $id)
    {
        return $this->model->find($id);
    }

    public function findWithRelations(string $id, array $relations)
    {
        return $this->model->with($relations)->find($id);
    }

    public function firstRandom()
    {
        return $this->model->inRandomOrder()->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data)
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);
        return $record;
    }

    public function delete(string $id)
    {
        return $this->model->destroy($id);
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
}
