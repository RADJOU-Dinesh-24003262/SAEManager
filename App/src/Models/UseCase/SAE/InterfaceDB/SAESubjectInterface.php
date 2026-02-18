<?php

namespace Models\UseCase\SAE\InterfaceDB;

use Models\Entity\SAE\SAESubject;

/**
 * Interface for SAE Subject operations.
 *
 * Defines the contract for SAE subject data access.
 * This is an Interface (Clean Architecture) - interface defined in Use Cases layer.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/SAE/InterfaceDB
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
interface SAESubjectInterface
{
    /**
     * Finds all SAE subjects.
     *
     * @return array<SAESubject>
     */
    public function findAll(): array;

    /**
     * Finds a SAE subject by ID.
     *
     * @param integer $id The SAE subject ID.
     * @return SAESubject|null
     */
    public function findById(int $id): ?SAESubject;

    /**
     * Finds SAE subjects by professor ID.
     *
     * @param integer $professorId The professor's user ID.
     * @return array<SAESubject>
     */
    public function findByProfessorId(int $professorId): array;

    /**
     * Finds SAE subjects by student ID.
     *
     * @param integer $studentId The student's user ID.
     * @return array<SAESubject>
     */
    public function findByStudentId(int $studentId): array;

    /**
     * Finds SAE subjects by client ID.
     *
     * @param integer $clientId The client's user ID.
     * @return array<SAESubject>
     */
    public function findByClientId(int $clientId): array;

    /**
     * Inserts a new SAE subject into the database.
     *
     * @param object $entity The SAE subject entity to insert.
     * @return integer|boolean The ID of the inserted SAE subject, or false on failure.
     */
    public function insert(object $entity): int|bool;

    /**
     * Updates an existing SAE subject in the database.
     *
     * @param object $entity The SAE subject entity to update.
     * @return boolean True on success, false on failure.
     */
    public function update(object $entity): bool;

    /**
     * Deletes a SAE subject.
     *
     * @param integer $id The SAE subject ID.
     * @return boolean True on success.
     */
    public function delete(int $id): bool;

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
     */
    public function getResponsibleProfessor(int $saeId): ?array;

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
     */
    public function getAllProfessorsInfo(int $saeId): array;

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
     */
    public function getClientInfo(int $saeId): ?array;
}
