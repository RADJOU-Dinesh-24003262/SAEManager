<?php

namespace Models\UseCase\User\InterfaceDB;

use Models\Entity\User\User;
use Core\Models\UseCase\InterfaceDB\RepositoryInterface;

/**
 * Interface for User repository operations.
 *
 * Defines the contract for user data access.
 * This is an Interface (Clean Architecture) - interface defined in Use Cases layer.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User/InterfaceDB
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 *
 * @extends RepositoryInterface<User>
 */
interface UserInterface extends RepositoryInterface
{
    /**
     * Finds a user by email.
     *
     * @param string $email The user's email.
     * @return User|null The user entity or null if not found.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Checks if a user exists by email.
     *
     * @param string $email The email to check.
     * @return boolean True if exists, false otherwise.
     */
    public function existsByEmail(string $email): bool;

    /**
     * Updates a user's password.
     *
     * @param integer $userId       The user ID.
     * @param string  $passwordHash The new hashed password.
     * @return boolean True on success, false on failure.
     */
    public function updatePassword(int $userId, string $passwordHash): bool;
}
