<?php

namespace Models\UseCase\User\InterfaceDB;

use Models\Entity\User\Professor;

/**
 * Interface for Professor repository operations.
 *
 * Defines the contract for professor-specific data access.
 * This is an Interface (Clean Architecture) - interface defined in Use Cases layer.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User/InterfaceDB
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 *
 * @extends UserInterface<Professor>
 */
interface ProfessorInterface extends UserInterface
{
    /**
     * Finds all professors.
     *
     * @return array<Professor> Array of professor entities.
     */
    public function findAll(): array;

    /**
     * Checks if a professor is the responsible professor for a SAE.
     *
     * @param integer $professorId The professor ID.
     * @param integer $saeId       The SAE ID.
     * @return boolean True if responsible, false otherwise.
     */
    public function isResponsibleProfessor(int $professorId, int $saeId): bool;

    /**
     * Checks if a professor can access a SAE.
     *
     * @param integer $professorId The professor ID.
     * @param integer $saeId       The SAE ID.
     * @return boolean True if accessible, false otherwise.
     */
    public function canAccessSAE(int $professorId, int $saeId): bool;
}
