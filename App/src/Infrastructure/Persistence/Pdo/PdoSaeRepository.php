<?php

namespace App\Infrastructure\Persistence\Pdo;

use App\Domain\SAE\SaeSubject;
use App\Domain\SAE\IRepository\ISaeRepository;
use Core\Database\Database;
use PDO;
use PDOException;

/**
 * PDO implementation of the SAE repository.
 * 
 * Handles persistence for SaeSubject entities using PostgreSQL.
 *
 * @package App\Infrastructure\Persistence\Pdo
 */
class PdoSaeRepository implements ISaeRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getInstance();
    }

    /**
     * Finds a SAE subject by ID.
     *
     * @param int $id The SAE subject ID.
     * @return SaeSubject|null The SAE subject or null if not found.
     */
    public function findById(int $id): ?SaeSubject
    {
        try {
            $stmt = $this->connection->prepare('SELECT * FROM sae_subjects WHERE sae_subject_id = :id');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ? $this->mapRowToSae($row) : null;
        }
        catch (PDOException $e) {
            error_log('Error finding SAE by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Finds all SAE subjects ordered by begin date.
     *
     * @return array<SaeSubject> Array of SAE subjects.
     */
    public function findAll(): array
    {
        try {
            $stmt = $this->connection->query('SELECT * FROM sae_subjects ORDER BY begin_date DESC');
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map([$this, 'mapRowToSae'], $data);
        }
        catch (PDOException $e) {
            error_log('Error finding all SAEs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Finds all SAE subjects accessible by a professor.
     * 
     * Includes SAEs where the professor is:
     * - Responsible professor
     * - Assigned to a group
     * - Listed as client (edge case)
     *
     * @param int $professorId The professor's user ID.
     * @return array<SaeSubject> Array of SAE subjects.
     */
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
            return array_map([$this, 'mapRowToSae'], $data);
        }
        catch (PDOException $e) {
            error_log('Error finding SAEs by professor ID: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Finds all SAE subjects a student is enrolled in.
     *
     * @param int $studentId The student's user ID.
     * @return array<SaeSubject> Array of SAE subjects.
     */
    public function findByStudentId(int $studentId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT s.* 
                FROM sae_subjects s
                JOIN sae_groups sg ON s.sae_subject_id = sg.sae_subject_id
                JOIN participated_in pi ON sg.sae_group_id = pi.sae_group_id
                WHERE pi.student_id = :student_id
                ORDER BY s.begin_date DESC'
            );
            $stmt->execute(['student_id' => $studentId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map([$this, 'mapRowToSae'], $data);
        }
        catch (PDOException $e) {
            error_log('Error finding SAEs by student ID: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Finds all SAE subjects commissioned by a client.
     *
     * @param int $clientId The client's user ID.
     * @return array<SaeSubject> Array of SAE subjects.
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
            return array_map([$this, 'mapRowToSae'], $data);
        }
        catch (PDOException $e) {
            error_log('Error finding SAEs by client ID: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Saves a new SAE subject to the database.
     * 
     * Uses RETURNING clause to get the generated ID.
     *
     * @param SaeSubject $sae The SAE subject to save.
     * @return int The generated SAE subject ID (0 on failure).
     * @throws PDOException If database operation fails.
     */







    public function save($sae): int
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
                'responsible_prof_id' => $sae->getResponsibleProfessorId(),
                'client_id' => $sae->getClientId(),
                'subject_name' => $sae->getName(),
                'begin_date' => $sae->getBeginDate(),
                'end_date' => $sae->getEndDate(),
                'file_path' => $sae->getDescriptionFilePath()
            ]);

            $id = $stmt->fetchColumn();
            if ($id === false) {
                throw new PDOException("Failed to retrieve inserted ID");
            }
            $stmt->closeCursor();
            $sae->setId((int)$id);

            $this->connection->commit();
            return (int)$id;
        }
        catch (PDOException $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            error_log('Error saving SAE: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Updates an existing SAE subject.
     *
     * @param SaeSubject $sae The SAE subject with updated data.
     * @return bool True on success, false on failure.
     */
    public function update($sae): bool
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
                'id' => $sae->getId(),
                'responsible_prof_id' => $sae->getResponsibleProfessorId(),
                'client_id' => $sae->getClientId(),
                'subject_name' => $sae->getName(),
                'begin_date' => $sae->getBeginDate(),
                'end_date' => $sae->getEndDate(),
                'file_path' => $sae->getDescriptionFilePath()
            ]);
        }
        catch (PDOException $e) {
            error_log('Error updating SAE: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a SAE subject by ID.
     *
     * @param int $id The SAE subject ID.
     * @return bool True on success, false on failure.
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare('DELETE FROM sae_subjects WHERE sae_subject_id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0;
        }
        catch (PDOException $e) {
            error_log('Error deleting SAE: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if a professor is the responsible professor for a SAE.
     *
     * @param int $saeId The SAE subject ID.
     * @param int $professorId The professor's user ID.
     * @return bool True if professor is responsible, false otherwise.
     */
    public function isResponsibleProfessor(int $saeId, int $professorId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(*) FROM sae_subjects 
                WHERE sae_subject_id = :sae_id AND responsible_prof_id = :prof_id'
            );
            $stmt->execute(['sae_id' => $saeId, 'prof_id' => $professorId]);
            return (int)$stmt->fetchColumn() > 0;
        }
        catch (PDOException $e) {
            error_log('Error in isResponsibleProfessor: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets the responsible professor's information for a SAE.
     *
     * @param int $saeId The SAE subject ID.
     * @return array<string, mixed>|null Professor data or null if not found.
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
            return $result !== false ? $result : null;
        }
        catch (PDOException $e) {
            error_log('Error retrieving responsible professor: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gets information for all professors involved in a SAE.
     * 
     * Includes responsible professor and group-assigned professors,
     * ordered with responsible professor first.
     *
     * @param int $saeId The SAE subject ID.
     * @return array<int, array<string, mixed>> Array of professor data.
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
                LEFT JOIN sae_groups sg ON s.sae_subject_id = sg.sae_subject_id
                JOIN professors p ON (p.professor_id = sg.professor_id OR p.professor_id = s.responsible_prof_id)
                JOIN users u ON p.professor_id = u.user_id
                WHERE s.sae_subject_id = :sae_id
                ORDER BY is_responsible DESC, u.last_name, u.first_name'
            );
            $stmt->execute(['sae_id' => $saeId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data !== false ? $data : [];
        }
        catch (PDOException $e) {
            error_log('Error retrieving all professors info: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets the client's information for a SAE.
     *
     * @param int $saeId The SAE subject ID.
     * @return array<string, mixed>|null Client data or null if no client assigned.
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
            return $result !== false ? $result : null;
        }
        catch (PDOException $e) {
            error_log('Error retrieving client info: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Maps a database row to a SaeSubject entity.
     *
     * @param array<string, mixed> $row The database row.
     * @return SaeSubject The mapped SAE subject entity.
     */
    private function mapRowToSae(array $row): SaeSubject
    {
        return new SaeSubject(
            (int)$row['responsible_prof_id'],
            (string)$row['subject_name'],
            (string)$row['begin_date'],
            (string)$row['end_date'],
            $row['client_id'] ? (int)$row['client_id'] : null,
            (string)$row['file_path'],
            (int)$row['sae_subject_id']
            );
    }
}