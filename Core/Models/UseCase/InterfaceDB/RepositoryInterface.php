<?php

namespace Core\Models\UseCase\InterfaceDB;

use Core\Models\BaseModel;

/**
 * Generic interface for repository CRUD operations.
 *
 * @category Models
 * @package  Src
 * @subpackage Models/UseCase/InterfaceDB
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 *
 * @template T of BaseModel
 */
interface RepositoryInterface
{
    /**
     * Finds an entity by its ID
     *
     * @param integer $id The ID of the entry to find in the database.
     * @return T|null Returns the entity if found, null otherwise
     */
    public function findById(int $id): ?BaseModel;

    /**
     * Finds all entities
     *
     * @return array<T> Returns an array of entities
     */
    public function findAll(): array;

    /**
     * Inserts an entity into the database.
     *
     * @param BaseModel $entity The entity to insert.
     * @return integer|boolean The ID of the inserted row or false on failure.
     */
    public function insert(BaseModel $entity): int|bool;

    /**
     * Updates an entity in the database.
     *
     * @param BaseModel $entity The entity to update.
     * @return boolean True on success, false on failure.
     */
    public function update(BaseModel $entity): bool;

    /**
     * Deletes an entity by its ID
     *
     * @param integer $id The ID of the entity to delete.
     * @return boolean True on success, false on failure.
     */
    public function delete(int $id): bool;
}
