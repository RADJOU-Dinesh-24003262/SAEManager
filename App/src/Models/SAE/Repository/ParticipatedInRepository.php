<?php

namespace Models\SAE\Repository;

use Core\Models\Repository\BaseRepository;
use Models\SAE\ParticipatedIn;
use Override;
use PDO;
use PDOException;

/**
 * Repository for ParticipatedIn operations.
 *
 * Handles database interactions for student participation in SAE groups.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 *
 * @extends BaseRepository<ParticipatedIn>
 */
class ParticipatedInRepository extends BaseRepository
{
    /**
     * The singleton instance.
     * @var ParticipatedInRepository|null
     */
    protected static ?ParticipatedInRepository $instance = null;

    /**
     * The table name.
     * @var string
     */
    protected string $table = 'participated_in';

    /**
     * The entity class name.
     * @var class-string<ParticipatedIn>
     */
    protected string $entityClass = ParticipatedIn::class;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Gets the singleton instance.
     *
     * @return ParticipatedInRepository
     */
    public static function getInstance(): ParticipatedInRepository
    {
        if (self::$instance === null) {
            self::$instance = new ParticipatedInRepository();
        }
        return self::$instance;
    }

    /**
     * Returns the name of the primary key.
     * Note: This table has a composite primary key (student_id, sae_group_id).
     * returning one part for compatibility, but methods should handle the composite key.
     *
     * @return string
     */
    #[Override]
    protected function getPrimaryKey(): string
    {
        return 'student_id'; // Partial PK, specific methods used instead of generic findById
    }

    /**
     * Creates a new participation entry.
     *
     * @param ParticipatedIn $entity The entity to create.
     * @return ParticipatedIn The created entity.
     * @throws PDOException If creation fails.
     */
    #[Override]
    public function create($entity)
    {
        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO participated_in (student_id, sae_group_id, sae_subject_id) 
                 VALUES (:student_id, :group_id, :subject_id)'
            );
            $stmt->execute([
                'student_id' => $entity->getStudentId(),
                'group_id' => $entity->getSaeGroupId(),
                'subject_id' => $entity->getSaeSubjectId()
            ]);
            return $entity;
        } catch (PDOException $e) {
            error_log('Erreur création participation : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Updates a participation entry.
     *
     * @param ParticipatedIn $entity The entity to update.
     * @return boolean
     */
    #[Override]
    public function update($entity): bool
    {
        // Typically not updated, just deleted and re-inserted or just inserted.
        // But for completeness:
        try {
            $stmt = $this->connection->prepare(
                'UPDATE participated_in 
                 SET sae_subject_id = :subject_id 
                 WHERE student_id = :student_id AND sae_group_id = :group_id'
            );
            return $stmt->execute([
                'subject_id' => $entity->getSaeSubjectId(),
                'student_id' => $entity->getStudentId(),
                'group_id' => $entity->getSaeGroupId()
            ]);
        } catch (PDOException $e) {
            error_log('Erreur mise à jour participation : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Assigns a student to a group.
     *
     * @param integer $studentId The student ID.
     * @param integer $groupId   The group ID.
     * @return boolean
     */
    public function assignStudent(int $studentId, int $groupId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO participated_in (student_id, sae_group_id) 
                 VALUES (:student_id, :group_id)'
            );
            return $stmt->execute(['group_id' => $groupId, 'student_id' => $studentId]);
        } catch (PDOException $e) {
            error_log('Erreur assignation étudiant : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Removes a student from a group.
     *
     * @param integer $studentId The student ID.
     * @param integer $groupId   The group ID.
     * @return boolean
     */
    public function unassignStudent(int $studentId, int $groupId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'DELETE FROM participated_in 
                 WHERE student_id = :student_id AND sae_group_id = :group_id'
            );
            return $stmt->execute(['student_id' => $studentId, 'group_id' => $groupId]);
        } catch (PDOException $e) {
            error_log('Erreur désassignation étudiant : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets all students in a group.
     *
     * @param integer $groupId The group ID.
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
     * }> Array of student data.
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur récupération étudiants du groupe : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets the group ID of a student in a SAE.
     *
     * @param integer $studentId The student ID.
     * @param integer $saeId     The SAE subject ID.
     * @return integer|null
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
            return $result ? intval($result) : null;
        } catch (PDOException $e) {
            error_log('Erreur récupération groupe étudiant : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gets student count for a group.
     *
     * @param integer $groupId The group ID.
     * @return integer
     */
    public function getStudentCount(int $groupId): int
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(*) FROM participated_in WHERE sae_group_id = :group_id'
            );
            $stmt->execute(['group_id' => $groupId]);
            return intval($stmt->fetchColumn());
        } catch (PDOException $e) {
            error_log('Erreur comptage étudiants : ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Gets students not assigned to any group in a SAE.
     *
     * @param integer $saeId The SAE subject ID.
     * @return array<int, array{
     *   student_id: string,
     *   amu_id: string,
     *   year: string,
     *   major: string,
     *   td: string,
     *   tp: string,
     *   first_name: string,
     *   last_name: string,
     *   email: string
     * }>
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur récupération étudiants disponibles : ' . $e->getMessage());
            return [];
        }
    }
}
