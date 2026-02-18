<?php

namespace Models\UseCase\User\InterfaceDB;

use Models\Entity\User\User;

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
 */
interface UserInterface
{
    /**
     * Finds a user by ID.
     *
     * @param integer $id The user ID.
     * @return User|null The user entity or null if not found.
     */
    public function findById(int $id): ?User;

    /**
     * Finds a user by email.
     *
     * @param string $email The user's email.
     * @return User|null The user entity or null if not found.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Inserts a user into the database.
     *
     * @param object $user The user entity.
     * @return integer|boolean The id of the created user or false on failure.
     */
    public function insert(object $user): int|bool;

    /**
     * Updates a user.
     *
     * @param object $user The user entity.
     * @return boolean True on success.
     */
    public function update(object $user): bool;

    /**
     * Deletes a user by ID.
     *
     * @param integer $id The user ID.
     * @return boolean True on success, false on failure.
     */
    public function delete(int $id): bool;

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
