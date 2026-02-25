<?php

namespace Models\Repository\ToDoList;

use Core\Models\Repository\BaseRepository;
use Models\Entity\ToDoItem\ToDoItem;
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
 * @extends    BaseRepository<ToDoItem>
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
        $this->entityClass = ToDoItem::class;
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
     * Finds all tasks for a given SAE group.
     *
     * @param integer $groupId The group ID.
     * @return array<ToDoItem> The list of tasks.
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
                return new ToDoItem($data);
            }, $results);
        } catch (PDOException $e) {
            error_log("Error in PdoToDoListRepository::findByGroupId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Updates an existing task.
     *
     * @param object $task The task entity to update.
     * @return boolean True on success, false on failure.
     */
    #[Override]
    public function update(object $task): bool
    {
        if (!$task instanceof ToDoItem) {
            return false;
        }

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
}
