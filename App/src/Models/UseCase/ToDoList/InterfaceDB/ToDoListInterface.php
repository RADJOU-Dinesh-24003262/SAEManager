<?php

namespace Models\UseCase\ToDoList\InterfaceDB;

use Models\Entity\ToDoItem\ToDoItem;

/**
 * Interface for ToDoList repository.
 *
 * Defines the contract for ToDoList data access.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/ToDoList/InterfaceDB
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
interface ToDoListInterface
{
    /**
     * Finds a task by ID.
     *
     * @param integer $id The task ID.
     * @return ToDoItem|null The task or null if not found.
     */
    public function findById(int $id): ?ToDoItem;

    /**
     * Finds all tasks for a given SAE group.
     *
     * @param integer $groupId The group ID.
     * @return array<ToDoItem> The list of tasks.
     */
    public function findByGroupId(int $groupId): array;

    /**
     * Inserts a new task.
     *
     * @param ToDoItem $task The task entity to insert.
     * @return integer|boolean The ID of the created task or false on failure.
     */
    public function insert(ToDoItem $task): int|bool;

    /**
     * Updates an existing task.
     *
     * @param ToDoItem $task The task entity to update.
     * @return boolean True on success, false on failure.
     */
    public function update(ToDoItem $task): bool;

    /**
     * Deletes a task by ID.
     *
     * @param integer $id The task ID.
     * @return boolean True on success, false on failure.
     */
    public function delete(int $id): bool;
}
