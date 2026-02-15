<?php

namespace Models\UseCase\ToDoList;

use Models\Entity\ToDoList\ToDoList;
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
     * @return ToDoList The created task.
     * @throws Exception If creation fails.
     */
    public function execute(int $groupId, string $description, int $priority): ToDoList
    {
        $task = new ToDoList([
            'sae_group_id' => $groupId,
            'tododesc' => $description,
            'priority' => $priority,
            'checked' => false
        ]);

        $createdTask = $this->toDoListInterface->create($task);

        if (!$createdTask) {
            throw new Exception("Impossible de créer la tâche.");
        }

        return $createdTask;
    }
}