<?php

namespace Models\SAE\Repository;

use Core\includes\Database;
use PDO;
use PDOException;

/**
 * Repository for SAEProfessorGroup operations.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/SAE
 * @author     SAE Manager Team
 * @license    MIT License https://opensource.org/licenses/MIT
 */
class SAEProfessorGroupRepository
{
    protected PDO $connection;
    protected static ?SAEProfessorGroupRepository $instance = null;

    private function __construct()
    {
        $this->connection = Database::getInstance();
    }

    public static function getInstance(): SAEProfessorGroupRepository
    {
        if (self::$instance === null) {
            self::$instance = new SAEProfessorGroupRepository();
        }
        return self::$instance;
    }

    /**
     * Assigns a professor to a SAE
     *
     * @param integer $saeId       The SAE subject ID
     * @param integer $professorId The professor ID
     * @return boolean
     */
    public function assignProfessor(int $saeId, int $professorId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO sae_professor_groups (sae_subject_id, professor_id)
                VALUES (:sae_id, :prof_id)
                ON CONFLICT DO NOTHING'
            );
            return $stmt->execute(['sae_id' => $saeId, 'prof_id' => $professorId]);
        } catch (PDOException $e) {
            error_log('Erreur assignation professeur : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Removes a professor from a SAE
     *
     * @param integer $saeId       The SAE subject ID
     * @param integer $professorId The professor ID
     * @return boolean
     */
    public function removeProfessor(int $saeId, int $professorId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'DELETE FROM sae_professor_groups 
                WHERE sae_subject_id = :sae_id AND professor_id = :prof_id'
            );
            return $stmt->execute(['sae_id' => $saeId, 'prof_id' => $professorId]);
        } catch (PDOException $e) {
            error_log('Erreur retrait professeur : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets all professors assigned to a SAE
     *
     * @param integer $saeId The SAE subject ID
     * @return array<int> Array of professor IDs
     */
    public function getAssignedProfessors(int $saeId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT professor_id FROM sae_professor_groups WHERE sae_subject_id = :sae_id'
            );
            $stmt->execute(['sae_id' => $saeId]);
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (PDOException $e) {
            error_log('Erreur récupération professeurs assignés : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets all SAEs assigned to a professor
     *
     * @param integer $professorId The professor ID
     * @return array<int> Array of SAE subject IDs
     */
    public function getProfessorSAEs(int $professorId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT sae_subject_id FROM sae_professor_groups WHERE professor_id = :prof_id'
            );
            $stmt->execute(['prof_id' => $professorId]);
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (PDOException $e) {
            error_log('Erreur récupération SAEs du professeur : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets group IDs assigned to a professor for a specific SAE
     *
     * @param integer $professorId The professor ID
     * @param integer $saeId       The SAE subject ID
     * @return array<int> Array of group IDs
     */
    public function getProfessorGroups(int $professorId, int $saeId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT DISTINCT sg.sae_group_id
                FROM sae_professor_groups spg
                JOIN sae_groups sg ON spg.sae_subject_id = sg.sae_subject_id
                WHERE spg.professor_id = :prof_id AND spg.sae_subject_id = :sae_id'
            );
            $stmt->execute(['prof_id' => $professorId, 'sae_id' => $saeId]);
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (PDOException $e) {
            error_log('Erreur récupération groupes du professeur : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Checks if a professor is assigned to a SAE
     *
     * @param integer $saeId       The SAE subject ID
     * @param integer $professorId The professor ID
     * @return boolean
     */
    public function isProfessorAssigned(int $saeId, int $professorId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(*) FROM sae_professor_groups 
                WHERE sae_subject_id = :sae_id AND professor_id = :prof_id'
            );
            $stmt->execute(['sae_id' => $saeId, 'prof_id' => $professorId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur vérification assignation : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Removes all professor assignments for a SAE
     *
     * @param integer $saeId The SAE subject ID
     * @return boolean
     */
    public function removeAllProfessors(int $saeId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'DELETE FROM sae_professor_groups WHERE sae_subject_id = :sae_id'
            );
            return $stmt->execute(['sae_id' => $saeId]);
        } catch (PDOException $e) {
            error_log('Erreur suppression assignations : ' . $e->getMessage());
            return false;
        }
    }
}
