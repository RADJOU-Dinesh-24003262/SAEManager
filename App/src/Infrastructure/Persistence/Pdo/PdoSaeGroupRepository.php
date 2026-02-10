<?php

namespace App\Infrastructure\Persistence\Pdo;

use App\Domain\SAE\SaeGroup;
use App\Domain\SAE\IRepository\ISaeGroupRepository;
use Core\Database\Database;
use PDO;
use PDOException;

/**
 * PDO implementation of the SAE Group repository.
 * 
 * Handles persistence for SaeGroup entities and group member management.
 *
 * @package App\Infrastructure\Persistence\Pdo
 */
class PdoSaeGroupRepository implements ISaeGroupRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getInstance();
    }

    /**
     * Finds a SAE group by ID.
     *
     * @param int $id The group ID.
     * @return SaeGroup|null The SAE group or null if not found.
     */
    public function findById(int $id): ?SaeGroup
    {
        try {
            $stmt = $this->connection->prepare('SELECT * FROM sae_groups WHERE sae_group_id = :id');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ? $this->mapRowToSaeGroup($row) : null;
        }
        catch (PDOException $e) {
            error_log('Error finding SAE Group by ID: ' . $e->getMessage());
            return null;
        }
    }


    /**
     * Finds all groups for a SAE subject.
     *
     * @param int $saeId The SAE subject ID.
     * @return array<SaeGroup> Array of SAE groups.
     */
    public function findBySaeId(int $saeId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM sae_groups WHERE sae_subject_id = :sae_id ORDER BY sae_group_id'
            );
            $stmt->execute(['sae_id' => $saeId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'mapRowToSaeGroup'], $data ?: []);
        }
        catch (PDOException $e) {
            error_log('Error finding groups by SAE ID: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Finds all groups assigned to a professor for a specific SAE.
     *
     * @param int $professorId The professor's user ID.
     * @param int $saeId The SAE subject ID.
     * @return array<SaeGroup> Array of SAE groups.
     */
    public function findByProfessorId(int $professorId, int $saeId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM sae_groups 
                 WHERE sae_subject_id = :sae_id AND professor_id = :prof_id 
                 ORDER BY sae_group_id'
            );
            $stmt->execute(['sae_id' => $saeId, 'prof_id' => $professorId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'mapRowToSaeGroup'], $data ?: []);
        }
        catch (PDOException $e) {
            error_log('Error finding groups by professor ID: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Saves a new SAE group.
     *
     * @param SaeGroup $group The group to save.
     * @return int The generated group ID (0 on failure).
     */
    public function save(SaeGroup $group): int
    {
        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO sae_groups (sae_subject_id, professor_id) 
                 VALUES (:sae_id, :prof_id) 
                 RETURNING sae_group_id'
            );
            $stmt->execute([
                'sae_id' => $group->getSaeId(),
                'prof_id' => $group->getProfessorId()
            ]);
            $id = (int)$stmt->fetchColumn();
            $group->setId($id);
            return $id;
        }
        catch (PDOException $e) {
            error_log('Error saving SAE Group: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Updates an existing SAE group.
     *
     * @param SaeGroup $group The group with updated data.
     * @return bool True on success, false on failure.
     */
    public function update(SaeGroup $group): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'UPDATE sae_groups 
                 SET sae_subject_id = :sae_id, professor_id = :prof_id 
                 WHERE sae_group_id = :id'
            );
            return $stmt->execute([
                'sae_id' => $group->getSaeId(),
                'prof_id' => $group->getProfessorId(),
                'id' => $group->getId()
            ]);
        }
        catch (PDOException $e) {
            error_log('Error updating SAE Group: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a SAE group by ID.
     *
     * @param int $id The group ID.
     * @return bool True on success, false on failure.
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare('DELETE FROM sae_groups WHERE sae_group_id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0;
        }
        catch (PDOException $e) {
            error_log('Error deleting SAE Group: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets the group ID for a student in a specific SAE.
     *
     * @param int $studentId The student's user ID.
     * @param int $saeId The SAE subject ID.
     * @return int|null The group ID or null if student not assigned.
     */
    public function getStudentGroupId(int $studentId, int $saeId): ?int
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT pi.sae_group_id 
                FROM participated_in pi
                JOIN sae_groups sg ON pi.sae_group_id = sg.sae_group_id
                WHERE pi.student_id = :student_id AND sg.sae_subject_id = :sae_id'
            );
            $stmt->execute(['student_id' => $studentId, 'sae_id' => $saeId]);
            $result = $stmt->fetchColumn();
            return $result ? (int)$result : null;
        }
        catch (PDOException $e) {
            error_log('Error getting student group ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Assigns a student to a group.
     *
     * @param int $studentId The student's user ID.
     * @param int $groupId The group ID.
     * @return bool True on success, false on failure.
     */
    public function assignStudent(int $studentId, int $groupId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO participated_in (student_id, sae_group_id) 
                 VALUES (:student_id, :group_id)'
            );
            return $stmt->execute(['group_id' => $groupId, 'student_id' => $studentId]);
        }
        catch (PDOException $e) {
            error_log('Error assigning student: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Removes a student from a group.
     *
     * @param int $studentId The student's user ID.
     * @param int $groupId The group ID.
     * @return bool True on success, false on failure.
     */
    public function unassignStudent(int $studentId, int $groupId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'DELETE FROM participated_in 
                 WHERE student_id = :student_id AND sae_group_id = :group_id'
            );
            return $stmt->execute(['student_id' => $studentId, 'group_id' => $groupId]);
        }
        catch (PDOException $e) {
            error_log('Error unassigning student: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets all students in a group with their details.
     *
     * @param int $groupId The group ID.
     * @return array<int, array<string, mixed>> Array of student data.
     */
    public function getGroupStudents(int $groupId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT 
                    s.student_id, s.amu_id, s.year, s.major, s.td, s.tp,
                    u.first_name, u.last_name, u.email, u.phone
                FROM students s
                JOIN users u ON s.student_id = u.user_id
                JOIN participated_in pi ON s.student_id = pi.student_id
                WHERE pi.sae_group_id = :group_id
                ORDER BY u.last_name, u.first_name'
            );
            $stmt->execute(['group_id' => $groupId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        catch (PDOException $e) {
            error_log('Error retrieving group students: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets students not yet assigned to any group in a SAE.
     *
     * @param int $saeId The SAE subject ID.
     * @return array<int, array<string, mixed>> Array of available student data.
     */
    public function getAvailableStudents(int $saeId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT 
                    s.student_id, s.amu_id, s.year, s.major, s.td, s.tp,
                    u.first_name, u.last_name, u.email
                FROM students s
                JOIN users u ON s.student_id = u.user_id
                WHERE s.student_id NOT IN (
                    SELECT pi.student_id 
                    FROM participated_in pi
                    JOIN sae_groups sg ON pi.sae_group_id = sg.sae_group_id
                    WHERE sg.sae_subject_id = :sae_id
                )
                ORDER BY u.last_name, u.first_name'
            );
            $stmt->execute(['sae_id' => $saeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        catch (PDOException $e) {
            error_log('Error retrieving available students: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets all group members for a SAE (responsible professor access).
     *
     * @param int $saeId The SAE subject ID.
     * @return array<int, array<string, mixed>> Array of member data with group IDs.
     */
    public function getAllMembers(int $saeId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email, u.phone,
                        pi.sae_group_id, st.td, st.tp
                 FROM sae_groups sg
                 JOIN participated_in pi ON sg.sae_group_id = pi.sae_group_id
                 JOIN students st ON pi.student_id = st.student_id
                 JOIN users u ON st.student_id = u.user_id
                 WHERE sg.sae_subject_id = :sae_id
                 ORDER BY pi.sae_group_id, u.last_name, u.first_name'
            );
            $stmt->execute(['sae_id' => $saeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        catch (PDOException $e) {
            error_log('Error retrieving all members: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets members of groups assigned to a specific professor.
     *
     * @param int $saeId The SAE subject ID.
     * @param int $profId The professor's user ID.
     * @return array<int, array<string, mixed>> Array of member data.
     */
    public function getAssignedGroupMembers(int $saeId, int $profId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email, u.phone,
                        pi.sae_group_id, st.td, st.tp
                 FROM sae_groups sg
                 JOIN participated_in pi ON sg.sae_group_id = pi.sae_group_id
                 JOIN students st ON pi.student_id = st.student_id
                 JOIN users u ON st.student_id = u.user_id
                 WHERE sg.professor_id = :prof_id AND sg.sae_subject_id = :sae_id
                 ORDER BY pi.sae_group_id, u.last_name, u.first_name'
            );
            $stmt->execute(['prof_id' => $profId, 'sae_id' => $saeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        catch (PDOException $e) {
            error_log('Error retrieving assigned group members: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets members of the same group as a student.
     *
     * @param int $saeId The SAE subject ID.
     * @param int $studentId The student's user ID.
     * @return array<int, array<string, mixed>> Array of group member data.
     */
    public function getStudentGroupMembers(int $saeId, int $studentId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT u.user_id, u.first_name, u.last_name, u.email, u.phone,
                        st.td, st.tp, pi.sae_group_id
                 FROM participated_in pi_self
                 JOIN participated_in pi ON pi.sae_group_id = pi_self.sae_group_id
                 JOIN students st ON st.student_id = pi.student_id
                 JOIN users u ON st.student_id = u.user_id
                 JOIN sae_groups sg ON pi.sae_group_id = sg.sae_group_id
                 WHERE pi_self.student_id = :user_id AND sg.sae_subject_id = :sae_id
                 ORDER BY u.last_name, u.first_name'
            );
            $stmt->execute(['user_id' => $studentId, 'sae_id' => $saeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        catch (PDOException $e) {
            error_log('Error retrieving student group members: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets all members of SAEs commissioned by a client.
     *
     * @param int $saeId The SAE subject ID.
     * @param int $clientId The client's user ID.
     * @return array<int, array<string, mixed>> Array of member data.
     */
    public function getClientSaeMembers(int $saeId, int $clientId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email, u.phone,
                        st.sae_group_id, st.td, st.tp
                 FROM sae_subjects s
                 JOIN sae_groups sg ON s.sae_subject_id = sg.sae_subject_id
                 JOIN students st ON sg.sae_group_id = st.sae_group_id
                 JOIN users u ON st.student_id = u.user_id
                 WHERE s.client_id = :client_id AND s.sae_subject_id = :sae_id
                 ORDER BY st.sae_group_id, u.last_name, u.first_name'
            );
            $stmt->execute(['client_id' => $clientId, 'sae_id' => $saeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        catch (PDOException $e) {
            error_log('Error retrieving client SAE members: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Maps a database row to a SaeGroup entity.
     *
     * @param array<string, mixed> $row The database row.
     * @return SaeGroup The mapped SAE group entity.
     */
    private function mapRowToSaeGroup(array $row): SaeGroup
    {
        return new SaeGroup(
            (int)$row['sae_subject_id'],
            $row['professor_id'] ? (int)$row['professor_id'] : null,
            (int)$row['sae_group_id']
            );
    }

}