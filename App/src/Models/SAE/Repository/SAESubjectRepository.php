<?php

namespace Models\SAE\Repository;

use Core\includes\exception\ExceptionBD\ExceptionFetchDataBD;
use PDO;
use PDOException;
use Models\SAE\SAESubject;
use Models\Repository\BaseRepository;
use PDepend\Util\Log;

/**
 * Repository for SAESubject operations.
 *
 * Handles all database interactions for SAE subjects.
 * Follows the Repository pattern for data access abstraction.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 *
 * @extends BaseRepository<SAESubject>
 */
class SAESubjectRepository extends BaseRepository
{
    /**
     * The singleton instance.
     *
     * @var SAESubjectRepository|null
     */
    protected static ?SAESubjectRepository $instance = null;

    /**
     * The table name.
     *
     * @var string
     */
    protected string $table = 'sae_subjects';

    /**
     * The entity class name.
     *
     * @var class-string<SAESubject>
     */
    protected string $entityClass = SAESubject::class;

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
     * @return SAESubjectRepository
     */
    public static function getInstance(): SAESubjectRepository
    {
        if (self::$instance === null) {
            self::$instance = new SAESubjectRepository();
        }
        return self::$instance;
    }

    /**
     * Returns the name of the primary key.
     *
     * @return string
     */
    protected function getPrimaryKey(): string
    {
        return 'sae_subject_id';
    }

