<?php

namespace Models\UseCase\ToDoList;

use Models\UseCase\ToDoList\InterfaceDB\ToDoListInterface;
use Exception;

/**
 * Use Case for deleting a task.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/ToDoList
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class DeleteTaskUseCase
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
     * @param integer $todoId The task ID to delete.
     *
     * @return void
     * @throws Exception If deletion fails.
     */
    public function execute(int $todoId): void
    {
        if (!$this->toDoListInterface->delete($todoId)) {
            throw new Exception("Impossible de supprimer la tâche (ID: $todoId).");
        }
    }
}
