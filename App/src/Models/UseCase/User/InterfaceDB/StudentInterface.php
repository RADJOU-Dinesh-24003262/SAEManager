<?php

namespace Models\UseCase\User\InterfaceDB;

use Models\Entity\User\Student;

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
 *
 * @extends UserInterface<Student>
 */
interface StudentInterface extends UserInterface
{
    /**
     * Finds students by TD group.
     *
     * @param string $td The TD group.
     * @return array<Student> Array of student entities.
     */
    public function findByTdGroup(string $td): array;

    /**
     * Finds students by TP group.
     *
     * @param string $tp The TP group.
     * @return array<Student> Array of student entities.
     */
    public function findByTpGroup(string $tp): array;

    /**
     * Checks if a student can access a SAE.
     *
     * @param integer $studentId The student ID.
     * @param integer $saeId     The SAE ID.
     * @return boolean True if accessible, false otherwise.
     */
    public function canAccessSAE(int $studentId, int $saeId): bool;

    /**
     * Checks if a student can modify a to-do.
     *
     * @param integer $studentId The student ID.
     * @param integer $todoId    The to-do ID.
     * @return boolean True if modifiable, false otherwise.
     */
    public function canModifyTodo(int $studentId, int $todoId): bool;
}
