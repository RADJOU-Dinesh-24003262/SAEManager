<?php

namespace Models\UseCase\SAE\InterfaceDB;

use Core\Models\UseCase\InterfaceDB\RepositoryInterface;
use Models\Entity\SAE\SAEGroup;

/**
 * Interface for SAE Group operations.
 *
 * Defines the contract for SAE group data access.
 * This is an Interface (Clean Architecture) - interface defined in Use Cases layer.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/SAE/InterfaceDB
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 *
 * @extends RepositoryInterface<SAEGroup>
 */
interface SAEGroupInterface extends RepositoryInterface
{
    /**
     * Finds all groups for a SAE subject.
     *
     * @param integer $saeSubjectId The SAE subject ID.
     * @return array<SAEGroup>
     */
    public function findBySaeSubjectId(int $saeSubjectId): array;


    /**
     * Gets students in a group with their details.
     *
     * @param integer $groupId The SAE group ID.
     * @return array<int, array{
     *   student_id: string,
     *   amu_id: string,
     *   year: string,
     *   td: string,
     *   tp: string,
     *   first_name: string,
     *   last_name: string,
     *   email: string,
     *   phone: string|null
     * }>
     */
    public function getStudentsInGroup(int $groupId): array;
}
