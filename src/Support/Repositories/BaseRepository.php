<?php

namespace Bahramn\EcdIpg\Support\Repositories;

use Bahramn\EcdIpg\Support\Interfaces\EloquentRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @package Bahramn\EcdIpg\Support\Repositories
 */
abstract class BaseRepository implements EloquentRepositoryInterface
{
    /**
     * Model instance.
     *
     * @var Model
     */
    protected Model $model;

    /**w
     * BaseRepository constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->makeModel();
    }

    /**
     * Return the class path of repository's model.
     *
     * @return string
     */
    abstract protected function getModelClass(): string;

    /**
     * Returns the model.
     *
     * @return Model|Builder
     */
    protected function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model
    {
        return $this->getModel()->create($attributes);
    }

    /**
     * @param int $id
     * @return Model|null
     */
    public function find(int $id): ?Model
    {
        return $this->getModel()->find($id);
    }

    /**
     * @param int $id
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): Model
    {
        return $this->getModel()->findOrFail($id);
    }

    /**
     * Create new instance of model.
     *
     * @throws Exception
     */
    private function makeModel()
    {
        $model = app($this->getModelClass());

        if (!$model instanceof Model) {
            throw new Exception('The model should extend the Eloquent.');
        }

        $this->model = $model;
    }
}
