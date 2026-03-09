<?php

namespace Models\UseCase\SAE\InterfaceDB;

use Core\Models\UseCase\InterfaceDB\RepositoryInterface;
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
 *
 * @extends RepositoryInterface<SAESubject>
 */
interface SAESubjectInterface extends RepositoryInterface
{
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

    /**
     * Finds SAE subjects by begin date.
     *
     * @param string $beginDate The begin date.
     * @return array<SAESubject>
     */
    public function findByBeginDate(string $beginDate): array;

    /**
     * Finds SAE subjects by end date.
     *
     * @param string $endDate The end date.
     * @return array<SAESubject>
     */
    public function findByEndDate(string $endDate): array;
}
