<?php

namespace Models\UseCase\User\InterfaceDB;

use Models\Entity\User\Student;
use Core\Models\UseCase\InterfaceDB\RepositoryInterface;

/**
 * Interface for Student repository operations.
 *
 * Defines the contract for student-specific data access.
 * This is an Interface (Clean Architecture) - interface defined in Use Cases layer.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User/InterfaceDB
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 * @extends RepositoryInterface<Student>
 */
interface StudentInterface extends RepositoryInterface, RoleAccessInterface
{
    /**
     * Checks if a student can modify a to-do.
     *
     * @param integer $studentId The student ID.
     * @param integer $todoId    The to-do ID.
     * @return boolean True if modifiable, false otherwise.
     */
    public function canModifyTodo(int $studentId, int $todoId): bool;
    /**
     * Finds students who are not participating in a specific SAE.
     *
     * @param integer $saeId The SAE subject ID.
     * @return array<Student> Array of students not in the SAE.
     */
    public function findStudentsNotInSAE(int $saeId): array;
}
