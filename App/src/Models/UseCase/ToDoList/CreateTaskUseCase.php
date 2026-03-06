<?php

namespace Models\UseCase\ToDoList;

use Models\Entity\ToDoItem\ToDoItem;
use Models\UseCase\ToDoList\InterfaceDB\ToDoListInterface;
use Exception;

/**
 * Use Case for creating a task.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/ToDoList
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class CreateTaskUseCase
{
    /**
     * @var ToDoListInterface
     */
    private ToDoListInterface $toDoListInterface;

    /**
     * Constructor.
     *
     * @param ToDoListInterface $toDoListInterface The repository.
     */
    public function __construct(ToDoListInterface $toDoListInterface)
    {
        $this->toDoListInterface = $toDoListInterface;
    }

    /**
     * Executes the use case.
     *
     * @param integer $groupId     The group ID.
     * @param string  $description The task description.
     * @param integer $priority    The task priority.
     *
     * @return ToDoItem The created task.
     * @throws Exception If creation fails.
     */
    public function execute(int $groupId, string $description, int $priority, string $endDate): ToDoItem
    {
        $task = new ToDoItem([
            'sae_group_id' => $groupId,
            'tododesc' => $description,
            'priority' => $priority,
            'checked' => false,
            'end_date' => $endDate
        ]);

        $taskId = $this->toDoListInterface->insert($task);
        if (!$taskId) {
            throw new Exception("Impossible de créer la tâche.");
        }

        $task->setTodoId((int)$taskId);
        return $task;
    }
}
