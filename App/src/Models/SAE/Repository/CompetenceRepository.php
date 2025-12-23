<?php

namespace Models\SAE\Repository;

use Models\SAE\Competence;
use Core\includes\Database;
use PDO;
use PDOException;

/**
 * Repository for Competence operations.
 *
 * Handles all database interactions for competences.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models\SAE
 * @author     SAE Manager Team
 * @license    MIT License https://opensource.org/licenses/MIT
 */
class CompetenceRepository
{
    private PDO $connection;
    private static ?CompetenceRepository $instance = null;

    private function __construct()
    {
        $this->connection = Database::getInstance();
    }

    public static function getInstance(): CompetenceRepository
    {
        if (self::$instance === null) {
            self::$instance = new CompetenceRepository();
        }
        return self::$instance;
    }

    /**
     * Finds all competences for a SAE
     *
     * @param integer $saeId The SAE subject ID
     * @return array<Competence>
     */
    public function findBySaeId(int $saeId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM competences WHERE sae_subject_id = :sae_id ORDER BY competence_name'
            );
            $stmt->execute(['sae_id' => $saeId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($row) => new Competence($row), $data);
        } catch (PDOException $e) {
            error_log('Erreur récupération compétences : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Creates a new competence
     *
     * @param integer $saeId          The SAE subject ID
     * @param string  $competenceName The competence name
     * @return Competence The created competence
     * @throws PDOException
     */
    public function create(int $saeId, string $competenceName): Competence
    {
        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO competences (competence_name, sae_subject_id) 
                VALUES (:name, :sae_id)
                ON CONFLICT DO NOTHING'
            );
            $stmt->execute(['name' => $competenceName, 'sae_id' => $saeId]);

            return new Competence([
                'competence_name' => $competenceName,
                'sae_subject_id' => $saeId
            ]);
        } catch (PDOException $e) {
            error_log('Erreur création compétence : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Deletes a competence
     *
     * @param integer $saeId          The SAE subject ID
     * @param string  $competenceName The competence name
     * @return boolean
     */
    public function delete(int $saeId, string $competenceName): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'DELETE FROM competences 
                WHERE sae_subject_id = :sae_id AND competence_name = :name'
            );
            return $stmt->execute(['sae_id' => $saeId, 'name' => $competenceName]);
        } catch (PDOException $e) {
            error_log('Erreur suppression compétence : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes all competences for a SAE
     *
     * @param integer $saeId The SAE subject ID
     * @return boolean
     */
    public function deleteAllBySaeId(int $saeId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'DELETE FROM competences WHERE sae_subject_id = :sae_id'
            );
            return $stmt->execute(['sae_id' => $saeId]);
        } catch (PDOException $e) {
            error_log('Erreur suppression compétences : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates competences for a SAE (replaces all existing)
     *
     * @param integer       $saeId           The SAE subject ID
     * @param array<string> $competenceNames Array of competence names
     * @return boolean  If update was successful
     */
    public function updateSaeCompetences(int $saeId, array $competenceNames): bool
    {
        try {
            $this->connection->beginTransaction();

            // Delete existing competences
            $this->deleteAllBySaeId($saeId);

            // Insert new competences
            $stmt = $this->connection->prepare(
                'INSERT INTO competences (competence_name, sae_subject_id) VALUES (:name, :sae_id)'
            );

            foreach ($competenceNames as $name) {
                $stmt->execute(['name' => $name, 'sae_id' => $saeId]);
            }

            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log('Erreur mise à jour compétences : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if a competence exists
     *
     * @param integer $saeId          The SAE subject ID
     * @param string  $competenceName The competence name
     * @return boolean
     */
    public function exists(int $saeId, string $competenceName): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(*) FROM competences 
                WHERE sae_subject_id = :sae_id AND competence_name = :name'
            );
            $stmt->execute(['sae_id' => $saeId, 'name' => $competenceName]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur vérification compétence : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets count of competences for a SAE
     *
     * @param integer $saeId The SAE subject ID
     * @return integer
     */
    public function getCount(int $saeId): int
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(*) FROM competences WHERE sae_subject_id = :sae_id'
            );
            $stmt->execute(['sae_id' => $saeId]);
            return intval($stmt->fetchColumn());
        } catch (PDOException $e) {
            error_log('Erreur comptage compétences : ' . $e->getMessage());
            return 0;
        }
    }
}
