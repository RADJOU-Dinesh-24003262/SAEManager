<?php

namespace Core\Models\Repository;

/**
 * Common interface for all repositories
 *
 * @category Model
 * @package  Src
 * @subpackage Models/Repository
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 *
 * @template T
 */
interface RepositoryInterface
{
    /**
     * Finds an entity by its ID in the database
     *
     * @param integer $id The ID of the entity.
     * @return T|null The entity if found, null otherwise.
     */
    public function findById(int $id);

    // phpcs:disable Squiz.Commenting.FunctionComment.TypeHintMissing
    /**
     * Inserts a new entity (entry) in the database
     *
     * @param T $entity The entity to insert.
     * @return T The inserted entity.
     */
    public function insert($entity);
    // phpcs:enable Squiz.Commenting.FunctionComment.TypeHintMissing

    // phpcs:disable Squiz.Commenting.FunctionComment.TypeHintMissing
    /**
     * Updates an entity (entry) in the database with new data
     *
     * @param T $entity The entity to update.
     * @return boolean True on success, false on failure.
     */
    public function update($entity): bool;
    // phpcs:enable Squiz.Commenting.FunctionComment.TypeHintMissing

    /**
     * Finds all entities in the database
     *
     * @return array<T> An array of entities.
     */
    public function findAll(): array;

    /**
     * Counts the total number of records
     *
     * @return integer The total number of records.
     */
    public function count(): int;

    /**
     * Deletes an entity by its ID in the database
     *
     * @param integer $id The ID of the entity to delete.
     * @return boolean True on success, false on failure.
     */
    public function delete(int $id): bool;
}
