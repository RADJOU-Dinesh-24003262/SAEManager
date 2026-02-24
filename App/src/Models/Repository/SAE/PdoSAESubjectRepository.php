<?php

namespace Models\Repository\SAE;

use Core\includes\exception\ExceptionBD\ExceptionFetchDataBD;
use Core\Models\Repository\BaseRepository;
use Models\Entity\SAE\SAESubject;
use Models\UseCase\SAE\InterfaceDB\SAESubjectInterface;
use Override;
use PDO;
use PDOException;

/**
 * PDO implementation of SAESubjectInterface.
 *
 * This is the Infrastructure layer implementation of the Interface.
 * It implements the Interface defined in the Use Cases layer.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Repository/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 *
 * @extends BaseRepository<SAESubject>
 */
class PdoSAESubjectRepository extends BaseRepository implements SAESubjectInterface
{
    /**
     * The singleton instance.
     *
     * @var PdoSAESubjectRepository|null
     */
    protected static ?PdoSAESubjectRepository $instance = null;

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
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns the name of the primary key.
     *
     * @return string
     */
    #[Override]
    protected function getPrimaryKey(): string
    {
        return 'sae_subject_id';
    }



    /**
     * Finds all SAE subjects.
     *
     * @return array<SAESubject>
     * @throws ExceptionFetchDataBD If the database query fails.
     */
    #[Override]
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
            return array_map(fn ($row) => new SAESubject($row), $data);
        } catch (PDOException $e) {
            error_log('Erreur récupération SAEs : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Finds SAE subjects by professor ID.
     *
     * @param integer $professorId The professor's user ID.
     * @return array<SAESubject>
     * @throws ExceptionFetchDataBD If the database query fails.
     */
    #[Override]
    public function findByProfessorId(int $professorId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT DISTINCT s.*
                FROM sae_subjects s
                WHERE s.responsible_prof_id = :prof_id
                   OR s.sae_subject_id IN (
                       SELECT sg.sae_subject_id 
                       FROM sae_groups sg
                       WHERE sg.professor_id = :prof_id
                   )
                   OR client_id = :prof_id 
                ORDER BY s.begin_date DESC'
            );
            $stmt->execute(['prof_id' => $professorId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map(fn ($row) => new SAESubject($row), $data);
        } catch (PDOException $e) {
            error_log('Erreur récupération SAEs du professeur : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Finds SAE subjects by student ID.
     *
     * @param integer $studentId The student's user ID.
     * @return array<SAESubject>
     * @throws ExceptionFetchDataBD If the database query fails.
     */
    #[Override]
    public function findByStudentId(int $studentId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT s.* FROM sae_subjects s
                JOIN sae_groups sg ON s.sae_subject_id = sg.sae_subject_id
                JOIN participated_in pi ON sg.sae_group_id = pi.sae_group_id
                WHERE pi.student_id = :student_id
                ORDER BY s.begin_date DESC'
            );
            $stmt->execute(['student_id' => $studentId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn ($row) => new SAESubject($row), $data);
        } catch (PDOException $e) {
            error_log('Erreur récupération SAEs de l\'étudiant : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Finds SAE subjects by client ID.
     *
     * @param integer $clientId The client's user ID.
     * @return array<SAESubject>
     * @throws ExceptionFetchDataBD If the database query fails.
     */
    #[Override]
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

            return array_map(fn ($row) => new SAESubject($row), $data);
        } catch (PDOException $e) {
            error_log('Erreur récupération SAEs du client : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }


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
     * }|null
     * @throws PDOException If a database error occurs.
     */
    #[Override]
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
     * user_id: string,
     * first_name: string,
     * last_name: string,
     * email: string,
     * phone: string|null,
     * amu_id: string,
     * is_responsible: int
     * }>
     * @throws PDOException If a database error occurs.
     */
    #[Override]
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
                LEFT JOIN sae_groups sg ON s.sae_subject_id = sg.sae_subject_id
                JOIN professors p ON (p.professor_id = sg.professor_id OR p.professor_id = s.responsible_prof_id)
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
     * }|null
     * @throws PDOException If a database error occurs.
     */
    #[Override]
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

    /**
     * Gets SAEs by begin date.
     *
     * @param string $beginDate The begin date.
     * @return array<SAESubject>
     * @throws ExceptionFetchDataBD If the database query fails.
     */
    public function findByBeginDate(string $beginDate): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM sae_subjects 
                WHERE begin_date = :begin_date 
                ORDER BY begin_date DESC'
            );
            $stmt->execute(['begin_date' => $beginDate]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn ($row) => new SAESubject($row), $data);
        } catch (PDOException $e) {
            error_log('Erreur récupération SAEs par date de début : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Gets SAEs by end date.
     *
     * @param string $endDate The end date.
     * @return array<SAESubject>
     * @throws ExceptionFetchDataBD If the database query fails.
     */
    public function findByEndDate(string $endDate): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM sae_subjects 
                WHERE end_date = :end_date 
                ORDER BY end_date DESC'
            );
            $stmt->execute(['end_date' => $endDate]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn ($row) => new SAESubject($row), $data);
        } catch (PDOException $e) {
            error_log('Erreur récupération SAEs par date de fin : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }
}
