<?php

namespace Models\UseCase\User\InterfaceDB;

/**
 * Interface RoleAccessInterface
 *
 * Defines the contract for role-specific access control operations
 * so they can be used polymorphically without instanceof checks.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models\UseCase\User\InterfaceDB
 *
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
interface RoleAccessInterface
{
    /**
     * Checks if a user has access to a specific SAE.
     *
     * @param integer $userId The specific user role ID.
     * @param integer $saeId  The SAE ID.
     * @return boolean True if accessible, false otherwise.
     */
    public function canAccessSAE(int $userId, int $saeId): bool;
}
