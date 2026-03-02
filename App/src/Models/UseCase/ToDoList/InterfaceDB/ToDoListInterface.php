<?php

namespace Models\UseCase\ToDoList\InterfaceDB;

use Core\Models\UseCase\InterfaceDB\RepositoryInterface;
use Models\Entity\ToDoItem\ToDoItem;

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
 *
 * @extends RepositoryInterface<ToDoItem>
 */
interface ToDoListInterface extends RepositoryInterface
{
    /**
     * Finds all tasks for a given SAE group.
     *
     * @param integer $groupId The group ID.
     * @return array<ToDoItem> The list of tasks.
     */
    public function findByGroupId(int $groupId): array;
}
