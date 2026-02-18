<?php

namespace Models\UseCase\ToDoList;

use Models\UseCase\ToDoList\InterfaceDB\ToDoListInterface;
use Models\Entity\ToDoItem\ToDoItem;

/**
 * Use Case for retrieving tasks.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/ToDoList
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class GetTasksUseCase
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
     * @param integer $groupId The group ID.
     * @return array<ToDoItem> The list of tasks.
     */
    public function execute(int $groupId): array
    {
        return $this->toDoListInterface->findByGroupId($groupId);
    }
}
