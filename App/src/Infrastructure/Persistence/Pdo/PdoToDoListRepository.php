<?php

namespace App\Infrastructure\Persistence\Pdo;

use App\Domain\ToDoList\ToDoList;
use App\Domain\ToDoList\IRepository\IToDoListRepository;
use Core\Database\Database;
use PDO;
use PDOException;

/**
 * PDO implementation of the ToDoList repository.
 * 
 * Handles persistence for ToDoList task entities.
 *
 * @package App\Infrastructure\Persistence\Pdo
 */
class PdoToDoListRepository implements IToDoListRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getInstance();
    }


    /**
     * Finds all tasks for a SAE group.
     * 
     * Ordered by completion status (unchecked first), then priority, then ID.
     *
     * @param int $groupId The group ID.
     * @return array<ToDoList> Array of ToDoList tasks.
     */
    public function findByGroupId(int $groupId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM sae_todolists 
                 WHERE sae_group_id = :group_id 
                 ORDER BY checked ASC, priority ASC, todoid ASC'
            );
            $stmt->execute(['group_id' => $groupId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $tasks = [];
            foreach ($data as $row) {
                $tasks[] = new ToDoList(
                    (int)$row['sae_group_id'],
                    $row['tododesc'],
                    (int)$row['priority'],
                    (bool)$row['checked'],
                    (int)$row['todoid']
                    );
            }
            return $tasks;
        }
        catch (PDOException $e) {
            error_log("Erreur récupération des tâches : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Saves a new task.
     *
     * @param ToDoList $task The task to save.
     * @return int The generated task ID (0 on failure).
     */
    public function save(ToDoList $task): int
    {
        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO sae_todolists(sae_group_id, tododesc, checked, priority)
                 VALUES (:sae_group_id, :tododesc, :checked, :priority)'
            );
            $stmt->execute([
                'sae_group_id' => $task->getGroupId(),
                'tododesc' => $task->getDescription(),
                'checked' => (int)$task->isChecked(), // Cast to int for DB
                'priority' => $task->getPriority()
            ]);

            $id = (int)$this->connection->lastInsertId();
            $task->setTodoId($id);
            return $id;
        }
        catch (PDOException $e) {
            error_log("Erreur création nouvelle tâche : " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Updates an existing task.
     *
     * @param ToDoList $task The task with updated data.
     * @return bool True on success, false on failure.
     */
    public function update(ToDoList $task): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'UPDATE sae_todolists 
                 SET tododesc = :tododesc, checked = :checked, priority = :priority 
                 WHERE todoid = :todo_id'
            );
            $stmt->execute([
                'tododesc' => $task->getDescription(),
                'checked' => (int)$task->isChecked(),
                'priority' => $task->getPriority(),
                'todo_id' => $task->getTodoId()
            ]);
            return $stmt->rowCount() > 0;
        }
        catch (PDOException $e) {
            error_log("Erreur mise à jour tâche : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a task by ID.
     *
     * @param int $taskId The task ID.
     * @return bool True on success, false on failure.
     */
    public function delete(int $taskId): bool
    {
        try {
            $stmt = $this->connection->prepare('DELETE FROM sae_todolists WHERE todoid = :todo_id');
            $stmt->execute(['todo_id' => $taskId]);
            return $stmt->rowCount() > 0;
        }
        catch (PDOException $e) {
            error_log("Erreur suppression tâche : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Finds a task by ID.
     *
     * @param int $taskId The task ID.
     * @return ToDoList|null The task or null if not found.
     */
    public function findById(int $taskId): ?ToDoList
    {
        try {
            $stmt = $this->connection->prepare('SELECT * FROM sae_todolists WHERE todoid = :todo_id');
            $stmt->execute(['todo_id' => $taskId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return new ToDoList(
                    (int)$row['sae_group_id'],
                    $row['tododesc'],
                    (int)$row['priority'],
                    (bool)$row['checked'],
                    (int)$row['todoid']
                    );
            }
            return null;
        }
        catch (PDOException $e) {
            error_log("Erreur recherche tâche par ID : " . $e->getMessage());
            return null;
        }
    }
}