<?php

namespace Models\UseCase\ToDoList;

use Models\UseCase\ToDoList\InterfaceDB\ToDoListInterface;
use Exception;

/**
 * Use Case for updating a task.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/ToDoList
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class UpdateTaskUseCase
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
     * @param integer $todoId  The task ID.
     * @param array   $updates Associative array of updates ('checked', 'priority', 'tododesc').
     *
     * @return void
     * @throws Exception If task not found or update fails.
     */
    public function execute(int $todoId, array $updates): void
    {
        $task = $this->toDoListInterface->findById($todoId);

        if (!$task) {
            throw new Exception("Tâche introuvable (ID: $todoId).");
        }

        if (array_key_exists('checked', $updates)) {
            $task->setChecked((bool)$updates['checked']);
        }

        if (array_key_exists('priority', $updates)) {
            $task->setPriority((int)$updates['priority']);
        }

        if (array_key_exists('tododesc', $updates)) {
            $task->setTododesc((string)$updates['tododesc']);
        }

        if (!$this->toDoListInterface->update($task)) {
            throw new Exception("Impossible de mettre à jour la tâche.");
        }
    }
}
