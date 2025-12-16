<?php

namespace Models\SAE;

use Core\includes\Database;
use Core\includes\exception\ExceptionBD\ExceptionFetchDataBD;
use PDO;
use PDOException;

/**
 * Repository class for SAE operations.
 * Handles all database interactions for SAE entities.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models\SAE
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SAERepository
{
    /**
     * Database connection instance.
     *
     * @var PDO
     */
    private PDO $connection;

    /**
     * Singleton instance of SAERepository.
     *
     * @var SAERepository|null
     */
    private static ?SAERepository $instance = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->connection = Database::getInstance();
    }

    /**
     * Gets the singleton instance of SAERepository.
     *
     * @return SAERepository
     */
    public static function getInstance(): SAERepository
    {
        if (self::$instance === null) {
            self::$instance = new SAERepository();
        }
        return self::$instance;
    }

    /**
     * Finds a SAE by its ID.
     *
     * @param integer $id The SAE subject ID.
     *
     * @return SAE|null The SAE object or null if not found.
     *
     * @throws ExceptionFetchDataBD If a database error occurs.
     */
    public function findById(int $id): ?SAE
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM sae_subjects WHERE sae_subject_id = :id'
            );
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }

            // Fetch associated competences.
            $competences = $this->findCompetencesBySaeId($id);
            $data['competences'] = $competences;

            return new SAE($data);
        } catch (PDOException $e) {
            error_log('Erreur récupération SAE : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Finds all SAEs.
     *
     * @return array<SAE> Array of SAE objects.
     *
     * @throws ExceptionFetchDataBD If a database error occurs.
     */
    public function findAll(): array
    {
        try {
            $stmt = $this->connection->query('SELECT * FROM sae_subjects ORDER BY begin_date DESC');

            if (!$stmt) {
                throw new PDOException('Impossible d\'exécuter la requête pour récupérer les SAEs.');
            }

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // For each SAE, fetch competences.
            foreach ($data as &$sae) {
                $sae['competences'] = $this->findCompetencesBySaeId($sae['sae_subject_id']);
            }

            return SAE::createSAEsFromArray($data);
        } catch (PDOException $e) {
            error_log('Erreur récupération SAEs : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Finds SAEs by professor ID (either responsible or assigned).
     *
     * @param integer $professorId The professor's user ID.
     *
     * @return array<SAE> Array of SAE objects.
     *
     * @throws ExceptionFetchDataBD If a database error occurs.
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
                ORDER BY s.begin_date DESC'
            );
            $stmt->execute(['prof_id' => $professorId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($data as &$sae) {
                $sae['competences'] = $this->findCompetencesBySaeId($sae['sae_subject_id']);
            }

            return SAE::createSAEsFromArray($data);
        } catch (PDOException $e) {
            error_log('Erreur récupération SAEs du professeur : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Finds SAEs by client ID.
     *
     * @param integer $clientId The client's user ID.
     *
     * @return array<SAE> Array of SAE objects.
     *
     * @throws ExceptionFetchDataBD If a database error occurs.
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

            foreach ($data as &$sae) {
                $sae['competences'] = $this->findCompetencesBySaeId($sae['sae_subject_id']);
            }

            return SAE::createSAEsFromArray($data);
        } catch (PDOException $e) {
            error_log('Erreur récupération SAEs du client : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Finds SAEs by student ID.
     *
     * @param integer $studentId The student's user ID.
     *
     * @return array<SAE> Array of SAE objects.
     *
     * @throws ExceptionFetchDataBD If a database error occurs.
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

            foreach ($data as &$sae) {
                $sae['competences'] = $this->findCompetencesBySaeId($sae['sae_subject_id']);
            }

            return SAE::createSAEsFromArray($data);
        } catch (PDOException $e) {
            error_log('Erreur récupération SAEs de l\'étudiant : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Finds active SAEs (currently in progress).
     *
     * @return array<SAE> Array of active SAE objects.
     *
     * @throws ExceptionFetchDataBD If a database error occurs.
     */
    public function findActive(): array
    {
        try {
            $stmt = $this->connection->query(
                'SELECT * FROM sae_subjects 
                WHERE CURRENT_DATE BETWEEN begin_date AND end_date
                ORDER BY begin_date DESC'
            );

            if (!$stmt) {
                throw new PDOException('Impossible d\'exécuter la requête pour récupérer les SAEs actives.');
            }

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($data as &$sae) {
                $sae['competences'] = $this->findCompetencesBySaeId($sae['sae_subject_id']);
            }

            return SAE::createSAEsFromArray($data);
        } catch (PDOException $e) {
            error_log('Erreur récupération SAEs actives : ' . $e->getMessage());
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Creates a new SAE in the database.
     *
     * @param SAE $sae The SAE object to create.
     *
     * @return SAE The created SAE with its ID set.
     *
     * @throws PDOException If a database error occurs.
     */
    public function create(SAE $sae): SAE
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
                'responsible_prof_id' => $sae->getResponsibleProfId(),
                'client_id' => $sae->getClientId(),
                'subject_name' => $sae->getSubjectName(),
                'begin_date' => $sae->getBeginDate(),
                'end_date' => $sae->getEndDate(),
                'file_path' => $sae->getFilePath()
            ]);

            $id = $stmt->fetchColumn();
            if ($id == false) {
                throw new PDOException('Failed to retrieve the inserted SAE ID.');
            }

            $id = intval($id);
            $sae->setSaeSubjectId($id);

            // Insert competences.
            if (!empty($sae->getCompetences())) {
                $this->saveCompetences($id, $sae->getCompetences());
            }

            $this->connection->commit();
            return $sae;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log('Erreur création SAE : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Updates an existing SAE in the database.
     *
     * @param SAE $sae The SAE object to update.
     *
     * @return boolean True if successful.
     *
     * @throws PDOException If a database error occurs.
     */
    public function update(SAE $sae): bool
    {
        try {
            $id = $sae->getSaeSubjectId();
            if ($id == null) {
                throw new PDOException('La SAE doit avoir un ID pour être mise à jour.'
                . 'Veuillez créer la SAE avant de la mettre à jour.');
            }

            $this->connection->beginTransaction();

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

            $result = $stmt->execute([
                'id' => $id,
                'responsible_prof_id' => $sae->getResponsibleProfId(),
                'client_id' => $sae->getClientId(),
                'subject_name' => $sae->getSubjectName(),
                'begin_date' => $sae->getBeginDate(),
                'end_date' => $sae->getEndDate(),
                'file_path' => $sae->getFilePath()
            ]);

            // Update competences.
            $this->deleteCompetences($id);
            if (!empty($sae->getCompetences())) {
                $this->saveCompetences($id, $sae->getCompetences());
            }

            $this->connection->commit();
            return $result;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log('Erreur mise à jour SAE : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Deletes a SAE from the database.
     *
     * @param integer $id The SAE subject ID.
     *
     * @return boolean True if successful.
     *
     * @throws PDOException If a database error occurs.
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'DELETE FROM sae_subjects WHERE sae_subject_id = :id'
            );
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log('Erreur suppression SAE : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Checks if a SAE exists by ID.
     *
     * @param integer $id The SAE subject ID.
     *
     * @return boolean True if exists, false otherwise.
     */
    public function exists(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(*) FROM sae_subjects WHERE sae_subject_id = :id'
            );
            $stmt->execute(['id' => $id]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur vérification existence SAE : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Finds competences associated with a SAE.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return array Array of competence names.
     */
    private function findCompetencesBySaeId(int $saeId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT competence_name FROM competences WHERE sae_subject_id = :sae_id'
            );
            $stmt->execute(['sae_id' => $saeId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log('Erreur récupération compétences : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Saves competences for a SAE.
     *
     * @param integer $saeId       The SAE subject ID.
     * @param array   $competences Array of competence names.
     *
     * @return void
     *
     * @throws PDOException If a database error occurs.
     */
    private function saveCompetences(int $saeId, array $competences): void
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO competences (competence_name, sae_subject_id) VALUES (:name, :sae_id)'
        );

        foreach ($competences as $competence) {
            $stmt->execute([
                'name' => $competence,
                'sae_id' => $saeId
            ]);
        }
    }

    /**
     * Deletes all competences for a SAE.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return void
     *
     * @throws PDOException If a database error occurs.
     */
    private function deleteCompetences(int $saeId): void
    {
        $stmt = $this->connection->prepare(
            'DELETE FROM competences WHERE sae_subject_id = :sae_id'
        );
        $stmt->execute(['sae_id' => $saeId]);
    }

    /**
     * Adds a professor to a SAE.
     *
     * @param integer $saeId       The SAE subject ID.
     * @param integer $professorId The professor's user ID.
     *
     * @return boolean True if successful.
     *
     * @throws PDOException If a database error occurs.
     */
    public function addProfessor(int $saeId, int $professorId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO sae_professor_groups (sae_subject_id, professor_id)
                VALUES (:sae_id, :prof_id)
                ON CONFLICT DO NOTHING'
            );
            return $stmt->execute([
                'sae_id' => $saeId,
                'prof_id' => $professorId
            ]);
        } catch (PDOException $e) {
            error_log('Erreur ajout professeur à SAE : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Removes a professor from a SAE.
     *
     * @param integer $saeId       The SAE subject ID.
     * @param integer $professorId The professor's user ID.
     *
     * @return boolean True if successful.
     *
     * @throws PDOException If a database error occurs.
     */
    public function removeProfessor(int $saeId, int $professorId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'DELETE FROM sae_professor_groups 
                WHERE sae_subject_id = :sae_id AND professor_id = :prof_id'
            );
            return $stmt->execute([
                'sae_id' => $saeId,
                'prof_id' => $professorId
            ]);
        } catch (PDOException $e) {
            error_log('Erreur retrait professeur de SAE : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gets all professors assigned to a SAE.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return array Array of professor IDs.
     */
    public function getProfessors(int $saeId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT professor_id FROM sae_professor_groups WHERE sae_subject_id = :sae_id'
            );
            $stmt->execute(['sae_id' => $saeId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log('Erreur récupération professeurs SAE : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets all groups for a SAE.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return array Array of group data.
     */
    public function getGroups(int $saeId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM sae_groups WHERE sae_subject_id = :sae_id'
            );
            $stmt->execute(['sae_id' => $saeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur récupération groupes SAE : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Creates a new group for a SAE.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return integer The created group ID.
     *
     * @throws PDOException If a database error occurs.
     */
    public function createGroup(int $saeId): int
    {
        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO sae_groups (sae_subject_id) VALUES (:sae_id) RETURNING sae_group_id'
            );
            $stmt->execute(['sae_id' => $saeId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Erreur création groupe SAE : ' . $e->getMessage());
            throw $e;
        }
    }

    // ========================================================================
    // STUDENT MANAGEMENT IN SAE GROUPS
    // ========================================================================

    /**
     * Gets all students in a specific SAE group.
     *
     * @param integer $groupId The SAE group ID.
     *
     * @return array Array of student data with user information.
     */
    public function getStudentsByGroupId(int $groupId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT 
                    s.student_id,
                    s.amu_id,
                    s.year,
                    s.major,
                    s.td,
                    s.tp,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone
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
     * Gets all students enrolled in a SAE (all groups).
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return array Array of student data grouped by group_id.
     */
    public function getAllStudentsBySaeId(int $saeId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT 
                    s.student_id,
                    s.amu_id,
                    s.year,
                    s.major,
                    s.td,
                    s.tp,
                    s.sae_group_id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone
                FROM students s
                JOIN users u ON s.student_id = u.user_id
                JOIN sae_groups sg ON s.sae_group_id = sg.sae_group_id
                WHERE sg.sae_subject_id = :sae_id
                ORDER BY s.sae_group_id, u.last_name, u.first_name'
            );
            $stmt->execute(['sae_id' => $saeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur récupération étudiants SAE : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Assigns a student to a SAE group.
     *
     * @param integer $studentId The student's user ID.
     * @param integer $groupId   The SAE group ID.
     *
     * @return boolean True if successful.
     *
     * @throws PDOException If a database error occurs.
     */
    public function assignStudentToGroup(int $studentId, int $groupId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'UPDATE students 
                SET sae_group_id = :group_id 
                WHERE student_id = :student_id'
            );
            return $stmt->execute([
                'group_id' => $groupId,
                'student_id' => $studentId
            ]);
        } catch (PDOException $e) {
            error_log('Erreur assignation étudiant : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Removes a student from their SAE group.
     *
     * @param integer $studentId The student's user ID.
     *
     * @return boolean True if successful.
     *
     * @throws PDOException If a database error occurs.
     */
    public function unassignStudentFromGroup(int $studentId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'UPDATE students 
                SET sae_group_id = NULL 
                WHERE student_id = :student_id'
            );
            return $stmt->execute(['student_id' => $studentId]);
        } catch (PDOException $e) {
            error_log('Erreur désassignation étudiant : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gets the count of students in each group for a SAE.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return array Array with group_id as key and student count as value.
     */
    public function getStudentCountsByGroup(int $saeId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT 
                    sg.sae_group_id,
                    COUNT(s.student_id) as student_count
                FROM sae_groups sg
                LEFT JOIN students s ON sg.sae_group_id = s.sae_group_id
                WHERE sg.sae_subject_id = :sae_id
                GROUP BY sg.sae_group_id
                ORDER BY sg.sae_group_id'
            );
            $stmt->execute(['sae_id' => $saeId]);

            $result = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[$row['sae_group_id']] = (int) $row['student_count'];
            }
            return $result;
        } catch (PDOException $e) {
            error_log('Erreur comptage étudiants par groupe : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets total number of students enrolled in a SAE.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return integer Total number of students.
     */
    public function getTotalStudentCount(int $saeId): int
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(DISTINCT s.student_id) 
                FROM students s
                JOIN sae_groups sg ON s.sae_group_id = sg.sae_group_id
                WHERE sg.sae_subject_id = :sae_id'
            );
            $stmt->execute(['sae_id' => $saeId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Erreur comptage total étudiants : ' . $e->getMessage());
            return 0;
        }
    }

    // ========================================================================
    // DETAILED INFORMATION ON THE PROFESSORS AND CLIENT
    // ========================================================================

    /**
     * Gets complete information about the responsible professor.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return array|null Professor information or null if not found.
     */
    public function getResponsibleProfessor(int $saeId): ?array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT 
                    u.user_id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
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
     * Gets complete information about all assigned professors.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return array Array of professor information.
     */
    public function getAllProfessorsInfo(int $saeId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT DISTINCT
                    u.user_id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur récupération infos professeurs : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets complete information about the client.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return array|null Client information or null if not found.
     */
    public function getClientInfo(int $saeId): ?array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT 
                    u.user_id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
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

    // ========================================================================
    // COMPLETE DATA (WITH ALL RELATIONS)
    // ========================================================================

    /**
     * Gets complete SAE data with all related information.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return array|null Complete SAE data or null if not found.
     */
    public function getCompleteData(int $saeId): ?array
    {
        $sae = $this->findById($saeId);
        if (!$sae) {
            return null;
        }

        return [
            'sae' => $sae->toArray(),
            'responsible_professor' => $this->getResponsibleProfessor($saeId),
            'all_professors' => $this->getAllProfessorsInfo($saeId),
            'client' => $this->getClientInfo($saeId),
            'groups' => $this->getGroupsWithDetails($saeId),
            'total_students' => $this->getTotalStudentCount($saeId),
            'statistics' => $this->getStatistics($saeId)
        ];
    }

    /**
     * Gets groups with detailed information including students.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return array Array of groups with student details.
     */
    public function getGroupsWithDetails(int $saeId): array
    {
        $groups = $this->getGroups($saeId);
        $result = [];

        foreach ($groups as $group) {
            $groupId = $group['sae_group_id'];
            $result[] = [
                'group_id' => $groupId,
                'students' => $this->getStudentsByGroupId($groupId),
                'student_count' => count($this->getStudentsByGroupId($groupId))
            ];
        }

        return $result;
    }

    // ========================================================================
    // STATISTICS
    // ========================================================================

    /**
     * Gets comprehensive statistics for a SAE.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return array Array with various statistics.
     */
    public function getStatistics(int $saeId): array
    {
        return [
            'total_groups' => count($this->getGroups($saeId)),
            'total_students' => $this->getTotalStudentCount($saeId),
            'total_professors' => count($this->getProfessors($saeId)) + 1, // +1 for responsible
            'students_per_group' => $this->getStudentCountsByGroup($saeId),
            'average_students_per_group' => $this->getAverageStudentsPerGroup($saeId),
            'groups_without_students' => $this->getEmptyGroupsCount($saeId),
            'competences_count' => count($this->findCompetencesBySaeId($saeId))
        ];
    }

    /**
     * Gets average number of students per group.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return float Average number of students.
     */
    public function getAverageStudentsPerGroup(int $saeId): float
    {
        $counts = $this->getStudentCountsByGroup($saeId);
        if (empty($counts)) {
            return 0.0;
        }
        return round(array_sum($counts) / count($counts), 2);
    }

    /**
     * Gets number of groups without any students.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return integer Number of empty groups.
     */
    public function getEmptyGroupsCount(int $saeId): int
    {
        $counts = $this->getStudentCountsByGroup($saeId);
        return count(array_filter($counts, fn($count) => $count === 0));
    }

    // ========================================================================
    // ADVANCED SEARCHING AND FILTERING
    // ========================================================================

    /**
     * Searches SAEs by various criteria.
     *
     * @param array $criteria Search criteria (name, year, status, etc.).
     *
     * @return array<SAE> Array of matching SAE objects.
     */
    public function search(array $criteria): array
    {
        try {
            $sql = 'SELECT * FROM sae_subjects WHERE 1=1';
            $params = [];

            if (isset($criteria['name'])) {
                $sql .= ' AND subject_name ILIKE :name';
                $params['name'] = '%' . $criteria['name'] . '%';
            }

            if (isset($criteria['year'])) {
                $sql .= ' AND EXTRACT(YEAR FROM begin_date) = :year';
                $params['year'] = $criteria['year'];
            }

            if (isset($criteria['status'])) {
                switch ($criteria['status']) {
                    case 'active':
                        $sql .= ' AND CURRENT_DATE BETWEEN begin_date AND end_date';
                        break;
                    case 'upcoming':
                        $sql .= ' AND begin_date > CURRENT_DATE';
                        break;
                    case 'ended':
                        $sql .= ' AND end_date < CURRENT_DATE';
                        break;
                }
            }

            if (isset($criteria['responsible_prof_id'])) {
                $sql .= ' AND responsible_prof_id = :prof_id';
                $params['prof_id'] = $criteria['responsible_prof_id'];
            }

            if (isset($criteria['client_id'])) {
                $sql .= ' AND client_id = :client_id';
                $params['client_id'] = $criteria['client_id'];
            }

            $sql .= ' ORDER BY begin_date DESC';

            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($data as &$sae) {
                $sae['competences'] = $this->findCompetencesBySaeId($sae['sae_subject_id']);
            }

            return SAE::createSAEsFromArray($data);
        } catch (PDOException $e) {
            error_log('Erreur recherche SAE : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets SAEs ending soon (within specified days).
     *
     * @param integer $days Number of days to look ahead.
     *
     * @return array<SAE> Array of SAE objects ending soon.
     */
    public function findEndingSoon(int $days = 7): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM sae_subjects 
                WHERE end_date BETWEEN CURRENT_DATE AND CURRENT_DATE + :days::interval
                ORDER BY end_date ASC'
            );
            $stmt->execute(['days' => "$days days"]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($data as &$sae) {
                $sae['competences'] = $this->findCompetencesBySaeId($sae['sae_subject_id']);
            }

            return SAE::createSAEsFromArray($data);
        } catch (PDOException $e) {
            error_log('Erreur recherche SAE se terminant bientôt : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets SAEs by year.
     *
     * @param integer $year The year to filter by.
     *
     * @return array<SAE> Array of SAE objects.
     */
    public function findByYear(int $year): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM sae_subjects 
                WHERE EXTRACT(YEAR FROM begin_date) = :year
                ORDER BY begin_date DESC'
            );
            $stmt->execute(['year' => $year]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($data as &$sae) {
                $sae['competences'] = $this->findCompetencesBySaeId($sae['sae_subject_id']);
            }

            return SAE::createSAEsFromArray($data);
        } catch (PDOException $e) {
            error_log('Erreur recherche SAE par année : ' . $e->getMessage());
            return [];
        }
    }

    // ========================================================================
    // ACCESS CONTROL AND PERMISSIONS
    // ========================================================================

    /**
     * Checks if a user has access to a SAE.
     *
     * @param integer $saeId  The SAE subject ID.
     * @param integer $userId The user's ID.
     * @param string  $role   The user's role (student, professor, client).
     *
     * @return boolean True if user has access, false otherwise.
     */
    public function userHasAccess(int $saeId, int $userId, string $role): bool
    {
        try {
            switch ($role) {
                case 'student':
                    $stmt = $this->connection->prepare(
                        'SELECT COUNT(*) FROM students s
                        JOIN sae_groups sg ON s.sae_group_id = sg.sae_group_id
                        WHERE sg.sae_subject_id = :sae_id AND s.student_id = :user_id'
                    );
                    break;

                case 'professor':
                    $stmt = $this->connection->prepare(
                        'SELECT COUNT(*) FROM sae_subjects s
                        WHERE s.sae_subject_id = :sae_id 
                        AND (s.responsible_prof_id = :user_id 
                            OR :user_id IN (
                                SELECT professor_id FROM sae_professor_groups 
                                WHERE sae_subject_id = :sae_id
                            )
                        )'
                    );
                    break;

                case 'client':
                    $stmt = $this->connection->prepare(
                        'SELECT COUNT(*) FROM sae_subjects
                        WHERE sae_subject_id = :sae_id AND client_id = :user_id'
                    );
                    break;

                default:
                    return false;
            }

            $stmt->execute(['sae_id' => $saeId, 'user_id' => $userId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur vérification accès : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if a professor is responsible for a SAE.
     *
     * @param integer $saeId       The SAE subject ID.
     * @param integer $professorId The professor's user ID.
     *
     * @return boolean True if responsible, false otherwise.
     */
    public function isResponsibleProfessor(int $saeId, int $professorId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(*) FROM sae_subjects 
                WHERE sae_subject_id = :sae_id AND responsible_prof_id = :prof_id'
            );
            $stmt->execute(['sae_id' => $saeId, 'prof_id' => $professorId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur vérification responsabilité : ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // COMPETENCE MANAGEMENT
    // ========================================================================

    /**
     * Updates competences for a SAE (replaces all existing).
     *
     * @param integer $saeId       The SAE subject ID.
     * @param array   $competences Array of competence names.
     *
     * @return boolean True if successful.
     *
     * @throws PDOException If a database error occurs.
     */
    public function updateCompetences(int $saeId, array $competences): bool
    {
        try {
            $this->connection->beginTransaction();

            $this->deleteCompetences($saeId);
            if (!empty($competences)) {
                $this->saveCompetences($saeId, $competences);
            }

            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log('Erreur mise à jour compétences : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Adds a single competence to a SAE.
     *
     * @param integer $saeId      The SAE subject ID.
     * @param string  $competence The competence name.
     *
     * @return boolean True if successful.
     *
     * @throws PDOException If a database error occurs.
     */
    public function addCompetence(int $saeId, string $competence): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO competences (competence_name, sae_subject_id) 
                VALUES (:name, :sae_id)
                ON CONFLICT DO NOTHING'
            );
            return $stmt->execute([
                'name' => $competence,
                'sae_id' => $saeId
            ]);
        } catch (PDOException $e) {
            error_log('Erreur ajout compétence : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Removes a specific competence from a SAE.
     *
     * @param integer $saeId      The SAE subject ID.
     * @param string  $competence The competence name.
     *
     * @return boolean True if successful.
     *
     * @throws PDOException If a database error occurs.
     */
    public function removeCompetence(int $saeId, string $competence): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'DELETE FROM competences 
                WHERE sae_subject_id = :sae_id AND competence_name = :name'
            );
            return $stmt->execute([
                'sae_id' => $saeId,
                'name' => $competence
            ]);
        } catch (PDOException $e) {
            error_log('Erreur suppression compétence : ' . $e->getMessage());
            throw $e;
        }
    }

    // ========================================================================
    // EXPORT ET RAPPORTS
    // ========================================================================

    /**
     * Exports SAE data with all students for reporting.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return array Complete export data.
     */
    public function exportData(int $saeId): array
    {
        $completeData = $this->getCompleteData($saeId);

        if (!$completeData) {
            return [];
        }

        // Add detailed student information for each group.
        foreach ($completeData['groups'] as &$group) {
            $students = $this->getStudentsByGroupId($group['group_id']);
            $group['students_details'] = $students;
        }

        return $completeData;
    }

    /**
     * Gets a summary report for multiple SAEs.
     *
     * @param array $saeIds Array of SAE subject IDs.
     *
     * @return array Summary report data.
     */
    public function getSummaryReport(array $saeIds): array
    {
        $report = [];

        foreach ($saeIds as $saeId) {
            $sae = $this->findById($saeId);
            if ($sae) {
                $report[] = [
                    'sae_id' => $saeId,
                    'name' => $sae->getSubjectName(),
                    'dates' => [
                        'begin' => $sae->getBeginDate(),
                        'end' => $sae->getEndDate()
                    ],
                    'status' => [
                        'is_active' => $sae->isActive(),
                        'has_ended' => $sae->hasEnded(),
                        'days_remaining' => $sae->getDaysRemaining()
                    ],
                    'statistics' => $this->getStatistics($saeId),
                    'competences' => $this->findCompetencesBySaeId($saeId)
                ];
            }
        }

        return $report;
    }

    /**
     * Gets students without a group for a SAE.
     *
     * @param integer $saeId The SAE subject ID.
     *
     * @return array Array of unassigned students (if any logic exists).
     */
    public function getUnassignedStudents(int $saeId): array
    {
        // Note: Cette méthode nécessiterait une logique supplémentaire
        // pour identifier quels étudiants devraient être dans cette SAE
        // mais ne sont pas encore assignés à un groupe.
        // Pour l'instant, retourne un tableau vide.
        return [];
    }
}
