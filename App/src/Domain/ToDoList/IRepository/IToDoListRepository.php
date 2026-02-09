<?php

namespace App\Domain\ToDoList\IRepository;

use App\Domain\ToDoList\ToDoList;
use Core\Models\Repository\RepositoryInterface;

/**
 * Repository interface for ToDoList persistence.
 * 
 * Extends RepositoryInterface with ToDoList-specific operations.
 *
 * @package App\Domain\ToDoList\IRepository
 * @extends RepositoryInterface<ToDoList>
 */
interface IToDoListRepository extends RepositoryInterface
{
    /**
     * Finds all ToDoList items for a specific SAE group.
     *
     * @param int $groupId The SAE group ID.
     * @return ToDoList[] Array of ToDoList tasks.
     * @throws \PDOException If database operation fails.
     */
    public function findByGroupId(int $groupId): array;

    /**
     * Saves a new ToDoList task.
     *
     * @param ToDoList $task The task to save.
     * @return int The new task ID.
     * @throws \PDOException If database operation fails.
     */
    public function save(ToDoList $task): int;
}