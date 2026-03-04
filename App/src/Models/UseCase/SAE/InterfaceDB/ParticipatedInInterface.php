<?php

namespace Models\UseCase\SAE\InterfaceDB;

/**
 * Interface for ParticipatedIn (student-group relationship) operations.
 *
 * Defines the contract for managing student participation in SAE groups.
 * This is an Interface (Clean Architecture) - interface defined in Use Cases layer.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/SAE/InterfaceDB
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
interface ParticipatedInInterface
{
    /**
     * Assigns a student to a group.
     *
     * @param integer $studentId The student ID.
     * @param integer $groupId   The SAE group ID.
     * @return boolean True on success.
     */
    public function assignStudentToGroup(int $studentId, int $groupId): bool;

    /**
     * Removes a student from a group.
     *
     * @param integer $studentId The student ID.
     * @param integer $groupId   The SAE group ID.
     * @return boolean True on success.
     */
    public function removeStudentFromGroup(int $studentId, int $groupId): bool;

    /**
     * Gets the group ID for a student in a specific SAE.
     *
     * @param integer $studentId    The student ID.
     * @param integer $saeSubjectId The SAE subject ID.
     * @return integer|null The group ID or null if not found.
     */
    public function getStudentGroupId(int $studentId, int $saeSubjectId): ?int;

    /**
     * Checks if a student is in a group.
     *
     * @param integer $studentId The student ID.
     * @param integer $groupId   The SAE group ID.
     * @return boolean True if student is in the group.
     */
    public function isStudentInGroup(int $studentId, int $groupId): bool;
}
