<?php

namespace App\Repositories;

interface RepositoryInterface
{
    /**
     * Creates a record.
     *
     * @param array $options  The record options.
     * @return mixed
     */
    public function create(array $options);

    /**
     * Updates a record.
     *
     * @param array $where  The conditions for finding a record.
     * @param array $options  The record options.
     * @return mixed
     */
    public function update(array $where, array $options);

    /**
     * Deletes a record.
     *
     * @param array $where  The conditions for finding a record.
     * @return mixed
     */
    public function delete(array $where);
}
