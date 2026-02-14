<?php

namespace Models\Repository\ToDoList;

use Core\Models\Repository\BaseRepository;
use Models\Entity\ToDoList\ToDoList;
use Models\UseCase\ToDoList\InterfaceDB\ToDoListInterface;
use Override;
use PDO;
use PDOException;

/**
 * PDO implementation of ToDoListInterface.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Repository/ToDoList
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class PdoToDoListRepository extends BaseRepository implements ToDoListInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->table = 'sae_todolists';
        $this->entityClass = ToDoList::class;
    }

    /**
     * Returns the primary key of the table.
     * @return string
     */
    #[Override]
    protected function getPrimaryKey(): string
    {
        return 'todoid';
    }

    /**
     * Finds a task by ID.
     *
     * @param integer $id The task ID.
     * @return ToDoList|null The task or null if not found.
     */
    #[Override]
    public function findById(int $id): ?ToDoList
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM sae_todolists WHERE todoid = :id'
            );
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!$data) {
                return null;
            }


            $data['todo_id'] = $data['todoid'];
            unset($data['todoid']);
            $data['checked'] = (bool) $data['checked'];
            
            return new ToDoList($data);
        } catch (PDOException $e) {
            error_log("Error in PdoToDoListRepository::findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Finds all tasks for a given SAE group.
     *
     * @param integer $groupId The group ID.
     * @return array<ToDoList> The list of tasks.
     */
    #[Override]
    public function findByGroupId(int $groupId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM sae_todolists WHERE sae_group_id = :groupId ORDER BY priority ASC, todoid ASC'
            );
            $stmt->execute(['groupId' => $groupId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function ($data) {
                $data['todo_id'] = $data['todoid'];
                unset($data['todoid']);
                $data['checked'] = (bool) $data['checked'];
                return new ToDoList($data);
            }, $results);
        } catch (PDOException $e) {
            error_log("Error in PdoToDoListRepository::findByGroupId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Creates a new task.
     *
     * @param ToDoList $task The task entity to create.
     * @return ToDoList|false The created task with ID or false on failure.
     */
    #[Override]
    public function create(ToDoList $task): ToDoList|bool
    {
        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO sae_todolists (sae_group_id, tododesc, checked, priority) 
                 VALUES (:groupId, :desc, :checked, :priority) RETURNING todoid'
            );
            

            $stmt->bindValue(':groupId', $task->getSaeGroupId(), PDO::PARAM_INT);
            $stmt->bindValue(':desc', $task->getTodoDesc(), PDO::PARAM_STR);
            $stmt->bindValue(':checked', $task->isChecked(), PDO::PARAM_BOOL); 
            $stmt->bindValue(':priority', $task->getPriority(), PDO::PARAM_INT);

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($result) {
                $task->setTodoId($result['todoid']);
                return $task;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error in PdoToDoListRepository::create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates an existing task.
     *
     * @param ToDoList $task The task entity to update.
     * @return boolean True on success, false on failure.
     */
    #[Override]
    public function update(ToDoList $task): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'UPDATE sae_todolists 
                 SET tododesc = :desc, checked = :checked, priority = :priority 
                 WHERE todoid = :id'
            );

            $stmt->bindValue(':id', $task->getTodoId(), PDO::PARAM_INT);
            $stmt->bindValue(':desc', $task->getTodoDesc(), PDO::PARAM_STR);
            $stmt->bindValue(':checked', $task->isChecked(), PDO::PARAM_BOOL);
            $stmt->bindValue(':priority', $task->getPriority(), PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in PdoToDoListRepository::update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a task by ID.
     *
     * @param integer $id The task ID.
     * @return boolean True on success, false on failure.
     */
    #[Override]
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'DELETE FROM sae_todolists WHERE todoid = :id'
            );
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error in PdoToDoListRepository::delete: " . $e->getMessage());
            return false;
        }
    }
}