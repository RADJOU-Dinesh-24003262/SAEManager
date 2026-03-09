<?php

namespace Models\Repository\User;

use Core\Includes\Database;
use Models\Entity\User\Professor;
use Models\Entity\User\User;
use Models\UseCase\User\InterfaceDB\ProfessorInterface;
use Override;
use PDO;
use PDOException;

/**
 * PDO implementation of ProfessorInterface.
 *
 * [Architecture Strategy]
 * Type 2 Repository (Inherited/Polymorphic).
 * This repository DOES NOT extend BaseRepository because it handles logic that involves
 * joining with the parent `users` table instead of mapping perfectly to a single table.
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
 */
class PdoProfessorRepository implements ProfessorInterface
{
    /**
     * The User repository for base user operations.
     *
     * @var PdoUserRepository
     */
    private PdoUserRepository $userRepository;

    /**
     * The PDO connection instance
     * @var PDO
     */
    protected PDO $connection;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->userRepository = new PdoUserRepository();
        $this->connection = Database::getInstance();
    }

    /**
     * Finds a professor by ID.
     *
     * @param integer $id The professor ID.
     * @return Professor|null The professor entity or null if not found.
     */
    public function findById(int $id): ?Professor
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT u.*, p.* 
                 FROM users u
                 JOIN professors p ON u.user_id = p.professor_id
                 WHERE u.user_id = :id'
            );
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ? new Professor($data) : null;
        } catch (PDOException $e) {
            error_log("Error in findById (Professor): " . $e->getMessage());
            return null;
        }
    }



    /**
     * Inserts a new professor into the database.
     *
     * @param object $user The professor entity to create.
     * @return integer|boolean The id of the created user or false on failure.
     */
    public function insert(object $user): int|bool
    {
        if (!$user instanceof Professor) {
            return false;
        }

        $userId = $this->userRepository->insert($user);
        if (!$userId) {
            return false;
        }

        $this->connection->beginTransaction();

        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO professors (professor_id, amu_id)
                             VALUES (:professor_id, :amu_id)'
            );
            $stmt->bindValue(':professor_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':amu_id', $user->getAmuId(), PDO::PARAM_STR);

            $stmt->execute();
            $stmt->closeCursor();

            $this->connection->commit();

            return $userId;
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
    public function canAccessSAE(int $professorId, int $saeId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(DISTINCT s.sae_subject_id)
                             FROM sae_subjects s
                             LEFT JOIN sae_groups sg ON s.sae_subject_id = sg.sae_subject_id
                             WHERE s.sae_subject_id = :sae_id
                             AND (s.responsible_prof_id = :prof_id 
                                  OR sg.professor_id = :prof_id 
                                  OR s.client_id = :prof_id)'
            );
            $stmt->execute(['sae_id' => $saeId, 'prof_id' => $professorId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Error in canAccessSAE (Professor): ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Updates an existing professor.
     *
     * @param object $professor The professor entity to update.
     * @return boolean True on success, false on failure.
     */
    public function update(object $professor): bool
    {
        if (!$professor instanceof Professor) {
            return false;
        }

        if (!$this->userRepository->update($professor)) {
            return false;
        }

        try {
            $stmt = $this->connection->prepare(
                'UPDATE professors
                             SET amu_id = :amu_id
                             WHERE professor_id = :id'
            );

            return $stmt->execute([
                'amu_id' => $professor->getAmuId(),
                'id' => $professor->getUserId()
            ]);
        } catch (PDOException $e) {
            error_log('Error updating professor: ' . $e->getMessage());
            return false;
        }
    }



    /**
     * Deletes a professor from the database.
     *
     * @param integer $id The ID of the professor to delete.
     * @return boolean True on success, false on failure.
     */
    public function delete(int $id): bool
    {
        // Start transaction.
        $this->connection->beginTransaction();
        try {
            // Delete specific professor data first.
            $stmt = $this->connection->prepare("DELETE FROM professors WHERE professor_id = :id");
            $stmt->execute(['id' => $id]);

            // Then delete base user data.
            $this->userRepository->delete($id);

            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            return false;
        }
    }
}
