<?php

namespace App\Domain\SAE\IRepository;

use App\Domain\SAE\SaeSubject;
use Core\Models\Repository\RepositoryInterface;

/**
 * Repository interface for SAE Subject persistence.
 * 
 * Extends RepositoryInterface with SAE-specific operations.
 *
 * @package App\Domain\SAE\IRepository
 * @extends RepositoryInterface<SaeSubject>
 */
interface ISaeRepository extends RepositoryInterface
{
    /**
     * Finds all SAE subjects.
     *
     * @return SaeSubject[] Array of all SAE subjects.
     * @throws \PDOException If database operation fails.
     */
    public function findAll(): array;

    /**
     * Saves a new SAE subject.
     *
     * @param SaeSubject $sae The SAE to save.
     * @return int The new SAE ID.
     * @throws \PDOException If database operation fails.
     */
    public function save($sae): int;

    /**
     * Finds SAE subjects by responsible professor ID.
     *
     * @param int $professorId The professor ID.
     * @return SaeSubject[] Array of SAE subjects.
     * @throws \PDOException If database operation fails.
     */
    public function findByProfessorId(int $professorId): array;

    /**
     * Finds SAE subjects by student ID.
     *
     * @param int $studentId The student ID.
     * @return SaeSubject[] Array of SAE subjects.
     * @throws \PDOException If database operation fails.
     */
    public function findByStudentId(int $studentId): array;

    /**
     * Finds SAE subjects by client ID.
     *
     * @param int $clientId The client ID.
     * @return SaeSubject[] Array of SAE subjects.
     * @throws \PDOException If database operation fails.
     */
    public function findByClientId(int $clientId): array;

    /**
     * Checks if a professor is the responsible for a SAE.
     *
     * @param int $saeId The SAE ID.
     * @param int $professorId The professor ID.
     * @return bool True if responsible, false otherwise.
     * @throws \PDOException If database operation fails.
     */
    public function isResponsibleProfessor(int $saeId, int $professorId): bool;

    /**
     * Gets the responsible professor information for a SAE.
     *
     * @param int $saeId The SAE ID.
     * @return array|null Professor data array or null.
     * @throws \PDOException If database operation fails.
     */
    public function getResponsibleProfessor(int $saeId): ?array;

    /**
     * Gets all professors information for a SAE.
     *
     * @param int $saeId The SAE ID.
     * @return array[] Array of professor data.
     * @throws \PDOException If database operation fails.
     */
    public function getAllProfessorsInfo(int $saeId): array;

    /**
     * Gets client information for a SAE.
     *
     * @param int $saeId The SAE ID.
     * @return array|null Client data array or null.
     * @throws \PDOException If database operation fails.
     */
    public function getClientInfo(int $saeId): ?array;
}