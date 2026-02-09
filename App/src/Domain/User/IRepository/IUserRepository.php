<?php

namespace App\Domain\User\IRepository;

use App\Domain\User\User;
use Core\Models\Repository\RepositoryInterface;
use App\Domain\User\Exception\EmailAlreadyExistsException;

/**
 * Repository interface for User persistence.
 * 
 * Extends the generic RepositoryInterface with User-specific operations.
 *
 * @package App\Domain\User\IRepository
 * @extends RepositoryInterface<User>
 */
interface IUserRepository extends RepositoryInterface
{
    // phpcs:disable Squiz.Commenting.FunctionComment.TypeHintMissing
    /**
     * Saves a new user to the database.
     *
     * @param User $user The user entity to save.
     * @return User The saved user entity with ID assigned.
     * @throws \PDOException If database operation fails.
     * @throws EmailAlreadyExistsException If email already exists.
     */
    public function save($user): User;
    // phpcs:enable Squiz.Commenting.FunctionComment.TypeHintMissing

    /**
     * Checks if a user exists with the given email.
     *
     * @param string $email The email to check.
     * @return bool True if exists, false otherwise.
     * @throws \PDOException If database operation fails.
     */
    public function existsByEmail(string $email): bool;

    /**
     * Finds a user by their email address.
     *
     * @param string $email The email address.
     * @return User|null The user if found, null otherwise.
     * @throws \PDOException If database operation fails.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Updates a user's password.
     *
     * @param User $user The user with the new password.
     * @return void
     * @throws \PDOException If database operation fails.
     */
    public function updatePassword(User $user): void;

    /**
     * Deletes a user by their email address.
     *
     * @param string $email The email of the user to delete.
     * @return void
     * @throws \PDOException If database operation fails.
     */
    public function deleteByEmail(string $email): void;

    /**
     * Gets all client users.
     *
     * @return User[] Array of client users.
     * @throws \PDOException If database operation fails.
     */
    public function getAllClients(): array;

    /**
     * Gets all professor users.
     *
     * @return User[] Array of professor users.
     * @throws \PDOException If database operation fails.
     */
    public function getAllProfessors(): array;
}