    /**
     * Finds all SAE subjects.
     *
     * @return array<SAESubject> The list of all SAE subjects in the database.
     * @throws ExceptionFetchDataBD If data cannot be fetched.
     */
    public function findAll(): array
    {
        try {
            $stmt = $this->connection->query(
                'SELECT * FROM sae_subjects ORDER BY begin_date DESC'
            );

            if (!$stmt) {
                throw new ExceptionFetchDataBD();
            }

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map(fn($row) => new SAESubject($row), $data);
        } catch (PDOException $e) {
            error_log('Erreur récupération SAEs : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Finds SAE subjects by professor ID.
     *
     * @param integer $professorId The professor's user ID.
     * @return array<SAESubject> The list of SAE subjects associated with the professor.
     * @throws ExceptionFetchDataBD If data cannot be fetched.
     */
    public function findByProfessorId(int $professorId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT DISTINCT s.*
                FROM sae_subjects s
                WHERE s.responsible_prof_id = :prof_id
                   OR s.sae_subject_id IN (
                       SELECT spg.sae_subject_id 
                       FROM sae_professor_groups spg 
                       WHERE spg.professor_id = :prof_id
                   )
                   OR client_id = :prof_id 
                ORDER BY s.begin_date DESC'
            );
            $stmt->execute(['prof_id' => $professorId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map(fn($row) => new SAESubject($row), $data);
        } catch (PDOException $e) {
            error_log('Erreur récupération SAEs du professeur : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Finds SAE subjects by student ID.
     *
     * @param integer $studentId The student's user ID.
     * @return array<SAESubject> The list of SAE subjects associated with the student.
     * @throws ExceptionFetchDataBD If data cannot be fetched.
     */
    public function findByStudentId(int $studentId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT s.* 
                FROM sae_subjects s
                JOIN sae_groups sg ON s.sae_subject_id = sg.sae_subject_id
                JOIN students st ON sg.sae_group_id = st.sae_group_id
                WHERE st.student_id = :student_id
                ORDER BY s.begin_date DESC'
            );
            $stmt->execute(['student_id' => $studentId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($row) => new SAESubject($row), $data);
        } catch (PDOException $e) {
            error_log('Erreur récupération SAEs de l\'étudiant : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Finds SAE subjects by client ID.
     *
     * @param integer $clientId The client's user ID.
     * @return array<SAESubject> The list of SAE subjects associated with the client.
     * @throws ExceptionFetchDataBD If data cannot be fetched.
     */
    public function findByClientId(int $clientId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM sae_subjects 
                WHERE client_id = :client_id 
                ORDER BY begin_date DESC'
            );
            $stmt->execute(['client_id' => $clientId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($row) => new SAESubject($row), $data);
        } catch (PDOException $e) {
            error_log('Erreur récupération SAEs du client : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    // phpcs:disable Squiz.Commenting.FunctionComment.TypeHintMissing
    /**
     * Creates a new SAE subject.
     *
     * @param SAESubject $subject The SAE subject to create.
     * @return SAESubject The created SAE with ID.
     * @throws PDOException If creation fails.
     */
    public function create($subject)
    {
        try {
            $this->connection->beginTransaction();

            $stmt = $this->connection->prepare(
                'INSERT INTO sae_subjects 
                (responsible_prof_id, client_id, subject_name, begin_date, end_date, file_path)
                VALUES (:responsible_prof_id, :client_id, :subject_name, :begin_date, :end_date, :file_path)
                RETURNING sae_subject_id'
            );

            $stmt->execute([
                'responsible_prof_id' => $subject->getResponsibleProfId(),
                'client_id' => $subject->getClientId(),
                'subject_name' => $subject->getSubjectName(),
                'begin_date' => $subject->getBeginDate(),
                'end_date' => $subject->getEndDate(),
                'file_path' => $subject->getFilePath()
            ]);

            $id = intval($stmt->fetchColumn());
            $subject->setSaeSubjectId($id);

            $this->connection->commit();
            return $subject;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log('Erreur création SAE : ' . $e->getMessage());
            throw $e;
        }
    }
    // phpcs:enable Squiz.Commenting.FunctionComment.TypeHintMissing

    // phpcs:disable Squiz.Commenting.FunctionComment.TypeHintMissing
    /**
     * Updates a SAE subject.
     *
     * @param SAESubject $subject The SAE subject to update.
     * @return boolean True on success.
     * @throws PDOException If update fails.
     */
    public function update($subject): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'UPDATE sae_subjects 
                SET responsible_prof_id = :responsible_prof_id,
                    client_id = :client_id,
                    subject_name = :subject_name,
                    begin_date = :begin_date,
                    end_date = :end_date,
                    file_path = :file_path
                WHERE sae_subject_id = :id'
            );

            return $stmt->execute([
                'id' => $subject->getSaeSubjectId(),
                'responsible_prof_id' => $subject->getResponsibleProfId(),
                'client_id' => $subject->getClientId(),
                'subject_name' => $subject->getSubjectName(),
                'begin_date' => $subject->getBeginDate(),
                'end_date' => $subject->getEndDate(),
                'file_path' => $subject->getFilePath()
            ]);
        } catch (PDOException $e) {
            error_log('Erreur mise à jour SAE : ' . $e->getMessage());
            throw $e;
        }
    }
    // phpcs:enable Squiz.Commenting.FunctionComment.TypeHintMissing

    /**
     * Gets responsible professor info.
     *
     * @param integer $saeId The SAE subject ID.
     * @return array{
     *   user_id: string,
     *   first_name: string,
     *   last_name: string,
     *   email: string,
     *   phone: string|null,
     *   amu_id: string
     * }|null The professor info or null.
     */
    public function getResponsibleProfessor(int $saeId): ?array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT 
                    u.user_id, u.first_name, u.last_name, u.email, u.phone,
                    p.amu_id
                FROM sae_subjects s
                JOIN professors p ON s.responsible_prof_id = p.professor_id
                JOIN users u ON p.professor_id = u.user_id
                WHERE s.sae_subject_id = :sae_id'
            );
            $stmt->execute(['sae_id' => $saeId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log('Erreur récupération professeur responsable : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gets all professors info for a SAE.
     *
     * @param integer $saeId The SAE subject ID.
     * @return array<int, array{
     *   user_id: string,
     *   first_name: string,
     *   last_name: string,
     *   email: string,
     *   phone: string|null,
     *   amu_id: string,
     *   is_responsible: int
     * }>
     * @throws PDOException If query fails.
     */
    public function getAllProfessorsInfo(int $saeId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT DISTINCT
                    u.user_id, u.first_name, u.last_name, u.email, u.phone,
                    p.amu_id,
                    CASE 
                        WHEN s.responsible_prof_id = p.professor_id THEN true 
                        ELSE false 
                    END as is_responsible
                FROM sae_subjects s
                LEFT JOIN sae_professor_groups spg ON s.sae_subject_id = spg.sae_subject_id
                JOIN professors p ON (p.professor_id = spg.professor_id OR p.professor_id = s.responsible_prof_id)
                JOIN users u ON p.professor_id = u.user_id
                WHERE s.sae_subject_id = :sae_id
                ORDER BY is_responsible DESC, u.last_name, u.first_name'
            );
            $stmt->execute(['sae_id' => $saeId]);

            if (!$stmt) {
                throw new PDOException('Statement preparation failed');
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur récupération infos professeurs : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets client info for a SAE.
     *
     * @param integer $saeId The SAE subject ID.
     * @return array{
     *   user_id: string,
     *   first_name: string,
     *   last_name: string,
     *   email: string,
     *   phone: string|null,
     *   organisation: string
     * }|null The client info or null.
     */
    public function getClientInfo(int $saeId): ?array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT 
                    u.user_id, u.first_name, u.last_name, u.email, u.phone,
                    c.organisation
                FROM sae_subjects s
                JOIN clients c ON s.client_id = c.client_id
                JOIN users u ON c.client_id = u.user_id
                WHERE s.sae_subject_id = :sae_id'
            );
            $stmt->execute(['sae_id' => $saeId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log('Erreur récupération info client : ' . $e->getMessage());
            return null;
        }
    }
}
