<?php

namespace Models\Repository;

/**
 * Common interface for all repositories
 *
 * @template T
 */
interface RepositoryInterface
{
    /**
     * Finds an entity by its ID in the database
     *
     * @param integer $id
     * @return T|null
     */
    public function findById(int $id);

    /**
     * Creates a new entity (entry) in the database
     *
     * @param T $entity
     * @return T
     */
    public function create($entity);

    /**
     * Updates an entity (entry) in the database with new data
     *
     * @param T $entity
     * @return boolean
     */
    public function update($entity): bool;

    /**
     * Deletes an entity by its ID in the database
     *
     * @param integer $id
     * @return boolean
     */
    public function delete(int $id): bool;
}
