<?php

namespace Bahramn\EcdIpg\Support\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @package Bahramn\EcdIpg\Support\Interfaces
 */
interface EloquentRepositoryInterface
{
    /**
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model;

    /**
     * @param int $id
     * @return Model|null
     */
    public function find(int $id): ?Model;

    /**
     * @param int $id
     * @return Model|null
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): Model;
}
