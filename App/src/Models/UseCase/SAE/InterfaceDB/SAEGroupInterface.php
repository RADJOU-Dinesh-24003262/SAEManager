<?php

namespace Models\UseCase\SAE\InterfaceDB;

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
 */
interface SAEGroupInterface
{
    /**
     * Finds a SAE group by ID.
     *
     * @param integer $id The SAE group ID.
     * @return SAEGroup|null
     */
    public function findById(int $id): ?SAEGroup;

    /**
     * Finds all groups for a SAE subject.
     *
     * @param integer $saeSubjectId The SAE subject ID.
     * @return array<SAEGroup>
     */
    public function findBySaeSubjectId(int $saeSubjectId): array;

    /**
     * Creates a new SAE group.
     *
     * @param SAEGroup $entity The SAE group to create.
     * @return SAEGroup The created group with ID.
     */
    public function create(SAEGroup $entity): SAEGroup;

    /**
     * Updates a SAE group.
     *
     * @param SAEGroup $entity The SAE group to update.
     * @return boolean True on success.
     */
    public function update(SAEGroup $entity): bool;

    /**
     * Deletes a SAE group.
     *
     * @param integer $id The SAE group ID.
     * @return boolean True on success.
     */
    public function delete(int $id): bool;

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
