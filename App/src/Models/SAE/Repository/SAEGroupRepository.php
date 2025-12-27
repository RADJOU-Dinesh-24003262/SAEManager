<?php

namespace Models\SAE\Repository;

use Core\includes\Database;
use PDO;
use PDOException;
use Models\SAE\SAEGroup;

/**
 * Repository for SAEGroup operations.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/SAE
 * @author     SAE Manager Team
 * @license    MIT License https://opensource.org/licenses/MIT
 */
class SAEGroupRepository
{
    protected PDO $connection;
    protected static ?SAEGroupRepository $instance = null;

    private function __construct()
    {
        $this->connection = Database::getInstance();
    }

    public static function getInstance(): SAEGroupRepository
    {
        if (self::$instance === null) {
            self::$instance = new SAEGroupRepository();
        }
        return self::$instance;
    }

    /**
     * Finds a group by ID
     *
     * @param integer $id The group ID
     * @return SAEGroup|null
     */
    public function findById(int $id): ?SAEGroup
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM sae_groups WHERE sae_group_id = :id'
            );
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ? new SAEGroup($data) : null;
        } catch (PDOException $e) {
            error_log('Erreur récupération groupe : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Finds all groups for a SAE
     *
     * @param integer $saeId The SAE subject ID
     * @return array<SAEGroup>
     */
    public function findBySaeId(int $saeId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM sae_groups WHERE sae_subject_id = :sae_id ORDER BY sae_group_id'
            );
            $stmt->execute(['sae_id' => $saeId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($row) => new SAEGroup($row), $data);
        } catch (PDOException $e) {
            error_log('Erreur récupération groupes : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Creates a new group
     *
     * @param integer $saeId The SAE subject ID
     * @return SAEGroup The created group
     * @throws PDOException
     */
    public function create(int $saeId): SAEGroup
    {
        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO sae_groups (sae_subject_id) VALUES (:sae_id) RETURNING sae_group_id'
            );
            $stmt->execute(['sae_id' => $saeId]);
            $id = intval($stmt->fetchColumn());

            return new SAEGroup(['sae_group_id' => $id, 'sae_subject_id' => $saeId]);
        } catch (PDOException $e) {
            error_log('Erreur création groupe : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Deletes a group
     *
     * @param integer $id The group ID
     * @return boolean
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare('DELETE FROM sae_groups WHERE sae_group_id = :id');
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log('Erreur suppression groupe : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets all students in a group
     *
     * @param integer $groupId The group ID
     * @return array<int, array{
     *   student_id: string,
     *   amu_id: string,
     *   year: string,
     *   major: string,
     *   td: string,
     *   tp: string,
     *   first_name: string,
     *   last_name: string,
     *   email: string,
     *   phone: string|null
     * }> Array of student data
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
                WHERE s.sae_group_id = :group_id
                ORDER BY u.last_name, u.first_name'
            );
            $stmt->execute(['group_id' => $groupId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur récupération étudiants du groupe : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Assigns a student to a group
     *
     * @param integer $studentId The student ID
     * @param integer $groupId   The group ID
     * @return boolean
     */
    public function assignStudent(int $studentId, int $groupId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'UPDATE students SET sae_group_id = :group_id WHERE student_id = :student_id'
            );
            return $stmt->execute(['group_id' => $groupId, 'student_id' => $studentId]);
        } catch (PDOException $e) {
            error_log('Erreur assignation étudiant : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Removes a student from a group
     *
     * @param integer $studentId The student ID
     * @return boolean
     */
    public function unassignStudent(int $studentId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'UPDATE students SET sae_group_id = NULL WHERE student_id = :student_id'
            );
            return $stmt->execute(['student_id' => $studentId]);
        } catch (PDOException $e) {
            error_log('Erreur désassignation étudiant : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets the group ID of a student in a SAE
     *
     * @param integer $studentId The student ID
     * @param integer $saeId     The SAE subject ID
     * @return integer|null
     */
    public function getStudentGroupId(int $studentId, int $saeId): ?int
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT st.sae_group_id 
                FROM students st
                JOIN sae_groups sg ON st.sae_group_id = sg.sae_group_id
                WHERE st.student_id = :student_id AND sg.sae_subject_id = :sae_id'
            );
            $stmt->execute(['student_id' => $studentId, 'sae_id' => $saeId]);
            $result = $stmt->fetchColumn();
            return $result ? intval($result) : null;
        } catch (PDOException $e) {
            error_log('Erreur récupération groupe étudiant : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gets student count for a group
     *
     * @param integer $groupId The group ID
     * @return integer
     */
    public function getStudentCount(int $groupId): int
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(*) FROM students WHERE sae_group_id = :group_id'
            );
            $stmt->execute(['group_id' => $groupId]);
            return intval($stmt->fetchColumn());
        } catch (PDOException $e) {
            error_log('Erreur comptage étudiants : ' . $e->getMessage());
            return 0;
        }
    }
}
