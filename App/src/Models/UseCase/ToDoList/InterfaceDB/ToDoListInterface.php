<?php

namespace Models\UseCase\ToDoList\InterfaceDB;

use Models\Entity\ToDoList\ToDoList;

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
     * @return ToDoList|null The task or null if not found.
     */
    public function findById(int $id): ?ToDoList;

    /**
     * Finds all tasks for a given SAE group.
     *
     * @param integer $groupId The group ID.
     * @return array<ToDoList> The list of tasks.
     */
    public function findByGroupId(int $groupId): array;

    /**
     * Creates a new task.
     *
     * @param ToDoList $task The task entity to create.
     * @return ToDoList|false The created task with ID or false on failure.
     */
    public function create(ToDoList $task): ToDoList|bool;

    /**
     * Updates an existing task.
     *
     * @param ToDoList $task The task entity to update.
     * @return boolean True on success, false on failure.
     */
    public function update(ToDoList $task): bool;

    /**
     * Deletes a task by ID.
     *
     * @param integer $id The task ID.
     * @return boolean True on success, false on failure.
     */
    public function delete(int $id): bool;
}