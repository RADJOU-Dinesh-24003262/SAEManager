<?php

namespace App\Domain\SAE\IRepository;

use App\Domain\SAE\SaeGroup;
use Core\Models\Repository\RepositoryInterface;

/**
 * Repository interface for SAE Group persistence.
 * 
 * Extends RepositoryInterface with SAE Group-specific operations.
 *
 * @package App\Domain\SAE\IRepository
 * @extends RepositoryInterface<SaeGroup>
 */
interface ISaeGroupRepository extends RepositoryInterface
{
    /**
     * Finds all groups for a given SAE ID.
     *
     * @param int $saeId The SAE ID.
     * @return SaeGroup[] Array of SAE groups.
     * @throws \PDOException If database operation fails.
     */
    public function findBySaeId(int $saeId): array;

    /**
     * Saves a new SAE group.
     *
     * @param SaeGroup $group The group to save.
     * @return int The new group ID.
     * @throws \PDOException If database operation fails.
     */
    public function save(SaeGroup $group): int;

    /**
     * Finds groups by professor ID for a specific SAE.
     *
     * @param int $professorId The professor ID.
     * @param int $saeId The SAE ID.
     * @return SaeGroup[] Array of SAE groups.
     * @throws \PDOException If database operation fails.
     */
    public function findByProfessorId(int $professorId, int $saeId): array;

    /**
     * Gets the group ID for a student in a specific SAE.
     *
     * @param int $studentId The student ID.
     * @param int $saeId The SAE ID.
     * @return int|null The group ID or null.
     * @throws \PDOException If database operation fails.
     */
    public function getStudentGroupId(int $studentId, int $saeId): ?int;

    /**
     * Assigns a student to a group.
     *
     * @param int $studentId The student ID.
     * @param int $groupId The group ID.
     * @return bool True on success, false on failure.
     * @throws \PDOException If database operation fails.
     */
    public function assignStudent(int $studentId, int $groupId): bool;

    /**
     * Unassigns a student from a group.
     *
     * @param int $studentId The student ID.
     * @param int $groupId The group ID.
     * @return bool True on success, false on failure.
     * @throws \PDOException If database operation fails.
     */
    public function unassignStudent(int $studentId, int $groupId): bool;

    /**
     * Gets all students in a group.
     *
     * @param int $groupId The group ID.
     * @return array[] Array of student data.
     * @throws \PDOException If database operation fails.
     */
    public function getGroupStudents(int $groupId): array;

    /**
     * Gets all students available for assignment in a SAE.
     *
     * @param int $saeId The SAE ID.
     * @return array[] Array of available student data.
     * @throws \PDOException If database operation fails.
     */
    public function getAvailableStudents(int $saeId): array;

    /**
     * Gets all members (students) of all groups in a SAE.
     *
     * @param int $saeId The SAE ID.
     * @return array[] Array of member data.
     * @throws \PDOException If database operation fails.
     */
    public function getAllMembers(int $saeId): array;

    /**
     * Gets assigned group members for a professor's groups in a SAE.
     *
     * @param int $saeId The SAE ID.
     * @param int $profId The professor ID.
     * @return array[] Array of member data.
     * @throws \PDOException If database operation fails.
     */
    public function getAssignedGroupMembers(int $saeId, int $profId): array;

    /**
     * Gets student group members for a specific student in a SAE.
     *
     * @param int $saeId The SAE ID.
     * @param int $studentId The student ID.
     * @return array[] Array of group member data.
     * @throws \PDOException If database operation fails.
     */
    public function getStudentGroupMembers(int $saeId, int $studentId): array;

    /**
     * Gets SAE group members for a client's SAE.
     *
     * @param int $saeId The SAE ID.
     * @param int $clientId The client ID.
     * @return array[] Array of member data.
     * @throws \PDOException If database operation fails.
     */
    public function getClientSaeMembers(int $saeId, int $clientId): array;
}