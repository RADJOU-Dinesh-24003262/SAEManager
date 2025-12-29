<?php

namespace Models\SAE\Repository;

use Core\includes\Database;
use Override;
use PDO;
use PDOException;
use Models\SAE\SAEGroup;
use Core\Models\Repository\BaseRepository;

/**
 * Repository for SAEGroup operations.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 *
 * @extends BaseRepository<SAEGroup>
 */
class SAEGroupRepository extends BaseRepository
{
    /**
     * The singleton instance.
     * @var SAEGroupRepository|null
     */
    protected static ?SAEGroupRepository $instance = null;

    /**
     * The table name.
     *
     * @var string
     */
    protected string $table = 'sae_groups';

    /**
     * The entity class name.
     *
     * @var class-string<SAEGroup>
     */
    protected string $entityClass = SAEGroup::class;

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
     * @return SAEGroupRepository
     */
    public static function getInstance(): SAEGroupRepository
    {
        if (self::$instance === null) {
            self::$instance = new SAEGroupRepository();
        }
        return self::$instance;
    }

    /**
     * Returns the name of the primary key.
     *
     * @return string
     */
    #[Override]
    protected function getPrimaryKey(): string
    {
        return 'sae_group_id';
    }

    /**
     * Finds all groups for a SAE.
     *
     * @param integer $saeId The SAE subject ID.
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

    // phpcs:disable Squiz.Commenting.FunctionComment.TypeHintMissing
    /**
     * Creates a new group.
     *
     * @param SAEGroup $entity The group to create.
     * @return SAEGroup The created group.
     * @throws PDOException If creation fails.
     */
    #[Override]
    public function create($entity)
    {
        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO sae_groups (sae_subject_id) VALUES (:sae_id) RETURNING sae_group_id'
            );
            $stmt->execute(['sae_id' => $entity->getSaeSubjectId()]);
            $id = intval($stmt->fetchColumn());

            $entity->setSaeGroupId($id);
            return $entity;
        } catch (PDOException $e) {
            error_log('Erreur création groupe : ' . $e->getMessage());
            throw $e;
        }
    }
    // phpcs:enable Squiz.Commenting.FunctionComment.TypeHintMissing

    // phpcs:disable Squiz.Commenting.FunctionComment.TypeHintMissing
    /**
     * Updates a group.
     *
     * @param SAEGroup $entity The group to update.
     * @return boolean True on success.
     */
    #[Override]
    public function update($entity): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'UPDATE sae_groups SET sae_subject_id = :sae_id WHERE sae_group_id = :id'
            );
            return $stmt->execute([
                'sae_id' => $entity->getSaeSubjectId(),
                'id' => $entity->getSaeGroupId()
            ]);
        } catch (PDOException $e) {
            error_log('Erreur mise à jour groupe : ' . $e->getMessage());
            return false;
        }
    }
    // phpcs:enable Squiz.Commenting.FunctionComment.TypeHintMissing

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
                'UPDATE students SET sae_group_id = :group_id WHERE student_id = :student_id'
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
     * Gets student count for a group.
     *
     * @param integer $groupId The group ID.
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
