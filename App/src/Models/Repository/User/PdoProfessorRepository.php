<?php

namespace Models\Repository\User;

use Models\Entity\User\Professor;
use Models\UseCase\User\InterfaceDB\ProfessorInterface;
use Override;
use PDO;
use PDOException;

/**
 * PDO implementation of ProfessorInterface.
 *
 * This is the Infrastructure layer implementation of the Interface.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Repository/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 *
 * @extends <PdoUserRepository>
 */
class PdoProfessorRepository extends PdoUserRepository implements ProfessorInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->entityClass = Professor::class;
    }

    /**
     * Finds a professor by ID.
     *
     * @param integer $id The professor ID.
     * @return Professor|null The professor entity or null if not found.
     */
    #[Override]
    public function findById(int $id): ?Professor
    {
        $data = parent::findByIdUser($id);
        return new Professor($data);
    }

    /**
     * Finds a professor by email.
     *
     * @param string $email The professor's email.
     * @return Professor|null The professor entity or null if not found.
     */
    public function findByEmail(string $email): ?Professor
    {
        $data = parent::findByEmailUser($email);
            return new Professor($data);
    }

    #[Override]
    public function create($professor): Professor|bool
    {
        $userId = parent::createUser($professor);
        $this->connection->beginTransaction();

        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO professors (professor_id, amu_id) 
                 VALUES (:professor_id, :amu_id)'
            );
            $stmt->bindValue(':professor_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':amu_id', $professor->getAmuId(), PDO::PARAM_STR);

            $stmt->execute();
            $stmt->closeCursor();

            $this->connection->commit();

            return $this->findById($userId);
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log('Error creating professor: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Finds all professors.
     *
     * @return array<Professor> Array of professor entities.
     */
    public function findAll(): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT u.*, p.* 
                 FROM users u
                 JOIN professors p ON u.user_id = p.professor_id
                 ORDER BY u.last_name, u.first_name'
            );
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($data) => new Professor($data), $results);
        } catch (PDOException $e) {
            error_log("Error in findAll (Professor): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Checks if a professor is the responsible professor for a SAE.
     *
     * @param integer $professorId The professor ID.
     * @param integer $saeId       The SAE ID.
     * @return boolean True if responsible, false otherwise.
     */
    #[Override]
    public function isResponsibleProfessor(int $professorId, int $saeId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(*) FROM sae_subjects 
                 WHERE sae_subject_id = :sae_id AND responsible_prof_id = :prof_id'
            );
            $stmt->execute(['sae_id' => $saeId, 'prof_id' => $professorId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Error in isResponsibleProfessor: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if a professor can access a SAE.
     *
     * @param integer $professorId The professor ID.
     * @param integer $saeId       The SAE ID.
     * @return boolean True if accessible, false otherwise.
     */
    #[Override]
    public function canAccessSAE(int $professorId, int $saeId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(DISTINCT s.sae_subject_id) 
                 FROM sae_subjects s
                 LEFT JOIN sae_groups sg ON s.sae_subject_id = sg.sae_subject_id
                 WHERE s.sae_subject_id = :sae_id
                 AND (s.responsible_prof_id = :prof_id OR sg.professor_id = :prof_id OR s.client_id = :prof_id)'
            );
            $stmt->execute(['sae_id' => $saeId, 'prof_id' => $professorId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Error in canAccessSAE (Professor): ' . $e->getMessage());
            return false;
        }
    }
}
